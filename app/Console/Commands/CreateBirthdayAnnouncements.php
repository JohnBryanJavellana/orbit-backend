<?php

namespace App\Console\Commands;

use App\Models\AuraPointsRecord;
use App\Models\User;
use App\Models\Announcement;
use App\Utils\GenerateTrace;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CreateBirthdayAnnouncements extends Command
{
    protected $signature = 'announcements:birthdays';
    protected $description = 'Create birthday announcements and rewards (runs every 5 mins safely)';

    public function handle(){
        $today = Carbon::today();
        $birthdayUsers = User::whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->get();

        if ($birthdayUsers->isEmpty()) {
            $this->info('No birthdays today.');
            return;
        }

        $admin = User::where('role', 'SUPERADMIN')->first();
        $creatorId = $admin ? $admin->id : 1;

        foreach ($birthdayUsers as $user) {
            $dailyKey = "BDAY_{$user->id}_" . $today->toDateString();
            $alreadyDone = Announcement::where('ctrl', 'LIKE', "%$dailyKey%")->exists();

            if (!$alreadyDone) {
                $s = $user->suffix ?? '';
                $fullName = trim("{$user->first_name} {$user->middle_name} {$user->last_name} {$s}");

                Announcement::create([
                    'ctrl' => GenerateTrace::createTraceNumber(Announcement::class, 'A-', 'ctrl') . "|$dailyKey",
                    'creator_id' => $creatorId,
                    'content' => $this->getBirthdayTemplate($fullName),
                    'status' => 'SHOW',
                    'removal_date' => $today->copy()->endOfDay(),
                ]);

                $reward = new AuraPointsRecord();
                $reward->point_receiver = $user->id;
                $reward->point = 300;
                $reward->reason = "Birthday Aura Points Reward";
                $reward->status = "INCREASE";
                $reward->save();

                $user->increment('total_points', 300);
                $this->info("Rewarded {$fullName} for their birthday!");
            }
        }
    }

    private function getBirthdayTemplate($name){
        return "
            <p><strong>Hear ye, Hear ye! </strong></p>
            <p>The High Council recognizes the glorious day of <strong>{$name}</strong>!</p>
            <p>The High Council has decreed a change to all upcoming tasks. To ensure the swiftness of our realm, a mystical Time Limit shall soon be enchanted upon every quest. Be warned: as the sands of the hourglass fall, so too shall your potential rewards. Should the timer strike zero before your task is complete, you shall be bound by duty to finish your work—but alas, no gold or glory (points) shall be granted for your tardiness. Sharpen your tools and act with haste!</p>
        ";
    }
}

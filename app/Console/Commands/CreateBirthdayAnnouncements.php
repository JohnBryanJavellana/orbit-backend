<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Announcement;
use App\Utils\GenerateTrace;
use App\Utils\NewAuraRecord;
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
                    'content' => $this->getBirthdayTemplate($fullName, $user->role === "MEMBER"),
                    'status' => 'SHOW',
                    'removal_date' => $today->copy()->endOfDay(),
                ]);

                if($user->role === "MEMBER") {
                    NewAuraRecord::createRecord(
                        $user->id,
                        300,
                        'INCREASE',
                        'Birthday Aura Points Reward'
                    );

                    $user->increment('total_points', 300);
                }

                $this->info("Rewarded {$fullName} for their birthday!");
            }
        }
    }

    private function getBirthdayTemplate($name, $isMember){
        $addText = $isMember ? '<strong>CURRENT STATUS:</strong> <strong>BIRTHDAY BUFF ACTIVE</strong> (+300 Aura Points)' : '';

        return "
            <p>Hear ye, <strong>Orbit Interns</strong>!</p>
            <p><strong>HERO:</strong> <strong>{$name}</strong></p>
            <p><strong>CLASS:</strong> Novice</p>
            <p>{$addText}</p>
            <p>A wish for you on your birthday, whatever you ask may you receive, whatever you seek may you find, whatever you wish may it be fulfilled on your birthday and always. Happy birthday!</p>
        ";
    }
}

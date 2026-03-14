<?php

namespace App\Console\Commands;

use App\Models\DailyActivitiesReward;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupExpiredDailyActivitiyRows extends Command
{
    protected $signature = 'app:cleanup-expired-daily-activitiy-rows';
    protected $description = 'Remove daily activities row where the created_at date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $deletedCount = DailyActivitiesReward::where('created_at', '<', $now)->delete();
        $this->info("Successfully deleted {$deletedCount} expired daily activities row.");
    }
}

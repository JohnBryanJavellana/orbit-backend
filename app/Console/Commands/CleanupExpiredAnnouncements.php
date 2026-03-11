<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanupExpiredAnnouncements extends Command
{
    protected $signature = 'announcements:cleanup';
    protected $description = 'Remove announcements where the removal_date has passed';

    public function handle()
    {
        $now = Carbon::now();
        $deletedCount = Announcement::whereNotNull('removal_date')
            ->where('removal_date', '<=', $now)
            ->delete();

        $this->info("Successfully deleted {$deletedCount} expired announcements.");
    }
}

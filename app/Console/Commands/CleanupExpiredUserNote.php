<?php

namespace App\Console\Commands;

use App\Models\UserNote;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanupExpiredUserNote extends Command
{
    protected $signature = 'announcements:user-note';
    protected $description = 'Remove user note where the created_at has passed 24 hours';

    public function handle()
    {
        $expirationThreshold = Carbon::now()->subHours(24);
        $deletedCount = UserNote::where('created_at', '<=', $expirationThreshold)->delete();

        $this->info("Successfully deleted {$deletedCount} expired user note.");
    }
}

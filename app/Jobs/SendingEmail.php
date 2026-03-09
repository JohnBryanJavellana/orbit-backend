<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Mail\Mailable;
use App\Models\User;

class SendingEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tries = 3;
    protected $timeout = 3600;

    public function __construct(
        protected User $user,
        protected Mailable $mailable
    ) {}

    public function handle() {
        try {
            \Log::info("mail job handle", [$this->user]);

            \Mail::to($this->user->email)->send($this->mailable);
        } catch (\Throwable $th) {
            \Log::error("error mail job handle", [$th]);
        }
    }
}

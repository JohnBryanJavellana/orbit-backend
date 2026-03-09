<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Utils\ConvertToBase64;

class SaveAvatar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $avatar,
        public string $filename,
        public string $path,
        public bool $isUrl = false,
        public bool $isBase64 = false,
        public string $deletableFile = ""
    ) {}

    public function handle() {
        if ($this->avatar) {
            if($this->deletableFile) {
                if(file_exists(public_path("$this->path/$this->filename"))) {
                    unlink(public_path("$this->path/$this->filename"));
                }
            }

            if($this->isUrl) {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ])->get($this->avatar);

                if ($response->successful()) {
                    $contentType = $response->header('Content-Type');
                    if (str_contains($contentType, 'image')) {
                        file_put_contents(public_path($this->path . $this->filename), $response->body());
                    } else {
                        \Log::error("URL did not return an image. Content-Type: " . $contentType);
                    }
                }
            }

            if($this->isBase64) {
                return ConvertToBase64::generate($this->avatar, 'image', "$this->path/$this->filename");
            }
        }
    }
}

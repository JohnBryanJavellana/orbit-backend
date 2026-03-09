<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutoPrint implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $pdfPath) {}

    public function handle() {
        if ($this->pdfPath) {
            try {
                $pdfBase64 = base64_encode(file_get_contents($this->pdfPath));
                Http::withBasicAuth(config('printing.drivers.printnode.key'), '')
                    ->timeout(120)
                    ->post('https://api.printnode.com/printjobs', [
                        'printerId'   => 75177532,
                        'title'       => 'Laravel Print Job',
                        'contentType' => 'pdf_base64',
                        'content'     => $pdfBase64,
                        'source'      => 'Laravel Backend'
                    ]);
            } catch (\Exception $e) {
                Log::error("Manual PrintNode Timeout/Error: " . $e->getMessage());
            }
        }
    }
}

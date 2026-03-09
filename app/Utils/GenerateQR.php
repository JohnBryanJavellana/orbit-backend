<?php
namespace App\Utils;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQR {
    protected $manager;

    public function __construct() {
        $this->manager = new ImageManager(new Driver());
    }

    public function generate($filename, $content, $qrText = null, $path) {
        $qr_path = public_path($path . $filename);

        QrCode::format('png')
            ->size(500)
            ->style('round')
            ->margin(1)
            ->backgroundColor(255, 255, 255)
            ->merge(public_path('system-images/62334fcadd0d9e6d0a152aca.png'), 0.19, true)
            ->generate($content, $qr_path);

        $img = $this->manager->read($qr_path);

        $img->resizeCanvas(500, 560, 'ffffff', false);

        if(!is_null($qrText)) {
            // Ensure the path is absolute and real
            $fontPath = public_path('fonts/Roboto/Roboto.ttf');

            if (!file_exists($fontPath)) {
                throw new \Exception("Font file not found at: " . $fontPath);
            }

            $img->text((string)$qrText, 250, 540, function($font) use ($fontPath) {
                $font->file($fontPath);
                $font->size(25);
                $font->color('#000000');
                $font->align('center');
                $font->valign('bottom');
            });
        }

        return $img->save($qr_path);
    }
}

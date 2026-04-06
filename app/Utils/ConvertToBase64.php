<?php

namespace App\Utils;

class ConvertToBase64 {
    public static function generate ($base64String, $type, $filename) {
        if (str_contains($base64String, ',')) {
            $base64String = explode(',', $base64String)[1];
        }

        $decodedString = base64_decode($base64String, true);

        if ($decodedString === false) {
            throw new \Exception('Base64 decoding failed');
        }

        $filePath = public_path($filename);

        if (file_put_contents($filePath, $decodedString) === false) {
            throw new \Exception('Failed to save file');
        }

        return $filePath;
    }
}

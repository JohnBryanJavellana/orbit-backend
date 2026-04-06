<?php

namespace App\Utils;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class SaveFile {
    /**
     * @param UploadedFile $file The raw file from the request
     * @param string $path The folder name (e.g., 'announcement_attachments')
     * @param string $filename The UUID generated in the controller
     */
    public static function save($file, $path, $filename) {
        $destinationPath = public_path($path);

        // Ensure directory exists
        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true, true);
        }

        // Move the file from temp storage to your public folder
        return $file->move($destinationPath, $filename);
    }
}

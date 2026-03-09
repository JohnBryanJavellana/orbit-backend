<?php

namespace App\Utils;

class GenerateUniqueFilename
{
    public static function generate($file)
    {
        return time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
    }
}
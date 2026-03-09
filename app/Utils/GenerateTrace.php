<?php

namespace App\Utils;
use Illuminate\Support\Str;
use Throwable;

class GenerateTrace
{
    public static function createTraceNumber($model, $prefix = "", $column = 'trace_number', $minRand = 100000, $maxRand = 999999) {
        try {
            $unique = now()->format('His') . rand($minRand, $maxRand);
            $trace = $prefix . $unique;

            if ($model::where($column, $trace)->exists()) return GenerateTrace::createTraceNumber($model, $prefix, $column, $minRand, $maxRand);
            return $trace;
        } catch (Throwable $e){
            \Log::error("Trace number generation failed: " . $e->getMessage());
            return 'NMP-FALLBACK-' . Str::random(6);
        }
    }
}

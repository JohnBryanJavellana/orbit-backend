<?php

namespace App\Utils;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransactionUtil {
    public static function transact($validations = null, $refreshCacheKey = [], callable $body) {
        if($validations) $validations->validated();

        try {
            return DB::transaction(function() use ($body, $refreshCacheKey) {
                foreach($refreshCacheKey as $key) {
                    if(Cache::has($key)) {
                        Cache::forget($key);
                    }
                }

                return $body();
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Transaction failed: ' . $e->getMessage()], 500);
        }
    }
}

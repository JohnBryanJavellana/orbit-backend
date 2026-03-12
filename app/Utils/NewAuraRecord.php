<?php

namespace App\Utils;

use App\Models\AuraPointsRecord;

class NewAuraRecord
{
    public static function createRecord(int $pointReceiver, int $point, string $modification = "INCREASE" | "DECREASE", string $reason){
        $new_record = new AuraPointsRecord();
        $new_record->points_receiver = $pointReceiver;
        $new_record->point = $point;
        $new_record->reason = $reason;
        $new_record->status = $modification;
        $new_record->save();
    }
}

<?php

namespace App\Utils;

use App\Models\AuditTrail;

class AuditHelper
{
    public static function log($user_id, $action)
    {
        try {
            $log = new AuditTrail();
            $log->user_id = $user_id;
            $log->actions = $action;
            $log->save();
            
        }
        catch (\Exception $e) {
        }
       
    }
}

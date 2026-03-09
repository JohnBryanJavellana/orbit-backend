<?php

namespace App\Utils;

use App\Events\BENotification;
use App\Models\Notification;

class Notifications
{
    public static function notify($userId, $to_user, $type = "DORMITORY" | "ENROLLMENT" | "LIBRARY" | "RECREATIONAL", $message){
        return TransactionUtil::transact(null, [], function() use ($userId, $to_user, $type, $message) {
            $notif = new Notification();
            $notif->user_id = $userId;
            $notif->to_user = $to_user;
            $notif->type = $type;
            $notif->message = $message;
            $notif->save();

            if(env('USE_EVENT')) {
                event(
                    new BENotification(''),
                );
            }

            return response()->json(['message' => ""], 201);
        });
    }
}

<?php

namespace App\Utils;

use App\Events\BENotification;
use App\Models\Notification;

class Notifications
{
    public static function notify($userId, $to_user, $message){
        return TransactionUtil::transact(null, [], function() use ($userId, $to_user, $message) {
            $notif = new Notification();
            $notif->user_id = $userId;
            $notif->to_user = $to_user;
            $notif->message = $message;
            $notif->save();

            return response()->json(['message' => ""], 201);
        });
    }
}

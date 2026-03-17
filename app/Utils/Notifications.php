<?php

namespace App\Utils;

use App\Models\Notification;

class Notifications
{
    public static function notify($userId, $to_user, $message){
        $notif = new Notification();
        $notif->from_user = $userId;
        $notif->to_user = $to_user;
        $notif->message = $message;
        $notif->is_read = false;
        $notif->save();

        return response()->json(['message' => ""], 201);
    }
}

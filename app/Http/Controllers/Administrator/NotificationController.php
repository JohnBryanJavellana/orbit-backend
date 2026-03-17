<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\Notification\SetAsRed;
use App\Models\Notification;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Summary of get_notifications
     * @param Request $request
     */
    public function get_notifications(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $notificationsTemp0 = Notification::with('sender');
            $user = $request->user();

            $notifications1 = \in_array($user->role, ['SUPERADMIN', 'ADMINISTRATOR'])
                ? $notificationsTemp0->where(function($q) use ($user) {
                    $q->whereNull('to_user')->orWhere('to_user', $user->id);
                }) : $notificationsTemp0->where('to_user', $user->id);

            $notifications = $notifications1->orderBy('created_at', 'DESC')->get();
            $this->set_as_read($request, $user);

            return response()->json(['notifications' => $notifications], 200);
        });
    }

    /**
     * Summary of get_unread_notifications
     * @param Request $request
     */
    public function check_ishas_unread_notifications(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $notificationsTemp0 = Notification::query();
            $user = $request->user();

            $notifications1 = \in_array($user->role, ['SUPERADMIN', 'ADMINISTRATOR'])
                ? $notificationsTemp0->where(function($q) use ($user) {
                    $q->whereNull('to_user')->orWhere('to_user', $user->id);
                }) : $notificationsTemp0->where('to_user', $user->id);

            $notifications = $notifications1->where('is_read', false)->exists();

            return response()->json(['notifications' => $notifications], 200);
        });
    }

    /**
     * Summary of set_as_red
     */
    public function set_as_read($request, $user) {
        return TransactionUtil::transact(null, [], function () use ($user) {
            $query = Notification::where('is_read', false);

            if (\in_array($user->role, ['SUPERADMIN', 'ADMINISTRATOR'])) {
                $query->where(function($q) use ($user) {
                    $q->whereNull('to_user')->orWhere('to_user', $user->id);
                });
            } else {
                $query->where('to_user', $user->id);
            }

            $query->update(['is_read' => true]);
            return response()->json(['message' => 'OK'], 200);
        });
    }
}

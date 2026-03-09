<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function logout_user(Request $request) {
        try {
            $user = $request->user();

            if ($user) {
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }

                $user->last_seen_at = now();
                $user->save();

                return response()->json(['message' => 'Successfully logged out'], 200);
            }

            return response()->json(['message' => 'No active session found'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}

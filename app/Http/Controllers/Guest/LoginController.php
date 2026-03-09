<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Utils\TransactionUtil;

class LoginController extends Controller
{
    public function login_user(LoginRequest $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            if(Auth::attempt($request->only('email', 'password'))){
                $user = Auth::user();

                if ($user->email_verified_at === null) {
                    Auth::logout();
                    return response()->json(['message' => 'Your email address is not yet verified. Please check your inbox.'], 403);
                }

                $user->last_seen_at = now();
                $user->save();

                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json(['token' => $token, 'role' => $user->role], 200);
            } else {
                return response()->json(['message' => "Invalid username or password. Please try again"], 422);
            }
        });
    }
}

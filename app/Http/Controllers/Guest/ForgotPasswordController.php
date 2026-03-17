<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\ResetPassword;
use App\Http\Requests\Guest\SubmitEmail;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    /**
     * Summary of forgotPassword
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(SubmitEmail $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $status = Password::sendResetLink($request->only('email'));
            return $status === Password::RESET_LINK_SENT
                ? response()->json(['status' => 'success', 'message' => __($status)], 200)
                : response()->json(['status' => 'error', 'message' => __($status)], 400);
        });
    }

    /**
     * Summary of resetPassword
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPassword $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) use ($request) {
                    $user->forceFill([ 'password' => bcrypt($password) ])->setRememberToken(Str::random(60));
                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            return $status == Password::PASSWORD_RESET
                ? response()->json(['status' => 'success', 'message' => __($status)], 200)
                : response()->json(['status' => 'error', 'message' => __($status)], 400);
        });
    }
}

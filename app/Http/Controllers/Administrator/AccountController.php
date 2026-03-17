<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\Account\UpdatePassword;
use App\Models\User;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    /**
     * Summary of update_password
     * @param UpdatePassword $request
     */
    public function update_password(UpdatePassword $request){
        return TransactionUtil::transact($request, [], function() use ($request) {
            $currentPassword = $request->current_password;
            $password = $request->password;

            $user = User::where('id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!Hash::check($currentPassword, $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect',
                    'reloggin' => false
                ], 409);
            }

            $user->password = bcrypt($password);
            $user->save();

            return response()->json([
                'message' => "Success! Your account password has been updated.",
                'reloggin' => true
            ], 200);
        });
    }
}

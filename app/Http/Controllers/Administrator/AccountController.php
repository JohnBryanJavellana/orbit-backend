<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\Account\CreateNote;
use App\Http\Requests\Administrator\Account\UpdatePassword;
use App\Models\User;
use App\Models\UserNote;
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
                'message' => "Success! Your account password has been updated. You will be logged out automatically, please log in again with your new credentials.",
                'reloggin' => true
            ], 200);
        });
    }

    /**
     * Summary of create_note
     * @param CreateNote $request
     */
    public function create_note (CreateNote $request){
        return TransactionUtil::transact($request, [], function() use ($request) {
            \Log::info($request->all());

            $note = $request->note;
            $music = $request->music;
            $userId = $request->user()->id;

            $checkIfExisting = UserNote::where('user_id', $userId);
            $this_note = $checkIfExisting->exists()
                ? $checkIfExisting->lockForUpdate()->first()
                : new UserNote();

            $this_note->user_id = $userId;
            $this_note->note = $note;
            $this_note->note_audio = $music;
            $this_note->created_at = now();
            $this_note->save();

            return response()->json(['message' => "Success! Note has been saved"], 200);
        });
    }
}

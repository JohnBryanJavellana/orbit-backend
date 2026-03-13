<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\CustomAvatar\CreateOrUpdateCustomAvatar;
use App\Http\Requests\Administrator\CustomAvatar\SetAsMyCustomAvatar;
use App\Jobs\SaveAvatar;
use App\Models\CustomAvatar;
use App\Models\UserCustomAvatar;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Str;

class CustomAvatarController extends Controller
{
    /**
     * Summary of get_custom_borders
     * @param Request $request
     */
    public function get_custom_avatars(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $avatars = CustomAvatar::withCount([
                'usersConnection'
            ])->get();

            return response()->json(['avatars' => $avatars], 200);
        });
    }

    /**
     * Summary of create_or_update_custom_avatar
     * @param CreateOrUpdateCustomAvatar $request
     */
    public function create_or_update_custom_avatar(CreateOrUpdateCustomAvatar $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $isPost = $request->httpMethod === "POST";
            $avatar = $request->avatar;
            $documentId = $request->documentId;

            $this_avatar = $isPost ? new CustomAvatar() : CustomAvatar::where([
                'id' => $documentId
            ])->lockForUpdate()->firstOrFail();

            if($avatar) {
                $filename = Str::uuid() . '.png';
                SaveAvatar::dispatch($avatar, $filename, 'custom-avatar-images', false, true, !$isPost && $avatar ? $this_avatar->filename : '');
                $this_avatar->filename = $filename;
            }

            $this_avatar->save();
            return response()->json(['message' => "Successs action!"], 200);
        });
    }

    /**
     * Summary of get_available_custom_avatars
     * @param Request $request
     */
    public function get_available_custom_avatars(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $userCustomAvatarId = $request->userCustomAvatarId;
            $main_profile = UserCustomAvatar::with('customAvatar')->findOrFail($userCustomAvatarId);
            $available_avatars = CustomAvatar::all();

            return response()->json([
                'main_profile' => $main_profile,
                'avatars' => $available_avatars
            ], 200);
        });
    }

    /**
     * Summary of set_as_my_custom_avatar
     * @param Request $request
     */
    public function set_as_my_custom_avatar(SetAsMyCustomAvatar $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $using = $request->using;
            $userCustomAvatarId = $request->userCustomAvatarId;
            $avatarId = $request->avatarId;
            $newMainAvatar = $request->newMainAvatar;

            $userCustomAvatar = UserCustomAvatar::findOrFail($userCustomAvatarId);
            $userCustomAvatar->custom_avatar_id = null;

            if($using === "MAIN" && $newMainAvatar) {
                $filename = Str::uuid() . '.png';
                SaveAvatar::dispatch($newMainAvatar, $filename, 'user-images', false, true, $newMainAvatar ? $userCustomAvatar->profile_picture : '');
                $userCustomAvatar->profile_picture = $filename;
            }

            if($using === "CUSTOM") {
                $userCustomAvatar->custom_avatar_id = $avatarId;
            }

            $userCustomAvatar->shown_avatar = $using;
            $userCustomAvatar->save();

            return response()->json(['message' => "Successs action!"], 200);
        });
    }
}

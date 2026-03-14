<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\Border\AddUserRareInvBorder;
use App\Http\Requests\Administrator\Border\CreateOrUpdateBorder;
use App\Http\Requests\Administrator\Border\GetUserNewRareBorder;
use App\Http\Requests\Administrator\Border\GetUserRareBorder;
use App\Http\Requests\Administrator\Border\RemoveUserRareBorder;
use App\Jobs\SaveAvatar;
use App\Models\CustomBorder;
use App\Models\User;
use App\Models\UserBorderInv;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Str;

class BorderController extends Controller
{
    /**
     * Summary of get_custom_borders
     * @param Request $request
     */
    public function get_custom_borders(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $borders = CustomBorder::all();
            return response()->json(['borders' => $borders], 200);
        });
    }

    /**
     * Summary of remove_custom_border
     * @param Request $request
     */
    public function remove_custom_border(Request $request, int $borderId) {
        return TransactionUtil::transact(null, [], function () use ($request, $borderId) {
            $this_border = CustomBorder::findOrFail($borderId);

            if($this_border->total_active_users > 0) {
                return response()->json(['message' => "Some players or maybe you are using this border or maybe it's in their inventory."], 409);
            }

            if(file_exists(public_path("border-images/$this_border->filename"))) {
                unlink(public_path("border-images/$this_border->filename"));
            }

            $this_border->delete();
            return response()->json(['message' => "Successs action!"], 200);
        });
    }

    /**
     * Summary of get_available_custom_borders
     * @param Request $request
     */
    public function get_available_custom_borders(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $type = $request->type;
            $user = $request->user();

            $borders = CustomBorder::where('type', $type)->get()
                ->map(function($self) use ($user, $type) {
                    $iCanUse = ($type === "FREE" || $user->role === "SUPERADMIN") ? true : $self->userInv()->where('user_id', $user->id)->exists();
                    return [
                        'id' => $self->id,
                        'border' => $self->filename,
                        'iCanUse' => $iCanUse
                    ];
                })->sortByDesc('iCanUse')->values();

            return response()->json(['borders' => $borders], 200);
        });
    }

    /**
     * Summary of set_as_my_custom_border
     * @param Request $request
     */
    public function set_as_my_custom_border(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $border = $request->border;
            $set_as_current_border = User::findOrFail($request->user()->id);
            $set_as_current_border->custom_border_id = $border;
            $set_as_current_border->save();

            return response()->json(['message' => "Successs action!"], 200);
        });
    }

    /**
     * Summary of get_user_rare_borders
     * @param Request $request
     */
    public function get_user_rare_borders(GetUserRareBorder $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $userId = $request->userId;
            $borders = UserBorderInv::with([ 'customBorder' ])->where([
                'user_id' => $userId
            ])->get();

            return response()->json(['borders' => $borders], 200);
        });
    }

    /**
     * Summary of get_user_new_rare_borders
     * @param Request $request
     */
    public function get_user_new_rare_borders(GetUserNewRareBorder $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $userId = $request->userId;

            $borders = CustomBorder::where('type', 'RARE')
                ->whereDoesntHave('userInv', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->get();

            return response()->json(['borders' => $borders], 200);
        });
    }

    /**
     * Summary of add_new_rare_borders
     * @param AddUserRareInvBorder $request
     */
    public function add_new_rare_borders(AddUserRareInvBorder $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $userId = $request->userId;
            $borderId = $request->input('border');

            foreach($borderId as $b) {
                $checkForExistence = UserBorderInv::where([
                    'user_id' => $userId,
                    'custom_border_id' => $b
                ])->exists();

                if(!$checkForExistence) {
                    $new_rare_in_inv = new UserBorderInv();
                    $new_rare_in_inv->user_id = $userId;
                    $new_rare_in_inv->custom_border_id = $b;
                    $new_rare_in_inv->save();
                }
            }

            return response()->json(['message' => "Successs action!"], 200);
        });
    }

    /**
     * Summary of remove_user_rare_borders
     * @param RemoveUserRareBorder $request
     */
    public function remove_user_rare_borders(RemoveUserRareBorder $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $userId = $request->userId;
            $borderId = $request->borderId;

            $this_user = User::findOrFail($userId);
            $this_rare_inv = UserBorderInv::where([
                'id' => $borderId,
                'user_id' => $userId
            ])->firstOrFail();

            if($this_user->custom_border_id === $this_rare_inv->custom_border_id) {
                $this_user->custom_border_id = null;
                $this_user->save();
            }

            $this_rare_inv->delete();
            return response()->json(['message' => "Successs action!"], 200);
        });
    }

    /**
     * Summary of create_or_update_custom_border
     * @param Request $request
     */
    public function create_or_update_custom_border(CreateOrUpdateBorder $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $isPost = $request->httpMethod === "POST";
            $border = $request->border;
            $type = $request->type;
            $documentId = $request->documentId;

            $this_border = $isPost ? new CustomBorder() : CustomBorder::where([
                'id' => $documentId
            ])->lockForUpdate()->firstOrFail();

            $this_border->type = $type;
            if($border) {
                $filename = Str::uuid() . '.png';
                SaveAvatar::dispatch($border, $filename, 'border-images', false, true, !$isPost && $border ? $this_border->filename : '');
                $this_border->filename = $filename;
            }

            $this_border->save();

            return response()->json(['message' => "Successs action!"], 200);
        });
    }
}

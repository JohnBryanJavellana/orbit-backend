<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
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
            $borders = CustomBorder::withCount([
                'borderUsers'
            ])->get();

            return response()->json(['borders' => $borders], 200);
        });
    }

    /**
     * Summary of get_available_custom_borders
     * @param Request $request
     */
    public function get_available_custom_borders(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $type = $request->type;
            $borders = CustomBorder::where('type', $type)->get()
                ->map(function($self) use ($request, $type) {
                    return [
                        'id' => $self->id,
                        'border' => $self->filename,
                        'iCanUse' => $type === "FREE" || $request->user()->role === "SUPERADMIN" ? true : $self->userInv()->where([
                            'user_id' => $request->user()->id,
                            'custom_border_id' => $self->id
                        ])->exists()
                    ];
                })->values();

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
            $borders = CustomBorder::where([
                'type' => "RARE"
            ])->whereDoesntHave('userInv', fn($query) => $query->user_id === $userId)->get();

            return response()->json(['borders' => $borders], 200);
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
                $this_user->custom_border_id = 0;
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

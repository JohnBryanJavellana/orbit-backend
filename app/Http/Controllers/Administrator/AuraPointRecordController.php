<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\AuraPointRecord\ModifyPoints;
use App\Models\AuraPointsRecord;
use App\Models\User;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;

class AuraPointRecordController extends Controller
{
    /**
     * Summary of get_aura_point_records
     * @param Request $request
     */
    public function get_aura_point_records(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $thisUser = $request->userId ?? $request->user()->id;
            $auraPointsRecord = AuraPointsRecord::with([
                'pointReceiver'
            ])->where([
                'point_receiver' => $thisUser
            ])->orderBy('created_at', 'DESC')->get();

            return response()->json(['auraPointsRecord' => $auraPointsRecord], 200);
        });
    }

    /**
     * Summary of modify_points
     * @param ModifyPoints $request
     */
    public function modify_points(ModifyPoints $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $contentText = $request->contentText;
            $points = $request->points;
            $modifyType = $request->modifyType;
            $playerId = $request->playerId;

            $this_player = User::where('id', $playerId)
                ->lockForUpdate()
                ->firstOrFail();

            if($modifyType === "DECREASE" && $this_player->total_points < $points) {
                return response()->json(['message' => "Point is bigger than player total aura points."], 409);
            }

            $this_modification = new AuraPointsRecord();
            $this_modification->point_receiver = $playerId;
            $this_modification->reason = $contentText;
            $this_modification->status = $modifyType;
            $this_modification->save();

            if($modifyType === "DECREASE") $this_player->total_points -= $points;
            if($modifyType === "INCREASE") $this_player->total_points += $points;
            $this_player->save();

            return response()->json(['message' => "Success Action!"], 200);
        });
    }
}

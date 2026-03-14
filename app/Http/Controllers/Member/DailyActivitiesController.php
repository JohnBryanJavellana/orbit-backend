<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CustomBorder;
use App\Models\DailyActivitiesReward;
use App\Models\User;
use App\Models\UserBorderInv;
use App\Models\UserCustomAvatar;
use App\Utils\NewAuraRecord;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyActivitiesController extends Controller
{
    /**
     * Summary of get_daily_activities
     * @param Request $request
     */
    public function get_daily_activities(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $checkIfICanPlayDailyActivities = DailyActivitiesReward::where([
                'initiator' => $request->user()->id
            ])->orderBy('created_at', 'DESC')->first();

            $activities = [
                'roulette' => $checkIfICanPlayDailyActivities ? $checkIfICanPlayDailyActivities->daily_roulette : 'PENDING'
            ];

            return response()->json(['activities' => $activities], 200);
        });
    }

    /**
     * Summary of save_roulette_score
     * @param Request $request
     */
    public function save_roulette_score(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $score = $request->score;
            $rareBorder = null;
            $userId = $request->user()->id;

            $alreadyClaimed = DailyActivitiesReward::where([
                'initiator' => $userId,
                'daily_roulette' => 'TAKEN',
                'created_at' => Carbon::today()
            ])->exists();

            if($alreadyClaimed) {
                return response()->json(['message' => "Reward already claimed for today."], 409);
            }

            if($score === 'RARE BORDER') {
                $rareBorder = $this->get_random_rare_border($userId);

                $new_rare_in_inv = new UserBorderInv();
                $new_rare_in_inv->user_id = $userId;
                $new_rare_in_inv->custom_border_id = $rareBorder->id;
                $new_rare_in_inv->save();
            }

            $numericScore = (int) $score;
            if($numericScore > 0){
                $request->user()->increment('total_points', $numericScore);
                NewAuraRecord::createRecord($userId, $numericScore, 'INCREASE', 'Added from a daily roulette game.');
            }

            $checkIfItHasRow = DailyActivitiesReward::where([
                'initiator' => $userId,
                'created_at' => Carbon::today()
            ]);

            $this_daily_games = $checkIfItHasRow->exists() ? $checkIfItHasRow->lockForUpdate()->first() : new DailyActivitiesReward();
            $this_daily_games->initiator = $userId;
            $this_daily_games->daily_roulette = "TAKEN";
            $this_daily_games->save();

            return response()->json([
                'message' => $score === 'RARE BORDER' ? "Legendary! You found a Rare Border!" : ($numericScore > 0 ? "$numericScore Aura Points successfully added." : "No Aura Points Added. Better luck next time!"),
                'rare_border_img' => $rareBorder ? "border-images/{$rareBorder->filename}" : null,
                'points_added' => $numericScore
            ], 200);
        });
    }

    /**
     * Summary of get_random_rare_border
     */
    protected function get_random_rare_border($userId) {
        $ownedIds = UserBorderInv::where('user_id', $userId)->pluck('custom_border_id');
        return CustomBorder::where('type', 'RARE')
            ->whereNotIn('id', $ownedIds)
            ->inRandomOrder()
            ->first();
    }
}

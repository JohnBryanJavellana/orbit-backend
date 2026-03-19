<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\DailyActivities\SaveGameRCSCGScore;
use App\Models\CustomBorder;
use App\Models\DailyActivitiesReward;
use App\Models\User;
use App\Models\UserBorderInv;
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
            $dailyRecord = DailyActivitiesReward::where('initiator', $request->user()->id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            $activities = [
                'roulette' => $dailyRecord ? $dailyRecord->daily_roulette : 'PENDING',
                'cupShuffle' => $dailyRecord ? $dailyRecord->cup_shuffle : 'PENDING',
                'colorGame' => $dailyRecord ? $dailyRecord->color_game : 'PENDING'
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
            $usingActualAPs = $request->usingActualAPs;
            $rareBorder = null;
            $userId = $request->user()->id;
            $user = User::where('id', $request->user()->id)->lockForUpdate()->first();

            $checkIfItHasRow = DailyActivitiesReward::where([
                'initiator' => $userId
            ])->whereDate('created_at', Carbon::today());

            if($checkIfItHasRow->exists()) {
                $a = $checkIfItHasRow->lockForUpdate()->first();
                if($a->daily_roulette === "TAKEN" && !$usingActualAPs) {
                    return response()->json(['message' => "We've detected a malicious gameplay. Please be aware of what you're doing."], 409);
                }
            }

            if($usingActualAPs) {
                if($usingActualAPs <= $user->total_points) {
                    $user->decrement('total_points', $usingActualAPs);
                    NewAuraRecord::createRecord($userId, $usingActualAPs, 'DECREASE', 'Deducted from a cup roulette game.');
                } else {
                    return response()->json(['message' => "It seems that you dont have remaining aura points."], 409);
                }
            }

            if($score === 'RARE BORDER' && $user->role !== "SUPERADMIN") {
                $rareBorder = $this->get_random_rare_border($userId);

                $new_rare_in_inv = new UserBorderInv();
                $new_rare_in_inv->user_id = $userId;
                $new_rare_in_inv->custom_border_id = $rareBorder->id;
                $new_rare_in_inv->save();
            }

            $numericScore = (int) $score;
            if($numericScore > 0){
                $user->increment('total_points', $numericScore);
                NewAuraRecord::createRecord($userId, $numericScore, 'INCREASE', 'Added from a roulette game.');
            }

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
     * Summary of save_game_r_cs_cg_score
     * @param SaveGameRCSCGScore $request
     */
    public function save_game_r_cs_cg_score(SaveGameRCSCGScore $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $score = $request->score;
            $usingActualAPs = $request->usingActualAPs;
            $gameService = $request->gameService;
            $userId = $request->user()->id;
            $rareBorder = null;

            $user = User::where('id', $request->user()->id)->lockForUpdate()->first();

            $checkIfItHasRow = DailyActivitiesReward::where([
                'initiator' => $userId
            ])->whereDate('created_at', Carbon::today());

            if($checkIfItHasRow->exists()) {
                $a = $checkIfItHasRow->lockForUpdate()->first();
                if($a->{$gameService} === "TAKEN" && !$usingActualAPs) {
                    return response()->json(['message' => "We've detected a malicious gameplay. Please be aware of what you're doing."], 409);
                }
            }

            if($usingActualAPs) {
                if($usingActualAPs <= $user->total_points) {
                    $user->decrement('total_points', $usingActualAPs);
                    NewAuraRecord::createRecord($userId, $usingActualAPs, 'DECREASE', "Deducted from a " . str_replace($gameService, '_', '') . " game.");
                } else {
                    return response()->json(['message' => "It seems that you dont have remaining aura points."], 409);
                }
            }

            if( $gameService === "daily_roulette" && ($score === 'RARE BORDER' && $user->role !== "SUPERADMIN")) {
                $rareBorder = $this->get_random_rare_border($userId);

                $new_rare_in_inv = new UserBorderInv();
                $new_rare_in_inv->user_id = $userId;
                $new_rare_in_inv->custom_border_id = $rareBorder->id;
                $new_rare_in_inv->save();
            }

            $numericScore = (int) $score;
            if($numericScore > 0){
                $user->increment('total_points', $numericScore);
                NewAuraRecord::createRecord($userId, $numericScore, 'INCREASE', "Added from a " . str_replace($gameService, '_', '') . " game.");
            }

            $this_daily_games = $checkIfItHasRow->exists() ? $checkIfItHasRow->lockForUpdate()->first() : new DailyActivitiesReward();
            $this_daily_games->initiator = $userId;
            $this_daily_games->{$gameService} = "TAKEN";
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

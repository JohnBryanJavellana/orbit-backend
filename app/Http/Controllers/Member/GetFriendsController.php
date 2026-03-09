<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Administrator\MembersController;
use App\Http\Controllers\Controller;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;

class GetFriendsController extends Controller
{
    protected $membersControllerInstance;

    public function __construct(MembersController $membersController){
        $this->membersControllerInstance = $membersController;
    }

    /**
     * Summary of get_friends
     * @param Request $request
     */
    public function get_friends(Request $request){
        return TransactionUtil::transact(null, [], function () use ($request) {
            $response = $this->membersControllerInstance->get_members($request);
            $data = json_decode($response->getContent(), true);

            $friends = collect($data['members'] ?? [])->values();

            return response()->json(['friends' => $friends], 200);
        });
    }
}

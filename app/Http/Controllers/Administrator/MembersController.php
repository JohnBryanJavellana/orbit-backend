<?php

namespace App\Http\Controllers\Administrator;

use App\Helpers\Administrator\General\CheckForDocumentExistence;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\Members\CreateOrUpdateMember;
use App\Http\Requests\Administrator\Members\CreateOrUpdateMemberRole;
use App\Jobs\SaveAvatar;
use App\Models\MemberRole;
use App\Models\Projects;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\{
    Member
};
use App\Utils\{
    TransactionUtil
};
use Str;

class MembersController extends Controller
{
    /**
     * Summary of get_members
     * @param Request $request
     */
    public function get_members(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $memberTemp = User::query();

            if($request->excludeOmega) $memberTemp->whereNot('role', 'SUPERADMIN');
            $members = $request->memberId
                ? $memberTemp->where('id', $request->memberId)->firstOrFail()
                : $memberTemp->orderBy('role', 'ASC')->get();

            return response()->json(['members' => $members], 200);
        });
    }

    /**
     * Summary of create_or_update_member
     * @param CreateOrUpdateMember $request
     */
    public function create_or_update_member(CreateOrUpdateMember $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $isPost = $request->httpMethod === "POST";
            $documentId = $request->documentId;
            $fname = $request->first_name;
            $mname = $request->middle_name;
            $lname = $request->last_name;
            $suffix = $request->suffix;
            $birthday = $request->birthday;
            $bio = $request->bio;
            $gender = $request->gender;
            $role = $request->role;
            $email = $request->email;
            $password = $request->password;
            $profilePicture = $request->profilePicture;

            $this_member = $isPost
                ? new User()
                : User::where('id', $documentId)->lockForUpdate()->firstOrFail();

            $this_member->first_name = $fname;
            $this_member->middle_name = $mname;
            $this_member->last_name = $lname;
            if($suffix !== 'null') $this_member->suffix = $suffix;
            $this_member->birthday = $birthday;
            $this_member->bio = $bio;
            $this_member->gender = $gender;
            if(\in_array($request->user()->role, ['SUPERADMIN'])) $this_member->role = $role;

            if($password) {
                $this_member->password = bcrypt($password);
            }

            if($email) {
                $this_member->email = $email;
                $this_member->email_verified_at = $this_member->email ? $this_member->email_verified_at : Carbon::now();
            }

            if($profilePicture) {
                $filename = Str::uuid() . '.png';
                SaveAvatar::dispatch($profilePicture, $filename, 'user-images', false, true, !$isPost && $profilePicture ? $this_member->profile_picture : '');
                $this_member->profile_picture = $filename;
            }

            $this_member->save();

            return response()->json(['message' => "Success action. $request->httpMethod!"], 200);
        });
    }

    /**
     * Summary of get_member_roles
     * @param Request $request
     */
    public function get_member_roles(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $memberRoles = MemberRole::withCount([
                'hasData'
            ])->get();

            return response()->json(['memberRoles' => $memberRoles], 200);
        });
    }

    /**
     * Summary of create_or_update_member_role
     * @param CreateOrUpdateMemberRole $request
     */
    public function create_or_update_member_role(CreateOrUpdateMemberRole $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $isPost = $request->httpMethod === "POST";
            $documentId = $request->documentId;
            $role = $request->role;

            $check = CheckForDocumentExistence::exists(
                MemberRole::class,
                ['role' => $role],
                !$isPost,
                $documentId,
                'id',
                "Member role already exist."
            );

            if($check) return $check;

            $this_member_role = $isPost
                ? new MemberRole()
                : MemberRole::where('id', $documentId)->lockForUpdate()->firstOrFail();

            $this_member_role->creator_id = $request->user()->id;
            $this_member_role->role = $role;
            $this_member_role->save();

            return response()->json(['message' => "Success action. $request->httpMethod!"], 200);
        });
    }
}

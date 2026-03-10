<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\Announcement\CreateOrUpdateAnnouncement;
use App\Models\Announcement;
use App\Utils\GenerateTrace;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Summary of get_announcement
     * @param Request $request
     */
    public function get_announcements(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $announcementsTemp = Announcement::with([ 'creator' ]);
            $announcements = $request->getNew
                ? $announcementsTemp->where([
                    'status' => 'SHOW'
                ])->orderBy('created_at', 'DESC')->get()
                : $announcementsTemp->orderBy('created_at')->get();

            return response()->json(['announcements' => $announcements], 200);
        });
    }

    /**
     * Summary of create_or_update_announcement
     * @param CreateOrUpdateAnnouncement $request
     */
    public function create_or_update_announcement(CreateOrUpdateAnnouncement $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $isPost = $request->httpMethod === "POST";
            $documentId = $request->documentId;
            $contentText = $request->contentText;
            $status = $request->status;

            $this_announcement = $isPost
                ? new Announcement()
                : Announcement::where('id', $documentId)->lockForUpdate()->firstOrFail();

            if($isPost) {
                $this_announcement->ctrl = GenerateTrace::createTraceNumber(Announcement::class, 'A-', 'ctrl');
            } else {
                $this_announcement->status = $status;
            }

            $this_announcement->creator_id = $request->user()->id;
            $this_announcement->content = $contentText;
            $this_announcement->save();

            return response()->json(['message' => "Success action. $request->httpMethod!"], 200);
        });
    }
}

<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\Announcement\CreateOrUpdateAnnouncement;
use App\Jobs\SaveAvatar;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Utils\GenerateTrace;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Str;

class AnnouncementController extends Controller
{
    /**
     * Summary of get_announcement
     * @param Request $request
     */
    public function get_announcements(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $announcementsTemp = Announcement::with([ 'creator', 'attachments' ]);
            $announcements = $request->getNew
                ? $announcementsTemp->where([
                    'status' => 'SHOW'
                ])->orderBy('created_at', 'DESC')->get()
                : $announcementsTemp->orderBy('created_at', 'DESC')->get();

            return response()->json(['announcements' => $announcements], 200);
        });
    }

    /**
     * Summary of remove_announcement
     * @param Request $request
     * @param int $announcementId
     */
    public function remove_announcement(Request $request, int $announcementId) {
        return TransactionUtil::transact(null, [], function () use ($request, $announcementId) {
            $this_announcement = Announcement::findOrFail($announcementId);

            foreach($this_announcement->attachments as $attachment) {
                if(file_exists(public_path("announcement_attachments/$attachment->filename"))) {
                    unlink(public_path("announcement_attachments/$attachment->filename"));
                }
            }

            $this_announcement->delete();
            return response()->json(['message' => "Successs action!"], 200);
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
            $removalDate = $request->removalDate;
            $oldAttachmentIds = $request->oldAttachmentIds;
            $newAttachments = $request->newAttachments;

            $this_announcement = $isPost
                ? new Announcement()
                : Announcement::where('id', $documentId)->lockForUpdate()->firstOrFail();

            if($isPost) {
                $this_announcement->ctrl = GenerateTrace::createTraceNumber(Announcement::class, 'A-', 'ctrl');
            } else {
                $this_announcement->status = $status;
            }

            $this_announcement->removal_date = $removalDate === 'null' ? null : $removalDate;
            $this_announcement->creator_id = $request->user()->id;
            $this_announcement->content = $contentText;
            $this_announcement->save();

            if($oldAttachmentIds) {
                $this_announcement->attachments()->whereNotIn('id', $oldAttachmentIds)->get()->each(function($attachment) {
                    if(file_exists(public_path("announcement_attachments/$attachment->filename"))) {
                        unlink(public_path("announcement_attachments/$attachment->filename"));
                    }
                });

                $this_announcement->attachments()->whereNotIn('id', $oldAttachmentIds)->delete();
            }

            if ($newAttachments) {
                foreach ($newAttachments as $attachment) {
                    preg_match('/^data:(.*);base64,/', $attachment, $match);
                    $base64Type = $match[1] ?? 'image/png';
                    $extension = str_contains($base64Type, 'video') ? 'mp4' : 'png';
                    $filename = Str::uuid() . '.' . $extension;

                    SaveAvatar::dispatch($attachment, $filename, 'announcement_attachments', false, true, '', $base64Type);

                    $this_attachment = new AnnouncementAttachment();
                    $this_attachment->announcement_id = $this_announcement->id;
                    $this_attachment->filename = $filename;
                    $this_attachment->save();
                }
            }

            return response()->json(['message' => "Success action. $request->httpMethod!"], 200);
        });
    }
}

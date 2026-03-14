<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\PhotoDocumentation\SubmitPhotoDocumentation;
use App\Jobs\SaveAvatar;
use App\Models\PhotoDocumentation;
use App\Models\PhotoDocumentationFiles;
use App\Models\Task;
use App\Models\User;
use App\Utils\NewAuraRecord;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Str;
use Carbon\Carbon;

class PhotoDocumentationController extends Controller
{
    /**
     * Summary of get_photo_documentations
     * @param Request $request
     */
    public function get_photo_documentations(Request $request) {
        $taskCtrl = $request->taskCtrl;
        $this_task = Task::where('ctrl', $taskCtrl)->firstOrFail();

        $documentations = PhotoDocumentation::withCount([
            'uploadedFiles'
        ])->with([
            'uploadedFiles' => function($query) { $query->orderBy('created_at', 'DESC'); },
            'uploadedFiles.uploader',
            'task'
        ])->where('task_id', $this_task->id)->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'task' => $this_task,
            'documentations' => $documentations
        ], 200);
    }

    /**
     * Summary of submit_photo_documentation
     * @param SubmitPhotoDocumentation $request
     */
    public function submit_photo_documentation(SubmitPhotoDocumentation $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $documentationFile = $request->documentation;
            $taskCtrl = $request->taskCtrl;

            $this_task = Task::where('ctrl', $taskCtrl)->firstOrFail();
            $existingDocumentation = PhotoDocumentation::where('task_id', $this_task->id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if ($existingDocumentation) {
                $parentDoc = $existingDocumentation;

                $countUploadedFiles = $existingDocumentation->uploadedFiles()->count();
                if($countUploadedFiles >= 5 && $request->user()->role !== "SUPERADMIN") {
                    return response()->json(['message' => "Today's upload max count has been reached."], 409);
                }
            } else {
                $parentDoc = new PhotoDocumentation();
                $parentDoc->task_id = $this_task->id;
                $parentDoc->save();
            }

            $message = "Documentation uploaded successfully!";

            if ($documentationFile) {
                $this_file = new PhotoDocumentationFiles();
                $this_file->photo_documentation_id = $parentDoc->id;
                $this_file->uploader = $request->user()->id;

                $filename = Str::uuid() . '.png';
                SaveAvatar::dispatch($documentationFile, $filename, 'documentation-files', false, true, '');

                $this_file->filename = $filename;
                $this_file->save();

                if($request->user()->role !== "SUPERADMIN") {
                    $this_uploader = User::findOrFail($request->user()->id);
                    $this_uploader->increment('total_points', 5);
                    $this_uploader->save();

                    NewAuraRecord::createRecord($this_uploader->id, 5, 'INCREASE', 'Added from an uploaded photo documentation.');
                    $message .= " Also you've received a 5 additional aura points.";
                }
            }

            return response()->json(['message' => $message], 200);
        });
    }
}

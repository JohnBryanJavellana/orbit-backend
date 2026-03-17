<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Administrator\ProjectsController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\Project\GetTaskProgress;
use App\Jobs\SaveAvatar;
use App\Models\Member;
use App\Models\ProgressAttachment;
use App\Models\Projects;
use App\Models\Task;
use App\Models\TaskProgress;
use App\Utils\Notifications;
use App\Utils\TransactionUtil;
use File;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Str;

class GetProjectsController extends Controller
{
    protected $adminProjectsController;

    public function __construct(ProjectsController $projectsController) {
        $this->adminProjectsController = $projectsController;
    }

    /**
     * Summary of get_assigned_projects
     * @param Request $request
     */
    public function get_assigned_projects(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $projects = Projects::with([
                'creator'
            ])->whereHas('collaborators', function($query) use($request) {
                $query->where('collaborator_id', $request->user()->id);
            })->get();

            return response()->json(['projects' => $projects], 200);
        });
    }

    /**
     * Summary of get_project_available_tasks
     * @param Request $request
     */
    public function get_project_available_tasks(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $this_project = Projects::where('ctrl', $request->projectCtrl)->firstOrFail();
            $availableTasks = Task::with([
                'creator',
                'members' => function ($query) use ($request) {
                    $query->where('member_id', $request->user()->id)->select('task_id', 'status', 'member_id');
                }
            ])
            ->where([
                'projects_id' => $this_project->id
            ])
            ->whereNotIn('status', ['COMPLETED', 'CLOSED'])
            ->whereDoesntHave('members', function ($query) use ($request) {
                $query->where('member_id', $request->user()->id)
                      ->whereNot('status', "CANCELLED");
            })->get();

            return response()->json([
                'project' => $this_project,
                'availableTasks' => $availableTasks
            ], 200);
        });
    }

    /**
     * Summary of get_applied_tasks
     * @param Request $request
     */
    public function get_applied_tasks(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $this_project = Projects::where('ctrl', $request->projectCtrl)->firstOrFail();
            $availableTasks = Task::with([
                'creator',
                'members' => function ($query) use ($request) {
                    $query->where('member_id', $request->user()->id)->select('task_id', 'status', 'member_id');
                }
            ])->whereHas('members', function($query) use($request) {
                $query->where('member_id', $request->user()->id);
            })->where('projects_id', $this_project->id)->get();

            return response()->json([
                'project' => $this_project,
                'availableTasks' => $availableTasks
            ], 200);
        });
    }

    /**
     * Summary of apply_to_a_task
     * @param Request $request
     */
    public function apply_to_a_task(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $taskId = $request->taskId;

            $this_task = Task::findOrFail($taskId);
            $checkForExistence = Member::where([
                'member_id' => $request->user()->id,
                'task_id' => $taskId
            ])->exists();

            if($checkForExistence) {
                return response()->json(['message' => "Already a member to the task."], 409);
            }

            $new_member = new Member();
            $new_member->task_id = $taskId;
            $new_member->member_id = $request->user()->id;
            $new_member->status = "PENDING";
            $new_member->save();

            Notifications::notify($request->user()->id, null, "submitted a task application. Task CTRL#$this_task->ctrl");

            return response()->json(['message' => "Success! Task application has been submitted. Please check for application updates."], 409);
        });
    }

    /**
     * Summary of cancel_application
     * @param Request $request
     */
    public function cancel_application(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $taskId = $request->taskId;

            $checkForExistence = Member::where([
                'member_id' => $request->user()->id,
                'task_id' => $taskId
            ])->exists();

            if(!$checkForExistence) {
                return response()->json(['message' => "Task membership not found."], 409);
            }

            $this_member = Member::where([
                'member_id' => $request->user()->id,
                'task_id' => $taskId
            ])->firstOrFail();

            $this_member->status = "CANCELLED";
            $this_member->save();

            return response()->json(['message' => "Success! Task application has been cancelled."], 409);
        });
    }

    /**
     * Summary of get_task_progresses
     * @param GetTaskProgress $request
     */
    public function get_task_progresses(GetTaskProgress $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            return $this->adminProjectsController->get_task_progress($request);
        });
    }

    /**
     * Summary of submit_progress
     * @param Request $request
     */
    public function submit_progress(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $taskCtrl = $request->taskCtrl;
            $activity = $request->activity;
            $attachments = $request->attachments;

            if(Carbon::now()->gte(Carbon::parse('16:30'))) {
                return response()->json(['message' => "I'm sorry. We can't accept your progress at this time. Try submitting tomorrow before 4:30 PM"], 409);
            }

            $this_task = Task::where('ctrl', $taskCtrl)->firstOrFail();
            $this_member = Member::where([
                'member_id' => $request->user()->id,
                'task_id' => $this_task->id
            ])->firstOrFail();
            $checkForExistence = TaskProgress::where([
                'member_id' => $this_member->id,
                'task_id' => $this_task->id,
                'status' => 'PENDING'
            ])->exists();

            if($checkForExistence) {
                return response()->json(['message' => "You still have a pending progress. Kindly contact our supreme."], 409);
            }

            $new_progress = new TaskProgress();
            $new_progress->task_id = $this_task->id;
            $new_progress->member_id = $this_member->id;
            $new_progress->activity = $activity;
            $new_progress->status = "PENDING";
            $new_progress->save();

            $this_task->status = "IN PROGRESS";
            $this_task->save();

            if ($attachments) {
                foreach ($attachments as $attachment) {
                    $extension = 'png';
                    $base64Type = 'image';

                    if (preg_match('/^data:(\w+)\/([\w\.\-]+);base64,/', $attachment, $matches)) {
                        $mimeMainType = $matches[1];
                        $mimeSubType = $matches[2];

                        $base64Type = $mimeMainType;
                        $extension = match(true) {
                            str_contains($mimeSubType, 'officedocument.wordprocessingml') => 'docx',
                            str_contains($mimeSubType, 'officedocument.spreadsheetml') => 'xlsx',
                            str_contains($mimeSubType, 'officedocument.presentationml') => 'pptx',
                            default => $mimeSubType
                        };
                    }

                    $fileName = Str::uuid() . '.' . $extension;
                    SaveAvatar::dispatch(
                        avatar: $attachment,
                        filename: $fileName,
                        path: 'progress-attachments',
                        isUrl: false,
                        isBase64: true,
                        base64Type: $base64Type
                    );

                    $new_attachment = new ProgressAttachment();
                    $new_attachment->progress_id = $new_progress->id;
                    $new_attachment->filename = $fileName;
                    $new_attachment->save();
                }
            }

            Notifications::notify($request->user()->id, null, "submitted a progress. Task CTRL#$this_task->ctrl");

            return response()->json(['message' => "Success! Task application progress has been submitted. Please check for application updates."], 409);
        });
    }
}

<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrator\Project\AssignUserAsProjectCollaborator;
use App\Http\Requests\Administrator\Project\AssignUserAsTaskCollaborator;
use App\Http\Requests\Administrator\Project\GetProjectCollaborator;
use App\Http\Requests\Administrator\Project\GetProjectTaskCollaborator;
use App\Http\Requests\Administrator\Project\GetTaskProgress;
use App\Http\Requests\Administrator\Project\TerminateTaskCollaborator;
use App\Http\Requests\Administrator\Project\UpdateProjectCollaboratorAssignmentStatus;
use App\Http\Requests\Administrator\Project\UpdateTaskProgress;
use App\Models\Member;
use App\Models\ProjectCollaborator;
use App\Models\TaskProgress;
use App\Models\User;
use App\Utils\NewAuraRecord;
use Illuminate\Http\Request;

use App\Http\Requests\Administrator\Project\{
    CreateOrUpdateProject,
    CreateOrUpdateTask,
    GetProjectTasks
};
use App\Models\{
    Projects,
    Task
};
use App\Utils\{
    GenerateTrace,
    TransactionUtil
};

class ProjectsController extends Controller
{
    /**
     * Summary of get_projects
     * @param Request $request
     */
    public function get_projects(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $projectsTemp = Projects::withCount([
                'tasks'
            ])->with([
                'creator',
                'collaborators' => fn($query) => $query->whereNotIn('status', ['PENDING', 'DECLINED', 'CANCELLED']),
                'collaborators.user'
            ]);

            $projects = $request->projectCtrl
                ? $projectsTemp->where('ctrl', $request->projectCtrl)->firstOrFail()
                : $projectsTemp->get();

            return response()->json(['projects' => $projects], 200);
        });
    }

    /**
     * Summary of get_project_collaborators
     * @param GetProjectCollaborator $request
     */
    public function get_project_collaborators(GetProjectCollaborator $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $this_project = Projects::where('ctrl', $request->projectCtrl)->firstOrFail();
            $members = ProjectCollaborator::with([
                'user'
            ])->where([
                'projects_id' => $this_project->id
            ])->get();

            return response()->json([
                'members' => $members,
                'project' => $this_project
            ], 200);
        });
    }

    /**
     * Summary of get_future_collaborators
     * @param Request $request
     */
    public function get_future_collaborators(Request $request) {
        return TransactionUtil::transact(null, [], function () use ($request) {
            $future_collaborators = User::all();
            return response()->json(['future_collaborators' => $future_collaborators], 200);
        });
    }

    /**
     * Summary of update_project_collaborator_assignment_status
     * @param TerminateTaskCollaborator $request
     */
    public function update_project_collaborator_assignment_status(UpdateProjectCollaboratorAssignmentStatus $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $projectCtrl = $request->projectCtrl;
            $status = $request->status;
            $collaboratorId = $request->collaboratorId;

            $this_project = Projects::where('ctrl', $projectCtrl)->firstOrFail();
            $this_member = ProjectCollaborator::where([
                'projects_id' => $this_project->id,
                'id' => $collaboratorId
            ])->lockForUpdate()->firstOrFail();

            $this_member->status = $status;
            $this_member->save();

            return response()->json(['message' => "$status successfully!"], status: 200);
        });
    }

    /**
     * Summary of assign_user_as_project_collaborator
     * @param AssignUserAsProjectCollaborator $request
     */
    public function assign_user_as_project_collaborator(AssignUserAsProjectCollaborator $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $isPost = $request->httpMethod === "POST";
            $projectCtrl = $request->projectCtrl;
            $collaboratorId = $request->collaboratorId;

            $this_project = Projects::where('ctrl', $projectCtrl)->firstOrFail();
            $assign_member = new ProjectCollaborator();
            $assign_member->added_by_id = $request->user()->id;
            $assign_member->projects_id = $this_project->id;
            $assign_member->collaborator_id = $collaboratorId;
            $assign_member->status = "ACTIVE";
            $assign_member->save();

            return response()->json(['message' => "Success action!"], status: 200);
        });
    }

    /**
     * Summary of create_or_update_project
     * @param CreateOrUpdateProject $request
     */
    public function create_or_update_project(CreateOrUpdateProject $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $isPost = $request->httpMethod === "POST";
            $documentId = $request->documentId;
            $name = $request->name;
            $status = $request->status;
            $description = $request->description;
            $completionPoints = $request->completionPoints;

            $this_project = $isPost
                ? new Projects()
                : Projects::where('id', $documentId)->lockForUpdate()->firstOrFail();

            $this_project->name = $name;
            $this_project->description = $description;
            $this_project->completion_points = $completionPoints;

            if($isPost) {
                $this_project->creator_id = $request->user()->id;
                $this_project->ctrl = GenerateTrace::createTraceNumber(Projects::class, 'P-', 'ctrl');
            } else {
                $this_project->status = $status;

                if($status === "COMPLETED") {
                    $listOfMembers = $this_project->collaborators()->where('status', 'ACTIVE')->get();
                    foreach ($listOfMembers as $collaborator) {
                        $this_main_account = User::findOrFail($collaborator->collaborator_id);
                        $this_main_account->total_points += $this_project->completion_points;
                        $this_main_account->save();

                        NewAuraRecord::createRecord(
                            $this_main_account->id,
                            $this_project->completion_points,
                            'INCREASE',
                            'Added from a completed project.'
                        );
                    }
                }
            }

            $this_project->save();

            return response()->json(['message' => "Success action. $request->httpMethod!"], 200);
        });
    }

    /**
     * Summary of get_project_tasks
     * @param GetProjectTasks $request
     */
    public function get_project_tasks(GetProjectTasks $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $projectCtrl = $request->projectCtrl;

            $project = Projects::where(['ctrl' => $projectCtrl])->firstOrFail();
            $tasksTemp = Task::with([
                'creator',
                'members' => fn($query) => $query->whereNotIn('status', ['PENDING', 'DECLINED', 'CANCELLED']),
                'members.user'
            ])->withCount([
                'members'
            ])->where('projects_id', $project->id);

            $tasks = $request->taskCtrl
                ? $tasksTemp->where('ctrl', $request->taskCtrl)->firstOrFail()
                : $tasksTemp->orderBy('status', 'DESC')->get();

            return response()->json([
                'project' => $project,
                'tasks' => $tasks
            ], 200);
        });
    }

    /**
     * Summary of create_or_update_project_task
     * @param GetProjectTasks $request
     */
    public function create_or_update_project_task(CreateOrUpdateTask $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $isPost = $request->httpMethod === "POST";
            $projectCtrl = $request->projectCtrl;
            $documentId = $request->documentId;
            $name = $request->name;
            $status = $request->status;
            $description = $request->description;
            $task_completion_points = $request->task_completion_points;
            $task_progress_points = $request->task_progress_points;

            $this_project = Projects::where([
                'ctrl' => $projectCtrl
            ])->firstOrFail();

            $this_project_task = $isPost
                ? new Task()
                : Task::where('id', $documentId)->lockForUpdate()->first();

            $this_project_task->projects_id = $this_project->id;
            $this_project_task->name = $name;
            $this_project_task->description = $description;
            $this_project_task->task_completion_points = $task_completion_points;
            $this_project_task->task_progress_points = $task_progress_points;

            if($isPost) {
                $this_project_task->creator_id = $request->user()->id;
                $this_project_task->ctrl = GenerateTrace::createTraceNumber(Task::class, 'PT-', 'ctrl');
            } else {
                $this_project_task->status = $status;

                if($status === "COMPLETED") {
                    $listOfMembers = $this_project_task->members()->where('status', 'ACTIVE')->get();

                    foreach ($listOfMembers as $member) {
                        $this_main_account = User::findOrFail($member->member_id);
                        $this_main_account->increment('total_points', $this_project_task->task_completion_points);

                        NewAuraRecord::createRecord(
                            $this_main_account->id,
                            $this_project_task->task_completion_points,
                            'INCREASE',
                            'Added from a completed task.'
                        );
                    }
                }
            }

            $this_project_task->save();

            return response()->json(['message' => "Success action. $request->httpMethod!"], status: 200);
        });
    }

    /**
     * Summary of get_project_task_collaborators
     * @param GetProjectTaskCollaborator $request
     */
    public function get_project_task_collaborators(GetProjectTaskCollaborator $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $this_task = Task::where('ctrl', $request->taskCtrl)->firstOrFail();
            $members = Member::with([
                'user',
                'member_role'
            ])->where([
                'task_id' => $this_task->id
            ])->whereNotIn('status', ['CANCELLED', 'DECLINED'])->get();

            return response()->json([
                'members' => $members,
                'task' => $this_task
            ], 200);
        });
    }

    /**
     * Summary of assign_user_as_task_collaborator
     * @param AssignUserAsTaskCollaborator $request
     */
    public function assign_user_as_task_collaborator(AssignUserAsTaskCollaborator $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $isPost = $request->httpMethod === "POST";
            $taskCtrl = $request->taskCtrl;
            $memberId = $request->memberId;
            $memberRoleId = $request->memberRoleId;

            $this_task = Task::where('ctrl', $taskCtrl)->firstOrFail();

            if($this_task->members()->where('member_id', $memberId)->exists()) {
                return response()->json(['message' => "Member is already a collaborator."], 409);
            }

            $assign_member = new Member();
            $assign_member->added_by_id = $request->user()->id;
            $assign_member->task_id = $this_task->id;
            $assign_member->member_id = $memberId;
            $assign_member->member_role_id = $memberRoleId;
            $assign_member->status = "ACTIVE";
            $assign_member->save();

            return response()->json(['message' => "Success action!"], 200);
        });
    }

    /**
     * Summary of terminate_task_member
     * @param TerminateTaskCollaborator $request
     */
    public function update_task_assignment_status(TerminateTaskCollaborator $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $taskCtrl = $request->taskCtrl;
            $status = $request->status;
            $collaboratorId = $request->collaboratorId;

            $this_task = Task::where('ctrl', $taskCtrl)->firstOrFail();
            $this_member = Member::where([
                'task_id' => $this_task->id,
                'id' => $collaboratorId
            ])->lockForUpdate()->firstOrFail();

            $this_member->status = $status;
            $this_member->save();

            return response()->json(['message' => "$status successfully!"], status: 200);
        });
    }

    /**
     * Summary of get_task_progress
     * @param GetTaskProgress $request
     */
    public function get_task_progress(GetTaskProgress $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $taskCtrl = $request->taskCtrl;

            $this_task = Task::where('ctrl', $taskCtrl)->firstOrFail();
            $progress = TaskProgress::where([
                'task_id' => $this_task->id
            ])->with([
                'initiator.user'
            ])->orderBy('created_at', 'DESC')->get();

            return response()->json(['progress' => $progress], status: 200);
        });
    }

    /**
     * Summary of update_task_progress
     * @param UpdateTaskProgress $request
     */
    public function update_task_progress(UpdateTaskProgress $request) {
        return TransactionUtil::transact($request, [], function () use ($request) {
            $progressId = $request->progressId;
            $status = $request->status;
            $remarks = $request->remarks;

            $this_progress = TaskProgress::where([
                'id' => $progressId
            ])->lockForUpdate()->firstOrFail();

            $this_progress->status = $status;
            $this_progress->remarks = $remarks;
            $this_progress->save();

            if($status === 'VERIFIED') {
                $initiator = $this_progress->initiator->user;
                $initiator->increment('total_points', $this_progress->task->task_progress_points);

                NewAuraRecord::createRecord(
                    $this_progress->initiator->user->id,
                    $this_progress->task->task_progress_points,
                    'INCREASE',
                    'Added from a verified task progress.'
                );
            }

            return response()->json(['message' => "$status successfully!"], status: 200);
        });
    }
}

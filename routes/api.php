<?php

use App\Http\Controllers\Administrator\AnnouncementController;
use App\Http\Controllers\Administrator\AuraPointRecordController;
use App\Http\Controllers\Administrator\BorderController;
use App\Http\Controllers\Administrator\CustomAvatarController;
use App\Http\Controllers\Administrator\LogoutController;
use App\Http\Controllers\Administrator\MembersController;
use App\Http\Controllers\Administrator\PhotoDocumentationController;
use App\Http\Controllers\Administrator\ProjectsController;
use App\Http\Controllers\Guest\{
    LoginController
};
use App\Http\Controllers\Member\DailyActivitiesController;
use App\Http\Controllers\Member\GetFriendsController;
use App\Http\Controllers\Member\GetProjectsController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [LoginController::class, 'login_user']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user', function(Request $request) {
        $this_user = User::findOrFail($request->userId ?? $request->user()->id);
        return response()->json(['user' => $this_user], 200);
    });

    Route::prefix('administrator')->group(function() {
        Route::prefix('projects')->group(function() {
            Route::match(['GET', 'POST'], '/get_projects', [ProjectsController::class, 'get_projects']);
            Route::post('/get_projects/get_project_tasks', [ProjectsController::class, 'get_project_tasks'])->middleware(['admin_route']);
            Route::post('/get_projects/get_project_collaborators', [ProjectsController::class, 'get_project_collaborators']);
            Route::post('/get_projects/assign_user_as_project_collaborator', [ProjectsController::class, 'assign_user_as_project_collaborator'])->middleware(['admin_route']);
            Route::post('/get_projects/update_project_collaborator_assignment_status', [ProjectsController::class, 'update_project_collaborator_assignment_status'])->middleware(['admin_route']);
            Route::post('/get_projects/get_future_collaborators', [ProjectsController::class, 'get_future_collaborators'])->middleware(['admin_route']);

            Route::post('/get_projects/get_project_tasks/get_project_task_collaborators', [ProjectsController::class, 'get_project_task_collaborators']);
            Route::post('/get_projects/get_project_tasks/assign_user_as_task_collaborator', [ProjectsController::class, 'assign_user_as_task_collaborator'])->middleware(['admin_route']);
            Route::post('/get_projects/get_project_tasks/update_task_assignment_status', [ProjectsController::class, 'update_task_assignment_status'])->middleware(['admin_route']);
            Route::post('/get_projects/get_project_tasks/get_task_progress', [ProjectsController::class, 'get_task_progress']);
            Route::post('/get_projects/get_project_tasks/update_task_progress', [ProjectsController::class, 'update_task_progress'])->middleware(['admin_route']);

            Route::post('/get_projects/create_or_update_project_task', [ProjectsController::class, 'create_or_update_project_task'])->middleware(['admin_route']);
            Route::post('/create_or_update_project', [ProjectsController::class, 'create_or_update_project'])->middleware(['admin_route']);
        });

        Route::prefix('members')->group(function() {
            Route::match(['GET', 'POST'], '/get_members', [MembersController::class, 'get_members'])->middleware(['admin_route']);
            Route::post('/get_members/create_or_update_member', [MembersController::class, 'create_or_update_member']);
            Route::get('/get_member_roles', [MembersController::class, 'get_member_roles'])->middleware(['admin_route']);
            Route::post('/get_member_roles/create_or_update_member_role', [MembersController::class, 'create_or_update_member_role'])->middleware(['admin_route']);
            Route::delete('/members/remove_role/{roleId}', [MembersController::class, 'remove_role'])->middleware(['admin_route']);
        });

        Route::prefix('announcement')->group(function() {
            Route::match(['GET', 'POST'], '/get_announcements/get_announcements', [AnnouncementController::class, 'get_announcements']);
            Route::post('/get_announcements/create_or_update_announcement', [AnnouncementController::class, 'create_or_update_announcement'])->middleware(['admin_route']);
        });

        Route::prefix('aura_point_record')->group(function() {
            Route::match(['GET', 'POST'], '/aura_point_record/get_aura_point_records', [AuraPointRecordController::class, 'get_aura_point_records']);
            Route::post('/aura_point_record/modify_points', [AuraPointRecordController::class, 'modify_points'])->middleware(['admin_route']);
        });

        Route::prefix('border')->group(function() {
            Route::match(['GET', 'POST'], '/border/get_custom_borders', [BorderController::class, 'get_custom_borders'])->middleware(['admin_route']);
            Route::post('/border/create_or_update_custom_border', [BorderController::class, 'create_or_update_custom_border'])->middleware(['admin_route']);
            Route::post('/border/get_available_custom_borders', [BorderController::class, 'get_available_custom_borders']);
            Route::post('/border/set_as_my_custom_border', [BorderController::class, 'set_as_my_custom_border']);
            Route::post('/border/get_user_rare_borders', [BorderController::class, 'get_user_rare_borders']);
            Route::post('/border/get_user_new_rare_borders', [BorderController::class, 'get_user_new_rare_borders']);
            Route::post('/border/add_new_rare_borders', [BorderController::class, 'add_new_rare_borders']);
            Route::post('/border/remove_user_rare_borders', [BorderController::class, 'remove_user_rare_borders']);

            Route::delete('/border/remove_custom_border/{borderId}', [BorderController::class, 'remove_custom_border'])->middleware(['admin_route']);
        });

        Route::prefix('avatar')->group(function() {
            Route::get('/avatar/get_custom_avatars', [CustomAvatarController::class, 'get_custom_avatars'])->middleware(['admin_route']);
            Route::post('/avatar/create_or_update_custom_avatar', [CustomAvatarController::class, 'create_or_update_custom_avatar'])->middleware(['admin_route']);
            Route::post('/avatar/get_available_custom_avatars', [CustomAvatarController::class, 'get_available_custom_avatars']);
            Route::post('/avatar/set_as_my_custom_avatar', [CustomAvatarController::class, 'set_as_my_custom_avatar']);

            Route::delete('/avatar/remove_custom_avatar/{avatarId}', [CustomAvatarController::class, 'remove_custom_avatar'])->middleware(['admin_route']);
        });

        Route::prefix('photo-documentation')->group(function() {
            Route::post('/photo-documentation/get_photo_documentations', [PhotoDocumentationController::class, 'get_photo_documentations']);
            Route::post('/photo-documentation/submit_photo_documentation', [PhotoDocumentationController::class, 'submit_photo_documentation']);
        });
    });

    Route::prefix('member')->group(function() {
        Route::prefix('daily-activities')->group(function() {
            Route::get('/daily-activities/get_daily_activities', [DailyActivitiesController::class, 'get_daily_activities']);
            Route::post('/daily-activities/save_roulette_score', [DailyActivitiesController::class, 'save_roulette_score']);
        });

        Route::get('/friends/get_friends', [GetFriendsController::class, 'get_friends']);
        Route::get('/projects/get_assigned_projects', [GetProjectsController::class, 'get_assigned_projects']);
        Route::post('/projects/get_assigned_projects/apply_to_a_task', [GetProjectsController::class, 'apply_to_a_task']);
        Route::post('/projects/get_assigned_projects/cancel_application', [GetProjectsController::class, 'cancel_application']);
        Route::post('/projects/get_assigned_projects/get_project_available_tasks', [GetProjectsController::class, 'get_project_available_tasks']);
        Route::post('/projects/get_assigned_projects/get_applied_tasks', [GetProjectsController::class, 'get_applied_tasks']);
        Route::post('/projects/get_assigned_projects/get_task_progresses', [GetProjectsController::class, 'get_task_progresses']);
        Route::post('/projects/get_assigned_projects/submit_progress', [GetProjectsController::class, 'submit_progress']);
    });

    Route::get('/ping-user', function(Request $request) {
        $user = $request->user();
        $user->update(['last_seen_at' => now()]);
    });

    Route::get('/logout-user', [LogoutController::class, 'logout_user']);
});

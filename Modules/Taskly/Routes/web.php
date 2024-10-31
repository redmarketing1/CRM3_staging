<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Support\Facades\Route;
use Modules\Project\Entities\Project;
use Modules\Taskly\Entities\ActivityLog;
use Modules\Taskly\Entities\EstimateQuote;
use Modules\Taskly\Entities\ProjectClientFeedback;
use Modules\Taskly\Entities\ProjectComment;
use Modules\Taskly\Http\Controllers\ProjectController;
use Modules\Taskly\Http\Controllers\DashboardController;
use Modules\Taskly\Http\Controllers\ProjectReportController;
use Modules\Taskly\Http\Controllers\ProjectProgressController;
use Modules\Taskly\Http\Controllers\ProjectEstimationController;

Route::get('/asadc', function () {
    $projectsc = Project::whereNotNull('client_final_quote_id')->get();
    foreach ($projectsc as $c) {
        $id                  = $c->client_final_quote_id;
        $e                   = EstimateQuote::where('id', $id)->first();
        $e->final_for_client = 1;
        $e->save();
    }

    return 'success';
});

Route::get('/asadp', function () {
    $projectsc = Project::whereNotNull('sub_contractor_final_quote_id')->get();

    foreach ($projectsc as $c) {
        $id                          = $c->sub_contractor_final_quote_id;
        $e                           = EstimateQuote::where('id', $id)->first();
        $e->final_for_sub_contractor = 1;
        $e->save();
    }

    return 'success';
});

Route::get('/asadfeed', function () {
    $feedbacks = ProjectClientFeedback::whereNotNull('project_id')->get();

    foreach ($feedbacks as $c) {

        // ActivityLog::updateOrCreate([
        //     'user_id'    => creatorId(),
        //     'user_type'  => get_class(auth()->user()),
        //     'project_id' => $c->project_id,
        //     'log_type'   => 'Feedback Create',
        //     'remark'     => json_encode(['title' => 'Project feedback created.', 'feedback_id' => $c->id]),
        //     'created_at' => $c->created_at,
        //     'updated_at' => $c->updated_at,
        // ]);

        if (! empty($c->file)) {
            $feed       = ProjectClientFeedback::find($c->id);
            $path       = 'uploads/projects/' . $c->file;
            $feed->file = $path;
            $feed->save();

        }
    }

    return 'success';
});

Route::get('/asadcment', function () {
    $comments = ProjectComment::whereNotNull('project_id')->get();

    foreach ($comments as $c) {

        ActivityLog::create([
            'user_id'    => creatorId(),
            'user_type'  => get_class(auth()->user()),
            'project_id' => $c->project_id,
            'log_type'   => 'Comment Create',
            'remark'     => json_encode(['title' => 'Project comment posted.', 'project_comment_id' => $c->id]),
            'created_at' => $c->created_at,
            'updated_at' => $c->updated_at,
        ]);
    }

    return 'success';
});


Route::group(['middleware' => 'PlanModuleCheck:Taskly'], function () {

    // Route::get('dashboard/project', [DashboardController::class, 'index'])
    //     ->name('taskly.dashboard')
    //     ->middleware(['auth']);

    Route::resource('projects', 'ProjectController')
        ->middleware(['auth']);

    Route::get('/project/copy/{id}', [ProjectController::class, 'copyproject'])->name('project.copy')->middleware(['auth']);
    Route::post('/project/copy/store/{id}', [ProjectController::class, 'copyprojectstore'])->name('project.copy.store')->middleware(['auth']);

    Route::resource('stages', 'StageController')->middleware(['auth']);
    Route::post('projects/bulk_action', [ProjectController::class, 'bulk_action_projects'])->name('projects.bulk_action');
    Route::post('project/custom-status', [ProjectController::class, 'changeCustomStatus'])->name('project.custom-status');
    Route::post('project/all-data', [ProjectController::class, 'all_data'])->name('project.all_data');
    Route::get('projects-grid', [ProjectController::class, 'grid'])->name('projects.grid')->middleware(['auth']);
    Route::get('projects-map', [ProjectController::class, 'project_map'])->name('projects.map')->middleware(['auth']);

    Route::post('project/{id}/status_data', [ProjectController::class, 'status_data'])->name('project.add.status_data');
    //Route::post('project/{project}/status', [ProjectController::class, 'changeStatus'])->name('project.status');
    Route::post('project/custom-status', [ProjectController::class, 'changeCustomStatus'])->name('project.custom-status');


    //project import
    Route::get('project/import/export', [ProjectController::class, 'fileImportExport'])->name('project.file.import')->middleware(['auth']);
    Route::post('project/import', [ProjectController::class, 'fileImport'])->name('project.import')->middleware(['auth']);
    Route::get('project/import/modal', [ProjectController::class, 'fileImportModal'])->name('project.import.modal')->middleware(['auth']);
    Route::post('project/data/import/', [ProjectController::class, 'projectImportdata'])->name('project.import.data')->middleware(['auth']);

    //project Setting
    Route::get('project/setting/{id}', [ProjectController::class, 'CopylinkSetting'])->name('project.setting')->middleware(['auth']);
    Route::post('project/setting/save{id}', [ProjectController::class, 'CopylinkSettingSave'])->name('project.setting.save')->middleware(['auth']);

    Route::post('send-mail', [ProjectController::class, 'sendMail'])->name('send.mail')->middleware(['auth']);
    // Task Board
    Route::get('projects/{id}/task-board', [ProjectController::class, 'taskBoard'])->name('projects.task.board')->middleware(['auth']);
    Route::get('projects/{id}/task-board/create', [ProjectController::class, 'taskCreate'])->name('tasks.create')->middleware(['auth']);
    Route::post('projects/{id}/task-board', [ProjectController::class, 'taskStore'])->name('tasks.store')->middleware(['auth']);
    Route::post('projects/{id}/task-board/order-update', [ProjectController::class, 'taskOrderUpdate'])->name('tasks.update.order')->middleware(['auth']);
    Route::get('projects/{id}/task-board/edit/{tid}', [ProjectController::class, 'taskEdit'])->name('tasks.edit')->middleware(['auth']);
    Route::post('projects/{id}/task-board/{tid}/update', [ProjectController::class, 'taskUpdate'])->name('tasks.update')->middleware(['auth']);
    Route::delete('projects/{id}/task-board/{tid}', [ProjectController::class, 'taskDestroy'])->name('tasks.destroy')->middleware(['auth']);
    Route::get('projects/{id}/task-board/{tid}/{cid?}', [ProjectController::class, 'taskShow'])->name('tasks.show')->middleware(['auth']);
    Route::get('projects/{id}/task-board-list', [ProjectController::class, 'TaskList'])->name('projecttask.list')->middleware(['auth']);

    // Gantt Chart
    Route::get('projects/{id}/gantt/{duration?}', [ProjectController::class, 'gantt'])->name('projects.gantt')->middleware(['auth']);
    Route::post('projects/{id}/gantt', [ProjectController::class, 'ganttPost'])->name('projects.gantt.post')->middleware(['auth']);


    // bug report
    Route::get('projects/{id}/bug_report', [ProjectController::class, 'bugReport'])->name('projects.bug.report')->middleware(['auth']);
    Route::get('projects/{id}/bug_report/create', [ProjectController::class, 'bugReportCreate'])->name('projects.bug.report.create')->middleware(['auth']);
    Route::post('projects/{id}/bug_report', [ProjectController::class, 'bugReportStore'])->name('projects.bug.report.store')->middleware(['auth']);
    Route::post('projects/{id}/bug_report/order-update', [ProjectController::class, 'bugReportOrderUpdate'])->name('projects.bug.report.update.order')->middleware(['auth']);
    Route::get('projects/{id}/bug_report/{bid}/show', [ProjectController::class, 'bugReportShow'])->name('projects.bug.report.show')->middleware(['auth']);
    Route::get('projects/{id}/bug_report/{bid}/edit', [ProjectController::class, 'bugReportEdit'])->name('projects.bug.report.edit')->middleware(['auth']);
    Route::post('projects/{id}/bug_report/{bid}/update', [ProjectController::class, 'bugReportUpdate'])->name('projects.bug.report.update')->middleware(['auth']);
    Route::delete('projects/{id}/bug_report/{bid}', [ProjectController::class, 'bugReportDestroy'])->name('projects.bug.report.destroy')->middleware(['auth']);
    Route::get('projects/{id}/bug_report-list', [ProjectController::class, 'BugList'])->name('projectbug.list')->middleware(['auth']);


    Route::get('projects/invite/{id}', [ProjectController::class, 'popup'])->name('projects.invite.popup')->middleware(['auth']);
    Route::get('projects/share/{id}', [ProjectController::class, 'sharePopup'])->name('projects.share.popup')->middleware(['auth']);
    Route::get('projects/share/vender/{id}', [ProjectController::class, 'sharePopupVender'])->name('projects.share.vender.popup')->middleware(['auth']);
    Route::post('projects/share/vender/store/{id}', [ProjectController::class, 'sharePopupVenderStore'])->name('projects.share.vender')->middleware(['auth']);
    Route::get('projects/milestone/{id}', [ProjectController::class, 'milestone'])->name('projects.milestone')->middleware(['auth']);
    Route::post('projects/{id}/file', [ProjectController::class, 'fileUpload'])->name('projects.file.upload')->middleware(['auth']);
    Route::post('projects/share/{id}', [ProjectController::class, 'share'])->name('projects.share')->middleware(['auth']);


    // stages.index
    // project
    Route::get('projects/milestone/{id}', [ProjectController::class, 'milestone'])->name('projects.milestone')->middleware();
    Route::post('projects/milestone/{id}/store', [ProjectController::class, 'milestoneStore'])->name('projects.milestone.store')->middleware();
    Route::get('projects/milestone/{id}/show', [ProjectController::class, 'milestoneShow'])->name('projects.milestone.show')->middleware(['auth']);
    Route::get('projects/milestone/{id}/edit', [ProjectController::class, 'milestoneEdit'])->name('projects.milestone.edit')->middleware(['auth']);
    Route::post('projects/milestone/{id}/update', [ProjectController::class, 'milestoneUpdate'])->name('projects.milestone.update')->middleware(['auth']);
    Route::delete('projects/milestone/{id}', [ProjectController::class, 'milestoneDestroy'])->name('projects.milestone.destroy')->middleware(['auth']);
    Route::delete('projects/{id}/file/delete/{fid}', [ProjectController::class, 'fileDelete'])->name('projects.file.delete')->middleware(['auth']);


    // Route::get('project/{id}/comment', [ProjectController::class, 'projectComment'])->name('project.comment.create');
    // Route::get('project/{id}/comment/{cid}/reply', [ProjectController::class, 'projectCommentReply'])->name('project.comment.reply');
    Route::delete('project/{pid}/comment/{id}/destroy-attachment', [ProjectController::class, 'projectCommentDestroyAttachment'])->name('project.comment.destroy_attachment');
    Route::post('project/{id}/comment/delete', [ProjectController::class, 'projectCommentDelete'])->name('project.comment.delete');
    Route::post('project/{id}/get-comment', [ProjectController::class, 'getProjectComments'])->name('get.project.comment');


    Route::delete('project/{pid}/client/feedback/{id}/destroy-attachment', [ProjectController::class, 'projectClientFeedbackDestroyAttachment'])->name('project.client.feedback.destroy_attachment');
    Route::post('project/{id}/client/feedback/delete', [ProjectController::class, 'projectClientFeedbackDelete'])->name('project.client.feedback.delete');
    Route::post('project/{id}/client/get-feedback', [ProjectController::class, 'getProjectClientFeedback'])->name('get.project.client.feedback');


    Route::post('projects/invite/{id}/update', [ProjectController::class, 'invite'])->name('projects.invite.update')->middleware(['auth']);
    //Update Team Members
    Route::post('project/{project}/member', [ProjectController::class, 'addProjectTeamMember'])->name('project.member.add');

    Route::resource('bugstages', 'BugStageController')->middleware(['auth']);

    Route::post('projects/{id}/comment/{tid}/file/{cid?}', [ProjectController::class, 'commentStoreFile'])->name('comment.store.file');
    Route::delete('projects/{id}/comment/{tid}/file/{fid}', [ProjectController::class, 'commentDestroyFile'])->name('comment.destroy.file');
    Route::post('projects/{id}/comment/{tid}/{cid?}', [ProjectController::class, 'commentStore'])->name('comment.store');
    Route::delete('projects/{id}/comment/{tid}/{cid}', [ProjectController::class, 'commentDestroy'])->name('comment.destroy');
    Route::post('projects/{id}/sub-task/update/{stid}', [ProjectController::class, 'subTaskUpdate'])->name('subtask.update');
    Route::post('projects/{id}/sub-task/{tid}/{cid?}', [ProjectController::class, 'subTaskStore'])->name('subtask.store');
    Route::delete('projects/{id}/sub-task/{stid}', [ProjectController::class, 'subTaskDestroy'])->name('subtask.destroy');

    Route::post('projects/{id}/bug_comment/{tid}/file/{cid?}', [ProjectController::class, 'bugStoreFile'])->name('bug.comment.store.file');
    Route::delete('projects/{id}/bug_comment/{tid}/file/{fid}', [ProjectController::class, 'bugDestroyFile'])->name('bug.comment.destroy.file');
    Route::post('projects/{id}/bug_comment/{tid}/{cid?}', [ProjectController::class, 'bugCommentStore'])->name('bug.comment.store');
    Route::delete('projects/{id}/bug_comment/{tid}/{cid}', [ProjectController::class, 'bugCommentDestroy'])->name('bug.comment.destroy');
    Route::delete('projects/{id}/client/{uid}', [ProjectController::class, 'clientDelete'])->name('projects.client.delete')->middleware(['auth']);
    Route::delete('projects/{id}/user/{uid}', [ProjectController::class, 'userDelete'])->name('projects.user.delete')->middleware(['auth']);
    Route::delete('projects/{id}/vendor/{uid}', [ProjectController::class, 'vendorDelete'])->name('projects.vendor.delete')->middleware(['auth']);

    // Project Estimations
    Route::get('estimations', [ProjectEstimationController::class, 'index'])->name('estimations.index');
    Route::post('estimations/bulk_delete', [ProjectEstimationController::class, 'bulkDeleteProjectEstimation'])->name('estimations.bulk_delete');
    Route::post('estimations/all-data', [ProjectEstimationController::class, 'all_data'])->name('estimations.all_data');
    Route::get('estimations/create/{project_id}/page', [ProjectEstimationController::class, 'create'])->name('estimations.create.page');
    Route::get('estimations/{id}/setup', [ProjectEstimationController::class, 'setup'])->name('estimations.setup.estimate');
    Route::get('estimations/{projectEstimation}/change-status/{id}', [ProjectEstimationController::class, 'changeStatus'])->name('estimations.changeStatus');
    Route::post('estimations/importdata', [ProjectEstimationController::class, 'ImportEstimationsData'])->name('estimations.importdata');
    Route::post('estimations/store_import', [ProjectEstimationController::class, 'StoreImportEstimations'])->name('estimations.store_import');
    Route::get('estimations/{id}/export-excel/{type}', [ProjectEstimationController::class, 'exportEstimationInExcel'])->name('estimation.export.excel');
    Route::get('estimations/{id}/export-csv/{type}', [ProjectEstimationController::class, 'exportEstimationInCSV'])->name('estimation.export.csv');
    Route::get('estimations/{id}/export-gaeb/{type}', [ProjectEstimationController::class, 'exportEstimationInGaeb'])->name('estimation.export.gaeb');
    Route::post('estimations/remove-items', [ProjectEstimationController::class, 'remove_items'])->name('estimations.remove_items.estimate');
    Route::post('estimations/reorder-group-modal', [ProjectEstimationController::class, 'reorder_group_modal'])->name('estimations.reorder_group_modal');
    Route::post('estimations/markup', [ProjectEstimationController::class, 'calculateMarkup'])->name('estimations.markup.calculate');
    Route::post('estimations/quote/final', [ProjectEstimationController::class, 'finalizeQuote'])->name('estimations.quote.final');
    Route::post('estimations/quote/duplicate/edit', [ProjectEstimationController::class, 'editClone'])->name('estimations.duplicate.quote.edit');
    Route::post('estimations/quote/duplicate', [ProjectEstimationController::class, 'clone'])->name('estimations.duplicate.quote');
    Route::get('estimations/{id}/finalize', [ProjectEstimationController::class, 'finalizeEstimation'])->name('estimations.finalize.estimate');
    Route::post('estimations/quote/send-client', [ProjectEstimationController::class, 'sendClient'])->name('estimations.quote.send.client');
    Route::post('estimations/create-imageszip', [ProjectEstimationController::class, 'createEstimationImagesZip'])->name('estimation.create.imageszip');
    Route::post('estimations/save-finalize', [ProjectEstimationController::class, 'saveFinalize'])->name('estimations.save_finalize.estimate');
    Route::post('estimations/update-grpname', [ProjectEstimationController::class, 'updateGrpname'])->name('estimations.updateGrpname');
    Route::post('estimations/save-estimation-title', [ProjectEstimationController::class, 'saveEstimationTitle'])->name('estimations.saveEstimationTitle');
    Route::post('estimations/update-pos', [ProjectEstimationController::class, 'updateEstimationPos'])->name('update.estimation.pos');
    Route::post('estimations/pos-ordering', [ProjectEstimationController::class, 'handlePosSaveOrder'])->name('estimations.pos.ordering');
    Route::post('estimations/quote/delete', [ProjectEstimationController::class, 'deleteQuote'])->name('estimations.delete.quote');
    Route::post('estimations/add-item', [ProjectEstimationController::class, 'add_item'])->name('estimations.add_item.estimate');
    Route::post('estimations/add-comment', [ProjectEstimationController::class, 'add_comment'])->name('estimations.add_comment.estimate');
    Route::post('estimations/store-group-reorder', [ProjectEstimationController::class, 'store_group_reorder'])->name('estimations.store_group_reorder');
    Route::post('estimations/call-ai-smart-template-new', [ProjectEstimationController::class, 'callAiSmartTemplate_new'])->name('estimations.call-ai-smart-template-new');
    Route::post('estimations/replace-ai-desc', [ProjectEstimationController::class, 'replace_ai_desc'])->name('estimations.replace_ai_desc.estimate');
    Route::match(['get', 'post'], 'smart-progress', [ProjectEstimationController::class, 'queuesProgress'])->name('smart.progress');
    Route::post('estimations/cancel_queue/{id}', [ProjectEstimationController::class, 'cancel_queue'])->name('estimations.cancel_queue');
    Route::get('estimations/copy/{id}', [ProjectEstimationController::class, 'copyEstimation'])->name('estimations.copy');
    Route::post('estimations/copy/store/{id}', [ProjectEstimationController::class, 'copyEstimationStore'])->name('estimations.copy.store');
    Route::delete('estimations/delete/{id}', [ProjectEstimationController::class, 'deleteProjectEstimation'])->name('estimations.deleteEstimation');
    Route::get('allowedUsers/{estimation_id}', [ProjectEstimationController::class, 'estimationAllowedUsers'])->name('estimation.allowedUsers');
    Route::post('users/{estimation_id}', [ProjectEstimationController::class, 'storeUsers'])->name('estimation.users.store');
    Route::post('estimation/remove-estimation-user', [ProjectEstimationController::class, 'remove_estimation_user'])->name('estimation.remove_estimation_user');

    //Project Progress Routes
    Route::get('project/{project_id}/project-progress', [ProjectController::class, 'project_progress'])->name('project.project_progress');
    Route::post('estimation/progress/sign/update', [ProjectProgressController::class, 'update'])->name('progress.sign.store');
    Route::post('progress/estimation/item', [ProjectProgressController::class, 'estimationItem'])->name('progress.estimation.item');
    Route::post('estimation/progress/delete-files', [ProjectProgressController::class, 'deleteProgressFiles'])->name('progress.files.delete');
    Route::post('estimation/progress/file', [ProjectProgressController::class, 'progressFileStore'])->name('progress.file.store');
    Route::get('estimations/{id}/progress-finalize', [ProjectProgressController::class, 'progressFinalize'])->name('progress.finalize');
    Route::post('estimations/progress-finalize/send-client', [ProjectProgressController::class, 'sendProgressFinalizeClient'])->name('progress.finalize.send.client');
    //Progress Invoices
    Route::get('progress/invoice/{progress_id}',[ProjectProgressController::class, 'progress_invoice'])->name('project.progress.invoice');
    Route::get('progress/view-invoice/{progress_id}',[ProjectProgressController::class, 'view_progress_invoice'])->name('project.progress.viewInvoice');

    // Project Progress
    Route::post('progress/list', [ProjectProgressController::class, 'list'])->name('progress.list');

    // Project Report
    Route::resource('project_report', 'ProjectReportController')->middleware(['auth']);
    Route::post('project_report_data', [ProjectReportController::class, 'ajax_data'])->name('projects.ajax')->middleware(['auth']);
    Route::post('project_report/tasks/{id}', [ProjectReportController::class, 'ajax_tasks_report'])->name('tasks.report.ajaxdata')->middleware(['auth']);

    Route::get('project/{project_id}/edit/{field}', [ProjectController::class, 'edit_form'])->name('projects.edit_form');
    Route::post('project/{project_id}/update_details/{field}', [ProjectController::class, 'update_details'])->name('project.update_details');
    Route::post('project/{project_id}/get_all_address', [ProjectController::class, 'get_all_address'])->name('project.get_all_address');
});
Route::get('projects/{id}/file/{fid}', [ProjectController::class, 'fileDownload'])->name('projects.file.download');

Route::post('project/password/check/{id}/{lang?}', [ProjectController::class, 'PasswordCheck'])->name('project.password.check');
Route::get('project/shared/link/{id}/{lang?}', [ProjectController::class, 'ProjectSharedLink'])->name('project.shared.link');
Route::get('projects/{id}/link/task/show/{tid}/', [ProjectController::class, 'ProjectLinkTaskShow'])->name('Project.link.task.show');
Route::get('projects/{id}/link/bug_report/{bid}/show', [ProjectController::class, 'ProjectLinkbugReportShow'])->name('projects.link.bug.report.show');
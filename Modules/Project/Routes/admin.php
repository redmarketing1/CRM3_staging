<?php

use Illuminate\Support\Facades\Route;
use Modules\Project\Http\Controllers\ProjectFilesController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/

/**
 * Project Dashboard.
 */
Route::get('dashboard/project', 'ProjectDashboardController@index')
    ->name('taskly.dashboard')
    ->middleware(['auth']);

Route::prefix('project')->group(function () {

    Route::resource('maps', 'ProjectMapController')
        ->names('project.map');

    Route::resource('{id}/feedback', 'ProjectFeedbackController')
        ->names('project.feedback');

    Route::resource('{id}/comment', 'ProjectCommentController')
        ->names('project.comment');

    Route::resource('{project}/delay', 'ProjectDelayController')
        ->names('project.delay');

    Route::resource('{project}/contactDetails', 'ProjectContactDetails')
        ->names('project.contactDetails');

    Route::resource('/', 'ProjectController')
        ->parameters(['' => 'project'])
        ->names('project');
});

//files
Route::post('project/{id}/files-upload', [ProjectFilesController::class, 'fileUpload'])->name('project.files_upload');
Route::post('project/{id}/files', [ProjectFilesController::class, 'all_files'])->name('project.all_files');
Route::get('project/{pid}/file-edit/{id}', [ProjectFilesController::class, 'fileEdit'])->name('project.file.edit');
Route::post('project/{id}/set-default-file', [ProjectFilesController::class, 'set_default_file'])->name('project.files.set_default_file');
Route::post('project/{id}/delete-files', [ProjectFilesController::class, 'delete_files'])->name('project.files.delete');
// Route::resource('project', 'ProjectController');
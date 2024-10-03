<?php

use Illuminate\Support\Facades\Route;

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

Route::prefix('project')->group(function () {

    Route::resource('/', 'ProjectController')->parameters(['' => 'project'])->names('project');

    Route::resource('{id}/feedback', 'ProjectFeedbackController')
        ->names('project.feedback');

    Route::resource('{id}/comment', 'ProjectCommentController')
        ->names('project.comment');

    Route::resource('{project}/delay', 'ProjectDelayController')
        ->names('project.delay');

});

// Route::resource('project', 'ProjectController');
<?php

use Illuminate\Support\Facades\Route;

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

Route::prefix('project')->group(function () {

    // ChatGPT : Route::resource('/', 'ProjectController')->parameters(['' => 'project'])->names('project');

    Route::resource('{id}/feedback', 'ProjectFeedbackController')
        ->names('project.feedback');

    Route::resource('{id}/comment', 'ProjectCommentController')
        ->names('project.comment');

    Route::get('tables', 'ProjectController@dataTables');

});

Route::resource('project', 'ProjectController');
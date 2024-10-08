<?php

use Illuminate\Support\Facades\Route;
use Modules\Project\Http\Controllers\TestController;

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


Route::get('add_team_permissions', [TestController::class, 'AddTeamTeamPermissions']);

Route::get('add_client_permissions', [TestController::class, 'AddClientermissions']);

// do not run
//Route::get('add_label_permissions', [TestController::class, 'AddLabelsPermissions']);

Route::get('add_files_permissions', [TestController::class, 'AddFilesPermissions']);

Route::get('add_project_progress_permissions', [TestController::class, 'AddProjectProgressPermissions']);

//Activity Log Permissions
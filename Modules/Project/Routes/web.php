<?php

use Illuminate\Support\Facades\Route;
use Modules\Project\Http\Controllers\ProjectController;

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


Route::middleware(['auth'])->group(function () {
    require module_path('Project', 'Routes/admin.php');

    Route::get('project/{project}/add/delay', [ProjectController::class, 'addProjectDelay'])->name('project.addProjectDelay');
    Route::post('project/{id}/delay/announcement', [ProjectController::class, 'delayAnnouncement'])->name('project.delay.announcement.store');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Backup\Http\Controllers\BackupController;


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Admin routes for your application.
|
*/

Route::resource('backup', BackupController::class);
Route::post('/start-backup', [BackupController::class, 'startBackup']);
Route::get('/check-backup-status/{id}', [BackupController::class, 'checkBackupStatus']);
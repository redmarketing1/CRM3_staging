<?php

namespace Modules\Backup\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Modules\Backup\Entities\BackupQueue;
use Modules\Backup\Jobs\BackupJob;

class BackupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('backup::index');
    }



    public function manualBackup(Request $request)
    {
        $backupType = $request->input('backup_type');

        if ($backupType == 'files') {
            Artisan::call('backup:run --only-files');
        } elseif ($backupType == 'database') {
            Artisan::call('backup:run --only-db');
        } else {
            Artisan::call('backup:run');
        }

        return redirect()->back()->with('status', 'Backup initiated!');
    }

    public function startBackup(Request $request)
    {
        // Create a backup record
        $backup = BackupQueue::create([
            'status' => 'pending',
        ]);

        // Dispatch the backup job
        BackupJob::dispatch($backup->id);

        // Return the backup ID so we can track it
        return response()->json(['backupId' => $backup->id]);
    }

    // Check backup status
    public function checkBackupStatus($id)
    {
        $backup = BackupQueue::find($id);

        if ($backup) {
            return response()->json(['status' => $backup->status]);
        }

        return response()->json(['status' => 'not-found']);
    }
}
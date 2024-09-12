<?php

namespace Modules\Backup\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use Modules\Backup\Entities\BackupQueue;

class BackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $backupId;

    public function __construct($backupId)
    {
        $this->backupId = $backupId;
    }

    public function handle()
    {
        // Mark backup as in-progress
        $backup         = BackupQueue::find($this->backupId);
        $backup->status = 'in-progress';
        $backup->save();

        // Run the backup (you can modify this for files/db only)
        Artisan::call('backup:run --only-db');

        // Mark backup as completed
        $backup->status = 'completed';
        $backup->save();
    }
}
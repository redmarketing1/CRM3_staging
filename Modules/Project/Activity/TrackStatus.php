<?php

namespace Modules\Project\Activity;

use Modules\Project\Entities\Project;
use Modules\Taskly\Entities\ActivityLog;

class TrackStatus
{
    private Project $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function track()
    {
        if ($this->project->isDirty('status')) {
            $this->store();
        }
    }

    protected function store() : void
    {
        $user      = auth()->user();
        $oldStatus = $this->project->getOriginal('status');
        $newStatus = $this->project->status;

        ActivityLog::create([
            'user_id'    => $user->id,
            'user_type'  => get_class($user),
            'project_id' => $this->project->id,
            'log_type'   => 'Status Changed',
            'remark'     => json_encode([
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus,
            ]),
        ]);
    }
}



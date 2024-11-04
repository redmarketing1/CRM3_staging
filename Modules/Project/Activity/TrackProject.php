<?php

namespace Modules\Project\Activity;

use Modules\Project\Entities\Project;
use Modules\Project\Entities\ProjectActivityLogs;

class TrackProject
{
    /**
     * Summary of project
     * @var Project
     */
    private Project $project;

    /**
     * Those fields will track activity
     * @var array
     */
    private array $trackableFields = [
        'status'     => [
            'log_type' => 'Status Changed',
            'oldKey'   => 'oldStatus',
            'newKey'   => 'newStatus',
        ],
        'name'       => [
            'log_type' => 'Name Changed',
            'oldKey'   => 'oldName',
            'newKey'   => 'newName',
        ],
        'start_date' => [
            'log_type' => 'Start Date Changed',
            'oldKey'   => 'oldStartDate',
            'newKey'   => 'newStartDate',
        ],
        'end_date'   => [
            'log_type' => 'End Date Changed',
            'oldKey'   => 'oldEndDate',
            'newKey'   => 'newEndDate',
        ],
    ];

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function track()
    {
        foreach ($this->trackableFields as $field => $info) {
            if ($this->project->isDirty($field)) {
                $this->logChange($field,
                    $info['log_type'],
                    $info['oldKey'],
                    $info['newKey'],
                );
            }
        }
    }

    protected function logChange(string $field, string $logType, string $oldKey, string $newKey) : void
    {
        $user     = auth()->user();
        $oldValue = $this->project->getOriginal($field);
        $newValue = $this->project->$field;

        ProjectActivityLogs::create([
            'user_id'    => $user->id,
            'user_type'  => get_class($user),
            'project_id' => $this->project->id,
            'log_type'   => $logType,
            'remark'     => json_encode([
                $oldKey => $oldValue,
                $newKey => $newValue,
            ]),
        ]);
    }
}

<?php

namespace Modules\Project\Entities;

use Modules\Project\Activity\LogType;
use Illuminate\Database\Eloquent\Model;
use Modules\Project\Activity\ActivityTemplate;

class ProjectActivityLogs extends Model
{
    use ActivityTemplate;

    protected $fillable = [
        'user_id',
        'project_id',
        'log_type',
        'remark',
    ];

    protected $casts = [
        'log_type' => LogType::class,
        'remark'   => 'json',
    ];

    public function getRemark()
    {
        $remark = $this->remark;

        return match ($this->log_type) {
            LogType::UPLOAD_FILE        => $this->userActionRemark('Upload new file', $remark['file_name']),
            LogType::CREATE_TIMESHEET   => $this->userActionRemark('Create new Timesheet'),
            LogType::CREATE_BUG         => $this->userActionRemark('Create new Bug', $remark['title']),
            LogType::MOVE_BUG           => $this->moveRemark('Bug', $remark),
            LogType::INVITE_USER        => $this->inviteUserRemark($remark['user_id']),
            LogType::SHARE_WITH_CLIENT  => $this->shareWithClientRemark($remark['client_id']),
            LogType::CREATE_TASK        => $this->userActionRemark('Create new Task', $remark['title']),
            LogType::MOVE               => $this->moveRemark('Task', $remark),
            LogType::CREATE_MILESTONE   => $this->userActionRemark('Create new Milestone', $remark['title']),
            LogType::FEEDBACK_CREATE    => $this->feedback($remark),
            LogType::COMMENT_CREATE     => $this->comment($remark),
            LogType::STATUS_CHANGED     => $this->status($remark),
            LogType::NAME_CHANGED       => $this->name($remark),
            LogType::START_DATE_CHANGED => $this->startDate($remark),
            LogType::END_DATE_CHANGED   => $this->endDate($remark),
            default                     => $this->remark,
        };
    }

    /**
     * Accessor for log_type to return details from the enum.
     *
     * @return array|null
     */
    public function getLogTypesAttribute()
    {
        $value = $this->log_type->value;
        return LogType::tryFrom($value)?->details();
    }
}

<?php

namespace Modules\Project\Activity;

enum LogType: string
{
    case UPLOAD_FILE        = 'Upload File';
    case CREATE_MILESTONE   = 'Create Milestone';
    case CREATE_TASK        = 'Create Task';
    case CREATE_BUG         = 'Create Bug';
    case MOVE               = 'Move';
    case MOVE_BUG           = 'Move Bug';
    case CREATE_INVOICE     = 'Create Invoice';
    case INVITE_USER        = 'Invite User';
    case SHARE_WITH_CLIENT  = 'Share with Client';
    case CREATE_TIMESHEET   = 'Create Timesheet';
    case COMMENT_CREATE     = 'Comment Create';
    case COMMENT_UPDATE     = 'Comment Update';
    case FEEDBACK_CREATE    = 'Feedback Create';
    case STATUS_CHANGED     = 'Status Changed';
    case NAME_CHANGED       = 'Name Changed';
    case Date_CHANGED       = 'Date Changed';
    case END_DATE_CHANGED   = 'End Date Changed';
    case START_DATE_CHANGED = 'Start Date Changed';
    case CREATE_NOTES       = 'Create Notes';

    /**
     * Method to provide additional properties for each case
     * @return array
     */
    public function details() : array
    {
        return match ($this) {
            self::UPLOAD_FILE       => [
                'type'        => 'file',
                'icon'        => 'fa-file',
                'label'       => __('File'),
            ],
            self::CREATE_MILESTONE  => [
                'type'   => 'milestone',
                'icon'   => 'fa-cubes',
                'label'  => __('Milestone'),
            ],
            self::CREATE_TASK       => [
                'type'        => 'task',
                'icon'        => 'fa-tasks',
                'label'       => __('Task'),
            ],
            self::CREATE_BUG        => [
                'type'         => 'bug',
                'icon'         => 'fa-bug',
                'label'        => __('Bug'),
            ],
            self::MOVE              => [
                'type'               => 'move',
                'icon'               => 'fa-align-justify',
                'label'              => __('Status'),
            ],
            self::MOVE_BUG          => [
                'type'           => 'move',
                'icon'           => 'fa-align-justify',
                'label'          => __('Bug status'),
            ],
            self::CREATE_INVOICE    => [
                'type'     => 'invoice',
                'icon'     => 'fa-file-invoice',
                'label'    => __('Invoice'),
            ],
            self::INVITE_USER       => [
                'type'        => 'user',
                'icon'        => 'fa-plus',
                'label'       => __('User'),
            ],
            self::SHARE_WITH_CLIENT => [
                'type'  => 'share',
                'icon'  => 'fa-share',
                'label' => __('Shared'),
            ],
            self::CREATE_TIMESHEET  => [
                'type'   => 'time',
                'icon'   => 'fa-clock-o',
                'label'  => __('Time'),
            ],
            self::COMMENT_CREATE    => [
                'type'     => 'comment',
                'icon'     => 'fa-comments',
                'label'    => __('Comment'),
            ],
            self::FEEDBACK_CREATE   => [
                'type'    => 'feedback',
                'icon'    => 'fa-message',
                'label'   => __('Mail'),
            ],
            self::STATUS_CHANGED    => [
                'type'     => 'status',
                'icon'     => 'fa-exchange-alt',
                'label'    => __('Status Changed'),
            ],
            default                 => [
                'type'                  => 'status',
                'icon'                  => 'fa-exchange-alt',
                'label'                 => __('Status Changed'),
            ],
        };
    }
}
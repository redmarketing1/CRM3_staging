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
    case FEEDBACK_CREATE    = 'Feedback Create';
    case STATUS_CHANGED     = 'Status Changed';
    case NAME_CHANGED       = 'Name Changed';
    case Date_CHANGED       = 'Date Changed';
    case END_DATE_CHANGED   = 'End Date Changed';
    case START_DATE_CHANGED = 'Start Date Changed';
}

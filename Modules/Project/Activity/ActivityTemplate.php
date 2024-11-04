<?php

namespace Modules\Project\Activity;

use App\Models\User;
use Modules\Lead\Entities\Label;
use Modules\Project\Activity\LogType;
use Modules\Taskly\Entities\ProjectComment;
use Modules\Taskly\Entities\ProjectClientFeedback;

trait ActivityTemplate
{
    public function getStatusClass() : string
    {
        return match ($this->log_type) {
            LogType::UPLOAD_FILE             => 'status-file',
            LogType::CREATE_MILESTONE        => 'status-milestone',
            LogType::CREATE_TASK             => 'status-task',
            LogType::CREATE_BUG              => 'status-bug',
            LogType::MOVE, LogType::MOVE_BUG => 'status-move',
            LogType::CREATE_INVOICE          => 'status-invoice',
            LogType::INVITE_USER             => 'status-user',
            LogType::SHARE_WITH_CLIENT       => 'status-share',
            LogType::CREATE_TIMESHEET        => 'status-time',
            LogType::COMMENT_CREATE          => 'status-comment',
            LogType::FEEDBACK_CREATE         => 'status-feedback',
            LogType::STATUS_CHANGED          => 'status-changed',
            LogType::NAME_CHANGED            => 'name-changed',
            default                          => 'status-default',
        };
    }

    public function getStatusIcon() : string
    {
        return match ($this->log_type) {
            LogType::UPLOAD_FILE                                   => 'fas fa-file',
            LogType::CREATE_MILESTONE                              => 'fas fa-cubes',
            LogType::CREATE_TASK                                   => 'fas fa-tasks',
            LogType::CREATE_BUG                                    => 'fas fa-bug',
            LogType::MOVE, LogType::MOVE_BUG                       => 'fas fa-align-justify',
            LogType::CREATE_INVOICE                                => 'fas fa-file-invoice',
            LogType::INVITE_USER                                   => 'fas fa-plus',
            LogType::SHARE_WITH_CLIENT                             => 'fas fa-share',
            LogType::CREATE_TIMESHEET                              => 'fas fa-clock-o',
            LogType::COMMENT_CREATE                                => 'fas fa-comments',
            LogType::FEEDBACK_CREATE                               => 'fas fa-message',
            LogType::STATUS_CHANGED                                => 'fas fa-exchange-alt',
            LogType::NAME_CHANGED                                  => 'fas fa-pen-to-square',
            LogType::END_DATE_CHANGED, LogType::START_DATE_CHANGED => 'fas fa-th',
            default                                                => 'fas fa-question',
        };
    }

    /**
     * Format user actions with optional title.
     */
    protected function userActionRemark(string $action, string $title = '') : string
    {
        return __($action) . ($title ? " <b>{$title}</b>" : '');
    }

    /**
     * Format move actions with title, old, and new statuses.
     */
    protected function moveRemark(string $itemType, array $remarkData) : string
    {
        return __("Move {$itemType}") .
            " <b>{$remarkData['title']}</b> " . __('from') .
            " " . __(ucwords($remarkData['old_status'])) .
            " " . __('to') . " " . __(ucwords($remarkData['new_status']));
    }

    /**
     * Format invite user action.
     */
    protected function inviteUserRemark(int $userId) : string
    {
        $inviteUser = User::find($userId);
        return __('Invite new User') .
            ' <b>' . ($inviteUser ? $inviteUser->name : __('Unknown')) . '</b>';
    }

    /**
     * Format share with client action.
     */
    protected function shareWithClientRemark(int $clientId) : string
    {
        $inviteClient = User::find($clientId);
        return __('Share Project with Client') .
            ' <b>' . ($inviteClient ? $inviteClient->name : __('Unknown')) . '</b>';
    }

    protected function comment($remark)
    {
        if (empty($remark['project_comment_id']))
            return;

        $commentID      = $remark['project_comment_id'];
        $projectComment = ProjectComment::with('commentUser')->find($commentID);
        $name           = e($projectComment->commentUser->name);

        $html = '';
        $html .= $projectComment->comment;

        if ($projectComment->file) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension   = pathinfo($projectComment->file, PATHINFO_EXTENSION);

            // Handle image files
            if (in_array(strtolower($fileExtension), $imageExtensions)) {
                $image = asset($projectComment->file);
                $html .= '<a class="lightbox-link" href="' . e($image) . '" data-lightbox="gallery" data-title="Image placeholder">
                            <img alt="Image placeholder" src="' . e($image) . '" class="img-thumbnail my-3" 
                                style="display: block;max-width: 200px;max-height: 140px;">
                        </a>';
            }
            // Handle other file types (non-images)
            else {
                $file = asset(rawurlencode($projectComment->file));
                $html .= '<a href="' . e($file) . '" class="" 
                        data-bs-toggle="tooltip" target="_blank" title="' . __('Download') . '">
                        ' . e(basename($projectComment->file)) . '
                        </a>';
            }
        }

        return $html;
    }

    protected function feedback($remark)
    {
        if (empty($remark['feedback_id']))
            return;

        $feedbackID            = $remark['feedback_id'];
        $projectClientFeedback = ProjectClientFeedback::with('feedbackUser')->find($feedbackID);
        $name                  = e($projectClientFeedback->feedbackUser->name);

        $html = '';
        // Don't escape feedback content if you want HTML to render
        // $html = '<p>Feedback was created by <b class="d-inline text-secondary text-sm">' . $name . '</b></p>';
        $html .= $projectClientFeedback->feedback;  // Allow HTML content

        if ($projectClientFeedback->file) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension   = pathinfo($projectClientFeedback->file, PATHINFO_EXTENSION);

            if (in_array(strtolower($fileExtension), $imageExtensions)) {
                $image = get_file('uploads/projects/' . $projectClientFeedback->file);
                $html .= '<a class="lightbox-link" href="' . $image . '" data-lightbox="gallery" data-title="Image placeholder">
                            <img alt="Image placeholder" src="' . $image . '" class="img-thumbnail my-3" 
                                style="display: block;max-width: 200px;max-height: 140px;">
                        </a>';
            } else {
                $file = get_file('uploads/projects/' . rawurlencode($projectClientFeedback->file));
                $html .= '<a href="' . $file . '" class="" 
                    data-bs-toggle="tooltip" target="_blank" title="' . __('Download') . '">
                    ' . $projectClientFeedback->file . '
                    </a>';
            }


        }

        return $html;
    }

    protected function status(array $remark) : string
    {
        $projectLabel = collect(Label::get_project_dropdowns()['project_status']);

        $oldStatus = $projectLabel->firstWhere('id', $remark['oldStatus']);
        $newStatus = $projectLabel->firstWhere('id', $remark['newStatus']);

        return trans('	Project status changed from ":old" to ":new"', [
            'old' => $oldStatus->name ?? __('unknown'),
            'new' => $newStatus->name ?? __('unknown'),
        ]);
    }

    protected function name(array $remark) : string
    {
        return trans('Project name changed from ":old" to ":new"', [
            'old' => $remark['oldName'] ?? __('unknown'),
            'new' => $remark['newName'] ?? __('unknown'),
        ]);
    }

    protected function startDate(array $remark) : string
    {
        return trans('Project start date changed from ":old" to ":new"', [
            'old' => $remark['oldStartDate'] ?? __('unknown'),
            'new' => $remark['newStartDate'] ?? __('unknown'),
        ]);
    }

    protected function endDate(array $remark) : string
    {
        return trans('Project end date changed from ":old" to ":new"', [
            'old' => $remark['oldEndDate'] ?? __('unknown'),
            'new' => $remark['newEndDate'] ?? __('unknown'),
        ]);
    }
}
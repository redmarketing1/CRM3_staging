<?php

namespace Modules\Project\Entities;

use App\Models\User;
use Modules\Lead\Entities\Label;
use Illuminate\Database\Eloquent\Model;
use Modules\Taskly\Entities\ProjectComment;
use Modules\Taskly\Entities\ProjectClientFeedback;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'project_id',
        'log_type',
        'remark',
    ];

    public static $user_name;

    public function getRemark()
    {
        $remark = json_decode($this->remark, true);
        if (is_array($remark)) {
            if ($this->user_name != null) {
                $user            = $this->user;
                $this->user_name = $user ? $user->name : '';
            }

            switch ($this->log_type) {
                case 'Upload File':
                    return $this->user_name . ' ' . __('Upload new file') . ' <b>' . $remark['file_name'] . '</b>';
                case 'Create Timesheet':
                    return $this->user_name . " " . __('Create new Timesheet');
                case 'Create Bug':
                    return $this->user_name . ' ' . __('Create new Bug') . " <b>" . $remark['title'] . "</b>";
                case 'Move Bug':
                    return $this->user_name . " " . __('Move Bug') . " <b>" . $remark['title'] . "</b> " . __('from') . " " . __(ucwords($remark['old_status'])) . " " . __('to') . " " . __(ucwords($remark['new_status']));
                case 'Invite User':
                    $inviteUser = User::find($remark['user_id']);
                    return $this->user_name . ' ' . __('Invite new User') . ' <b>' . ($inviteUser ? $inviteUser->name : '') . '</b>';
                case 'Share with Client':
                    $inviteClient = User::find($remark['client_id']);
                    return $this->user_name . ' ' . __('Share Project with Client') . ' <b>' . ($inviteClient ? $inviteClient->name : '') . '</b>';
                case 'Create Task':
                    return $this->user_name . ' ' . __('Create new Task') . " <b>" . $remark['title'] . "</b>";
                case 'Move':
                    return $this->user_name . " " . __('Move Task') . " <b>" . $remark['title'] . "</b> " . __('from') . " " . __(ucwords($remark['old_status'])) . " " . __('to') . " " . __(ucwords($remark['new_status']));
                case 'Create Milestone':
                    return $this->user_name . " " . __('Create new Milestone') . " <b>" . $remark['title'] . "</b>";
                case 'Feedback Create':
                    return $this->feedback($remark['feedback_id']);
                case 'Comment Create':
                    return $this->comment($remark['project_comment_id']);
                case 'Status Changed':
                    return $this->status($remark);
                case 'Name Changed':
                    return $this->name($remark);
                case 'Start Date Changed':
                    return $this->startDate($remark);
                case 'End Date Changed':
                    return $this->endDate($remark);
            }
        } else {
            return $this->remark;
        }
    }

    protected function comment($commentID)
    {
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

    protected function feedback($feedbackID)
    {
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

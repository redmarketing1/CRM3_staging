<?php

namespace Modules\Taskly\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Taskly\Entities\ProjectClientFeedback;
  
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'user_type', 'project_id', 'log_type', 'remark',
    ];

    public static $user_name;

    public function getRemark()
    {
        $remark = json_decode($this->remark, true);
        if (is_array($remark)) {
            if ($this->user_name != null) {
                $user = $this->user;
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
            }
        } else {
            return $this->remark;
        }
    }

    protected function comment($commentID)
    {
        $projectComment = ProjectComment::with('commentUser')->find($commentID);
        $name = e($projectComment->commentUser->name);

        // Don't escape comment content if you want HTML to render
        $html = '<p>Comment was created by <b class="d-inline text-secondary text-sm">' . $name . '</b></p>';
        $html .= $projectComment->comment;  // Allow HTML content

        if ($projectComment->file) {
            $image = asset($projectComment->file);
            $html .= '<a class="lightbox-link" href="' . e($image) . '" data-lightbox="gallery" data-title="Image placeholder">
                <img alt="Image placeholder" src="' . e($image) . '" class="img-thumbnail my-3" style="display: block;max-width: 200px;max-height: 140px;">
              </a>';
        }

        return $html;
    }

    protected function feedback($feedbackID)
    {
        $projectClientFeedback = ProjectClientFeedback::with('feedbackUser')->find($feedbackID);
        $name = e($projectClientFeedback->feedbackUser->name);

        // Don't escape feedback content if you want HTML to render
        $html = '<p>Feedback was created by <b class="d-inline text-secondary text-sm">' . $name . '</b></p>';
        $html .= $projectClientFeedback->feedback;  // Allow HTML content

        if ($projectClientFeedback->file) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension = pathinfo($projectClientFeedback->file, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($fileExtension), $imageExtensions)) {
                $image = get_file('uploads/projects.'.$projectClientFeedback->file);
                $html .= '<a class="lightbox-link" href="' . $image . '" data-lightbox="gallery" data-title="Image placeholder">
                            <img alt="Image placeholder" src="' . $image . '" class="img-thumbnail my-3" 
                                style="display: block;max-width: 200px;max-height: 140px;">
                        </a>';
            }

            $file = get_file('uploads/projects/'.$projectClientFeedback->file);
            $html .= '<a href="' . $file . '" class="" 
                data-bs-toggle="tooltip" title="' . __('Download') . '">
                '.$file.'
                </a>';
        }

        return $html;
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}

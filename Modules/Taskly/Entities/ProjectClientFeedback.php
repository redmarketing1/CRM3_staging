<?php

namespace Modules\Taskly\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ProjectClientFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'file',
        'feedback',
        'feedback_by',
        'parent',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Taskly\Database\factories\ProjectClientFeedbackFactory::new();
    }

	public function feedbackUser()
    {
        return $this->hasOne(User::class,'id','feedback_by');
    }

    public function subFeedback()
    {
        return $this->hasMany(ProjectClientFeedback::class, 'parent', 'id');
    }
}

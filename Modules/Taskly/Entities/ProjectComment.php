<?php

namespace Modules\Taskly\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ProjectComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'file',
        'comment',
        'comment_by',
        'parent',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Taskly\Database\factories\ProjectCommentFactory::new();
    }

	public function commentUser()
    {
        return $this->hasOne(User::class, 'id', 'comment_by');
    }

    public function subComment()
    {
        return $this->hasMany(ProjectComment::class, 'parent', 'id');
    }
}

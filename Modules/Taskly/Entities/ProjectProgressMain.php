<?php

namespace Modules\Taskly\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ProjectProgressMain extends Model
{
    use HasFactory;

    protected $fillable = [
        "estimation_id",
        "project_id",
        "user_id",
        "name",
        "signature"
    ];
    
    protected static function newFactory()
    {
        return \Modules\Taskly\Database\factories\ProjectProgressMainFactory::new();
    }

	public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function project_progress()
    {
        return $this->hasMany(ProjectProgress::class, 'progress_id', 'id');
    }
}

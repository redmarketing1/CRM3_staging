<?php

namespace Modules\Project\Traits;

use Modules\Lead\Entities\Label;
use Modules\Taskly\Entities\Task;
use Illuminate\Support\Facades\Auth;
use Modules\Taskly\Entities\Timesheet;
use Modules\Taskly\Entities\ActivityLog;
use Modules\Taskly\Entities\EstimateQuote;

trait Relationship
{
    public function milestones()
    {
        return $this->hasMany('Modules\Taskly\Entities\Milestone', 'project_id', 'id');
    }

    public function estimation()
    {
        return $this->hasMany(EstimateQuote::class, 'project_estimation_id')
            ->where('user_id', Auth::id());
    }

    public function creater()
    {
        return $this->hasOne('App\Models\User::class', 'id', 'created_by');
    }
    public function task()
    {
        return $this->hasMany('Modules\Taskly\Entities\Task', 'project_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_projects', 'project_id', 'user_id')->withPivot('is_active')->orderBy('id', 'ASC');
    }

    public function clients()
    {
        return $this->belongsToMany('App\Models\User', 'client_projects', 'project_id', 'client_id')->withPivot('is_active')->orderBy('id', 'ASC');
    }

    public function countTask()
    {
        return Task::where('project_id', '=', $this->id)->count();
    }

    public function countTaskComments()
    {
        return Task::join('comments', 'comments.task_id', '=', 'tasks.id')->where('project_id', '=', $this->id)->count();
    }

    public function user_tasks($user_id)
    {
        return Task::where('project_id', '=', $this->id)->where('assign_to', '=', $user_id)->get();
    }
    public function user_done_tasks($user_id)
    {
        return Task::join('stages', 'stages.id', '=', 'tasks.status')->where('project_id', '=', $this->id)->where('assign_to', '=', $user_id)->where('stages.complete', '=', '1')->get();
    }

    public function timesheet()
    {
        return Timesheet::where('project_id', '=', $this->id)->get();
    }

    /**
     * Get project status data
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function statusData()
    {
        return $this->hasOne(Label::class, 'id', 'status');
    }

    public function activities()
    {
        return $this->hasMany(ActivityLog::class, 'project_id', 'id')->latest();
    }

    public function files()
    {
        return $this->hasMany('Modules\Taskly\Entities\ProjectFile', 'project_id', 'id');
    }

    public function client_final_quote()
    {
        return $this->hasOne(EstimateQuote::class, 'project_id', 'id')->where('final_for_client', 1);
    }

    public function sub_contractor_final_quote()
    {
        return $this->hasOne(EstimateQuote::class, 'project_id', 'id')->where('final_for_sub_contractor', 1);
    }
}
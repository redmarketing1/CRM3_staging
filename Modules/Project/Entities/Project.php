<?php

namespace Modules\Project\Entities;

use Carbon\Carbon;
use App\Models\User;
use Carbon\CarbonPeriod;
use Modules\Lead\Entities\Label;
use Modules\Taskly\Entities\Task;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Modules\Taskly\Entities\Timesheet;
use Illuminate\Database\Eloquent\Model;
use Modules\Taskly\Entities\ActivityLog;
use Modules\Taskly\Entities\EstimateQuote;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'description',
        'start_date',
        'end_date',
        'budget',
        'copylinksetting',
        'password',
        'construction_detail_id',
        'is_same_invoice_address',
        'client',
        'technical_description',
        'label',
        'construction_type',
        'priority',
        'property_type',
        'workspace',
        'created_by',
        'is_active',
        'is_archive',
    ];

    /**
     * Extract short name
     * @return string
     */
    public function getShortNameAttribute()
    {
        $shortName = $this->status_data->name ?? 'NA';

        $words = explode(' ', $shortName);

        if (count($words) >= 2) {
            $shortName = '';
            foreach ($words as $word) {
                if (! empty($word)) {
                    $shortName .= substr($word, 0, 1);
                }
            }
        }

        return strtoupper(substr($shortName, 0, 2));
    }

    /**
     * Calculate project "Days Left" for show projcet
     * Calculate the number of days between the start and end dates.
     *
     * @return string|int The difference in days between the start date and end date. 
     */
    public function getExpiredDateAttribute()
    {
        $endDate     = Carbon::parse($this->end_date);
        $currentDate = Carbon::today();

        if ($endDate->isPast()) {
            return "Expired on " . $endDate->toFormattedDateString();
        }

        return $endDate->diffInDays($currentDate);
    }

    /**
     * Get project background color
     * @return string
     */
    public function getBackgroundColorAttribute()
    {
        return $this->status_data->background_color ?? '#eeeeee';
    }

    public function getProjectCountAttribute()
    {
        return self::whereHas('status_data', function ($query) {
            $query->where('name', $this->status_data->name);
        })->count();
    }

    /**
     * Return project URL
     * @return string
     */
    public function url()
    {
        return route('project.show', [$this->id]);
    }

    public function milestones()
    {
        return $this->hasMany('Modules\Taskly\Entities\Milestone', 'project_id', 'id');
    }

    public function estimation()
    {
        return $this->hasMany(EstimateQuote::class, 'project_estimation_id')
            ->where('user_id', Auth::id());
    }

    public static function getFirstSeventhWeekDay($week)
    {
        $first_day = $seventh_day = null;

        if (isset($week)) {
            $first_day   = Carbon::now()->addWeeks($week)->startOfWeek();
            $seventh_day = Carbon::now()->addWeeks($week)->endOfWeek();
        }

        $dateCollection['first_day']   = $first_day;
        $dateCollection['seventh_day'] = $seventh_day;

        $period = CarbonPeriod::create($first_day, $seventh_day);

        foreach ($period as $key => $dateobj) {
            $dateCollection['datePeriod'][$key] = $dateobj;
        }

        return $dateCollection;
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

    public function status_data()
    {
        return $this->hasOne(Label::class, 'id', 'status');
    }

    public function activities()
    {
        return $this->hasMany(ActivityLog::class, 'project_id', 'id')->latest();
    }

}
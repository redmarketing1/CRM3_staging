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

    public function getFontColorAttribute()
    {
        return $this->status_data->font_color ?? '#777777';
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

    public function scopeProjectOnly($query)
    {
        return $query->where('type', 'project');
    }

    public static function get_all($filters_request = array())
    {
        $user = Auth::user();
        if (isset($filters_request['user'])) {
            $user = $filters_request['user'];
        }

        if ($user->type == 'company') {
            $projects = Project::where('projects.created_by', '=', $user->id);
        } else {
            $projects = Project::leftjoin('client_projects', 'client_projects.project_id', 'projects.id')->leftjoin('estimate_quotes', 'estimate_quotes.project_id', 'projects.id');
            $projects->where(function ($query) use ($user) {
                $query->where('client_projects.client_id', $user->id)
                    ->orWhere('estimate_quotes.user_id', $user->id);
            });
        }

        if (isset($filters_request['project_type']) && $filters_request['project_type'] == "archieve") {
            $projects->where('projects.is_archive', 1);
        } else {
            $projects->where('projects.is_archive', 0);
        }

        $projects->with(['users'])->select('projects.*');

        $filter_count = $total_count = $projects->distinct()->count('projects.id');

        if (isset($search) && $search['value'] != "") {
            $search = $search['value'];
            $projects->where(function ($query) use ($search) {
                $query->where('projects.name', 'LIKE', '%' . $search . '%')
                    ->orWhere('projects.status', 'LIKE', '%' . $search . '%')
                    ->orWhere('projects.priority', 'LIKE', '%' . $search . '%')
                    ->orWhere('projects.progress', 'LIKE', '%' . $search . '%')
                    ->orWhere('projects.construction_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('projects.property_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('projects.price', 'LIKE', '%' . $search . '%')
                    ->orwhereRaw("DATE_FORMAT(created_at,'%m.%d.%Y') like ?", ["%$search%"]);
            });

            $filter_count = $projects->count('projects.id');
        }

        if (count($filters_request) > 0) {
            if (isset($filters_request['name']) && $filters_request['name'] != "") {
                $projects->where('projects.name', 'LIKE', '%' . $filters_request['name'] . '%');
            }

            if (isset($filters_request['comment']) && $filters_request['comment'] != "") {
                $projects->leftjoin('project_comments', 'project_comments.project_id', 'projects.id')->where('project_comments.comment', 'LIKE', '%' . $filters_request['comment'] . '%')->groupBy('projects.id');
            }

            if (isset($filters_request['status']) && $filters_request['status'] != "" && count($filters_request['status']) > 0) {
                $projects->whereIn('projects.status', $filters_request['status']);
            }

            if (isset($filters_request['label']) && $filters_request['label'] != "" && count($filters_request['label']) > 0) {
                $projects->whereIn('projects.label', $filters_request['label']);
            }

            if (isset($filters_request['priority']) && $filters_request['priority'] != "" && count($filters_request['priority']) > 0) {
                $projects->whereIn('projects.priority', $filters_request['priority']);
            }

            if (isset($filters_request['construction']) && $filters_request['construction'] != "" && count($filters_request['construction']) > 0) {
                $projects->whereIn('projects.construction_type', $filters_request['construction']);
            }

            if (isset($filters_request['property']) && $filters_request['property'] != "" && count($filters_request['property']) > 0) {
                $projects->whereIn('projects.property_type', $filters_request['property']);
            }

            if (isset($filters_request['users']) && $filters_request['users'] != "" && count($filters_request['users']) > 0) {
                $projects->leftjoin('client_projects', 'client_projects.project_id', 'projects.id')->whereIn('client_projects.client_id', $filters_request['users'])->groupBy('projects.id');
            }

            if (isset($filters_request['date_from']) && $filters_request['date_from'] != "" && isset($filters_request['date_to']) && $filters_request['date_to'] != "") {
                $from_date = date('Y-m-d', strtotime($filters_request['date_from']));
                $to_date   = date('Y-m-d', strtotime($filters_request['date_to']));

                $projects->whereDate('projects.created_at', '>=', $from_date)->whereDate('projects.created_at', '<=', $to_date);
            }

            if (isset($filters_request['progress_from']) && $filters_request['progress_from'] != "" && isset($filters_request['progress_to']) && $filters_request['progress_to'] != "") {
                $projects->where('projects.progress', '>=', $filters_request['progress_from'])->where('projects.progress', '<=', $filters_request['progress_to']);
            }

            if (isset($filters_request['price_from']) && $filters_request['price_from'] != "" && isset($filters_request['price_to']) && $filters_request['price_to'] != "") {
                $projects->where('projects.budget', '>=', $filters_request['price_from'])->where('projects.budget', '<=', $filters_request['price_to']);
            }

            if (isset($filters_request['city']) || isset($filters_request['state']) || isset($filters_request['country'])) {
                $projects->leftjoin('users', 'users.id', 'projects.construction_detail_id');
                $projects->leftjoin('users as invoice_users', 'invoice_users.id', 'projects.client');

                if (isset($filters_request['city']) && $filters_request['city'] != "") {
                    $city = $filters_request['city'];

                    $projects->where(function ($query) use ($city) {
                        $query->where('users.city', 'LIKE', $city)
                            ->orWhere('invoice_users.city', 'LIKE', $city);
                    });
                }
                if (isset($filters_request['state']) && $filters_request['state'] != "") {
                    $state = $filters_request['state'];

                    $projects->where(function ($query) use ($state) {
                        $query->where('users.state', 'LIKE', $state)
                            ->orWhere('invoice_users.state', 'LIKE', $state);
                    });
                }
                if (isset($filters_request['country']) && $filters_request['country'] != "") {
                    $country = $filters_request['country'];

                    $projects->where(function ($query) use ($country) {
                        $query->where('users.country', '=', $country)
                            ->orWhere('invoice_users.country', '=', $country);
                    });
                }
            }

            $filter_count = $projects->count('projects.id');
        }

        $projects->groupBy('projects.id');

        if (isset($filters_request['order_by'])) {
            $projects->orderBy($filters_request['order_by']['field'], $filters_request['order_by']['order']);
        }

        if (isset($filters_request['start']) && isset($filters_request['take'])) {
            $projects->skip($filters_request['start'])->take($filters_request['take']);
        }

        $records = $projects->get();

        return ['total_count' => $total_count, 'filter_count' => $filter_count, 'records' => $records];
    }


}
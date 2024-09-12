<?php

namespace Modules\Taskly\Entities;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Modules\Taskly\Entities\Timesheet;
use Modules\Lead\Entities\Label;

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


    protected static function newFactory()
    {
        return \Modules\Taskly\Database\factories\ProjectFactory::new();
    }

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

    public function construction_detail()
    {
        return $this->hasOne(User::class, 'id', 'construction_detail_id');
    }

    public function client()
    {
        return $this->hasOne('App\Models\User', 'id', 'client')->first();
    }

    public function client_data()
    {
        return $this->hasOne(User::class, 'id', 'client');
    }

    public function scopeProjectOnly($query)
    {
        return $query->where('type', 'project');
    }
    public function creater()
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'created_by');
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

    public function venders()
    {
        return $this->belongsToMany('App\Models\User', 'vender_projects', 'project_id', 'vender_id')->withPivot('is_active')->orderBy('id', 'ASC');
    }

    public function comments()
    {
        return $this->hasMany(ProjectComment::class, 'project_id', 'id');
    }

    public function countTask()
    {
        return Task::where('project_id', '=', $this->id)->count();
    }

    public function tasks()
    {
        return Task::where('project_id', '=', $this->id)->get();
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


    public function countTaskComments()
    {
        return Task::join('comments', 'comments.task_id', '=', 'tasks.id')->where('project_id', '=', $this->id)->count();
    }

    public function getProgress()
    {

        $total     = Task::where('project_id', '=', $this->id)->count();
        $totalDone = Task::where('project_id', '=', $this->id)->where('status', '=', 'done')->count();
        if ($totalDone == 0) {
            return 0;
        }

        return round(($totalDone * 100) / $total);
    }

    public function milestones()
    {
        return $this->hasMany('Modules\Taskly\Entities\Milestone', 'project_id', 'id');
    }

    public function estimations()
    {
        return $this->hasMany(ProjectEstimation::class, 'project_id', 'id');
    }

    public function files()
    {
        return $this->hasMany('Modules\Taskly\Entities\ProjectFile', 'project_id', 'id');
    }

    public function activities()
    {
        return $this->hasMany('Modules\Taskly\Entities\ActivityLog', 'project_id', 'id')->orderBy('id', 'desc');
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

    public function project_progress()
    {
        $stage = \Modules\Taskly\Entities\Stage::where('workspace_id', '=', getActiveWorkSpace())->where('complete', 1)->first();
        if (! empty($stage)) {
            $status = $stage->id;
        } else {
            $status = 0;
        }
        $total_task     = Task::where('project_id', '=', $this->id)->count();
        $completed_task = Task::where('project_id', '=', $this->id)->where('status', '=', $status)->count();
        if ($total_task > 0) {
            $percentage = intval(($completed_task / $total_task) * 100);


            return [

                'percentage' => $percentage . '%',
            ];
        } else {
            return [

                'percentage' => 0,
            ];

        }
    }

    public function project_milestone_progress()
    {
        $total_milestone    = Milestone::where('project_id', '=', $this->id)->count();
        $total_progress_sum = Milestone::where('project_id', '=', $this->id)->sum('progress');

        if ($total_milestone > 0) {
            $percentage = intval(($total_progress_sum / $total_milestone));


            return [

                'percentage' => $percentage . '%',
            ];
        } else {
            return [

                'percentage' => 0,
            ];

        }
    }

    // For Delete project and it's based sub record
    public static function count_progress($project_id)
    {
        $project = Project::find($project_id);
        if ($project) {
            $estimation_ids = ProjectEstimation::where('project_id', $project->id)->pluck('id');
            if (count($estimation_ids) > 0) {
                $project_progress          = ProjectProgress::whereIn('estimation_id', $estimation_ids)->whereRaw('id in (select max(id) from project_progress WHERE status = 2 group by (product_id))')->sum('progress');
                $total_estimation_products = ProjectEstimationProduct::whereIn('project_estimation_id', $estimation_ids)->count('id');
                $progress                  = 0;
                if ($project_progress > 0) {
                    $progress = floatval($project_progress) / $total_estimation_products;
                }
                $update_data = array(
                    "progress" => isset($progress) ? $progress : 0,
                );
                Project::where("id", $project->id)->update($update_data);
            }
        }
    }

    public static function customerProject($customerId)
    {
        $project = '';
        if (module_is_active('Account')) {
            $customer = \Modules\Account\Entities\Customer::find($customerId);
            $project  = ClientProject::where('client_id', $customer->user_id)->get();
        }
        return $project;
    }

    public static function vendorProject($vendorId)
    {
        $project = '';
        if (module_is_active('Account')) {
            $vendor  = \Modules\Account\Entities\Vender::find($vendorId);
            $project = VenderProject::where('vender_id', $vendor->user_id)->get();
        }
        return $project;
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

    public function project_default_file()
    {
        $image_array = array('gif', 'jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'heic', 'HEIC');
        $file        = $this->hasOne('Modules\Taskly\Entities\ProjectFile', 'project_id', 'id')->where('is_default', 1);

        $file->where(function ($query) use ($image_array) {
            foreach ($image_array as $key => $image) {
                if ($key == 0) {
                    $query->where('file_name', 'LIKE', '%.' . $image);
                } else {
                    $query->orWhere('file_name', 'LIKE', '%.' . $image);
                }
            }
        });

        $result = $file;
        if (! isset($result->file_name)) {
            // $file = $this->hasMany(ProjectFile::class, 'project_id', 'id')->first();
            $file2 = $this->hasOne(ProjectFile::class, 'project_id', 'id');
            $file2->where(function ($query) use ($image_array) {
                foreach ($image_array as $key => $image) {
                    if ($key == 0) {
                        $query->where('file_name', 'LIKE', '%.' . $image);
                    } else {
                        $query->orWhere('file_name', 'LIKE', '%.' . $image);
                    }
                }
            });

            $result = $file2;
        }

        return $result;
    }

    public function client_final_quote()
    {
        return $this->hasOne(EstimateQuote::class, 'project_id', 'id')->where('final_for_client', 1);
    }

    public function latest_final_quote()
    {
        return $this->hasOne(EstimateQuote::class, 'project_id', 'id')->where('is_official_final', 1);
    }

    public function user_latest_quote()
    {
        return $this->hasOne(EstimateQuote::class, 'project_id', 'id')->where('is_final', 1)->orderBy('id', 'DESC');
    }

}
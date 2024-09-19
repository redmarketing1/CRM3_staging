<?php

namespace Modules\Project\Entities;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Modules\Lead\Entities\Label;
use Modules\Project\Traits\Scope;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Traits\Attribute;
use Illuminate\Database\Eloquent\Model;
use Modules\Project\Traits\Relationship;
use Modules\Project\DataTables\ProjectsTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory, Attribute, Scope, Relationship;

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

    // public function getPriorityAttribute($priority)
    // {
    //     $labelOfProject = Label::get_project_dropdowns();

    //     $projectStatus = collect($labelOfProject['priority']);

    //     return $projectStatus->where('id', $priority)->first();
    // }

    /**
     * Return project URL
     * @return string
     */
    public function url()
    {
        return route('project.show', [$this->id]);
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

    /**
     * Get table data for the resource
     *
     * @param \Illuminate\Http\Request $request 
     */
    public function table($request)
    {
        $user = Auth::user();

        $query = ($user->type == 'company') ?

            Project::whereCreatedBy($user->id)
                ->with(['statusData'])

            : Project::leftjoin('client_projects', 'client_projects.project_id', 'projects.id')
                ->leftjoin('estimate_quotes', 'estimate_quotes.project_id', 'projects.id')
                ->where(function ($query) use ($user) {
                    $query->where('client_projects.client_id', $user->id)
                        ->orWhere('estimate_quotes.user_id', $user->id);
                });

        return new ProjectsTable($query);
    }

}
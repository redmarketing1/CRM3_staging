<?php

namespace Modules\Project\Entities;

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

    /**
     * Return project URL
     * @return string
     */
    public function url()
    {
        return route('project.show', [$this->id]);
    }

    /**
     * Get table data for the resource
     * 
     */
    // public function table($request)
    // {
    //     $user        = Auth::user();
    //     $workspaceID = getActiveWorkSpace();

    //     $query = ($user->type == 'company') ?
    //         self::forCompany($user->id)->latest() :
    //         self::forClient($user->id, $workspaceID)->latest();

    //     return new ProjectsTable($query);
    // }

    public function table($request){
        $user        = Auth::user();
        $workspaceID = getActiveWorkSpace();
        if ($user->type == 'company') {
            $projects = Project::where('projects.created_by', '=', $user->id)
                ->with(['statusData', 'priorityData', 'constructionData', 'constructionDetail', 'property', 'thumbnail', 'comments']);
        } else {
            $projects = Project::leftJoin('client_projects', 'client_projects.project_id', '=', 'projects.id')
                ->leftJoin('estimate_quotes', 'estimate_quotes.project_id', '=', 'projects.id')
                ->leftJoin('user_projects', 'user_projects.project_id', '=', 'projects.id')
                ->where(function ($query) use ($user) {
                    $query->where('client_projects.client_id', $user->id)
                        ->orWhere('user_projects.user_id', $user->id)
                        ->orWhere('estimate_quotes.user_id', $user->id);
                })
                ->with(['statusData', 'priorityData', 'constructionData', 'constructionDetail', 'property', 'thumbnail', 'comments']);
            
            $projects->select('projects.*')  
                     ->groupBy('projects.id'); 
        }
        
        
        return new ProjectsTable($projects);
    }
    

    public function delays()
    {
        return $this->hasMany(ProjectDelay::class, 'project_id', 'id');
    }

    public function files()
    {
        return $this->hasMany('Modules\Project\Entities\ProjectFile', 'project_id', 'id');
    }

}

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
     * @param \Illuminate\Http\Request $request 
     */
    public function table($request)
    {
        $user = Auth::user();

        $query = ($user->type == 'company') ?

            self::whereCreatedBy($user->id)
                ->with(['statusData', 'priorityData', 'thumbnail'])

            : self::leftjoin('client_projects', 'client_projects.project_id', 'projects.id')
                ->leftjoin('estimate_quotes', 'estimate_quotes.project_id', 'projects.id')
                ->where(function ($query) use ($user) {
                    $query->where('client_projects.client_id', $user->id)
                        ->orWhere('estimate_quotes.user_id', $user->id);
                })
                ->with(['statusData', 'priorityData']);

        return new ProjectsTable($query);
    }

}
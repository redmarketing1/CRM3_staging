<?php

namespace Modules\Estimation\Traits;

use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\Project;
use Modules\Taskly\Entities\EstimationGroup;
use Modules\Estimation\Entities\EstimateQuote;
use Modules\Taskly\Entities\ProjectEstimationProduct;


trait Relationship
{
    /**
     * Retrieve project from estimation 
     */
    public function project()
    {
        return $this->hasOne(Project::class, 'id', 'project_id');
    }

    /**
     * Retrieve project from estimation 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'id', 'project_id');
    }

    /**
     * Retrieve Quote from estimation 
     */
    public function Quote()
    {
        return $this->hasMany(EstimateQuote::class, 'project_estimation_id')
            ->where('is_display', 1);
    }

    /**
     * Retrieve User Quote from estimation Quote
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userQuote()
    {
        return $this->hasMany(EstimateQuote::class, 'project_estimation_id')
            ->where('user_id', Auth::user()->id);
    }

    /**
     * Retrieve product from ProjectEstimationProduct
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(ProjectEstimationProduct::class, 'project_estimation_id', 'id')
            ->orderByRaw('position');
    }

    /**
     * Retrieve estimationGroups
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function estimationGroups()
    {
        return $this->hasMany(EstimationGroup::class, 'estimation_id', 'id')
            // ->whereNull('parent_id')
            // ->with(['children_data', 'estimation_products'])
            ->orderBy('position');
    }

    public function all_quotes()
    {
        return $this->hasMany(EstimateQuote::class, 'project_estimation_id');
    }

    public function all_quotes_list()
    {
        return $this->hasMany(EstimateQuote::class, 'project_estimation_id')->whereNotNull('user_id')->groupBy('user_id', 'project_estimation_id');
    }

    public function estimation_groups()
    {
        return $this->hasMany(EstimationGroup::class, 'estimation_id', 'id');
    }

    public function estimation_products()
    {
        return $this->hasMany(ProjectEstimationProduct::class, 'project_estimation_id', 'id');
    }

    public function final_quote()
    {
        return $this->hasOne(EstimateQuote::class, 'project_estimation_id', 'id')->where('is_final', 1);
    }
}
<?php

namespace Modules\Taskly\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EstimationGroup extends Model
{
    use HasFactory;

    protected $fillable = [
		'parent_id',
        'estimation_id',
        'group_pos',
        'group_name',
        'position',
	];
    
    protected static function newFactory()
    {
        return \Modules\Taskly\Database\factories\EstimationGroupFactory::new();
    }

	public function estimation_products()
    {
        return $this->hasMany(ProjectEstimationProduct::class,'group_id','id')->orderBy('position');
    }

	public function children()
	{
		return $this->hasMany(EstimationGroup::class, 'parent_id')->with('children');
	}

	public function children_data()
	{
		return $this->hasMany(EstimationGroup::class, 'parent_id')->orderBy('position');
	}
}

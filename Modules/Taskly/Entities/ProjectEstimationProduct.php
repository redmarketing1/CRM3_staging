<?php

namespace Modules\Taskly\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectEstimationProduct extends Model
{
    use HasFactory;

    protected $fillable = [
		'project_estimation_id',
        'group_id',
        'type',
        'name',
        'description',
        'pos',
        'unit',
        'quantity',
        'is_optional',
        'comment',
        'campare_percent',
        'ai_description',
		'smart_template_data',
        'position'
	];
    
    protected static function newFactory()
    {
        return \Modules\Taskly\Database\factories\ProjectEstimationProductFactory::new();
    }

	public function quoteItems()
    {
        return $this->hasMany(EstimateQuoteItem::class,"product_id","id");
    }

	public function group()
    {
        return $this->hasOne(EstimationGroup::class,'id','group_id');
    }

	public function progress(){
        return $this->hasMany(ProjectProgress::class,"product_id","id");
    }

    public function progress_files(){
        return $this->hasMany(ProjectProgressFiles::class,"product_id","id");
    }
}

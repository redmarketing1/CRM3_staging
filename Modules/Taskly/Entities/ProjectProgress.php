<?php

namespace Modules\Taskly\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        "estimation_id",
        "progress_id",
        "product_id",
        "progress",
        "status",
        "approve_date",
        "remarks",
        "signature",
        "progress_amount"
    ];
    
    protected static function newFactory()
    {
        return \Modules\Taskly\Database\factories\ProjectProgressFactory::new();
    }

	public function product()
    {
        return $this->belongsTo(ProjectEstimationProduct::class,'product_id','id');
    }

    public function estimation()
    {
        return $this->belongsTo(ProjectEstimation::class,'estimation_id','id');
    }

    public function project_progress(){
        return $this->hasMany(ProjectProgressMain::class,'id','progress_id');
    }

	public function main_progress(){
        return $this->hasOne(ProjectProgressMain::class, 'id', 'progress_id');
    }
}

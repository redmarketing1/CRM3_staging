<?php

namespace Modules\Taskly\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class EstimateQuote extends Model
{
	use HasFactory;

	protected $fillable = [
		'title',
		'user_id',
		'is_display',
		'project_id',
		'project_estimation_id',
		'tax',
		'discount',
		'net',
		'gross',
		'net_with_discount',
		'gross_with_discount',
		'is_clone',
		'is_ai',
		'smart_template_id',
		'markup',
		'is_final',
		'is_official_final',
		'final_for_client',
		'final_for_sub_contractor',
	];

	protected static function newFactory()
	{
		return \Modules\Taskly\Database\factories\EstimateQuoteFactory::new();
	}

	public function estimation()
	{
		return $this->hasOne(ProjectEstimation::class, 'id', 'project_estimation_id')->withTrashed();
	}

	public function user()
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}

	public function quoteItem()
	{
		return $this->hasMany(EstimateQuoteItem::class, 'estimate_quote_id', 'id');
	}

	public function estimationItem()
	{
		return $this->hasMany(ProjectEstimationProduct::class, 'estimate_quote_id', 'id');
	}

	public function subContractor()
    {
    //   return $this->hasOne(SubContractor::class,'id','sub_contractor_id');
	   return $this->hasOne(User::class,'id','user_id');
    }

}

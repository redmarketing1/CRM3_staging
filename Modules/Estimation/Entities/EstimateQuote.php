<?php

namespace Modules\Estimation\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Taskly\Entities\EstimateQuoteItem;
use Modules\Taskly\Entities\ProjectEstimationProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}

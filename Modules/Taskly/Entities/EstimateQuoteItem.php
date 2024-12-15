<?php

namespace Modules\Taskly\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EstimateQuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
		'estimate_quote_id',
        'product_id',
		'base_price',
        'price',
        'total_price',
		'all_results',
		'smart_template_data',
	];

	protected $guarded = ['id', 'created_at', 'updated_at'];
    
    protected static function newFactory()
    {
        return \Modules\Taskly\Database\factories\EstimateQuoteItemFactory::new();
    }

    public function quote(){
        return $this->hasOne(EstimateQuote::class,'id','estimate_quote_id');
    }

    public function projectEstimationProduct()
    {
        return $this->belongsTo(ProjectEstimationProduct::class, 'product_id', 'id');
    }

	public function progress()
    {
        return $this->hasMany(ProjectProgress::class,'product_id','product_id');
    }
}

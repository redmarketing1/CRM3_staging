<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartTemplate extends Model
{
    use HasFactory;

	protected $fillable = [
        'title',
        'type',
        'request_count',
        'outliner',
        'result_operation',
		'ai_model_id',
		'extraction_ai_model_id',
        'created_by',
		'workspace'
    ];

	public function template_details()
    {
        return $this->hasMany(SmartTemplatesDetail::class,'template_id','id');
    }

	public function ai_model()
    {
        return $this->hasOne(AiModel::class,'id','ai_model_id');
    }

	public function extraction_ai_model()
    {
        return $this->hasOne(AiModel::class,'id','extraction_ai_model_id');
    }
}

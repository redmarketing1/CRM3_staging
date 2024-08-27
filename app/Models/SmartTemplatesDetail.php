<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartTemplatesDetail extends Model
{
    use HasFactory;

	protected $fillable = [
        'template_id',
        'prompt_id',
        'prompt_title',
        'prompt_slug',
		'prompt_desc',
        'created_by',
    ];

	public function template()
    {
        return $this->hasOne(SmartTemplate::class,'id','template_id');
    }

	public function prompt()
    {
        return $this->hasOne(Content::class,'id','prompt_id');
    }
}

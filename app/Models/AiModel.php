<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    use HasFactory;

	protected $fillable = [
        'provider',
        'model',
        'model_label',
		'max_tokens',
		'status',
    ];
}

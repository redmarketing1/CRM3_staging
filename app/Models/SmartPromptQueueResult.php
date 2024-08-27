<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartPromptQueueResult extends Model
{
    use HasFactory;

	protected $fillable = [
        'spq_id',
        'result_description',
        'result_number'
    ];
}

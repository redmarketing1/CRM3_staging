<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTemplateLang extends Model
{
    use HasFactory;

	protected $fillable = [
        'parent_id',
        'lang',
        'content',
        'variables',
        'created_by',
		'workspace',
    ];
}

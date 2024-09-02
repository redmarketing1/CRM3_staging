<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {

            if (empty($model->lang)) {
                $model->lang = app()->getLocale();
            }

            $model->created_by = auth()->id();
        });
    }
}
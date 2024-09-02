<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_ai',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($content) {
            if (empty($content->slug)) {
                $content->slug = Str::slug($content->name);
            }
        });
    }

    /**
     * Get the content template langs for the content.
     */
    public function contentTemplate($lang = null)
    {
        if ($lang == null) {
            $lang = app()->getLocale();
        }

        return $this->hasMany(ContentTemplateLang::class, 'parent_id')
            ->where('lang', $lang);
    }
}
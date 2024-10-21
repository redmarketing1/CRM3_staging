<?php

namespace Modules\Project\Entities;

use Modules\Project\Traits\Scope;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Project\Traits\Attribute;
use Illuminate\Database\Eloquent\Model;
use Modules\Project\Traits\Relationship;
use Spatie\MediaLibrary\InteractsWithMedia;
use Modules\Project\DataTables\ProjectsTable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Project extends Model implements HasMedia
{
    use HasFactory, Attribute, Scope, Relationship, InteractsWithMedia;

    protected $fillable = [
        'name',
        'status',
        'description',
        'start_date',
        'end_date',
        'budget',
        'copylinksetting',
        'password',
        'construction_detail_id',
        'is_same_invoice_address',
        'client',
        'technical_description',
        'label',
        'construction_type',
        'priority',
        'property_type',
        'workspace',
        'created_by',
        'is_active',
        'is_archive',
    ];
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($project) {
            if ($project->isDirty('status')) {
                Cache::forget('projectSubmenu-' . auth()->id());
                Cache::forget('filterableStatusList-' . auth()->id());
            }
        });
    }

    /**
     * Return project URL
     * @return string
     */
    public function url()
    {
        return route('project.show', [$this->id]);
    }

    /**
     * Get table data for the resource
     */
    public function table($request) : ProjectsTable
    {
        $user        = Auth::user();
        $workspaceID = getActiveWorkSpace();

        $query = ($user->type == 'company') ?
            self::forCompany($user->id)->latest() :
            self::forClient($user->id, $workspaceID)->latest();

        return new ProjectsTable($query);
    }

    public function delays()
    {
        return $this->hasMany(ProjectDelay::class, 'project_id', 'id');
    }

    public function files()
    {
        return $this->hasMany('Modules\Project\Entities\ProjectFile', 'project_id', 'id');
    }

    /**
     * Set selected diretory where can store image
     * Set default image URL 
     */
    public function registerMediaCollections() : void
    {
        $defaultThumbnail = asset('assets/images/default_thumbnail3.png');

        $this->addMediaCollection('projects')
            ->useFallbackUrl($defaultThumbnail)
            ->useFallbackPath($defaultThumbnail)
            ->useDisk('projects');
    }

    /**
     * Will generated project thumbnail image 
     * @note Please run background `php artisan queue:work` rather it will not generated image
     * @help `php artisan media-library:regenerate` for re-generated new thumbnail 
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|null $media
     * @return void
     */
    public function registerMediaConversions(Media $media = null) : void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->keepOriginalImageFormat()
            ->nonQueued();
    }

}

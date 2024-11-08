<?php

namespace Modules\Estimation\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Estimation\Traits\Relationship;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ProjectEstimation extends Model
{
    use Relationship, HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title',
        'project_id',
        'issue_date',
        'technical_description',
        'created_by',
        'status',
        'is_active',
        'init_status',
    ];

    public static $statues      = [
        'Draft',
        'Open',
        'Sent',
        'Close',
    ];
    public static $statuesColor = [
        'Draft' => 'dark',
        'Open'  => 'info',
        'Sent'  => 'success',
        'Close' => 'danger',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array $with
     */
    protected $with = [
        'Quote',
        'project',
        'products',
        'estimationGroups',
    ];


    /**
     * Get status
     * @return array
     */
    public function getAllStatusAttribute()
    {
        return self::$statues ?? [];
    }

    /**
     * Get statusColor
     * @return array
     */
    public function getStatusColorAttribute()
    {
        return self::$statuesColor ?? [];
    }
}

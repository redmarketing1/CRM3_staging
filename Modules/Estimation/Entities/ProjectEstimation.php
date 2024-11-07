<?php

namespace Modules\Estimation\Entities;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ProjectEstimation extends Model
{
    // use HasFactory, SoftDeletes, InteractsWithMedia;

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

}

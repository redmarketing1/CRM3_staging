<?php

namespace Modules\Project\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDelay extends Model
{
    use HasFactory;
    protected $fillable = [
        'new_deadline',
        'reason',
        'media',
        'project_id',
        'delay_in_weeks',
        'internal_comment',
    ];

    public function creator(){
        return $this->morphTo();
    }
}

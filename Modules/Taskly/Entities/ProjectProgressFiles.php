<?php

namespace Modules\Taskly\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectProgressFiles extends Model
{
    use HasFactory;

    protected $fillable = [
        "estimation_id",
        "product_id",
        "file",
        "description"
    ];
    
    protected static function newFactory()
    {
        return \Modules\Taskly\Database\factories\ProjectProgressFilesFactory::new();
    }
}

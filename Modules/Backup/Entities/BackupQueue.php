<?php

namespace Modules\Backup\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BackupQueue extends Model
{
    use HasFactory;

    protected $fillable = ['status'];
}

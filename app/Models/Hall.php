<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hall extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

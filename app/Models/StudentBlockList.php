<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentBlockList extends Model
{
    use SoftDeletes;

    protected $table = 'student_block_lists';

    protected $fillable = [
        'student_id',
        'blocked_by',
        'reason',
        'blocked_at',
        'unblocked_at',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
        'unblocked_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }
}

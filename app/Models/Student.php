<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'roll',
        'registration',
        'name',
        'father_name',
        'mother_name',
        'email',
        'mobile_number',
        'address',
        'hall_id',
        'hall_status',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function blockList()
    {
        return $this->hasMany(StudentBlockList::class);
    }

    public function hallAllotments()
    {
        return $this->hasMany(HallAllotment::class);
    }

    public function activeAllotment()
    {
        return $this->hasOne(HallAllotment::class)
                    ->whereIn('status', ['active', 'cancel_requested'])
                    ->latest();
    }
}

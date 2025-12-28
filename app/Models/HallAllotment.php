<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HallAllotment extends Model
{
    protected $fillable = [
        'hall_id',
        'student_id',
        'seat_id',
        'allotment_date',
        'starting_month',
        'ending_month',
        'cancel_request_date',
        'cancelled_at',
        'cancellation_reason',
        'status'
    ];

    protected $casts = [
        'allotment_date' => 'date',
        'starting_month' => 'date',
        'ending_month' => 'date',
        'cancel_request_date' => 'date',
        'cancelled_at' => 'datetime'
    ];

    // NOTE: Auto-expiration logic has been moved to a scheduled command
    // See: app/Console/Commands/ExpireHallAllotments.php
    // This prevents unexpected updates during model retrieval
    
    // protected static function boot()
    // {
    //     parent::boot();
    //     // Auto-expiration removed - now handled by scheduled command
    // }


    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }
}

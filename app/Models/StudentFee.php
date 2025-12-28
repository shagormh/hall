<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFeeFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'hall_id',
        'transaction_id',
        'fee_details',
        'amount',
        'payment_date',
        'voucher_path',
        'months_count',
        'status',
        'processed_at',
        'processed_by',
        'rejection_reason',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'processed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}

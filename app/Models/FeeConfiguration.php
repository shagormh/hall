<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_id',
        'fee_type',
        'amount',
        'period',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the hall that owns the fee configuration.
     */
    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * Get the user who created this configuration.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this configuration.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get active configurations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get configurations by fee type.
     */
    public function scopeByFeeType($query, $feeType)
    {
        return $query->where('fee_type', $feeType);
    }

    /**
     * Get the fee amount for a specific hall and fee type.
     * Returns null if no configuration exists.
     */
    public static function getFeeAmount($hallId, $feeType = 'hall_rent')
    {
        $config = static::where('hall_id', $hallId)
            ->where('fee_type', $feeType)
            ->where('is_active', true)
            ->first();

        return $config ? $config->amount : null;
    }

    /**
     * Get the default fee amount (fallback when no hall-specific config exists).
     */
    public static function getDefaultFeeAmount($feeType = 'hall_rent')
    {
        $config = static::whereNull('hall_id')
            ->where('fee_type', $feeType)
            ->where('is_active', true)
            ->first();

        return $config ? $config->amount : 150; // 150 as ultimate fallback
    }
    /**
     * Get available fee types.
     */
    public static function getFeeTypes()
    {
        return [
            ['value' => 'hall_rent', 'label' => 'Hall Rent'],
            ['value' => 'dining_charge', 'label' => 'Dining Charge'],
            ['value' => 'security_deposit', 'label' => 'Security Deposit'],
            ['value' => 'utility_charge', 'label' => 'Utility Charge'],
            ['value' => 'other', 'label' => 'Other'],
        ];
    }
}

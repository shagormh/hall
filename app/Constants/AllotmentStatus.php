<?php

namespace App\Constants;

class AllotmentStatus
{
    const ACTIVE = 'active';
    const CANCEL_REQUESTED = 'cancel_requested';
    const CANCELLED = 'cancelled';
    const BLOCKED = 'blocked';
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const CONFIRMED = 'confirmed';

    /**
     * Get all statuses
     */
    public static function all(): array
    {
        return [
            self::ACTIVE,
            self::CANCEL_REQUESTED,
            self::CANCELLED,
            self::BLOCKED,
            self::PENDING,
            self::APPROVED,
            self::CONFIRMED,
        ];
    }

    /**
     * Get active statuses (not cancelled or blocked)
     */
    public static function active(): array
    {
        return [
            self::ACTIVE,
            self::CANCEL_REQUESTED,
            self::PENDING,
            self::APPROVED,
            self::CONFIRMED,
        ];
    }
}

<?php

namespace App\Constants;

class SeatStatus
{
    const EMPTY = 'empty';
    const ALLOTTED = 'allotted';  // Fixed spelling from 'alloted'

    /**
     * Get all statuses
     */
    public static function all(): array
    {
        return [
            self::EMPTY,
            self::ALLOTTED,
        ];
    }
}

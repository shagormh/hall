<?php

namespace App\Constants;

class HallStatus
{
    const ATTACHMENT = 'attachment';
    const ALLOTTED = 'allotted';  // Fixed spelling from 'alloted'
    const CANCEL = 'cancel';

    /**
     * Get all statuses
     */
    public static function all(): array
    {
        return [
            self::ATTACHMENT,
            self::ALLOTTED,
            self::CANCEL,
        ];
    }
}

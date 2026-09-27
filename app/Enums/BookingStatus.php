<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Represents the possible states of a booking.
 */
enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    /**
     * Determine whether this booking still blocks the seat.
     */
    public function isActive(): bool
    {
        return match ($this) {
            self::Pending,
            self::Confirmed => true,

            self::Cancelled => false,
        };
    }
}

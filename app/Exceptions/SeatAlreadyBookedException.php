<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a seat already has an active booking.
 */
final class SeatAlreadyBookedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'This seat is already booked for this trip.'
        );
    }
}

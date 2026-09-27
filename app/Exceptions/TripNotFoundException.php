<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when the requested seat does not exist.
 */
final class SeatNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'The requested seat was not found.'
        );
    }
}

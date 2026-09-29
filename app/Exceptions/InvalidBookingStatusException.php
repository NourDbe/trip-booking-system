<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class InvalidBookingStatusException extends RuntimeException
{
    public function __construct(
        string $message = 'The requested booking status change is not allowed.'
    ) {
        parent::__construct($message);
    }

    /**
     * Return the exception as a JSON API response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => null,
        ], 409);
    }
}

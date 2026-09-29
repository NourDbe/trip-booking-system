<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class SeatAlreadyBookedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'This seat is already booked for this trip.'
        );
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => null,
        ], 409);
    }
}

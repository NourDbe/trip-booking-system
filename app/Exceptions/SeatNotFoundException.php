<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class SeatNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'The requested seat was not found for this trip.'
        );
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => null,
        ], 404);
    }
}

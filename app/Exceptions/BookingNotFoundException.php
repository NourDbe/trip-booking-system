<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class BookingNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The requested booking was not found.');
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
        ], 404);
    }
}

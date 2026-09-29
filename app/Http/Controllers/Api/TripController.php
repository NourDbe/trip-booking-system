<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripResource;
use App\Services\TripService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TripController extends Controller
{
    use HasApiResponse;

    public function __construct(
        private TripService $tripService
    ) {
    }

    /**
     * List upcoming trips.
     */
    public function index(Request $request): JsonResponse
    {
        $trips = $this->tripService
            ->getUpcomingTrips();

        return $this->successResponse(
            TripResource::collection($trips)->resolve($request),
            'Trips retrieved successfully.'
        );
    }

    /**
     * Show one trip.
     */
    public function show(
        Request $request,
        int $tripId
    ): JsonResponse {
        $trip = $this->tripService
            ->find($tripId);

        return $this->successResponse(
            (new TripResource($trip))->resolve($request),
            'Trip retrieved successfully.'
        );
    }
}

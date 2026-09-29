<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\SeatResource;
use App\Services\BookingService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BookingController extends Controller
{
    use HasApiResponse;

    public function __construct(
        private BookingService $bookingService
    ) {
    }

    /**
     * Get available seats for a trip.
     */
    public function availableSeats(
        Request $request,
        int $tripId
    ): JsonResponse {
        $seats = $this->bookingService
            ->availableSeats($tripId);

        return $this->successResponse(
            SeatResource::collection($seats)->resolve($request),
            'Available seats retrieved successfully.'
        );
    }

    /**
     * Create a booking.
     */
    public function store(
        StoreBookingRequest $request,
        int $tripId
    ): JsonResponse {
        $booking = $this->bookingService->create(
            $tripId,
            (int) $request->validated('seat_id'),
            (int) $request->validated('customer_id')
        );

        /*
         * Load relations required by BookingResource.
         */
        $booking->load([
            'customer',
            'seat.trip.originCity',
            'seat.trip.destinationCity',
        ]);

        return $this->successResponse(
            (new BookingResource($booking))->resolve($request),
            'Booking created successfully.',
            201
        );
    }

    /**
     * Show one booking.
     */
    public function show(
        Request $request,
        int $bookingId
    ): JsonResponse {
        $booking = $this->bookingService
            ->find($bookingId);

        return $this->successResponse(
            (new BookingResource($booking))->resolve($request),
            'Booking retrieved successfully.'
        );
    }

    /**
     * Confirm a pending booking.
     */
    public function confirm(
        Request $request,
        int $bookingId
    ): JsonResponse {
        $booking = $this->bookingService
            ->confirm($bookingId);

        return $this->successResponse(
            (new BookingResource($booking))->resolve($request),
            'Booking confirmed successfully.'
        );
    }

    /**
     * Cancel a booking.
     */
    public function cancel(
        Request $request,
        int $bookingId
    ): JsonResponse {
        $booking = $this->bookingService
            ->cancel($bookingId);

        return $this->successResponse(
            (new BookingResource($booking))->resolve($request),
            'Booking cancelled successfully.'
        );
    }
}

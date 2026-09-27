<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\SeatAlreadyBookedException;
use App\Exceptions\SeatNotFoundException;
use App\Exceptions\TripNotFoundException;
use App\Models\Booking;
use App\Models\Seat;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class BookingService
{
    /**
     * Create a new booking safely.
     *
     * The seat row is locked during the transaction
     * to prevent two users from booking the same seat
     * at the same time.
     */
    public function create(
        int $tripId,
        int $seatId,
        int $customerId
    ): Booking {
        return DB::transaction(function () use (
            $tripId,
            $seatId,
            $customerId
        ) {
            // Make sure the requested trip exists.
            $trip = Trip::query()->find($tripId);

            if ($trip === null) {
                throw new TripNotFoundException();
            }

            /*
             * Lock this seat until the transaction finishes.
             *
             * If another request tries to book the same seat
             * at the same time, it must wait.
             */
            $seat = Seat::query()
                ->whereKey($seatId)
                ->where('trip_id', $tripId)
                ->lockForUpdate()
                ->first();

            if ($seat === null) {
                throw new SeatNotFoundException();
            }

            /*
             * Pending and confirmed bookings both block
             * the seat.
             *
             * Cancelled bookings do not.
             */
            $hasActiveBooking = Booking::query()
                ->where('seat_id', $seat->id)
                ->whereIn(
                    'status',
                    BookingStatus::activeValues()
                )
                ->exists();

            if ($hasActiveBooking) {
                throw new SeatAlreadyBookedException();
            }

            return Booking::query()->create([
                'customer_id' => $customerId,
                'seat_id' => $seat->id,
                'status' => BookingStatus::Pending,

                // Temporary pricing.
                // We will move pricing to a dedicated
                // service in the next commit.
                'price' => $trip->base_price,
            ]);
        });
    }

    /**
     * Get all currently available seats for a trip.
     */
    public function availableSeats(int $tripId): Collection
    {
        $tripExists = Trip::query()
            ->whereKey($tripId)
            ->exists();

        if (! $tripExists) {
            throw new TripNotFoundException();
        }

        return Seat::query()
            ->where('trip_id', $tripId)
            ->whereDoesntHave(
                'bookings',
                function ($query) {
                    $query->whereIn(
                        'status',
                        BookingStatus::activeValues()
                    );
                }
            )
            ->orderBy('seat_number')
            ->get();
    }
}

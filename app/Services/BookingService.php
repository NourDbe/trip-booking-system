<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\TripType;
use App\Exceptions\BookingNotFoundException;
use App\Exceptions\InvalidBookingStatusException;
use App\Exceptions\SeatAlreadyBookedException;
use App\Exceptions\SeatNotFoundException;
use App\Exceptions\TripNotFoundException;
use App\Models\Booking;
use App\Models\Seat;
use App\Models\Trip;
use App\Services\Pricing\RegularTripPricingService;
use App\Services\Pricing\VipTripPricingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class BookingService
{
    /**
     * Inject the pricing services.
     *
     * Regular trips use the regular pricing service.
     * VIP trips use the VIP pricing service.
     */
    public function __construct(
        private RegularTripPricingService $regularPricing,
        private VipTripPricingService $vipPricing,
    ) {
    }

    /**
     * Create a new booking safely.
     *
     * The whole operation runs inside a transaction.
     * The seat is locked to prevent two customers
     * from booking the same seat at the same time.
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
        ): Booking {

            /*
             * Find the requested trip.
             */
            $trip = Trip::query()->find($tripId);

            if ($trip === null) {
                throw new TripNotFoundException();
            }

            /*
             * Find the seat and make sure
             * it belongs to this trip.
             *
             * lockForUpdate() prevents race conditions
             * during the booking process.
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
             * Pending and Confirmed bookings
             * keep the seat unavailable.
             *
             * Cancelled bookings do not block the seat.
             */
            $hasActiveBooking = Booking::query()
                ->where('seat_id', $seat->id)
                ->whereIn(
                    'status',
                    BookingStatus::activeValues()
                )
                ->exists();

            /*
             * Prevent double booking.
             */
            if ($hasActiveBooking) {
                throw new SeatAlreadyBookedException();
            }

            /*
             * Calculate the booking price
             * depending on the trip type.
             */
            $price = match ($trip->type) {
                TripType::Regular =>
                    $this->regularPricing->calculate($trip),

                TripType::Vip =>
                    $this->vipPricing->calculate($trip),
            };

            /*
             * Create the booking.
             *
             * Every new booking starts as Pending.
             */
            return Booking::query()->create([
                'customer_id' => $customerId,
                'seat_id' => $seat->id,
                'status' => BookingStatus::Pending,
                'price' => $price,
            ]);
        });
    }

    /**
     * Get all currently available seats
     * for a specific trip.
     */
    public function availableSeats(int $tripId): Collection
    {
        /*
         * Make sure the trip exists.
         */
        $tripExists = Trip::query()
            ->whereKey($tripId)
            ->exists();

        if (! $tripExists) {
            throw new TripNotFoundException();
        }

        /*
         * Return seats that do not have
         * a Pending or Confirmed booking.
         */
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

    /**
     * Find one booking by ID.
     *
     * Related customer, seat, trip,
     * origin city and destination city
     * are loaded for the API response.
     */
    public function find(int $bookingId): Booking
    {
        $booking = Booking::query()
            ->with([
                'customer',
                'seat.trip.originCity',
                'seat.trip.destinationCity',
            ])
            ->find($bookingId);

        if ($booking === null) {
            throw new BookingNotFoundException();
        }

        return $booking;
    }

    /**
     * Confirm a pending booking.
     */
    public function confirm(int $bookingId): Booking
    {
        return DB::transaction(function () use (
            $bookingId
        ): Booking {

            /*
             * Lock the booking while changing its status.
             */
            $booking = Booking::query()
                ->lockForUpdate()
                ->find($bookingId);

            if ($booking === null) {
                throw new BookingNotFoundException();
            }

            /*
             * Only Pending bookings
             * can become Confirmed.
             */
            if ($booking->status !== BookingStatus::Pending) {
                throw new InvalidBookingStatusException(
                    'Only pending bookings can be confirmed.'
                );
            }

            /*
             * Change booking status.
             */
            $booking->update([
                'status' => BookingStatus::Confirmed,
            ]);

            /*
             * Reload the booking and its relations
             * before returning it.
             */
            return $booking
                ->refresh()
                ->load([
                    'customer',
                    'seat.trip.originCity',
                    'seat.trip.destinationCity',
                ]);
        });
    }

    /**
     * Cancel an active booking.
     *
     * Pending and Confirmed bookings
     * can both be cancelled.
     */
    public function cancel(int $bookingId): Booking
    {
        return DB::transaction(function () use (
            $bookingId
        ): Booking {

            /*
             * Lock the booking while changing its status.
             */
            $booking = Booking::query()
                ->lockForUpdate()
                ->find($bookingId);

            if ($booking === null) {
                throw new BookingNotFoundException();
            }

            /*
             * A cancelled booking cannot
             * be cancelled again.
             */
            if ($booking->status === BookingStatus::Cancelled) {
                throw new InvalidBookingStatusException(
                    'This booking is already cancelled.'
                );
            }

            /*
             * Mark the booking as cancelled.
             *
             * After cancellation, the seat
             * becomes available again.
             */
            $booking->update([
                'status' => BookingStatus::Cancelled,
            ]);

            /*
             * Reload relations for the API response.
             */
            return $booking
                ->refresh()
                ->load([
                    'customer',
                    'seat.trip.originCity',
                    'seat.trip.destinationCity',
                ]);
        });
    }
}

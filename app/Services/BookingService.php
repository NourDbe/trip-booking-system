<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\TripType;
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
     * Inject the pricing services using Laravel's
     * Dependency Injection system.
     *
     * RegularTripPricingService:
     * Used for regular trips.
     *
     * VipTripPricingService:
     * Used for VIP trips.
     *
     * Laravel's Service Container will resolve
     * the dependencies required by these services.
     */
    public function __construct(
        private RegularTripPricingService $regularPricing,
        private VipTripPricingService $vipPricing,
    ) {
    }

    /**
     * Create a new booking.
     *
     * This method contains the main booking business logic:
     *
     * 1. Check that the trip exists.
     * 2. Find and lock the requested seat.
     * 3. Make sure the seat belongs to the requested trip.
     * 4. Check that the seat does not already have
     *    an active booking.
     * 5. Calculate the booking price depending
     *    on the trip type.
     * 6. Create the booking.
     *
     * The whole process runs inside a database transaction
     * to keep the booking operation safe and consistent.
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
             *
             * We do not use findOrFail() here because we want
             * to throw our own domain exception and later
             * convert it to a consistent JSON API response.
             */
            $trip = Trip::query()->find($tripId);

            if ($trip === null) {
                throw new TripNotFoundException();
            }

            /*
             * Find the requested seat.
             *
             * The seat must:
             *
             * - Have the requested ID.
             * - Belong to the requested trip.
             *
             * lockForUpdate() locks the seat row until
             * the current transaction finishes.
             *
             * This helps prevent two requests from booking
             * the same seat at exactly the same time.
             */
            $seat = Seat::query()
                ->whereKey($seatId)
                ->where('trip_id', $tripId)
                ->lockForUpdate()
                ->first();

            /*
             * If the seat does not exist, or exists but belongs
             * to another trip, we treat it as an invalid seat
             * for this booking request.
             */
            if ($seat === null) {
                throw new SeatNotFoundException();
            }

            /*
             * Check whether this seat already has
             * an active booking.
             *
             * Active booking statuses are:
             *
             * - Pending
             * - Confirmed
             *
             * A Cancelled booking does not block the seat,
             * so the seat can be booked again.
             */
            $hasActiveBooking = Booking::query()
                ->where('seat_id', $seat->id)
                ->whereIn(
                    'status',
                    BookingStatus::activeValues()
                )
                ->exists();

            /*
             * If an active booking already exists,
             * prevent another customer from booking
             * the same seat.
             */
            if ($hasActiveBooking) {
                throw new SeatAlreadyBookedException();
            }

            /*
             * Calculate the final booking price according
             * to the trip type.
             *
             * Regular trips use RegularTripPricingService.
             * VIP trips use VipTripPricingService.
             *
             * The actual pricing calculators behind these
             * services are resolved through Laravel's
             * Service Container.
             */
            $price = match ($trip->type) {
                TripType::Regular =>
                    $this->regularPricing->calculate($trip),

                TripType::Vip =>
                    $this->vipPricing->calculate($trip),
            };

            /*
             * Create the new booking.
             *
             * New bookings start with Pending status.
             *
             * The calculated price is stored in the booking
             * itself so that changing the trip price later
             * does not change old booking prices.
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
     * Get all currently available seats for a trip.
     *
     * A seat is considered available when it does not have
     * a Pending or Confirmed booking.
     *
     * Cancelled bookings do not prevent the seat
     * from appearing as available.
     */
    public function availableSeats(int $tripId): Collection
    {
        /*
         * First make sure that the requested trip exists.
         */
        $tripExists = Trip::query()
            ->whereKey($tripId)
            ->exists();

        if (! $tripExists) {
            throw new TripNotFoundException();
        }

        /*
         * Get all seats belonging to the trip that do NOT
         * have an active booking.
         *
         * whereDoesntHave() means:
         *
         * Give me seats where no related booking exists
         * with a Pending or Confirmed status.
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
}

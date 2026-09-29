<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TripType;
use App\Models\City;
use App\Models\Customer;
use App\Models\Seat;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that available seats can be retrieved.
     */
    public function test_it_returns_available_seats(): void
    {
        // Create a regular trip.
        $trip = $this->createTrip();

        // Create one seat for the trip.
        $seat = Seat::query()->create([
            'trip_id' => $trip->id,
            'seat_number' => 1,
        ]);

        // Request available seats.
        $response = $this->getJson(
            "/api/trips/{$trip->id}/available-seats"
        );

        // The request must succeed.
        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.id', $seat->id)
            ->assertJsonPath('data.0.seat_number', 1);
    }

    /**
     * Test that a customer can create a booking.
     */
    public function test_it_creates_a_booking(): void
    {
        $trip = $this->createTrip();

        $seat = Seat::query()->create([
            'trip_id' => $trip->id,
            'seat_number' => 1,
        ]);

        $customer = $this->createCustomer();

        $response = $this->postJson(
            "/api/trips/{$trip->id}/bookings",
            [
                'customer_id' => $customer->id,
                'seat_id' => $seat->id,
            ]
        );

        /*
         * A new booking must be created
         * with Pending status.
         */
        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.price', '100.00');

        /*
         * Also make sure the booking
         * was actually saved in the database.
         */
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'seat_id' => $seat->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test that the same seat
     * cannot be booked twice.
     */
    public function test_it_prevents_double_booking(): void
    {
        $trip = $this->createTrip();

        $seat = Seat::query()->create([
            'trip_id' => $trip->id,
            'seat_number' => 1,
        ]);

        $firstCustomer = $this->createCustomer(
            'First Customer',
            '0999000001'
        );

        $secondCustomer = $this->createCustomer(
            'Second Customer',
            '0999000002'
        );

        /*
         * First customer successfully
         * books the seat.
         */
        $this->postJson(
            "/api/trips/{$trip->id}/bookings",
            [
                'customer_id' => $firstCustomer->id,
                'seat_id' => $seat->id,
            ]
        )->assertCreated();

        /*
         * Second customer tries to book
         * exactly the same seat.
         */
        $response = $this->postJson(
            "/api/trips/{$trip->id}/bookings",
            [
                'customer_id' => $secondCustomer->id,
                'seat_id' => $seat->id,
            ]
        );

        /*
         * The API must reject
         * the second booking.
         */
        $response
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'This seat is already booked for this trip.'
            );

        /*
         * Only one booking must exist
         * for this seat.
         */
        $this->assertDatabaseCount('bookings', 1);
    }

    /**
     * Test that a pending booking
     * can be confirmed.
     */
    public function test_it_confirms_a_booking(): void
    {
        $trip = $this->createTrip();

        $seat = Seat::query()->create([
            'trip_id' => $trip->id,
            'seat_number' => 1,
        ]);

        $customer = $this->createCustomer();

        /*
         * Create the booking first.
         */
        $bookingResponse = $this->postJson(
            "/api/trips/{$trip->id}/bookings",
            [
                'customer_id' => $customer->id,
                'seat_id' => $seat->id,
            ]
        );

        $bookingId = $bookingResponse->json('data.id');

        /*
         * Confirm the booking.
         */
        $response = $this->patchJson(
            "/api/bookings/{$bookingId}/confirm"
        );

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Test that cancelling a booking
     * makes its seat available again.
     */
    public function test_cancelled_booking_makes_seat_available_again(): void
    {
        $trip = $this->createTrip();

        $seat = Seat::query()->create([
            'trip_id' => $trip->id,
            'seat_number' => 1,
        ]);

        $customer = $this->createCustomer();

        /*
         * Create the booking.
         */
        $bookingResponse = $this->postJson(
            "/api/trips/{$trip->id}/bookings",
            [
                'customer_id' => $customer->id,
                'seat_id' => $seat->id,
            ]
        );

        $bookingId = $bookingResponse->json('data.id');

        /*
         * Cancel the booking.
         */
        $this->patchJson(
            "/api/bookings/{$bookingId}/cancel"
        )
            ->assertOk()
            ->assertJsonPath(
                'data.status',
                'cancelled'
            );

        /*
         * Ask the API for available seats again.
         */
        $response = $this->getJson(
            "/api/trips/{$trip->id}/available-seats"
        );

        $response->assertOk();

        /*
         * Extract all available seat IDs.
         */
        $availableSeatIds = collect(
            $response->json('data')
        )->pluck('id')->all();

        /*
         * The cancelled booking should no longer
         * block the seat.
         */
        $this->assertContains(
            $seat->id,
            $availableSeatIds
        );
    }

    /**
     * Test that VIP pricing uses
     * the VIP pricing implementation.
     */
    public function test_vip_booking_uses_vip_pricing(): void
    {
        /*
         * VIP trip with base price 120.
         */
        $trip = $this->createTrip(
            TripType::Vip,
            '120.00'
        );

        $seat = Seat::query()->create([
            'trip_id' => $trip->id,
            'seat_number' => 1,
        ]);

        $customer = $this->createCustomer();

        /*
         * Create a VIP booking.
         */
        $response = $this->postJson(
            "/api/trips/{$trip->id}/bookings",
            [
                'customer_id' => $customer->id,
                'seat_id' => $seat->id,
            ]
        );

        /*
         * VIP pricing:
         *
         * 120 × 1.25 = 150
         */
        $response
            ->assertCreated()
            ->assertJsonPath(
                'data.price',
                '150.00'
            );
    }

    /**
     * Helper method for creating
     * a test trip.
     */
    private function createTrip(
        TripType $type = TripType::Regular,
        string $basePrice = '100.00'
    ): Trip {
        $origin = City::query()->create([
            'name' => 'Damascus',
        ]);

        $destination = City::query()->create([
            'name' => 'Latakia',
        ]);

        return Trip::query()->create([
            'origin_city_id' => $origin->id,
            'destination_city_id' => $destination->id,
            'departure_at' => now()->addDay(),
            'base_price' => $basePrice,
            'type' => $type,
        ]);
    }

    /**
     * Helper method for creating
     * a test customer.
     */
    private function createCustomer(
        string $name = 'Test Customer',
        string $phone = '0999555555'
    ): Customer {
        return Customer::query()->create([
            'name' => $name,
            'phone' => $phone,
        ]);
    }
}

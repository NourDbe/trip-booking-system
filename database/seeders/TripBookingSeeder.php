<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TripType;
use App\Models\City;
use App\Models\Customer;
use App\Models\Seat;
use App\Models\Trip;
use Illuminate\Database\Seeder;

class TripBookingSeeder extends Seeder
{
    /**
     * Seed sample data for testing the booking API.
     */
    public function run(): void
    {
        /*
         * Create sample cities.
         */
        $damascus = City::query()->create([
            'name' => 'Damascus',
        ]);

        $latakia = City::query()->create([
            'name' => 'Latakia',
        ]);

        $aleppo = City::query()->create([
            'name' => 'Aleppo',
        ]);

        /*
         * Create a regular trip.
         */
        $regularTrip = Trip::query()->create([
            'origin_city_id' => $damascus->id,
            'destination_city_id' => $latakia->id,
            'departure_at' => now()->addDays(2)->setTime(8, 0),
            'base_price' => '100.00',
            'type' => TripType::Regular,
        ]);

        /*
         * Create a VIP trip.
         */
        $vipTrip = Trip::query()->create([
            'origin_city_id' => $damascus->id,
            'destination_city_id' => $aleppo->id,
            'departure_at' => now()->addDays(3)->setTime(10, 0),
            'base_price' => '120.00',
            'type' => TripType::Vip,
        ]);

        /*
         * Add 20 seats to each trip.
         */
        foreach (range(1, 20) as $seatNumber) {
            Seat::query()->create([
                'trip_id' => $regularTrip->id,
                'seat_number' => $seatNumber,
            ]);

            Seat::query()->create([
                'trip_id' => $vipTrip->id,
                'seat_number' => $seatNumber,
            ]);
        }

        /*
         * Create sample customers.
         */
        Customer::query()->create([
            'name' => 'Nour',
            'phone' => '0999000001',
        ]);

        Customer::query()->create([
            'name' => 'Shahd',
            'phone' => '0999000002',
        ]);
    }
}

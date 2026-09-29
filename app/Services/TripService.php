<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TripNotFoundException;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Collection;

final class TripService
{
    /**
     * Get upcoming trips.
     */
    public function getUpcomingTrips(): Collection
    {
        return Trip::query()
            ->with([
                'originCity',
                'destinationCity',
            ])
            ->where('departure_at', '>=', now())
            ->orderBy('departure_at')
            ->get();
    }

    /**
     * Get one trip with its cities.
     */
    public function find(int $tripId): Trip
    {
        $trip = Trip::query()
            ->with([
                'originCity',
                'destinationCity',
            ])
            ->find($tripId);

        if ($trip === null) {
            throw new TripNotFoundException();
        }

        return $trip;
    }
}

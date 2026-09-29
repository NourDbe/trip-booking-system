<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Contracts\PricingCalculatorInterface;
use App\Models\Trip;

final class RegularTripPricingService
{
    /**
     * The regular pricing calculator will be
     * resolved by Laravel's Service Container.
     */
    public function __construct(
        private PricingCalculatorInterface $calculator
    ) {
    }

    /**
     * Calculate the final price
     * for a regular trip.
     */
    public function calculate(Trip $trip): string
    {
        return $this->calculator->calculate($trip);
    }
}

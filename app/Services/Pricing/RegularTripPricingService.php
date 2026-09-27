<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Contracts\PricingCalculatorInterface;
use App\Models\Trip;

final class RegularTripPricingService
{
    public function __construct(
        private PricingCalculatorInterface $calculator
    ) {
    }

    public function calculate(Trip $trip): string
    {
        return $this->calculator->calculate($trip);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Contracts\PricingCalculatorInterface;
use App\Models\Trip;

final class RegularPricingCalculator implements PricingCalculatorInterface
{
    public function calculate(Trip $trip): string
    {
        return (string) $trip->base_price;
    }
}

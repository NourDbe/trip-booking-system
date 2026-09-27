<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Contracts\PricingCalculatorInterface;
use App\Models\Trip;

final class VipPricingCalculator implements PricingCalculatorInterface
{
    public function calculate(Trip $trip): string
    {
        return bcmul(
            (string) $trip->base_price,
            '1.25',
            2
        );
    }
}

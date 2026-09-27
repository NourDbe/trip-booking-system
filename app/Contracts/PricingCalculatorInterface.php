<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Trip;

interface PricingCalculatorInterface
{
    public function calculate(Trip $trip): string;
}

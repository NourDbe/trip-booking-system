<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PricingCalculatorInterface;
use App\Services\Pricing\RegularPricingCalculator;
use App\Services\Pricing\VipPricingCalculator;
use App\Services\Pricing\VipTripPricingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        /*
         * Default binding:
         *
         * Whenever PricingCalculatorInterface is requested,
         * Laravel will use RegularPricingCalculator
         * unless another contextual rule overrides it.
         */
        $this->app->bind(
            PricingCalculatorInterface::class,
            RegularPricingCalculator::class
        );

        /*
         * Contextual Binding:
         *
         * When VipTripPricingService specifically asks for
         * PricingCalculatorInterface, Laravel injects
         * VipPricingCalculator instead of the default one.
         */
        $this->app
            ->when(VipTripPricingService::class)
            ->needs(PricingCalculatorInterface::class)
            ->give(VipPricingCalculator::class);
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        //
    }
}

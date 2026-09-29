<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\PricingCalculatorInterface;
use App\Services\Pricing\RegularPricingCalculator;
use App\Services\Pricing\VipPricingCalculator;
use App\Services\Pricing\VipTripPricingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
   public function register(): void
{
    /*
     * Default implementation.
     *
     * Any class asking for PricingCalculatorInterface
     * receives RegularPricingCalculator by default.
     */
    $this->app->bind(
        PricingCalculatorInterface::class,
        RegularPricingCalculator::class
    );

    /*
     * Contextual Binding.
     *
     * When VipTripPricingService asks for
     * PricingCalculatorInterface, inject
     * VipPricingCalculator instead.
     */
    $this->app
        ->when(VipTripPricingService::class)
        ->needs(PricingCalculatorInterface::class)
        ->give(VipPricingCalculator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

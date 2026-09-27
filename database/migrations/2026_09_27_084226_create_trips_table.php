<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the trips table.
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();

            // City where the trip starts.
            $table->foreignId('origin_city_id')
                ->constrained('cities')
                ->restrictOnDelete();

            // City where the trip ends.
            $table->foreignId('destination_city_id')
                ->constrained('cities')
                ->restrictOnDelete();

            // Scheduled departure date and time.
            $table->dateTime('departure_at');

            // Base ticket price for the trip.
            $table->decimal('base_price', 10, 2);

            // Trip type will later help us support
            // different pricing behavior.
            $table->string('type')
                ->default('regular');

            $table->timestamps();
        });
    }

    /**
     * Drop the trips table.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};

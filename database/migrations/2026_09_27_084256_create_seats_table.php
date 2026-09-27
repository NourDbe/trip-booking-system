<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the seats table.
     */
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();

            // Every seat belongs to one specific trip.
            $table->foreignId('trip_id')
                ->constrained()
                ->cascadeOnDelete();

            // Seat number inside the trip.
            $table->unsignedSmallInteger('seat_number');

            $table->timestamps();

            // The same seat number cannot appear twice
            // inside the same trip.
            $table->unique([
                'trip_id',
                'seat_number',
            ]);
        });
    }

    /**
     * Drop the seats table.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};

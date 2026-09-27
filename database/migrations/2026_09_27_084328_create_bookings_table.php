<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the bookings table.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Customer who owns the booking.
            $table->foreignId('customer_id')
                ->constrained()
                ->restrictOnDelete();

            // Reserved seat.
            // The trip can be reached through the seat relation.
            $table->foreignId('seat_id')
                ->constrained()
                ->restrictOnDelete();

            // Booking status will later be handled
            // using BookingStatus Enum.
            $table->string('status')
                ->default('pending');

            // Store the actual price at booking time.
            $table->decimal('price', 10, 2);

            $table->timestamps();

            // Improves the query used to check whether
            // a seat already has an active booking.
            $table->index([
                'seat_id',
                'status',
            ]);
        });
    }

    /**
     * Drop the bookings table.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

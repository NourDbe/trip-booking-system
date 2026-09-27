<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the cities table.
     */
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();

            // City name must be unique to avoid duplicate cities.
            $table->string('name')->unique();

            $table->timestamps();
        });
    }

    /**
     * Drop the cities table.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};

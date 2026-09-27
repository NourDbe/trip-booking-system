<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the customers table.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // Phone is unique to reduce duplicate customer records.
            $table->string('phone')->unique();

            $table->timestamps();
        });
    }

    /**
     * Drop the customers table.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

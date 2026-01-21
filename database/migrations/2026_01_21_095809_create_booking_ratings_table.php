<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                ->constrained('bookings')   // or bookings table
                ->cascadeOnDelete();

            $table->foreignId('worker_id')
                ->constrained('workers')
                ->cascadeOnDelete();

            $table->foreignId('customer_id') // customer
                ->constrained('users')
                ->cascadeOnDelete();

            $table->tinyInteger('rating')->comment('1 to 5 stars');
            $table->text('description')->nullable();

            $table->timestamps();

            // One rating per booking
            $table->unique(['booking_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_ratings');
    }
};

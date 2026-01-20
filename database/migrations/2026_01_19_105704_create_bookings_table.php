<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->constrained('services'); // change if table name differs

            $table->date('booking_date');
            $table->string('time_slot');
            $table->text('address');

            $table->decimal('amount', 10, 2);

            $table->enum('status', ['pending', 'confirmed', 'cancelled'])
                ->default('pending');

            $table->enum('payment_status', [
                'unpaid',
                'verification_pending',
                'paid',
                'rejected'
            ])->default('unpaid');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->string('payment_ref')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // Optional performance indexes
            $table->index(['booking_date', 'time_slot']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

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

            // Relations
            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnDelete();

            $table->foreignId('worker_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Booking info
            $table->date('booking_date');
            $table->string('time_slot');
            $table->text('address');

            // Location (Bot)
            $table->string('city')->nullable();
            $table->string('pincode')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            // Pricing & payment
            $table->decimal('amount', 10, 2);

            $table->enum('status', [
                'pending',
                'confirmed',
                'completed',
                'cancelled'
            ])->default('pending');

            $table->enum('payment_status', [
                'unpaid',
                'verification_pending',
                'paid',
                'rejected'
            ])->default('unpaid');

            $table->string('payment_ref')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Bot tracking
            $table->string('source')->default('whatsapp');
            $table->string('bot_session_id')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['booking_date', 'time_slot']);
            $table->index('status');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

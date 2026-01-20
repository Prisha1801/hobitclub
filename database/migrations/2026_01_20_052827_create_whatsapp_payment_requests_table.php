<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_payment_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            $table->string('upi_link');
            $table->enum('status', [
                'sent',
                'paid_clicked',
                'expired'
            ])->default('sent');

            $table->timestamps();

            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_payment_requests');
    }
};

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
        Schema::create('worker_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->jsonb('available_dates'); 
            $table->jsonb('available_times'); 
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_availabilities');
    }
};

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
        Schema::create('serviceable_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('pincode')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['zone_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serviceable_areas');
    }
};

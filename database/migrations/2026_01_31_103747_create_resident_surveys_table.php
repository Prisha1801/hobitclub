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
        Schema::create('resident_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('surveyor_name')->nullable();

            // Basic info
            $table->string('building_name')->nullable();
            $table->string('flat_number')->nullable();
            $table->string('resident_name')->nullable();
            $table->integer('family_members')->nullable();

            // Work info
            $table->integer('maids_count')->nullable();
            $table->json('work_types')->nullable();
            $table->string('work_duration')->nullable();
            $table->tinyInteger('reliability_rating')->nullable();

            $table->json('problems_faced')->nullable();
            $table->json('preferred_time_slots')->nullable();

            $table->string('monthly_payment_range')->nullable();
            $table->json('maid_source')->nullable();

            // Digital openness
            $table->string('app_openness')->nullable();
            $table->json('convincing_factors')->nullable();
            $table->string('extra_payment')->nullable();
            $table->string('whatsapp_group_interest')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resident_surveys');
    }
};

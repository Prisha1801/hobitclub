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
        Schema::table('workers', function (Blueprint $table) {

            // Basic profile
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();

                                                   // KYC
            $table->string('id_type')->nullable(); // aadhar, voter, dl
            $table->string('id_number')->nullable();
            $table->string('id_front_path')->nullable();
            $table->string('id_back_path')->nullable();

                                                // Skills & location
            $table->json('skills')->nullable(); // ["electrician","plumber"]
            $table->string('preferred_area')->nullable();

                                                        // Availability
            $table->json('available_days')->nullable(); // ["mon","tue"]
            $table->json('available_time')->nullable(); // {"from":"09:00","to":"18:00"}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            //
        });
    }
};

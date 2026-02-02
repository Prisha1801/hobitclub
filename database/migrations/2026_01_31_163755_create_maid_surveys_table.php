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
        Schema::create('maid_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('supervisor_name')->nullable();

            $table->string('maid_name');
            $table->integer('age')->nullable();
            $table->string('mobile_number', 15)->nullable();
            $table->text('address')->nullable();

            $table->integer('houses_count')->nullable();
            $table->integer('buildings_count')->nullable();
            $table->integer('family_members')->nullable();

            $table->string('only_earning_member')->nullable();
            $table->string('has_children')->nullable();

            $table->json('work_types')->nullable();

            $table->integer('daily_work_hours')->nullable();
            $table->string('area_experience')->nullable();

            $table->string('charge_per_house')->nullable();
            $table->integer('monthly_income')->nullable();

            $table->string('paid_on_time')->nullable();
            $table->string('holidays')->nullable();
            $table->string('paid_leave')->nullable();

            $table->string('has_smartphone')->nullable();
            $table->string('uses_whatsapp')->nullable();
            $table->string('uses_online_payment')->nullable();

            $table->string('salary_online')->nullable();
            $table->string('interested_extra_work_app')->nullable();

            $table->json('work_priority')->nullable();

            $table->string('feels_safe')->nullable();
            $table->string('interested_in_benefits')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maid_surveys');
    }
};

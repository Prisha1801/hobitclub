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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }

            if (Schema::hasColumn('users', 'service_id')) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            }

            // ADD JSON COLUMNS
            $table->json('category_ids')->nullable()->after('updated_at');
            $table->json('service_ids')->nullable()->after('category_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_category_id_foreign');
            $table->dropForeign('users_service_id_foreign');
            $table->dropColumn('category_ids');
            $table->dropColumn('service_ids');
        });
    }
};

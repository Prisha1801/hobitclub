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
        // 1. Add role_id column (nullable first)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('id');
        });

        // 2. Map enum role -> role_id
        DB::statement("
            UPDATE users u
            JOIN roles r ON r.slug = u.role
            SET u.role_id = r.id
        ");

        // 3. Add foreign key constraint
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->nullOnDelete();
        });

        // 4. Drop enum column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add enum column
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'worker', 'customer'])
                  ->nullable();
        });

        // 2. Restore enum value from role_id
        DB::statement("
            UPDATE users u
            JOIN roles r ON r.id = u.role_id
            SET u.role = r.slug
        ");

        // 3. Drop foreign key and column
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};

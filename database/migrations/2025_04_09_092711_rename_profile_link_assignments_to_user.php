<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('network_profile_link_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('profile_id');
        });

        DB::statement('
            UPDATE network_profile_link_assignments AS a
            JOIN network_profiles AS p ON a.profile_id = p.id
            SET a.user_id = p.user_id
        ');

        Schema::table('network_profile_link_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $fk = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'network_profile_link_assignments'
              AND COLUMN_NAME = 'profile_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if ($fk) {
            DB::statement("ALTER TABLE network_profile_link_assignments DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }

        Schema::table('network_profile_link_assignments', function (Blueprint $table) {
            $table->dropColumn('profile_id');
        });

        Schema::rename('network_profile_link_assignments', 'user_link_assignments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('user_link_assignments', 'network_profile_link_assignments');

        Schema::table('network_profile_link_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('profile_id')->nullable();
        });

        DB::statement('
            UPDATE network_profile_link_assignments AS a
            JOIN (
                SELECT id, user_id
                FROM network_profiles
                GROUP BY user_id
            ) AS p ON a.user_id = p.user_id
            SET a.profile_id = p.id
        ');

        Schema::table('network_profile_link_assignments', function (Blueprint $table) {
            $table->foreign('profile_id')->references('id')->on('network_profiles')->onDelete('cascade');
        });

        Schema::table('network_profile_link_assignments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};

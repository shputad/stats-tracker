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
        Schema::table('network_profiles', function (Blueprint $table) {
            $table->text('api_username')->nullable()->after('account_id'); // encrypted
            $table->text('api_password')->nullable()->after('api_username'); // encrypted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('network_profiles', function (Blueprint $table) {
            $table->dropColumn(['api_username', 'api_password']);
        });
    }
};

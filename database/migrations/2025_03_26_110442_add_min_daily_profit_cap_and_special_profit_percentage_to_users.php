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
            $table->integer('min_daily_profit_cap')->nullable()->after('profit_percentage');
            $table->integer('special_profit_percentage')->nullable()->after('min_daily_profit_cap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('min_daily_profit_cap');
            $table->dropColumn('special_profit_percentage');
        });
    }
};

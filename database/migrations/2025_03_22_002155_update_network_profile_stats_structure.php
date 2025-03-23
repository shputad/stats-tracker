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
        Schema::table('network_profile_stats', function (Blueprint $table) {
            $table->date('date')->nullable()->after('profile_id');
            $table->decimal('opening_balance', 12, 2)->nullable()->after('date');
            $table->decimal('closing_balance', 12, 2)->nullable()->after('opening_balance');
            $table->decimal('current_balance', 12, 2)->nullable()->after('closing_balance');
            $table->decimal('topup_today', 12, 2)->default(0)->after('current_balance');
    
            $table->dropColumn('topups');
            $table->dropColumn('budget');
    
            $table->unique(['profile_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('network_profile_stats', function (Blueprint $table) {
            $table->dropUnique(['profile_id', 'date']);
            $table->dropColumn(['date', 'opening_balance', 'closing_balance', 'current_balance', 'topup_today']);
    
            $table->decimal('topups', 10, 2)->default(0);
            $table->decimal('budget', 10, 2)->default(0);
        });
    }
};

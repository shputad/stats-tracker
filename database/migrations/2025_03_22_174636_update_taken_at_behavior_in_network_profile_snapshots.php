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
        Schema::table('network_profile_snapshots', function (Blueprint $table) {
            $table->timestamp('taken_at')->default(DB::raw('CURRENT_TIMESTAMP'))->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('network_profile_snapshots', function (Blueprint $table) {
            $table->timestamp('taken_at')->nullable()->change();
        });
    }
};

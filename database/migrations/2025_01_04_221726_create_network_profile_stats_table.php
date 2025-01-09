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
        Schema::create('network_profile_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('network_profiles')->onDelete('cascade');
            $table->decimal('topups', 10, 2)->default(0);
            $table->decimal('budget', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_profile_stats');
    }
};

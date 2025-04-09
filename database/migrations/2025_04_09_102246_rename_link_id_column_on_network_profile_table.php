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
            $table->dropForeign(['link_id']);
        });

        Schema::table('network_profiles', function (Blueprint $table) {
            $table->renameColumn('link_id', 'deprecated_link_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('network_profiles', function (Blueprint $table) {
            $table->renameColumn('deprecated_link_id', 'link_id');
        });

        Schema::table('network_profiles', function (Blueprint $table) {
            $table->foreign('link_id')->references('id')->on('links')->onDelete('cascade');
        });
    }
};

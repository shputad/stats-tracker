<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropForeign(['channel_id']); // Drop the foreign key constraint if it exists
            $table->dropColumn('channel_id');    // Drop the column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id')->nullable(); // Add the column back
            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade'); // Re-add the foreign key
        });
    }
};

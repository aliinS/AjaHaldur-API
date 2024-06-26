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
        Schema::table('table_contents', function (Blueprint $table) {
            // Adds start_time and end_time columns as timestamps
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->boolean('supports_time_range')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table_contents', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};

<?php

use App\Models\Shift;
use App\Models\ShiftJob;
use App\Models\User;
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
        Schema::create('shift_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Shift::class);
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(ShiftJob::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_staff');
    }
};

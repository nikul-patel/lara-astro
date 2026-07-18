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
        // Recurring weekly working hours for astrologers in "manual" mode
        // (PRD §5.1). Actual bookable-slot computation (subtracting existing
        // bookings) happens in the read API (#14).
        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('astrologer_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 0 (Sunday) - 6 (Saturday)
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_slots');
    }
};

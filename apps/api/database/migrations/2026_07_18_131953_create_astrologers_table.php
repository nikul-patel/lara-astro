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
        Schema::create('astrologers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->json('specialties')->nullable();
            $table->json('languages')->nullable();
            // Per PRD §5.1: which mode this astrologer's bookable slots come
            // from. google_calendar wiring itself lands in #17.
            $table->enum('availability_mode', ['manual', 'google_calendar'])->default('manual');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('astrologers');
    }
};

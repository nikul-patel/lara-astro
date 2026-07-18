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
        // OAuth wiring itself lands in #17; this just holds the connection
        // once an astrologer has linked their Google Calendar (PRD §5.1/§9).
        // access_token/refresh_token are encrypted at the model level.
        Schema::create('google_calendar_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('astrologer_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('google_account_email');
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('token_expires_at');
            $table->string('calendar_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_calendar_connections');
    }
};

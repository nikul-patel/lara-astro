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
        // Mirrors Booking/CreateBookingInput in docs/API_CONTRACT.md.
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('astrologer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('birth_chart_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('slot');
            $table->enum('status', ['pending_payment', 'confirmed', 'completed', 'cancelled', 'no_show'])
                ->default('pending_payment');
            // Guests (no Client password) look up their booking with this
            // token; account holders use /me/bookings instead.
            $table->string('guest_token')->nullable()->unique();
            // Inline birth details when not attached to a saved BirthChart.
            $table->json('birth_details')->nullable();
            // Filled by admin when manually confirming the UPI payment (PRD §5.2).
            $table->string('upi_reference')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

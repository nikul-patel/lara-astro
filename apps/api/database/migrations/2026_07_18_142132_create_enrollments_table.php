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
        // Mirrors Enrollment/CreateEnrollmentInput in docs/API_CONTRACT.md.
        // Same pending_payment -> confirmed pattern as bookings, but a
        // narrower status set (no completed/cancelled/no_show).
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending_payment', 'confirmed'])->default('pending_payment');
            $table->string('guest_token')->nullable()->unique();
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
        Schema::dropIfExists('enrollments');
    }
};

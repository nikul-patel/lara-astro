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
        // Per-deployment overrides for the birth chart engine's region-aware
        // recommendation (PRD §8 point 4: "a business client could disable
        // Western mode entirely, or force South Indian style only").
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('astrology_western_enabled')->default(true);
            $table->string('astrology_forced_chart_style')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['astrology_western_enabled', 'astrology_forced_chart_style']);
        });
    }
};

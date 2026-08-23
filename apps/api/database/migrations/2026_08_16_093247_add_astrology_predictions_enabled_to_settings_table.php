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
        // Gates the prediction (Services/Astrology/Predictions) and remedy
        // (Services/Astrology/Remedies) content blocks, so a white-label
        // deployment can offer just the raw chart without the deeper
        // interpretive content, same rationale as astrology_western_enabled.
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('astrology_predictions_enabled')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('astrology_predictions_enabled');
        });
    }
};

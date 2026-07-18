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
        // Singleton site-wide config — mirrors Settings in
        // docs/API_CONTRACT.md. Deliberately a single-row table with typed
        // columns (not per-deployment key/value) since #10a's model is one
        // codebase redeployed per client, each with its own DB.
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name');
            $table->string('logo_path')->nullable();
            $table->json('supported_languages');
            $table->string('default_currency')->default('INR');
            $table->json('currencies');
            $table->string('upi_id')->nullable();
            $table->string('upi_qr_path')->nullable();
            $table->json('contact')->nullable();
            $table->json('social_links')->nullable();
            $table->json('legal_links')->nullable();
            $table->json('seo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

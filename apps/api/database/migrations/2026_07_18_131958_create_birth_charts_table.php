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
        // Mirrors ChartInput/ChartResult/SavedChart in docs/API_CONTRACT.md.
        // client_id is nullable: the chart tool (#25) is usable without an
        // account, per PRD §5.1 ("save to account" is optional).
        Schema::create('birth_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->date('dob');
            $table->time('time');
            $table->string('place');
            $table->enum('system', ['vedic', 'western']);
            $table->enum('chart_style', ['north_indian', 'south_indian', 'east_indian'])->nullable();
            $table->json('result'); // ChartResult payload (§16 finalizes internals)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birth_charts');
    }
};

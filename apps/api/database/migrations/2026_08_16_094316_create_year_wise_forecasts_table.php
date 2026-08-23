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
        // Unlike birth_charts.result (immutable-at-birth facts computed
        // once), a year-wise forecast is date-dependent and would go stale
        // if baked into the saved chart — it gets its own table, computed
        // and cached the first time a given (chart, year, style) is
        // requested (see YearWiseForecastController::forSavedChart()).
        Schema::create('year_wise_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('birth_chart_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->enum('style', ['simplified', 'varshphal']);
            $table->json('result');
            $table->timestamps();

            $table->unique(['birth_chart_id', 'year', 'style']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('year_wise_forecasts');
    }
};

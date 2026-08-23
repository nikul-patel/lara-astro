<?php

namespace App\Models;

use Database\Factories\YearWiseForecastFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YearWiseForecast extends Model
{
    /** @use HasFactory<YearWiseForecastFactory> */
    use HasFactory;

    protected $fillable = [
        'birth_chart_id',
        'year',
        'style',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'result' => 'array',
        ];
    }

    public function birthChart(): BelongsTo
    {
        return $this->belongsTo(BirthChart::class);
    }
}

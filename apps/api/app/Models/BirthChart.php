<?php

namespace App\Models;

use Database\Factories\BirthChartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirthChart extends Model
{
    /** @use HasFactory<BirthChartFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'dob',
        'time',
        'place',
        'system',
        'chart_style',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'result' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}

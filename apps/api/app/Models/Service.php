<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'astrologer_id',
        'slug',
        'name',
        'description',
        'duration_minutes',
        'price_inr',
        'price_usd',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_inr' => 'decimal:2',
            'price_usd' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function astrologer(): BelongsTo
    {
        return $this->belongsTo(Astrologer::class);
    }
}

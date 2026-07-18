<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'astrologer_id',
        'service_id',
        'client_id',
        'birth_chart_id',
        'slot',
        'status',
        'guest_token',
        'birth_details',
        'upi_reference',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'slot' => 'datetime',
            'birth_details' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            $booking->reference_number ??= 'BK-'.strtoupper(Str::random(8));
            $booking->guest_token ??= Str::random(40);
        });
    }

    public function astrologer(): BelongsTo
    {
        return $this->belongsTo(Astrologer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function birthChart(): BelongsTo
    {
        return $this->belongsTo(BirthChart::class);
    }
}

<?php

namespace App\Models;

use Database\Factories\AstrologerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Astrologer extends Model
{
    /** @use HasFactory<AstrologerFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'bio',
        'photo_path',
        'specialties',
        'languages',
        'availability_mode',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'specialties' => 'array',
            'languages' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class);
    }

    public function googleCalendarConnection(): HasOne
    {
        return $this->hasOne(GoogleCalendarConnection::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}

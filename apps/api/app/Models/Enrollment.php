<?php

namespace App\Models;

use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'course_id',
        'client_id',
        'status',
        'guest_token',
        'upi_reference',
        'admin_notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Enrollment $enrollment): void {
            $enrollment->reference_number ??= 'EN-'.strtoupper(Str::random(8));
            $enrollment->guest_token ??= Str::random(40);
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}

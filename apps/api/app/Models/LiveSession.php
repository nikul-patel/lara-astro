<?php

namespace App\Models;

use Database\Factories\LiveSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveSession extends Model
{
    /** @use HasFactory<LiveSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'course_id',
        'starts_at',
        'ends_at',
        'meeting_url',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}

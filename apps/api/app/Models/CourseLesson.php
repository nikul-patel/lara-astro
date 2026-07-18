<?php

namespace App\Models;

use Database\Factories\CourseLessonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseLesson extends Model
{
    /** @use HasFactory<CourseLessonFactory> */
    use HasFactory;

    protected $fillable = [
        'course_module_id',
        'title',
        'duration_minutes',
        'video_url',
        'order',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }
}

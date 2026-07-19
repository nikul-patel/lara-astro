<?php

namespace App\Http\Resources;

use App\Models\CourseLesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Unlike the public CourseLessonResource, this exposes video_url — used
 * only for confirmed enrollments (docs/API_CONTRACT.md: "GET /me/enrollments
 * ... includes lesson access ... for recorded courses").
 *
 * @mixin CourseLesson
 */
class EnrolledCourseLessonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'duration_minutes' => $this->duration_minutes,
            'video_url' => $this->video_url,
        ];
    }
}

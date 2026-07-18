<?php

namespace App\Http\Resources;

use App\Models\CourseLesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public curriculum outline only — video_url is gated behind enrollment
 * (docs/API_CONTRACT.md: "GET /me/enrollments ... includes lesson access
 * ... for recorded courses"), so it's deliberately omitted here.
 *
 * @mixin CourseLesson
 */
class CourseLessonResource extends JsonResource
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
        ];
    }
}

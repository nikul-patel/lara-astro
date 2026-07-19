<?php

namespace App\Http\Resources;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Enrollment */
class EnrollmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'status' => $this->status,
            'reference_number' => $this->reference_number,
            'guest_token' => $this->guest_token,
            'client' => new ClientResource($this->whenLoaded('client')),
            // Lesson video/live-session links only unlock once payment is
            // confirmed (docs/API_CONTRACT.md's learner-access note).
            'course' => $this->whenLoaded('course', fn () => $this->status === 'confirmed'
                ? new EnrolledCourseResource($this->course)
                : new CourseResource($this->course)),
        ];
    }
}

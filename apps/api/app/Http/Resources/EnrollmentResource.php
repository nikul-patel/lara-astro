<?php

namespace App\Http\Resources;

use App\Models\Enrollment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Enrollment */
class EnrollmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $setting = Setting::current();

        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'status' => $this->status,
            'reference_number' => $this->reference_number,
            'guest_token' => $this->guest_token,
            // Mirrors BookingResource: "same pending→confirmed pattern as
            // bookings" (docs/API_CONTRACT.md) includes current UPI details
            // for the confirmation screen, read live rather than from a
            // page-level Settings fetch that may be ISR-cached and stale.
            'upi_id' => $setting->upi_id,
            'upi_qr_url' => $setting->upi_qr_path ? Storage::disk('public')->url($setting->upi_qr_path) : null,
            'client' => new ClientResource($this->whenLoaded('client')),
            // Lesson video/live-session links only unlock once payment is
            // confirmed (docs/API_CONTRACT.md's learner-access note).
            'course' => $this->whenLoaded('course', fn () => $this->status === 'confirmed'
                ? new EnrolledCourseResource($this->course)
                : new CourseResource($this->course)),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Course */
class EnrolledCourseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'price_inr' => (float) $this->price_inr,
            'price_usd' => (float) $this->price_usd,
            'instructor' => $this->whenLoaded('instructor', fn () => $this->instructor ? new AstrologerResource($this->instructor) : null),
            'modules' => EnrolledCourseModuleResource::collection($this->whenLoaded('modules')),
            'live_sessions' => EnrolledLiveSessionResource::collection($this->whenLoaded('liveSessions')),
        ];
    }
}

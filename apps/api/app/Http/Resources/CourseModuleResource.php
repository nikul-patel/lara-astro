<?php

namespace App\Http\Resources;

use App\Models\CourseModule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CourseModule */
class CourseModuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'lessons' => CourseLessonResource::collection($this->whenLoaded('lessons')),
        ];
    }
}

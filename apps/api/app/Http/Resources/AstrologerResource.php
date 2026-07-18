<?php

namespace App\Http\Resources;

use App\Models\Astrologer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Astrologer */
class AstrologerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'bio' => $this->bio,
            'photo_url' => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
            'specialties' => $this->specialties,
            'languages' => $this->languages,
            'services' => ServiceResource::collection($this->whenLoaded('services')),
        ];
    }
}

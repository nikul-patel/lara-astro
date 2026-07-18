<?php

namespace App\Http\Resources;

use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Unlike the public LiveSessionResource, this exposes meeting_url — used
 * only for confirmed enrollments (docs/API_CONTRACT.md: "GET /me/enrollments
 * ... live session schedule/links for live courses").
 *
 * @mixin LiveSession
 */
class EnrolledLiveSessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'meeting_url' => $this->meeting_url,
        ];
    }
}

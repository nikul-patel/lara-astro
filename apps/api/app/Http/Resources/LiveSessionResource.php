<?php

namespace App\Http\Resources;

use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public schedule only — meeting_url is gated behind enrollment
 * (docs/API_CONTRACT.md: "GET /me/enrollments ... live session schedule/
 * links for live courses"), so it's deliberately omitted here.
 *
 * @mixin LiveSession
 */
class LiveSessionResource extends JsonResource
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
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AstrologerResource;
use App\Models\Astrologer;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AstrologerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $astrologers = Astrologer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(15);

        return AstrologerResource::collection($astrologers);
    }

    public function show(string $slug): AstrologerResource
    {
        $astrologer = Astrologer::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['services' => fn ($query) => $query->where('is_active', true)])
            ->firstOrFail();

        return new AstrologerResource($astrologer);
    }
}

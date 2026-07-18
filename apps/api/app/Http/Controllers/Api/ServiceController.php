<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $services = Service::query()
            ->where('is_active', true)
            ->whereHas('astrologer', fn ($query) => $query->where('is_active', true))
            ->when(
                $request->integer('astrologer_id'),
                fn ($query, $astrologerId) => $query->where('astrologer_id', $astrologerId)
            )
            ->orderBy('name')
            ->paginate(15);

        return ServiceResource::collection($services);
    }
}

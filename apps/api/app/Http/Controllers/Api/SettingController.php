<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function show(): JsonResponse
    {
        // Setting::current() firstOrCreate()s the singleton row on first
        // ever call, which would otherwise make JsonResource's default
        // status-code heuristic (freshly-created Model -> 201) return 201
        // for what is always a GET/read. Force 200 explicitly.
        return (new SettingResource(Setting::current()))
            ->response()
            ->setStatusCode(200);
    }
}

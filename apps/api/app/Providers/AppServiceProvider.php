<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Public API v1 single-resource endpoints (docs/API_CONTRACT.md)
        // return the bare object, not {"data": {...}} — paginated list
        // endpoints keep the "data"/"meta" envelope regardless, since
        // Laravel forces that wrapper whenever pagination metadata is
        // present.
        JsonResource::withoutWrapping();
    }
}

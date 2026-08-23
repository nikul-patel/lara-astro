<?php

namespace App\Http\Resources;

use App\Models\YearWiseForecast;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin YearWiseForecast
 */
class YearWiseForecastResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'year' => $this->year,
            'style' => $this->style,
            'result' => $this->result,
        ];
    }
}

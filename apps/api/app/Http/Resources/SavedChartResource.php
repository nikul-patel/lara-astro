<?php

namespace App\Http\Resources;

use App\Models\BirthChart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * BirthChart stores its calculation input as flat columns; this reassembles
 * them into the nested {input, result} shape apps/web's SavedChart type
 * expects, matching the ChartInput used to request the calculation.
 *
 * @mixin BirthChart
 */
class SavedChartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'input' => [
                'name' => $this->name,
                'dob' => $this->dob?->toDateString(),
                'time' => $this->time,
                'place' => $this->place,
                'system' => $this->system,
                'chart_style' => $this->chart_style,
            ],
            'result' => $this->result,
        ];
    }
}

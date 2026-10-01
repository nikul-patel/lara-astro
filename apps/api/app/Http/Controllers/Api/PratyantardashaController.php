<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Astrology\VimshottariDasha;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PratyantardashaController extends Controller
{
    /**
     * Divides a single Vimshottari Antardasha (identified by its lord,
     * start, and end — as returned in a `/chart` response's
     * `dasha.mahadasha[].antardashas[]` entries) into its 9 Pratyantardasha
     * sub-periods. Deliberately not part of `/chart`'s own response: eagerly
     * computing all 729 leaf periods (9 Mahadashas x 9 Antardashas x 9
     * Pratyantardashas) for every chart request would bloat an already
     * large payload for data almost no caller needs all at once — see
     * VimshottariDasha::timeline()'s class docblock.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lord' => ['required', 'string', 'in:'.implode(',', array_keys(VimshottariDasha::LORD_YEARS))],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $pratyantardashas = VimshottariDasha::pratyantardashas(
            $validated['lord'],
            CarbonImmutable::parse($validated['start']),
            CarbonImmutable::parse($validated['end']),
        );

        return response()->json(['pratyantardashas' => $pratyantardashas]);
    }
}

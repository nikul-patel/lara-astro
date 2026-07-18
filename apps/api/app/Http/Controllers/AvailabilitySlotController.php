<?php

namespace App\Http\Controllers;

use App\Models\Astrologer;
use App\Models\AvailabilitySlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvailabilitySlotController extends Controller
{
    public function index(): View
    {
        $slots = AvailabilitySlot::with('astrologer')
            ->orderBy('astrologer_id')
            ->orderBy('weekday')
            ->paginate(15);

        return view('pages.availability.index', compact('slots'));
    }

    public function create(): View
    {
        $astrologers = Astrologer::orderBy('name')->get();

        return view('pages.availability.create', compact('astrologers'));
    }

    public function store(Request $request): RedirectResponse
    {
        AvailabilitySlot::create($this->validated($request));

        return redirect()->route('availability.index')->with('status', 'Availability slot created.');
    }

    public function edit(AvailabilitySlot $availability): View
    {
        $astrologers = Astrologer::orderBy('name')->get();
        $slot = $availability;

        return view('pages.availability.edit', compact('slot', 'astrologers'));
    }

    public function update(Request $request, AvailabilitySlot $availability): RedirectResponse
    {
        $availability->update($this->validated($request));

        return redirect()->route('availability.index')->with('status', 'Availability slot updated.');
    }

    public function destroy(AvailabilitySlot $availability): RedirectResponse
    {
        $availability->delete();

        return redirect()->route('availability.index')->with('status', 'Availability slot removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'astrologer_id' => ['required', 'exists:astrologers,id'],
            'weekday' => ['required', 'integer', 'min:0', 'max:6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}

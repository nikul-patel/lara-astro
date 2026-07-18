<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $bookings = Booking::query()
            ->with(['client', 'service', 'astrologer'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('slot')
            ->paginate(15)
            ->withQueryString();

        return view('pages.bookings.index', compact('bookings', 'status'));
    }

    public function edit(Booking $booking): View
    {
        $booking->load(['client', 'service', 'astrologer', 'birthChart']);

        return view('pages.bookings.edit', compact('booking'));
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending_payment,confirmed,completed,cancelled,no_show'],
            'slot' => ['required', 'date'],
            'upi_reference' => ['nullable', 'string', 'max:255'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'confirmed' && empty($validated['upi_reference']) && empty($booking->upi_reference)) {
            return back()->withErrors([
                'upi_reference' => 'A UPI reference is required to confirm payment.',
            ])->withInput();
        }

        $booking->update($validated);

        return redirect()->route('bookings.index')->with('status', 'Booking updated.');
    }
}

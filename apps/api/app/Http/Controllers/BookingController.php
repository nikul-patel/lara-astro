<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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

        if ($validated['status'] === 'confirmed') {
            // A blank submission shouldn't wipe out an already-recorded
            // reference; only an explicit new value should overwrite it.
            $validated['upi_reference'] = ($validated['upi_reference'] ?? null) ?: $booking->upi_reference;

            if (empty($validated['upi_reference'])) {
                return back()->withErrors([
                    'upi_reference' => 'A UPI reference is required to confirm payment.',
                ])->withInput();
            }
        }

        // Capture the transition before writing so the payment-confirmed email
        // fires only once, when a booking first becomes confirmed — re-saving
        // an already-confirmed booking must not resend it.
        $justConfirmed = $validated['status'] === 'confirmed' && $booking->status !== 'confirmed';

        $booking->update($validated);

        if ($justConfirmed) {
            // Queued (BookingConfirmedMail implements ShouldQueue): payment
            // verified, sends the client their confirmation + meeting details.
            Mail::to($booking->client->email)->send(new BookingConfirmedMail($booking));
        }

        return redirect()->route('bookings.index')->with('status', 'Booking updated.');
    }
}

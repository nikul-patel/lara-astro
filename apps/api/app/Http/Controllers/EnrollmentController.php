<?php

namespace App\Http\Controllers;

use App\Mail\EnrollmentConfirmedMail;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $enrollments = Enrollment::query()
            ->with(['client', 'course'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.enrollments.index', compact('enrollments', 'status'));
    }

    public function edit(Enrollment $enrollment): View
    {
        $enrollment->load(['client', 'course.modules.lessons']);

        return view('pages.enrollments.edit', compact('enrollment'));
    }

    public function update(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending_payment,confirmed'],
            'upi_reference' => ['nullable', 'string', 'max:255'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'confirmed') {
            // A blank submission shouldn't wipe out an already-recorded
            // reference; only an explicit new value should overwrite it.
            $validated['upi_reference'] = ($validated['upi_reference'] ?? null) ?: $enrollment->upi_reference;

            if (empty($validated['upi_reference'])) {
                return back()->withErrors([
                    'upi_reference' => 'A UPI reference is required to confirm payment.',
                ])->withInput();
            }
        }

        // Capture the transition before writing so the confirmation email
        // fires only once, when an enrollment first becomes confirmed.
        $justConfirmed = $validated['status'] === 'confirmed' && $enrollment->status !== 'confirmed';

        $enrollment->update($validated);

        if ($justConfirmed) {
            // Queued (EnrollmentConfirmedMail implements ShouldQueue): payment
            // verified, unlocks course content and notifies the client.
            Mail::to($enrollment->client->email)->send(new EnrollmentConfirmedMail($enrollment));
        }

        return redirect()->route('enrollments.index')->with('status', 'Enrollment updated.');
    }
}

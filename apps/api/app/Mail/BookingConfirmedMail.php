<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the client once an admin has verified the UPI payment and marked the
 * booking confirmed (PRD §5.1) — includes the scheduled time and any meeting
 * details the admin recorded in admin_notes.
 */
class BookingConfirmedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your booking '.$this->booking->reference_number.' is confirmed',
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing(['client', 'service', 'astrologer']);

        return new Content(
            markdown: 'emails.bookings.confirmed',
            with: [
                'booking' => $this->booking,
                'setting' => Setting::current(),
            ],
        );
    }
}

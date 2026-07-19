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
 * Sent to the client the moment a booking is placed (status pending_payment),
 * carrying the UPI payment instructions they need to complete checkout.
 */
class BookingReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your booking '.$this->booking->reference_number,
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing(['client', 'service', 'astrologer']);

        return new Content(
            markdown: 'emails.bookings.received',
            with: [
                'booking' => $this->booking,
                'setting' => Setting::current(),
            ],
        );
    }
}

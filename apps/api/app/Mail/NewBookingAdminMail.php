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
 * Admin alert on a new pending booking (PRD §5.1) so the practice knows to
 * watch for the incoming UPI payment and confirm it.
 */
class NewBookingAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New booking '.$this->booking->reference_number.' awaiting payment',
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing(['client', 'service', 'astrologer']);

        return new Content(
            markdown: 'emails.bookings.admin-alert',
            with: [
                'booking' => $this->booking,
                'setting' => Setting::current(),
            ],
        );
    }
}

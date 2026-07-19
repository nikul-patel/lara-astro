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
 * Upcoming-consultation reminder, dispatched by the scheduled
 * bookings:send-reminders command for confirmed bookings whose slot is within
 * the next 24 hours.
 */
class BookingReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: your consultation '.$this->booking->reference_number.' is coming up',
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing(['client', 'service', 'astrologer']);

        return new Content(
            markdown: 'emails.bookings.reminder',
            with: [
                'booking' => $this->booking,
                'setting' => Setting::current(),
            ],
        );
    }
}

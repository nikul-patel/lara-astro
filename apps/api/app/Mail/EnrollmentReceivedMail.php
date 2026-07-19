<?php

namespace App\Mail;

use App\Models\Enrollment;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the client when a course enrollment is placed (pending_payment),
 * with the UPI payment instructions to complete checkout — mirrors the booking
 * pending→confirmed flow.
 */
class EnrollmentReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Enrollment $enrollment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your enrollment '.$this->enrollment->reference_number,
        );
    }

    public function content(): Content
    {
        $this->enrollment->loadMissing(['client', 'course']);

        return new Content(
            markdown: 'emails.enrollments.received',
            with: [
                'enrollment' => $this->enrollment,
                'setting' => Setting::current(),
            ],
        );
    }
}

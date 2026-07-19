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
 * Sent to the client once an admin has verified payment and confirmed the
 * enrollment — course content (recorded lessons / live session links) unlocks
 * in their account at this point.
 */
class EnrollmentConfirmedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Enrollment $enrollment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your enrollment '.$this->enrollment->reference_number.' is confirmed',
        );
    }

    public function content(): Content
    {
        $this->enrollment->loadMissing(['client', 'course.liveSessions']);

        return new Content(
            markdown: 'emails.enrollments.confirmed',
            with: [
                'enrollment' => $this->enrollment,
                'setting' => Setting::current(),
            ],
        );
    }
}

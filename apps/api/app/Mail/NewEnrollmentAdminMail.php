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
 * Admin alert on a new pending enrollment so the practice knows to watch for
 * the incoming UPI payment and confirm it.
 */
class NewEnrollmentAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Enrollment $enrollment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New enrollment '.$this->enrollment->reference_number.' awaiting payment',
        );
    }

    public function content(): Content
    {
        $this->enrollment->loadMissing(['client', 'course']);

        return new Content(
            markdown: 'emails.enrollments.admin-alert',
            with: [
                'enrollment' => $this->enrollment,
                'setting' => Setting::current(),
            ],
        );
    }
}

<?php

namespace App\Console\Commands;

use App\Mail\BookingReminderMail;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Emails a reminder to clients whose confirmed consultation is within the next
 * 24 hours. Idempotent: each booking is stamped with reminder_sent_at once its
 * reminder goes out, so re-running (or the hourly schedule) never double-sends.
 */
class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';

    protected $description = 'Queue upcoming-consultation reminder emails for confirmed bookings within the next 24 hours';

    public function handle(): int
    {
        $bookings = Booking::query()
            ->where('status', 'confirmed')
            ->whereNull('reminder_sent_at')
            ->whereBetween('slot', [now(), now()->addDay()])
            ->with(['client', 'service', 'astrologer'])
            ->get();

        foreach ($bookings as $booking) {
            Mail::to($booking->client->email)->send(new BookingReminderMail($booking));
            $booking->forceFill(['reminder_sent_at' => now()])->save();
        }

        $this->info("Queued {$bookings->count()} booking reminder(s).");

        return self::SUCCESS;
    }
}

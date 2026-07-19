<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Upcoming-consultation reminder emails (issue #18). Runs hourly; the command
// only emails confirmed bookings inside the next 24h that haven't been
// reminded yet, so hourly cadence stays cheap and never double-sends.
Schedule::command('bookings:send-reminders')->hourly();

<?php

use App\Services\Astrology\Panchang\SunriseSunset;
use Carbon\CarbonImmutable;

test('sunrise and sunset stay on the requested date and change smoothly across an equinox', function () {
    // Regression test: the Sun's apparent longitude and the mean longitude
    // used for the equation-of-time correction are each normalized to
    // [0, 360) independently, so right around an equinox (when the Sun's
    // longitude crosses 0°) their raw difference can read as ~360 degrees
    // instead of the true few-degree gap, throwing the equation of time
    // off by about 24 hours and landing sunrise/sunset on the WRONG
    // calendar date entirely. Checked across the March 2024 equinox at
    // the equator/prime meridian, where this was first caught.
    $dates = ['2024-03-18', '2024-03-19', '2024-03-20', '2024-03-21'];

    $previousSunriseMinutesPastMidnight = null;
    foreach ($dates as $date) {
        $localMidnight = CarbonImmutable::parse($date, 'UTC')->startOfDay();
        $moments = SunriseSunset::moments($localMidnight, 0.0, 0.0);

        expect($moments['sunrise'])->not->toBeNull();
        expect($moments['sunrise']->toDateString())->toBe($date);
        expect($moments['sunset']->toDateString())->toBe($date);

        $minutesPastMidnight = $moments['sunrise']->diffInMinutes($localMidnight);
        if ($previousSunriseMinutesPastMidnight !== null) {
            // Day-to-day drift near an equinox is on the order of seconds,
            // never hours — catches the ~1440-minute (24h) jump the bug
            // produced.
            expect(abs($minutesPastMidnight - $previousSunriseMinutesPastMidnight))->toBeLessThan(5);
        }
        $previousSunriseMinutesPastMidnight = $minutesPastMidnight;
    }
});

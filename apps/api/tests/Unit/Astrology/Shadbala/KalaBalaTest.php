<?php

use App\Services\Astrology\Shadbala\KalaBala;
use Carbon\CarbonImmutable;

test('Nathonnatha Bala peaks for day-strong planets at noon and night-strong planets at midnight', function () {
    $midnight = KalaBala::nathonnathaBala(CarbonImmutable::parse('2024-01-01 00:00:00', 'UTC'));
    expect($midnight['Sun'])->toBe(0.0);
    expect($midnight['Jupiter'])->toBe(0.0);
    expect($midnight['Venus'])->toBe(0.0);
    expect($midnight['Moon'])->toBe(60.0);
    expect($midnight['Mars'])->toBe(60.0);
    expect($midnight['Saturn'])->toBe(60.0);
    expect($midnight['Mercury'])->toBe(60.0);

    $noon = KalaBala::nathonnathaBala(CarbonImmutable::parse('2024-01-01 12:00:00', 'UTC'));
    expect($noon['Sun'])->toBe(60.0);
    expect($noon['Moon'])->toBe(0.0);
    expect($noon['Mercury'])->toBe(60.0);

    $sixAm = KalaBala::nathonnathaBala(CarbonImmutable::parse('2024-01-01 06:00:00', 'UTC'));
    expect($sixAm['Sun'])->toBe(30.0);
    expect($sixAm['Moon'])->toBe(30.0);
});

test('Paksha Bala scales with Moon-Sun elongation, doubled for the Moon regardless of waxing/waning', function () {
    // Elongation 177 degrees (just shy of full moon, Shukla paksha): pb = 177/3 = 59.
    $waxing = KalaBala::pakshaBala(0.0, 177.0);
    expect($waxing['Moon'])->toBe(118.0); // 2 * 59
    expect($waxing['Mercury'])->toBe(59.0); // Benefic: scores pb directly.
    expect($waxing['Jupiter'])->toBe(59.0);
    expect($waxing['Venus'])->toBe(59.0);
    expect($waxing['Sun'])->toBe(1.0); // Malefic: scores 60 - pb.
    expect($waxing['Mars'])->toBe(1.0);
    expect($waxing['Saturn'])->toBe(1.0);

    // Elongation 183 degrees (just past full, Krishna paksha) folds to the
    // same 177-degree angular distance -> pb is identical, and the Moon's
    // doubling applies the same regardless of phase direction.
    $waning = KalaBala::pakshaBala(0.0, 183.0);
    expect($waning['Moon'])->toBe(118.0);
});

test('Tribhaga Bala and Hora Bala identify the correct day/night third and planetary hour, hand-derived from real sunrise/sunset', function () {
    // At the equator/prime meridian on 2024-03-20 (a Wednesday, Mercury's
    // weekday): sunrise 06:04:06, sunset 18:10:46 (verified directly via
    // SunriseSunset::moments — see SunriseSunsetTest). Day length
    // 12h06m40s, so each Tribhaga third is ~4h02m13s:
    //   1st 06:04:06-10:06:19 (Mercury), 2nd 10:06:19-14:08:33 (Sun), 3rd 14:08:33-18:10:46 (Saturn).
    // Noon falls in the 2nd third -> Sun. For Hora: fraction of the day
    // elapsed by noon = (12:00:00-06:04:06)/(12h06m40s) = 0.4897, so
    // hora-within-day = floor(0.4897*12) = 5. Wednesday's lord Mercury
    // sits at Chaldean-order index 5 ([Saturn,Jupiter,Mars,Sun,Venus,
    // Mercury,Moon]); hora lord = Chaldean[(5+5)%7] = Chaldean[3] = Sun.
    $noon = CarbonImmutable::parse('2024-03-20 12:00:00', 'UTC');

    $tribhagaNoon = KalaBala::tribhagaBala($noon, 0.0, 0.0);
    expect($tribhagaNoon['Sun'])->toBe(60.0);
    expect($tribhagaNoon['Jupiter'])->toBe(60.0); // Universal lord, always 60.
    expect($tribhagaNoon['Mercury'])->toBe(0.0);
    expect($tribhagaNoon['Saturn'])->toBe(0.0);

    $horaNoon = KalaBala::horaBala($noon, 0.0, 0.0);
    expect($horaNoon['Sun'])->toBe(60.0);
    expect(array_sum($horaNoon))->toBe(60.0); // Exactly one planet scores.

    // Midnight (2024-03-20 00:00:00) is before today's sunrise, so it's
    // still inside the Panchang day that began at yesterday (Tuesday,
    // Mars's weekday)'s sunset (18:10:44) and runs to today's sunrise
    // (06:04:06) — night length 11h53m22s, thirds ~3h57m47s:
    //   1st 18:10:44-22:08:31 (Moon), 2nd 22:08:31-02:06:19 (Venus), 3rd 02:06:19-06:04:06 (Mars).
    // Midnight falls in the 2nd third -> Venus. For Hora: fraction =
    // (00:00:00-18:10:44)/(11h53m22s) = 0.4896, hora-within-night =
    // floor(0.4896*12) = 5, hora index = 12+5 = 17. Tuesday's lord Mars
    // sits at Chaldean index 2; hora lord = Chaldean[(2+17)%7] = Chaldean[5] = Mercury.
    $midnight = CarbonImmutable::parse('2024-03-20 00:00:00', 'UTC');

    $tribhagaMidnight = KalaBala::tribhagaBala($midnight, 0.0, 0.0);
    expect($tribhagaMidnight['Venus'])->toBe(60.0);
    expect($tribhagaMidnight['Jupiter'])->toBe(60.0);
    expect($tribhagaMidnight['Moon'])->toBe(0.0);
    expect($tribhagaMidnight['Mars'])->toBe(0.0);

    $horaMidnight = KalaBala::horaBala($midnight, 0.0, 0.0);
    expect($horaMidnight['Mercury'])->toBe(60.0);
    expect(array_sum($horaMidnight))->toBe(60.0);
});

test('Vara Bala awards 45 to the civil weekday lord', function () {
    // 2024-03-20 is a Wednesday -> Mercury.
    $result = KalaBala::varaBala(CarbonImmutable::parse('2024-03-20 15:00:00', 'UTC'));

    expect($result['Mercury'])->toBe(45.0);
    expect(array_sum($result))->toBe(45.0);
});

test('Ayana Bala is 60 for the Sun (doubled) and 30 for every other planet at zero declination', function () {
    $longitudes = array_fill_keys(['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'], 0.0);

    $result = KalaBala::ayanaBala($longitudes, 23.4367);

    expect($result['Sun'])->toBe(60.0);
    expect($result['Moon'])->toBe(30.0);
    expect($result['Saturn'])->toBe(30.0);
});

test('Yuddha Bala only engages planets within 1 degree, giving the stronger one the diameter-scaled difference', function () {
    $chartLongitudes = ['Mars' => 100.0, 'Saturn' => 100.5, 'Mercury' => 10.0, 'Jupiter' => 200.0, 'Venus' => 300.0];
    $otherTotals = ['Mars' => 50.0, 'Saturn' => 80.0, 'Mercury' => 10.0, 'Jupiter' => 10.0, 'Venus' => 10.0];

    // |50-80|=30, disc diameters Mars=9.4/Saturn=158.0 -> diff=148.6, 30/148.6 = 0.20.
    $result = KalaBala::yuddhaBala($chartLongitudes, $otherTotals);

    expect($result['Saturn'])->toBe(0.2);
    expect($result['Mars'])->toBe(-0.2);
    expect($result['Mercury'])->toBe(0.0);
    expect($result['Jupiter'])->toBe(0.0);
    expect($result['Venus'])->toBe(0.0);
});

test('Yuddha Bala is zero for every planet when none are within 1 degree of each other', function () {
    $chartLongitudes = ['Mars' => 10.0, 'Saturn' => 100.0, 'Mercury' => 200.0, 'Jupiter' => 250.0, 'Venus' => 300.0];
    $otherTotals = array_fill_keys(['Mars', 'Saturn', 'Mercury', 'Jupiter', 'Venus'], 50.0);

    expect(KalaBala::yuddhaBala($chartLongitudes, $otherTotals))
        ->toBe(['Mars' => 0.0, 'Mercury' => 0.0, 'Jupiter' => 0.0, 'Venus' => 0.0, 'Saturn' => 0.0]);
});

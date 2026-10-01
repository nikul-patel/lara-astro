<?php

use App\Services\Astrology\ZodiacSigns;

test('formats a mid-sign longitude as degrees and minutes', function () {
    expect(ZodiacSigns::formatDegreeInSign(132.3))->toBe('12° 18′'); // 12.3° into Leo
});

test('clamps rather than rolling over when rounding lands on the sign boundary', function () {
    // 29.999 44 (deg into sign) rounds to 29deg 60min, which would either
    // print an invalid "30 deg" or silently disagree with the separately
    // computed sign name — it must clamp to 29 deg 59 min instead.
    expect(ZodiacSigns::formatDegreeInSign(149.9994))->toBe('29° 59′');
});

test('ABBREVIATIONS has exactly one unique 3-letter entry for every sign in NAMES', function () {
    expect(array_keys(ZodiacSigns::ABBREVIATIONS))->toBe(ZodiacSigns::NAMES);

    foreach (ZodiacSigns::ABBREVIATIONS as $sign => $abbreviation) {
        expect($abbreviation)->toHaveLength(3);
        expect(ucfirst(strtolower($abbreviation)))->toBe($abbreviation); // Title case, e.g. "Sag" not "SAG"/"sag".
    }

    expect(array_unique(ZodiacSigns::ABBREVIATIONS))->toHaveCount(12);
});

test('sign name and formatted degree stay consistent at a sign boundary', function () {
    $longitude = 149.9994; // just inside Leo (120-149.999...)

    expect(ZodiacSigns::forLongitude($longitude))->toBe('Leo')
        ->and(ZodiacSigns::formatDegreeInSign($longitude))->toBe('29° 59′');
});

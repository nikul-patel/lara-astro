<?php

use App\Services\Astrology\Nakshatra;

test('0 sidereal degrees is the start of Ashwini, pada 1, ruled by Ketu', function () {
    expect(Nakshatra::forLongitude(0.0))->toBe([
        'index' => 0,
        'name' => 'Ashwini',
        'lord' => 'Ketu',
        'pada' => 1,
    ]);
});

test('each pada spans exactly 3°20\' within a nakshatra', function () {
    expect(Nakshatra::forLongitude(0.0)['pada'])->toBe(1)
        ->and(Nakshatra::forLongitude(3.0)['pada'])->toBe(1)
        ->and(Nakshatra::forLongitude(3.3334)['pada'])->toBe(2)
        ->and(Nakshatra::forLongitude(6.6667)['pada'])->toBe(3)
        ->and(Nakshatra::forLongitude(10.0)['pada'])->toBe(4);
});

test('the nakshatra index rolls over at each 13°20\' boundary', function () {
    expect(Nakshatra::forLongitude(13.3332)['index'])->toBe(0)
        ->and(Nakshatra::forLongitude(13.3334)['index'])->toBe(1)
        ->and(Nakshatra::forLongitude(13.3334)['name'])->toBe('Bharani');
});

test('a longitude just under 360° falls in the final nakshatra, Revati', function () {
    expect(Nakshatra::forLongitude(359.9)['name'])->toBe('Revati')
        ->and(Nakshatra::forLongitude(359.9)['lord'])->toBe('Mercury');
});

test('longitudes outside [0, 360) are normalized before lookup', function () {
    expect(Nakshatra::forLongitude(360.0))->toBe(Nakshatra::forLongitude(0.0))
        ->and(Nakshatra::forLongitude(-0.1)['name'])->toBe('Revati');
});

test('the 9-lord cycle repeats unchanged across all 3 passes through the 27 nakshatras', function () {
    $span = 360 / 27;

    for ($index = 0; $index < 27; $index++) {
        $lord = Nakshatra::forLongitude($index * $span + 0.001)['lord'];
        expect($lord)->toBe(Nakshatra::LORD_CYCLE[$index % 9]);
    }
});

test('fractionElapsed is 0 at the start of a nakshatra and approaches 1 at its end', function () {
    $span = 360 / 27;

    expect(Nakshatra::fractionElapsed(0.0))->toBe(0.0)
        ->and(Nakshatra::fractionElapsed($span / 2))->toBe(0.5)
        ->and(Nakshatra::fractionElapsed($span * 0.999))->toBeGreaterThan(0.99);
});

<?php

use App\Services\Astrology\Yogas\RajYoga;
use Tests\Support\YogaChartFixture;

test('a conjunct kendra lord and trikona lord form a Raj Yoga', function () {
    // Ascendant Aries: 4th house (Cancer) lord is the Moon, 5th house (Leo)
    // lord is the Sun. Placing both in the same house conjoins them.
    $houses = YogaChartFixture::houses('Aries', ['Moon' => 2, 'Sun' => 2]);

    $found = RajYoga::detect($houses);

    expect($found)->toHaveCount(1)
        ->and($found[0]['planets'])->toEqualCanonicalizing(['Moon', 'Sun'])
        ->and($found[0]['houses'])->toEqualCanonicalizing([4, 5]);
});

test('a kendra lord and trikona lord in mutual exchange (parivartana) also form a Raj Yoga', function () {
    // Ascendant Aries: 4th house lord is the Moon, 9th house (Sagittarius)
    // lord is Jupiter. Moon sits in Jupiter's house and vice versa.
    $houses = YogaChartFixture::houses('Aries', ['Moon' => 9, 'Jupiter' => 4]);

    $found = RajYoga::detect($houses);

    expect($found)->toHaveCount(1)
        ->and($found[0]['planets'])->toEqualCanonicalizing(['Moon', 'Jupiter']);
});

test('unrelated kendra and trikona lords produce no Raj Yoga', function () {
    $houses = YogaChartFixture::houses('Aries'); // no planets placed at all

    expect(RajYoga::detect($houses))->toBe([]);
});

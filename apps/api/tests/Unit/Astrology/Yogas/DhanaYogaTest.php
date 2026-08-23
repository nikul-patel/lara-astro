<?php

use App\Services\Astrology\Yogas\DhanaYoga;
use Tests\Support\YogaChartFixture;

test('a conjunct wealth-house lord and trikona lord form a Dhana Yoga', function () {
    // Ascendant Aries: 2nd house (Taurus) lord is Venus, 9th house
    // (Sagittarius) lord is Jupiter. Conjoining them triggers the yoga.
    $houses = YogaChartFixture::houses('Aries', ['Venus' => 6, 'Jupiter' => 6]);

    $found = DhanaYoga::detect($houses);

    expect($found)->toHaveCount(1)
        ->and($found[0]['planets'])->toEqualCanonicalizing(['Venus', 'Jupiter'])
        ->and($found[0]['houses'])->toEqualCanonicalizing([2, 9]);
});

test('no wealth or trikona lords linked produces no Dhana Yoga', function () {
    $houses = YogaChartFixture::houses('Aries');

    expect(DhanaYoga::detect($houses))->toBe([]);
});

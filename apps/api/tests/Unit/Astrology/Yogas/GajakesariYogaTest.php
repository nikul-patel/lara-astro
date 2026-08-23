<?php

use App\Services\Astrology\Yogas\GajakesariYoga;
use Tests\Support\YogaChartFixture;

test('Jupiter in a kendra from the Moon forms Gajakesari Yoga', function () {
    $houses = YogaChartFixture::houses('Aries', ['Moon' => 2, 'Jupiter' => 5]); // offset 3 = 4th from Moon

    $found = GajakesariYoga::detect($houses);

    expect($found)->toHaveCount(1)
        ->and($found[0]['planets'])->toEqualCanonicalizing(['Jupiter', 'Moon']);
});

test('Jupiter outside a kendra from the Moon does not form Gajakesari Yoga', function () {
    $houses = YogaChartFixture::houses('Aries', ['Moon' => 2, 'Jupiter' => 4]); // offset 2 = 3rd from Moon

    expect(GajakesariYoga::detect($houses))->toBe([]);
});

test('a missing Moon or Jupiter placement produces no Gajakesari Yoga', function () {
    $houses = YogaChartFixture::houses('Aries', ['Moon' => 2]);

    expect(GajakesariYoga::detect($houses))->toBe([]);
});

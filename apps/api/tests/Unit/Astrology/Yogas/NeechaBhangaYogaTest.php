<?php

use App\Services\Astrology\Yogas\NeechaBhangaYoga;
use Tests\Support\YogaChartFixture;

test('a debilitated planet is cancelled when its dispositor sits in a kendra from the ascendant', function () {
    // Saturn is debilitated in Aries. Aries' ruler is Mars; placing Mars in
    // a kendra house cancels Saturn's debilitation.
    $houses = YogaChartFixture::houses('Aries', ['Saturn' => 1, 'Mars' => 4]);

    $found = NeechaBhangaYoga::detect($houses);

    expect($found)->toHaveCount(1)
        ->and($found[0]['planets'])->toContain('Saturn', 'Mars');
});

test('a debilitated planet is cancelled when its exaltation-sign ruler aspects its placement', function () {
    // The Sun is debilitated in Libra. The Sun's exaltation sign is Aries,
    // ruled by Mars; Mars in the 1st house aspects the 7th (Libra).
    $houses = YogaChartFixture::houses('Aries', ['Sun' => 7, 'Mars' => 1]);

    $found = NeechaBhangaYoga::detect($houses);

    expect($found)->toHaveCount(1)
        ->and($found[0]['planets'])->toContain('Sun', 'Mars');
});

test('a debilitated planet with neither cancellation condition met is not cancelled', function () {
    // Sun debilitated in Libra, but neither Venus (Libra's ruler) nor Mars
    // (Aries' ruler, the Sun's exaltation sign) are placed anywhere.
    $houses = YogaChartFixture::houses('Aries', ['Sun' => 7]);

    expect(NeechaBhangaYoga::detect($houses))->toBe([]);
});

test('a well-dignified chart with no debilitated planets produces no Neecha Bhanga entries', function () {
    $houses = YogaChartFixture::houses('Aries', ['Sun' => 1]); // Sun in Aries: neither exalted nor debilitated

    expect(NeechaBhangaYoga::detect($houses))->toBe([]);
});

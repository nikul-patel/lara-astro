<?php

use App\Services\Astrology\Doshas\MangalDosha;
use Tests\Support\YogaChartFixture;

test('Mars in an afflicting house from both the Ascendant and Moon is flagged as Manglik', function () {
    // Aries ascendant: house 8 = Scorpio. Moon also in house 1, so "from
    // Moon" is identical to "from Ascendant" here.
    $houses = YogaChartFixture::houses('Aries', ['Mars' => 8, 'Moon' => 1]);

    $result = MangalDosha::detect($houses);

    expect($result['from_ascendant'])->toMatchArray(['afflicted' => true, 'house' => 8])
        ->and($result['from_moon'])->toMatchArray(['afflicted' => true, 'house' => 8]);
});

test('Mars is not cancelled and is_manglik is true when neither cancellation condition holds', function () {
    // Aries ascendant, Mars in house 2 = Taurus (not Mars's own sign or
    // exaltation), Jupiter absent so it can't aspect and cancel it.
    $houses = YogaChartFixture::houses('Aries', ['Mars' => 2, 'Moon' => 1]);

    $result = MangalDosha::detect($houses);

    expect($result['is_manglik'])->toBeTrue()
        ->and($result['cancelled'])->toBeFalse()
        ->and($result['cancellation_reason'])->toBeNull();
});

test('Mars in its own sign in an afflicting house cancels the dosha', function () {
    // Aries ascendant, Mars in house 8 = Scorpio, one of Mars's own signs.
    $houses = YogaChartFixture::houses('Aries', ['Mars' => 8, 'Moon' => 1]);

    $result = MangalDosha::detect($houses);

    expect($result['cancelled'])->toBeTrue()
        ->and($result['is_manglik'])->toBeFalse()
        ->and($result['cancellation_reason'])->toContain('own sign or exalted');
});

test("Jupiter's aspect on Mars's house cancels the dosha", function () {
    // Aries ascendant, Mars in house 2 = Taurus (undignified), Jupiter in
    // house 8 — Jupiter's universal 7th-house aspect reaches house 2.
    $houses = YogaChartFixture::houses('Aries', ['Mars' => 2, 'Moon' => 1, 'Jupiter' => 8]);

    $result = MangalDosha::detect($houses);

    expect($result['cancelled'])->toBeTrue()
        ->and($result['is_manglik'])->toBeFalse()
        ->and($result['cancellation_reason'])->toContain('Jupiter');
});

test('Mars outside every afflicting house produces no Mangal Dosha at all', function () {
    // Aries ascendant, Mars in house 5 = Leo, not one of [1,2,4,7,8,12].
    $houses = YogaChartFixture::houses('Aries', ['Mars' => 5, 'Moon' => 1]);

    $result = MangalDosha::detect($houses);

    expect($result['is_manglik'])->toBeFalse()
        ->and($result['from_ascendant']['afflicted'])->toBeFalse()
        ->and($result['from_moon']['afflicted'])->toBeFalse()
        ->and($result['cancelled'])->toBeFalse();
});

test('the from_venus reference is reported independently of the primary verdict', function () {
    // Aries ascendant, Mars house 5 (not afflicting from Ascendant/Moon),
    // Venus house 10 — Mars is house 8 from Venus (afflicting), but this
    // must not affect is_manglik since Venus isn't a primary reference.
    $houses = YogaChartFixture::houses('Aries', ['Mars' => 5, 'Moon' => 1, 'Venus' => 10]);

    $result = MangalDosha::detect($houses);

    expect($result['from_venus'])->toMatchArray(['afflicted' => true, 'house' => 8])
        ->and($result['is_manglik'])->toBeFalse();
});

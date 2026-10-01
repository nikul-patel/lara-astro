<?php

use App\Services\Astrology\WesternAspects;

test('detects a sextile within orb', function () {
    $aspects = WesternAspects::detect(['Sun' => 0.0, 'Moon' => 63.0]);

    expect($aspects)->toHaveCount(1)
        ->and($aspects[0])->toMatchArray(['from' => 'Sun', 'to' => 'Moon', 'aspect' => 'sextile', 'angle' => 63.0, 'orb' => 3.0]);
});

test('detects an opposition across the 0/360 wraparound', function () {
    // 0 to 185: raw diff 185 > 180, so the true angular separation is
    // 360-185 = 175, within 6 degrees of a 180 degree opposition.
    $aspects = WesternAspects::detect(['Sun' => 0.0, 'Mars' => 185.0]);

    expect($aspects)->toHaveCount(1)
        ->and($aspects[0])->toMatchArray(['from' => 'Sun', 'to' => 'Mars', 'aspect' => 'opposition', 'angle' => 175.0, 'orb' => 5.0]);
});

test('detects an exact conjunction', function () {
    $aspects = WesternAspects::detect(['Sun' => 10.0, 'Mercury' => 10.0]);

    expect($aspects)->toHaveCount(1)
        ->and($aspects[0])->toMatchArray(['aspect' => 'conjunction', 'angle' => 0.0, 'orb' => 0.0]);
});

test('a 30 degree separation matches no aspect (outside every orb)', function () {
    $aspects = WesternAspects::detect(['Sun' => 0.0, 'Jupiter' => 30.0]);

    expect($aspects)->toBeEmpty();
});

test('detect() checks every pair exactly once, not twice', function () {
    // Sun-Moon: 63 degrees -> sextile. Sun-Mars: 200 -> 160 after
    // wraparound, 20 degrees from the nearest aspect (opposition) -> none.
    // Moon-Mars: 137 degrees, 17 degrees from the nearest aspect (trine)
    // -> none. So of the 3 possible pairs, only 1 aspect is found.
    $aspects = WesternAspects::detect(['Sun' => 0.0, 'Moon' => 63.0, 'Mars' => 200.0]);

    expect($aspects)->toHaveCount(1);
    $pairs = collect($aspects)->map(fn ($a) => "{$a['from']}-{$a['to']}");
    expect($pairs->all())->toBe(['Sun-Moon']);
});

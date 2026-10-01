<?php

use App\Services\Astrology\Varga\VargaCalculator;

// Every case below is hand-worked against the classical rule cited in
// VargaCalculator's docblock/comments, not just asserted against the
// implementation's own output.

test('D1 is just the natal sign', function () {
    expect(VargaCalculator::sign('D1', 125.0))->toBe('Leo');
});

test('D2 Hora: odd sign first half is Sun\'s Hora (Leo), even sign first half is Moon\'s Hora (Cancer)', function () {
    expect(VargaCalculator::sign('D2', 5.0))->toBe('Leo') // Aries (odd), 5° < 15°
        ->and(VargaCalculator::sign('D2', 35.0))->toBe('Cancer'); // Taurus (even), 5° < 15°
});

test('D3 Drekkana: Aries 12° falls in the 5th-from-Aries (Leo) drekkana', function () {
    expect(VargaCalculator::sign('D3', 12.0))->toBe('Leo');
});

test('D4 Chaturthamsa: Taurus 20° falls in the 7th-from-Taurus (Scorpio) quarter', function () {
    expect(VargaCalculator::sign('D4', 50.0))->toBe('Scorpio'); // 30 (Taurus) + 20
});

test('D7 Saptamsa: odd sign counts from itself, even sign from the 7th sign from it', function () {
    expect(VargaCalculator::sign('D7', 190.0))->toBe('Sagittarius') // Libra (odd, index 6) + 10
        ->and(VargaCalculator::sign('D7', 40.0))->toBe('Capricorn'); // Taurus (even, index 1) + 10
});

test('D9 Navamsa: fire sign starts from Aries, water sign starts from Cancer', function () {
    expect(VargaCalculator::sign('D9', 5.0))->toBe('Taurus') // Aries (fire) + 5
        ->and(VargaCalculator::sign('D9', 90.1))->toBe('Cancer'); // Cancer (water) + 0.1
});

test('D10 Dasamsa: odd sign counts from itself, even sign from the 9th sign from it', function () {
    expect(VargaCalculator::sign('D10', 25.0))->toBe('Sagittarius') // Aries (odd) + 25
        ->and(VargaCalculator::sign('D10', 55.0))->toBe('Virgo'); // Taurus (even, index 1) + 25
});

test('D12 Dwadasamsa counts forward from the natal sign itself', function () {
    expect(VargaCalculator::sign('D12', 85.0))->toBe('Aries'); // Gemini (index 2) + 25
});

test('D16 Shodasamsa: movable sign starts from Aries', function () {
    expect(VargaCalculator::sign('D16', 10.0))->toBe('Virgo'); // Aries (movable) + 10
});

test('D20 Vimsamsa: fixed sign starts from Sagittarius', function () {
    expect(VargaCalculator::sign('D20', 35.0))->toBe('Pisces'); // Taurus (fixed, index 1) + 5
});

test('D24 Chaturvimsamsa: odd sign starts from Leo, even sign starts from Cancer', function () {
    expect(VargaCalculator::sign('D24', 121.0))->toBe('Leo') // Leo (odd, index 4) + 1
        ->and(VargaCalculator::sign('D24', 91.0))->toBe('Cancer'); // Cancer (even, index 3) + 1
});

test('D27 Saptavimsamsa: water sign starts from Capricorn', function () {
    expect(VargaCalculator::sign('D27', 212.0))->toBe('Aquarius'); // Scorpio (water, index 7) + 2
});

test('D30 Trimsamsa: odd sign 5-10° is Saturn\'s span (Aquarius), even sign 12-20° is Jupiter\'s span (Pisces)', function () {
    expect(VargaCalculator::sign('D30', 7.0))->toBe('Aquarius') // Aries (odd) at 7
        ->and(VargaCalculator::sign('D30', 45.0))->toBe('Pisces'); // Taurus (even) at 15
});

test('D40 Khavedamsa: odd sign starts from Aries', function () {
    expect(VargaCalculator::sign('D40', 61.0))->toBe('Taurus'); // Gemini (odd, index 2) + 1
});

test('D45 Akshavedamsa: fixed sign starts from Leo', function () {
    expect(VargaCalculator::sign('D45', 211.0))->toBe('Virgo'); // Scorpio (fixed, index 7) + 1
});

test('D60 Shashtiamsa counts forward from the natal sign itself', function () {
    expect(VargaCalculator::sign('D60', 0.5))->toBe('Taurus'); // Aries + 0.5
});

test('housesFromSigns places each planet in its varga-sign house, whole-sign style', function () {
    $houses = VargaCalculator::housesFromSigns('Leo', ['Sun' => 'Leo', 'Moon' => 'Scorpio']);

    expect($houses)->toHaveCount(12)
        ->and($houses[0])->toMatchArray(['number' => 1, 'sign' => 'Leo', 'planets' => ['Sun']])
        ->and($houses[3])->toMatchArray(['number' => 4, 'sign' => 'Scorpio', 'planets' => ['Moon']]);
});

test('an unknown varga code throws', function () {
    VargaCalculator::sign('D99', 10.0);
})->throws(InvalidArgumentException::class);

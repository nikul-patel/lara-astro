<?php

use App\Services\Astrology\Aspects;

test('every planet fully aspects the 7th house from itself', function () {
    expect(Aspects::aspectedHouses('Venus', 1))->toContain(7)
        ->and(Aspects::aspectedHouses('Mercury', 5))->toContain(11);
});

test('Mars additionally aspects the 4th and 8th houses from itself', function () {
    $aspected = Aspects::aspectedHouses('Mars', 1);

    expect($aspected)->toContain(4, 7, 8)->toHaveCount(3);
});

test('Jupiter additionally aspects the 5th and 9th houses from itself', function () {
    $aspected = Aspects::aspectedHouses('Jupiter', 1);

    expect($aspected)->toContain(5, 7, 9)->toHaveCount(3);
});

test('Saturn additionally aspects the 3rd and 10th houses from itself', function () {
    $aspected = Aspects::aspectedHouses('Saturn', 1);

    expect($aspected)->toContain(3, 7, 10)->toHaveCount(3);
});

test('house-offset counting wraps around past the 12th house', function () {
    // From house 10, the 7th aspect lands on house 4 (10 -> 11 -> 12 -> 1 -> 2 -> 3 -> 4).
    expect(Aspects::aspectedHouses('Venus', 10))->toBe([4]);
});

test('aspectsHouse is a convenience wrapper around aspectedHouses', function () {
    expect(Aspects::aspectsHouse('Mars', 1, 8))->toBeTrue()
        ->and(Aspects::aspectsHouse('Mars', 1, 5))->toBeFalse();
});

<?php

use App\Services\Numerology\DigitReducer;

test('single-digit input is returned unchanged', function () {
    expect(DigitReducer::reduce(7))->toBe(7);
});

test('a multi-digit number with no Master Number along the way reduces to a single digit', function () {
    // 1994 -> 1+9+9+4=23 -> 2+3=5
    expect(DigitReducer::reduce(1994))->toBe(5)
        // 24 -> 2+4=6
        ->and(DigitReducer::reduce(24))->toBe(6);
});

test('Master Numbers 11, 22, and 33 given directly are never reduced further', function () {
    expect(DigitReducer::reduce(11))->toBe(11)
        ->and(DigitReducer::reduce(22))->toBe(22)
        ->and(DigitReducer::reduce(33))->toBe(33);
});

test('a Master Number reached mid-reduction stops there instead of reducing further', function () {
    // 29 -> 2+9=11 (a Master Number) -> must stop at 11, not continue to 1+1=2
    expect(DigitReducer::reduce(29))->toBe(11)
        // 499 -> 4+9+9=22 -> must stop at 22, not continue to 2+2=4
        ->and(DigitReducer::reduce(499))->toBe(22)
        // 6999 -> 6+9+9+9=33 -> must stop at 33, not continue to 3+3=6
        ->and(DigitReducer::reduce(6999))->toBe(33);
});

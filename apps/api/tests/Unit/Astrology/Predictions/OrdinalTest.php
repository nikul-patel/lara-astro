<?php

use App\Services\Astrology\Predictions\Ordinal;

test('suffix() applies the correct English ordinal suffix, including the 11-13 exception', function (int $number, string $expected) {
    expect(Ordinal::suffix($number))->toBe($expected);
})->with([
    [1, '1st'], [2, '2nd'], [3, '3rd'], [4, '4th'], [5, '5th'],
    [9, '9th'], [10, '10th'],
    [11, '11th'], [12, '12th'], [13, '13th'],
    [21, '21st'], [22, '22nd'], [23, '23rd'], [24, '24th'],
    [111, '111th'], [112, '112th'], [113, '113th'], [121, '121st'],
]);

<?php

use App\Services\Astrology\Ashtakvarga\BinduTables;

test('each planet\'s bindu table sums to its known classical total', function () {
    foreach (BinduTables::KNOWN_TOTALS as $planet => $expectedTotal) {
        $total = array_sum(array_map('count', BinduTables::TABLES[$planet]));

        expect($total)->toBe($expectedTotal, "{$planet}'s bindu table should sum to {$expectedTotal}, got {$total}.");
    }
});

test('the known classical totals sum to 337, the standard Sarvashtakavarga grand total', function () {
    expect(array_sum(BinduTables::KNOWN_TOTALS))->toBe(337);
});

test('every contributor house offset is within the valid 1-12 range', function () {
    foreach (BinduTables::TABLES as $subject => $contributors) {
        foreach ($contributors as $contributor => $houses) {
            foreach ($houses as $house) {
                expect($house)->toBeGreaterThanOrEqual(1)
                    ->toBeLessThanOrEqual(12, "{$subject}'s table for {$contributor} has an out-of-range house {$house}.");
            }
        }
    }
});

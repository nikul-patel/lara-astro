<?php

test('dividing an antardasha returns 9 pratyantardasha sub-periods cycling from its own lord', function () {
    $response = $this->postJson('/api/v1/dasha/pratyantardasha', [
        'lord' => 'Mars',
        'start' => '2010-01-01',
        'end' => '2012-01-01',
    ]);

    $response->assertOk()->assertJsonCount(9, 'pratyantardashas');

    $periods = $response->json('pratyantardashas');
    expect($periods[0]['lord'])->toBe('Mars')
        ->and($periods[0]['start'])->toBe('2010-01-01')
        ->and(end($periods)['end'])->toBe('2012-01-01');
});

test('validation rejects an unknown lord and a non-chronological range', function () {
    $this->postJson('/api/v1/dasha/pratyantardasha', [
        'lord' => 'Pluto', 'start' => '2010-01-01', 'end' => '2012-01-01',
    ])->assertJsonValidationErrors('lord');

    $this->postJson('/api/v1/dasha/pratyantardasha', [
        'lord' => 'Mars', 'start' => '2012-01-01', 'end' => '2010-01-01',
    ])->assertJsonValidationErrors('end');
});

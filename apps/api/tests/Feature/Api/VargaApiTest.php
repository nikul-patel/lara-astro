<?php

test('calculating a divisional chart returns the varga, ascendant, and 12 whole-sign houses', function () {
    $response = $this->postJson('/api/v1/varga', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'varga' => 'D9',
    ]);

    $response->assertOk()
        ->assertJsonPath('varga', 'D9')
        ->assertJsonCount(12, 'houses');

    $houseNumbers = collect($response->json('houses'))->pluck('number');
    expect($houseNumbers->all())->toBe(range(1, 12));

    $allPlacedPlanets = collect($response->json('houses'))->pluck('planets')->flatten();
    expect($allPlacedPlanets)->toContain('Sun', 'Moon', 'Rahu', 'Ketu');
});

test('every documented varga code is accepted', function () {
    foreach (['D1', 'D2', 'D3', 'D4', 'D7', 'D9', 'D10', 'D12', 'D16', 'D20', 'D24', 'D27', 'D30', 'D40', 'D45', 'D60'] as $varga) {
        $this->postJson('/api/v1/varga', [
            'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
            'varga' => $varga,
        ])->assertOk()->assertJsonPath('varga', $varga);
    }
});

test('an unknown varga code is rejected with a validation error', function () {
    $this->postJson('/api/v1/varga', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'varga' => 'D99',
    ])->assertJsonValidationErrors('varga');
});

<?php

test('calculating doshas returns both manglik and kaal_sarp analyses', function () {
    $response = $this->postJson('/api/v1/doshas', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
    ]);

    $response->assertOk()->assertJsonStructure([
        'manglik' => ['is_manglik', 'from_ascendant', 'from_moon', 'from_venus', 'cancelled', 'cancellation_reason', 'description'],
        'kaal_sarp' => ['present', 'type', 'rahu_house', 'description'],
    ]);
});

test('validation rejects an invalid time', function () {
    $this->postJson('/api/v1/doshas', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => 'not-a-time', 'place' => 'Delhi, India',
    ])->assertJsonValidationErrors('time');
});

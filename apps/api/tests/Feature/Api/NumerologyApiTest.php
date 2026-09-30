<?php

test('calculating a numerology reading returns all four numbers with meanings', function () {
    $response = $this->postJson('/api/v1/numerology', [
        'name' => 'John Smith',
        'dob' => '1990-05-15',
    ]);

    $response->assertOk()->assertJsonStructure([
        'life_path' => ['number', 'meaning'],
        'destiny' => ['number', 'meaning'],
        'soul_urge' => ['number', 'meaning'],
        'personality' => ['number', 'meaning'],
    ]);

    expect($response->json('life_path.number'))->toBe(3) // matches LifePathNumberTest's hand-verified case
        ->and($response->json('destiny.meaning'))->not->toBeEmpty();
});

test('validation rejects a missing required field', function () {
    $this->postJson('/api/v1/numerology', ['dob' => '1990-05-15'])
        ->assertJsonValidationErrors('name');
});

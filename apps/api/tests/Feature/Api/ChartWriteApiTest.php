<?php

use App\Models\BirthChart;
use App\Models\Client;

test('an authenticated client can save a calculated chart', function () {
    $client = Client::factory()->create();
    $token = $client->createToken('test')->plainTextToken;

    $response = $this->postJson('/api/v1/charts', [
        'name' => 'Ananya Singh',
        'dob' => '1994-05-12',
        'time' => '14:30',
        'place' => 'Jaipur, India',
        'system' => 'vedic',
        'chart_style' => 'north_indian',
        'result' => ['timezone' => 'Asia/Kolkata', 'planetary_positions' => [], 'houses' => []],
    ], ['Authorization' => "Bearer {$token}"]);

    $response->assertStatus(201)
        ->assertJsonPath('input.name', 'Ananya Singh')
        ->assertJsonPath('input.place', 'Jaipur, India')
        ->assertJsonPath('result.timezone', 'Asia/Kolkata');

    expect(BirthChart::where('client_id', $client->id)->count())->toBe(1);
});

test('saving a chart without an overridden system falls back to the result\'s system', function () {
    $client = Client::factory()->create();
    $token = $client->createToken('test')->plainTextToken;

    $response = $this->postJson('/api/v1/charts', [
        'name' => 'Ananya Singh',
        'dob' => '1994-05-12',
        'time' => '14:30',
        'place' => 'Jaipur, India',
        'result' => ['timezone' => 'Asia/Kolkata', 'system' => 'vedic', 'planetary_positions' => [], 'houses' => []],
    ], ['Authorization' => "Bearer {$token}"]);

    $response->assertStatus(201)->assertJsonPath('input.system', 'vedic');
    expect(BirthChart::where('client_id', $client->id)->firstOrFail()->system)->toBe('vedic');
});

test('saving a chart requires authentication', function () {
    $this->postJson('/api/v1/charts', [
        'name' => 'Ananya Singh',
        'dob' => '1994-05-12',
        'time' => '14:30',
        'place' => 'Jaipur, India',
        'result' => [],
    ])->assertStatus(401);
});

test('an authenticated client can list only their own saved charts', function () {
    $client = Client::factory()->create();
    $otherClient = Client::factory()->create();
    BirthChart::factory()->create(['client_id' => $client->id, 'name' => 'Mine']);
    BirthChart::factory()->create(['client_id' => $otherClient->id, 'name' => 'Not Mine']);
    $token = $client->createToken('test')->plainTextToken;

    $response = $this->getJson('/api/v1/me/charts', ['Authorization' => "Bearer {$token}"]);

    $response->assertOk();
    expect($response->json())->toHaveCount(1)
        ->and($response->json('0.name'))->toBe('Mine');
});

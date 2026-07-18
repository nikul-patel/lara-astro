<?php

use App\Models\Client;
use Laravel\Sanctum\PersonalAccessToken;

test('a new client can register and receives a bearer token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Priya Sharma',
        'email' => 'priya@example.com',
        'phone' => '+91 98765 43210',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('client.email', 'priya@example.com')
        ->assertJsonStructure(['token', 'client' => ['id', 'name', 'email', 'phone']]);

    $client = Client::where('email', 'priya@example.com')->firstOrFail();
    expect($client->password)->not->toBeNull();
});

test('a client can register without a phone number', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'No Phone',
        'email' => 'nophone@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);
    expect(Client::where('email', 'nophone@example.com')->firstOrFail()->phone)->toBe('');
});

test('registering with an email that already has a password is rejected', function () {
    Client::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Someone Else',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertJsonValidationErrors('email')->assertStatus(422);
});

test('registering upgrades an existing guest client instead of erroring', function () {
    $guest = Client::factory()->guest()->create(['email' => 'guest@example.com', 'name' => 'Guest Name']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Real Name',
        'email' => 'guest@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);
    expect(Client::query()->where('email', 'guest@example.com')->count())->toBe(1);
    $upgraded = $guest->fresh();
    expect($upgraded->name)->toBe('Real Name')
        ->and($upgraded->password)->not->toBeNull();
});

test('a client can log in with correct credentials', function () {
    $client = Client::factory()->create(['email' => 'login@example.com']);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()->assertJsonStructure(['token', 'client']);
});

test('login fails with the wrong password', function () {
    Client::factory()->create(['email' => 'login2@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'login2@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(422);
});

test('a guest client (no password) cannot log in', function () {
    Client::factory()->guest()->create(['email' => 'guestonly@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'guestonly@example.com',
        'password' => 'anything',
    ])->assertStatus(422);
});

test('an authenticated client can fetch their own profile via /me', function () {
    $client = Client::factory()->create();
    $token = $client->createToken('test')->plainTextToken;

    $this->getJson('/api/v1/me', ['Authorization' => "Bearer {$token}"])
        ->assertOk()
        ->assertJsonPath('client.email', $client->email);
});

test('an unauthenticated request to /me is rejected', function () {
    $this->getJson('/api/v1/me')->assertStatus(401);
});

test('logout revokes the current token', function () {
    $client = Client::factory()->create();
    $accessToken = $client->createToken('test');
    $tokenId = $accessToken->accessToken->id;

    $this->postJson('/api/v1/auth/logout', [], ['Authorization' => "Bearer {$accessToken->plainTextToken}"])
        ->assertStatus(204);

    expect(PersonalAccessToken::find($tokenId))->toBeNull();
});

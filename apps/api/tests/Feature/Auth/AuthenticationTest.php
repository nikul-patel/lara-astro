<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

test('users can sign in with correct credentials', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
});

test('users cannot sign in with incorrect password', function () {
    $user = User::factory()->create();

    $response = $this
        ->from('/signin')
        ->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

    $response->assertRedirect('/signin');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('authenticated users can sign out', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/signin');
    $this->assertGuest();
});

test('roles seeded by RoleSeeder are assignable', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Admin');

    expect($user->hasRole('Admin'))->toBeTrue();
    expect($user->hasRole('Astrologer'))->toBeFalse();
});

<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'password' => 'current-password',
    ]);
});

test('guests are redirected away from the profile page', function () {
    $this->get('/profile')->assertRedirect('/signin');
});

test('a user can view their own profile', function () {
    $this->actingAs($this->user)->get('/profile')
        ->assertOk()
        ->assertSee('Original Name')
        ->assertSee('original@example.com');
});

test('a user can update their name and email', function () {
    $this->actingAs($this->user)->put('/profile', [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ])->assertRedirect('/profile');

    $this->user->refresh();
    expect($this->user->name)->toBe('Updated Name')
        ->and($this->user->email)->toBe('updated@example.com');
});

test('a user cannot update their email to one already taken', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($this->user)->put('/profile', [
        'name' => 'Updated Name',
        'email' => 'taken@example.com',
    ])->assertSessionHasErrors('email');
});

test('a user can change their password', function () {
    $this->actingAs($this->user)->put('/profile/password', [
        'current_password' => 'current-password',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertRedirect('/profile');

    expect(Hash::check('brand-new-password', $this->user->refresh()->password))->toBeTrue();
});

test('changing the password requires the correct current password', function () {
    $this->actingAs($this->user)->put('/profile/password', [
        'current_password' => 'wrong-password',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check('current-password', $this->user->refresh()->password))->toBeTrue();
});

test('changing the password requires confirmation to match', function () {
    $this->actingAs($this->user)->put('/profile/password', [
        'current_password' => 'current-password',
        'password' => 'brand-new-password',
        'password_confirmation' => 'does-not-match',
    ])->assertSessionHasErrors('password');
});

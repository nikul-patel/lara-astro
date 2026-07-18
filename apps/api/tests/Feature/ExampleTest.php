<?php

use App\Models\User;

test('guests are redirected to sign in', function () {
    $response = $this->get('/');

    $response->assertRedirect('/signin');
});

test('authenticated users see the dashboard', function () {
    $response = $this->actingAs(User::factory()->create())->get('/');

    $response->assertStatus(200);
});

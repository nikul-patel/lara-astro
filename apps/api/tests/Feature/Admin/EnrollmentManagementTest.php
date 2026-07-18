<?php

use App\Models\Enrollment;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

test('non-admin users cannot reach enrollment management', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    $this->actingAs($editor)->get('/enrollments')->assertForbidden();
});

test('guests are redirected away from enrollment management', function () {
    $this->get('/enrollments')->assertRedirect('/signin');
});

test('an admin can list and filter enrollments by status', function () {
    $pending = Enrollment::factory()->create(['status' => 'pending_payment']);
    $confirmed = Enrollment::factory()->confirmed()->create();

    $response = $this->actingAs($this->admin)->get('/enrollments?status=confirmed');

    $response->assertOk()
        ->assertSee($confirmed->reference_number)
        ->assertDontSee($pending->reference_number);
});

test('an admin can view an enrollment with client and course details', function () {
    $enrollment = Enrollment::factory()->create();

    $this->actingAs($this->admin)
        ->get("/enrollments/{$enrollment->id}/edit")
        ->assertOk()
        ->assertSee($enrollment->client->email)
        ->assertSee($enrollment->course->title);
});

test('confirming an enrollment requires a upi reference', function () {
    $enrollment = Enrollment::factory()->create(['status' => 'pending_payment']);

    $this->actingAs($this->admin)->put("/enrollments/{$enrollment->id}", [
        'status' => 'confirmed',
    ])->assertSessionHasErrors('upi_reference');

    expect($enrollment->fresh()->status)->toBe('pending_payment');
});

test('an admin can confirm an enrollment with a upi reference', function () {
    $enrollment = Enrollment::factory()->create(['status' => 'pending_payment']);

    $this->actingAs($this->admin)->put("/enrollments/{$enrollment->id}", [
        'status' => 'confirmed',
        'upi_reference' => 'UPI-TEST1234',
    ])->assertRedirect('/enrollments');

    $fresh = $enrollment->fresh();
    expect($fresh->status)->toBe('confirmed')
        ->and($fresh->upi_reference)->toBe('UPI-TEST1234');
});

test('leaving the upi reference blank on an already-confirmed enrollment keeps the existing reference', function () {
    $enrollment = Enrollment::factory()->confirmed()->create(['upi_reference' => 'UPI-EXISTING']);

    $this->actingAs($this->admin)->put("/enrollments/{$enrollment->id}", [
        'status' => 'confirmed',
        'upi_reference' => '',
    ])->assertRedirect('/enrollments');

    expect($enrollment->fresh()->upi_reference)->toBe('UPI-EXISTING');
});

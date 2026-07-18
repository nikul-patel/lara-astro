<?php

use App\Models\Booking;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

test('non-admin users cannot reach booking management', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    $this->actingAs($editor)->get('/bookings')->assertForbidden();
});

test('guests are redirected away from booking management', function () {
    $this->get('/bookings')->assertRedirect('/signin');
});

test('an admin can list bookings', function () {
    $booking = Booking::factory()->create();

    $this->actingAs($this->admin)
        ->get('/bookings')
        ->assertOk()
        ->assertSee($booking->reference_number);
});

test('an admin can filter bookings by status', function () {
    $pending = Booking::factory()->create(['status' => 'pending_payment']);
    $confirmed = Booking::factory()->confirmed()->create();

    $response = $this->actingAs($this->admin)->get('/bookings?status=confirmed');

    $response->assertOk()
        ->assertSee($confirmed->reference_number)
        ->assertDontSee($pending->reference_number);
});

test('an admin can view a booking with client and birth details', function () {
    $booking = Booking::factory()->create(['birth_details' => ['place' => 'Mumbai, India']]);

    $this->actingAs($this->admin)
        ->get("/bookings/{$booking->id}/edit")
        ->assertOk()
        ->assertSee($booking->client->email)
        ->assertSee('Mumbai, India');
});

test('confirming a booking requires a upi reference', function () {
    $booking = Booking::factory()->create(['status' => 'pending_payment']);

    $this->actingAs($this->admin)->put("/bookings/{$booking->id}", [
        'status' => 'confirmed',
        'slot' => $booking->slot->format('Y-m-d\TH:i'),
    ])->assertSessionHasErrors('upi_reference');

    expect($booking->fresh()->status)->toBe('pending_payment');
});

test('an admin can confirm a booking with a upi reference', function () {
    $booking = Booking::factory()->create(['status' => 'pending_payment']);

    $this->actingAs($this->admin)->put("/bookings/{$booking->id}", [
        'status' => 'confirmed',
        'slot' => $booking->slot->format('Y-m-d\TH:i'),
        'upi_reference' => 'UPI-TEST1234',
    ])->assertRedirect('/bookings');

    $fresh = $booking->fresh();
    expect($fresh->status)->toBe('confirmed')
        ->and($fresh->upi_reference)->toBe('UPI-TEST1234');
});

test('leaving the upi reference blank on an already-confirmed booking keeps the existing reference', function () {
    $booking = Booking::factory()->confirmed()->create(['upi_reference' => 'UPI-EXISTING']);

    $this->actingAs($this->admin)->put("/bookings/{$booking->id}", [
        'status' => 'confirmed',
        'slot' => $booking->slot->format('Y-m-d\TH:i'),
        'upi_reference' => '',
    ])->assertRedirect('/bookings');

    expect($booking->fresh()->upi_reference)->toBe('UPI-EXISTING');
});

test('an admin can reschedule and cancel a booking with notes', function () {
    $booking = Booking::factory()->confirmed()->create();
    $newSlot = now()->addWeek();

    $this->actingAs($this->admin)->put("/bookings/{$booking->id}", [
        'status' => 'cancelled',
        'slot' => $newSlot->format('Y-m-d\TH:i'),
        'admin_notes' => 'Client requested cancellation.',
    ])->assertRedirect('/bookings');

    $fresh = $booking->fresh();
    expect($fresh->status)->toBe('cancelled')
        ->and($fresh->slot->format('Y-m-d H:i'))->toBe($newSlot->format('Y-m-d H:i'))
        ->and($fresh->admin_notes)->toBe('Client requested cancellation.');
});

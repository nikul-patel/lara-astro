<?php

use App\Models\Astrologer;
use App\Models\AvailabilitySlot;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

test('non-admin users cannot reach availability management', function () {
    $this->actingAs(User::factory()->create())->get('/availability')->assertForbidden();
});

test('an admin can list availability slots', function () {
    $astrologer = Astrologer::factory()->create(['name' => 'Rohan Mehta']);
    AvailabilitySlot::factory()->for($astrologer)->create(['weekday' => 1]);

    $this->actingAs($this->admin)
        ->get('/availability')
        ->assertOk()
        ->assertSee('Rohan Mehta')
        ->assertSee('Monday');
});

test('an admin can create an availability slot', function () {
    $astrologer = Astrologer::factory()->create();

    $response = $this->actingAs($this->admin)->post('/availability', [
        'astrologer_id' => $astrologer->id,
        'weekday' => 3,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'is_active' => '1',
    ]);

    $response->assertRedirect('/availability');

    $slot = AvailabilitySlot::firstOrFail();
    expect($slot->astrologer_id)->toBe($astrologer->id)
        ->and($slot->weekday)->toBe(3)
        ->and($slot->is_active)->toBeTrue();
});

test('end time must be after start time', function () {
    $astrologer = Astrologer::factory()->create();

    $this->actingAs($this->admin)->post('/availability', [
        'astrologer_id' => $astrologer->id,
        'weekday' => 3,
        'start_time' => '17:00',
        'end_time' => '09:00',
    ])->assertSessionHasErrors('end_time');
});

test('an admin can update an availability slot', function () {
    $slot = AvailabilitySlot::factory()->create(['weekday' => 1]);

    $this->actingAs($this->admin)->put("/availability/{$slot->id}", [
        'astrologer_id' => $slot->astrologer_id,
        'weekday' => 5,
        'start_time' => '10:00',
        'end_time' => '14:00',
    ])->assertRedirect('/availability');

    expect($slot->fresh()->weekday)->toBe(5);
});

test('an admin can delete an availability slot', function () {
    $slot = AvailabilitySlot::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/availability/{$slot->id}")
        ->assertRedirect('/availability');

    expect(AvailabilitySlot::find($slot->id))->toBeNull();
});

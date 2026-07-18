<?php

use App\Models\Astrologer;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

test('non-admin users cannot reach service management', function () {
    $astrologerRoleUser = User::factory()->create();
    $astrologerRoleUser->assignRole('Astrologer');

    $this->actingAs($astrologerRoleUser)->get('/services')->assertForbidden();
});

test('an admin can list services with their astrologer', function () {
    $astrologer = Astrologer::factory()->create(['name' => 'Meera Rao']);
    Service::factory()->for($astrologer)->create(['name' => 'Career Reading']);

    $this->actingAs($this->admin)
        ->get('/services')
        ->assertOk()
        ->assertSee('Career Reading')
        ->assertSee('Meera Rao');
});

test('an admin can create a service for an astrologer with explicit INR and USD pricing', function () {
    $astrologer = Astrologer::factory()->create();

    $response = $this->actingAs($this->admin)->post('/services', [
        'astrologer_id' => $astrologer->id,
        'name' => '30-Minute Consultation',
        'description' => 'A quick reading.',
        'duration_minutes' => 30,
        'price_inr' => 999,
        'price_usd' => 12.99,
        'is_active' => '1',
    ]);

    $response->assertRedirect('/services');

    $service = Service::firstOrFail();
    expect($service->astrologer_id)->toBe($astrologer->id)
        ->and($service->slug)->toBe('30-minute-consultation')
        ->and((float) $service->price_inr)->toBe(999.0)
        ->and((float) $service->price_usd)->toBe(12.99);
});

test('creating a service requires a valid astrologer', function () {
    $this->actingAs($this->admin)->post('/services', [
        'astrologer_id' => 999999,
        'name' => 'Test Service',
        'duration_minutes' => 30,
        'price_inr' => 100,
        'price_usd' => 2,
    ])->assertSessionHasErrors('astrologer_id');
});

test('an admin can update and deactivate a service', function () {
    $service = Service::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)->put("/services/{$service->id}", [
        'astrologer_id' => $service->astrologer_id,
        'name' => 'Renamed Service',
        'duration_minutes' => 45,
        'price_inr' => 1500,
        'price_usd' => 18,
        // is_active omitted -> false
    ])->assertRedirect('/services');

    $fresh = $service->fresh();
    expect($fresh->name)->toBe('Renamed Service')
        ->and($fresh->is_active)->toBeFalse();
});

test('an admin can delete a service', function () {
    $service = Service::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/services/{$service->id}")
        ->assertRedirect('/services');

    expect(Service::find($service->id))->toBeNull();
});

test('an admin cannot delete a service with existing bookings', function () {
    $service = Service::factory()->create();
    Booking::factory()->create(['service_id' => $service->id, 'astrologer_id' => $service->astrologer_id]);

    $this->actingAs($this->admin)
        ->delete("/services/{$service->id}")
        ->assertRedirect('/services');

    expect(Service::find($service->id))->not->toBeNull();
});

test('service prices must fit the decimal(10,2) columns', function () {
    $astrologer = Astrologer::factory()->create();

    $this->actingAs($this->admin)->post('/services', [
        'astrologer_id' => $astrologer->id,
        'name' => 'Overpriced Service',
        'duration_minutes' => 30,
        'price_inr' => 100000000000,
        'price_usd' => 12.999,
    ])->assertSessionHasErrors(['price_inr', 'price_usd']);
});

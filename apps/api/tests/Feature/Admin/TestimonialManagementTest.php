<?php

use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

test('non-admin non-editor users cannot reach testimonial management', function () {
    $astrologerRoleUser = User::factory()->create();
    $astrologerRoleUser->assignRole('Astrologer');

    $this->actingAs($astrologerRoleUser)->get('/testimonials')->assertForbidden();
});

test('an admin can list testimonials', function () {
    $testimonial = Testimonial::factory()->create();

    $this->actingAs($this->admin)
        ->get('/testimonials')
        ->assertOk()
        ->assertSee($testimonial->name);
});

test('an admin can create a testimonial with a per-locale quote', function () {
    $response = $this->actingAs($this->admin)->post('/testimonials', [
        'name' => 'Priya Sharma',
        'quote' => ['en' => 'Wonderful reading.', 'hi' => 'अद्भुत अनुभव।'],
        'rating' => 5,
        'is_active' => '1',
    ]);

    $response->assertRedirect('/testimonials');

    $testimonial = Testimonial::firstOrFail();
    expect($testimonial->name)->toBe('Priya Sharma')
        ->and($testimonial->getTranslation('quote', 'en'))->toBe('Wonderful reading.')
        ->and($testimonial->getTranslation('quote', 'hi'))->toBe('अद्भुत अनुभव।')
        ->and($testimonial->rating)->toBe(5)
        ->and($testimonial->is_active)->toBeTrue();
});

test('an admin can update and deactivate a testimonial', function () {
    $testimonial = Testimonial::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)->put("/testimonials/{$testimonial->id}", [
        'name' => 'Updated Name',
        'quote' => ['en' => 'Updated quote.'],
        // is_active omitted -> false
    ])->assertRedirect('/testimonials');

    $fresh = $testimonial->fresh();
    expect($fresh->name)->toBe('Updated Name')
        ->and($fresh->is_active)->toBeFalse();
});

test('an admin can delete a testimonial', function () {
    $testimonial = Testimonial::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/testimonials/{$testimonial->id}")
        ->assertRedirect('/testimonials');

    expect(Testimonial::find($testimonial->id))->toBeNull();
});

<?php

use App\Models\Astrologer;
use App\Models\Booking;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

test('non-admin users cannot reach astrologer management', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    $this->actingAs($editor)->get('/astrologers')->assertForbidden();
});

test('guests are redirected away from astrologer management', function () {
    $this->get('/astrologers')->assertRedirect('/signin');
});

test('an admin can list astrologers', function () {
    Astrologer::factory()->count(2)->create();

    $this->actingAs($this->admin)
        ->get('/astrologers')
        ->assertOk()
        ->assertSee(Astrologer::first()->name);
});

test('an admin can create an astrologer with a unique slug', function () {
    $response = $this->actingAs($this->admin)->post('/astrologers', [
        'name' => 'Jane Doe',
        'bio' => 'An experienced astrologer.',
        'specialties' => 'Vedic Astrology, Tarot',
        'languages' => 'English, Hindi',
        'availability_mode' => 'manual',
        'is_active' => '1',
    ]);

    $response->assertRedirect('/astrologers');

    $astrologer = Astrologer::firstOrFail();
    expect($astrologer->name)->toBe('Jane Doe')
        ->and($astrologer->slug)->toBe('jane-doe')
        ->and($astrologer->specialties)->toBe(['Vedic Astrology', 'Tarot'])
        ->and($astrologer->languages)->toBe(['English', 'Hindi'])
        ->and($astrologer->is_active)->toBeTrue();
});

test('creating a second astrologer with the same name gets a unique slug', function () {
    Astrologer::factory()->create(['slug' => 'jane-doe', 'name' => 'Jane Doe']);

    $this->actingAs($this->admin)->post('/astrologers', [
        'name' => 'Jane Doe',
        'availability_mode' => 'manual',
    ])->assertRedirect('/astrologers');

    expect(Astrologer::where('name', 'Jane Doe')->count())->toBe(2)
        ->and(Astrologer::where('name', 'Jane Doe')->pluck('slug')->all())
        ->toContain('jane-doe-2');
});

test('creating an astrologer requires a name', function () {
    $this->actingAs($this->admin)
        ->post('/astrologers', ['availability_mode' => 'manual'])
        ->assertSessionHasErrors('name');
});

test('an admin can update an astrologer', function () {
    $astrologer = Astrologer::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)->put("/astrologers/{$astrologer->id}", [
        'name' => 'Updated Name',
        'availability_mode' => 'google_calendar',
    ])->assertRedirect('/astrologers');

    expect($astrologer->fresh()->name)->toBe('Updated Name')
        ->and($astrologer->fresh()->availability_mode)->toBe('google_calendar')
        ->and($astrologer->fresh()->is_active)->toBeFalse();
});

test('an admin can delete an astrologer', function () {
    $astrologer = Astrologer::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/astrologers/{$astrologer->id}")
        ->assertRedirect('/astrologers');

    expect(Astrologer::find($astrologer->id))->toBeNull();
});

test('an admin cannot delete an astrologer with existing bookings', function () {
    $astrologer = Astrologer::factory()->create();
    Booking::factory()->create(['astrologer_id' => $astrologer->id]);

    $this->actingAs($this->admin)
        ->delete("/astrologers/{$astrologer->id}")
        ->assertRedirect('/astrologers');

    expect(Astrologer::find($astrologer->id))->not->toBeNull();
});

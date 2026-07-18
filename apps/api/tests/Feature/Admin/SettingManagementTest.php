<?php

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

test('editors cannot reach settings', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    $this->actingAs($editor)->get('/settings')->assertForbidden();
});

test('guests are redirected away from settings', function () {
    $this->get('/settings')->assertRedirect('/signin');
});

test('an admin can view settings and it auto-creates the singleton row', function () {
    expect(Setting::query()->count())->toBe(0);

    $this->actingAs($this->admin)->get('/settings')->assertOk();

    expect(Setting::query()->count())->toBe(1);
});

test('an admin can update settings', function () {
    $this->actingAs($this->admin)->put('/settings', [
        'site_name' => 'Updated Astrology Co.',
        'supported_languages' => ['en', 'hi'],
        'default_currency' => 'USD',
        'currencies' => ['INR', 'USD'],
        'upi_id' => 'astro@upi',
        'contact' => [
            'email' => 'hello@example.com',
            'phone' => '+91 98765 43210',
            'address' => '123 Main St',
        ],
        'social_links' => [
            'facebook' => 'https://facebook.com/astro',
        ],
        'legal_links' => [
            'privacy_policy' => 'privacy-policy',
        ],
        'seo' => [
            'default_meta_title' => 'Astrology Co.',
            'ga_measurement_id' => 'G-TEST123',
        ],
    ])->assertRedirect('/settings');

    $setting = Setting::current();
    expect($setting->site_name)->toBe('Updated Astrology Co.')
        ->and($setting->supported_languages)->toBe(['en', 'hi'])
        ->and($setting->default_currency)->toBe('USD')
        ->and($setting->upi_id)->toBe('astro@upi')
        ->and($setting->contact['email'])->toBe('hello@example.com')
        ->and($setting->social_links['facebook'])->toBe('https://facebook.com/astro')
        ->and($setting->legal_links['privacy_policy'])->toBe('privacy-policy')
        ->and($setting->seo['ga_measurement_id'])->toBe('G-TEST123');
});

test('replacing the logo deletes the previous one', function () {
    Storage::fake('public');
    $originalPath = UploadedFile::fake()->image('old-logo.jpg')->store('branding', 'public');
    Setting::current()->update(['logo_path' => $originalPath]);

    $this->actingAs($this->admin)->put('/settings', [
        'site_name' => 'Astrology Co.',
        'supported_languages' => ['en'],
        'default_currency' => 'INR',
        'currencies' => ['INR'],
        'logo' => UploadedFile::fake()->image('new-logo.jpg'),
    ])->assertRedirect('/settings');

    Storage::disk('public')->assertMissing($originalPath);
    Storage::disk('public')->assertExists(Setting::current()->logo_path);
});

test('settings requires at least one supported language and one currency', function () {
    $this->actingAs($this->admin)->put('/settings', [
        'site_name' => 'Astrology Co.',
        'supported_languages' => [],
        'default_currency' => 'INR',
        'currencies' => [],
    ])->assertSessionHasErrors(['supported_languages', 'currencies']);
});

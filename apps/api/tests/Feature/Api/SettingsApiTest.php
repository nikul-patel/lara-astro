<?php

use App\Models\Setting;

test('it returns the current settings with derived urls', function () {
    Setting::current()->update([
        'site_name' => 'Cosmic Insights',
        'logo_path' => 'branding/logo.png',
        'upi_qr_path' => 'branding/qr.png',
        'legal_links' => ['privacy_policy' => 'privacy-policy'],
    ]);

    $response = $this->getJson('/api/v1/settings');

    $response->assertOk()
        ->assertJsonPath('site_name', 'Cosmic Insights')
        ->assertJsonPath('supported_languages', ['en', 'hi', 'gu'])
        ->assertJson(fn ($json) => $json
            ->where('site_name', 'Cosmic Insights')
            ->has('logo_url')
            ->has('upi_qr_url')
            ->where('legal_links.0.slug', 'privacy-policy')
            ->where('legal_links.0.label', 'Privacy Policy')
            ->etc()
        );

    expect($response->json('logo_url'))->toContain('branding/logo.png');
});

test('legal links omit entries that have no configured slug', function () {
    Setting::current()->update(['legal_links' => []]);

    $response = $this->getJson('/api/v1/settings');

    $response->assertOk()->assertJsonPath('legal_links', []);
});

test('the very first request auto-creates the singleton row but still returns 200, not 201', function () {
    expect(Setting::query()->count())->toBe(0);

    $this->getJson('/api/v1/settings')->assertOk();
});

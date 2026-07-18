<?php

use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

test('non-admin non-editor users cannot reach page management', function () {
    $astrologerRoleUser = User::factory()->create();
    $astrologerRoleUser->assignRole('Astrologer');

    $this->actingAs($astrologerRoleUser)->get('/pages')->assertForbidden();
});

test('an editor can reach page management', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    $this->actingAs($editor)->get('/pages')->assertOk();
});

test('guests are redirected away from page management', function () {
    $this->get('/pages')->assertRedirect('/signin');
});

test('an admin can list pages', function () {
    $page = Page::factory()->create();

    $this->actingAs($this->admin)
        ->get('/pages')
        ->assertOk()
        ->assertSee($page->title);
});

test('the pages list shows the configured default locale even when english is disabled', function () {
    Setting::current()->update(['supported_languages' => ['hi', 'gu']]);
    $page = Page::factory()->create([
        'title' => ['hi' => 'हमारे बारे में', 'gu' => 'અમારા વિશે'],
    ]);

    $this->actingAs($this->admin)
        ->get('/pages')
        ->assertOk()
        ->assertSee('हमारे बारे में')
        ->assertDontSee($page->getTranslation('title', 'gu'));
});

test('an admin can create a page with per-locale content', function () {
    $response = $this->actingAs($this->admin)->post('/pages', [
        'slug' => 'about-us',
        'title' => ['en' => 'About Us', 'hi' => 'हमारे बारे में', 'gu' => 'અમારા વિશે'],
        'content' => ['en' => 'English content.', 'hi' => 'हिंदी सामग्री।', 'gu' => 'ગુજરાતી સામગ્રી.'],
        'meta_title' => ['en' => 'About'],
    ]);

    $response->assertRedirect('/pages');

    $page = Page::firstOrFail();
    expect($page->slug)->toBe('about-us')
        ->and($page->getTranslation('title', 'en'))->toBe('About Us')
        ->and($page->getTranslation('title', 'hi'))->toBe('हमारे बारे में')
        ->and($page->getTranslation('content', 'gu'))->toBe('ગુજરાતી સામગ્રી.');
});

test('creating a page requires a unique slug', function () {
    Page::factory()->create(['slug' => 'about-us']);

    $this->actingAs($this->admin)->post('/pages', [
        'slug' => 'about-us',
        'title' => ['en' => 'Duplicate'],
        'content' => ['en' => 'Content'],
    ])->assertSessionHasErrors('slug');
});

test('an admin can update a page', function () {
    $page = Page::factory()->create();

    $this->actingAs($this->admin)->put("/pages/{$page->id}", [
        'slug' => $page->slug,
        'title' => ['en' => 'Updated Title'],
        'content' => ['en' => 'Updated content.'],
    ])->assertRedirect('/pages');

    expect($page->fresh()->getTranslation('title', 'en'))->toBe('Updated Title');
});

test('an admin can delete a page', function () {
    $page = Page::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/pages/{$page->id}")
        ->assertRedirect('/pages');

    expect(Page::find($page->id))->toBeNull();
});

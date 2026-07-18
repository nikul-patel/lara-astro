<?php

use App\Models\Page;
use App\Models\Post;
use App\Models\Testimonial;

test('it shows a page resolved in the requested locale', function () {
    $page = Page::factory()->create([
        'slug' => 'about-us',
        'title' => ['en' => 'About Us', 'hi' => 'हमारे बारे में'],
    ]);

    $this->getJson('/api/v1/pages/about-us')
        ->assertOk()
        ->assertJsonPath('title', 'About Us');

    $this->getJson('/api/v1/pages/about-us?locale=hi')
        ->assertOk()
        ->assertJsonPath('title', 'हमारे बारे में');
});

test('an unknown page slug 404s', function () {
    $this->getJson('/api/v1/pages/does-not-exist')->assertNotFound();
});

test('it lists only published posts and hides drafts and future-scheduled posts', function () {
    Post::factory()->create(['title' => ['en' => 'Published'], 'published_at' => now()->subDay()]);
    Post::factory()->create(['title' => ['en' => 'Draft'], 'published_at' => null]);
    Post::factory()->create(['title' => ['en' => 'Scheduled'], 'published_at' => now()->addWeek()]);

    $response = $this->getJson('/api/v1/posts');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.title'))->toBe('Published');
});

test('a draft post 404s on the public detail endpoint', function () {
    $post = Post::factory()->create(['slug' => 'draft-post', 'published_at' => null]);

    $this->getJson("/api/v1/posts/{$post->slug}")->assertNotFound();
});

test('it lists only active testimonials', function () {
    Testimonial::factory()->create(['name' => 'Active', 'is_active' => true]);
    Testimonial::factory()->create(['name' => 'Inactive', 'is_active' => false]);

    $response = $this->getJson('/api/v1/testimonials');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Active');
});

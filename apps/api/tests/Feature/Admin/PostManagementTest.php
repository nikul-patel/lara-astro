<?php

use App\Models\Post;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

test('non-admin non-editor users cannot reach post management', function () {
    $astrologerRoleUser = User::factory()->create();
    $astrologerRoleUser->assignRole('Astrologer');

    $this->actingAs($astrologerRoleUser)->get('/posts')->assertForbidden();
});

test('an admin can list posts', function () {
    $post = Post::factory()->create();

    $this->actingAs($this->admin)
        ->get('/posts')
        ->assertOk()
        ->assertSee($post->title);
});

test('an admin can create a post with per-locale content and no published date is a draft', function () {
    $response = $this->actingAs($this->admin)->post('/posts', [
        'slug' => 'first-post',
        'title' => ['en' => 'First Post', 'hi' => 'पहली पोस्ट', 'gu' => 'પ્રથમ પોસ્ટ'],
        'excerpt' => ['en' => 'A short excerpt.'],
        'content' => ['en' => 'Full content.', 'hi' => 'पूरी सामग्री।', 'gu' => 'સંપૂર્ણ સામગ્રી.'],
    ]);

    $response->assertRedirect('/posts');

    $post = Post::firstOrFail();
    expect($post->slug)->toBe('first-post')
        ->and($post->getTranslation('title', 'hi'))->toBe('पहली पोस्ट')
        ->and($post->published_at)->toBeNull();
});

test('creating a post requires a unique slug', function () {
    Post::factory()->create(['slug' => 'first-post']);

    $this->actingAs($this->admin)->post('/posts', [
        'slug' => 'first-post',
        'title' => ['en' => 'Duplicate'],
        'content' => ['en' => 'Content'],
    ])->assertSessionHasErrors('slug');
});

test('an admin can update and publish a post', function () {
    $post = Post::factory()->create(['published_at' => null]);
    $publishAt = now()->addMinute();

    $this->actingAs($this->admin)->put("/posts/{$post->id}", [
        'slug' => $post->slug,
        'title' => ['en' => 'Updated Title'],
        'content' => ['en' => 'Updated content.'],
        'published_at' => $publishAt->format('Y-m-d\TH:i'),
    ])->assertRedirect('/posts');

    $fresh = $post->fresh();
    expect($fresh->getTranslation('title', 'en'))->toBe('Updated Title')
        ->and($fresh->published_at->format('Y-m-d H:i'))->toBe($publishAt->format('Y-m-d H:i'));
});

test('an admin can delete a post', function () {
    $post = Post::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/posts/{$post->id}")
        ->assertRedirect('/posts');

    expect(Post::find($post->id))->toBeNull();
});

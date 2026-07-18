<?php

use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Testimonial;

test('a page stores per-locale title and content and returns the active-locale value', function () {
    $page = Page::factory()->create([
        'slug' => 'about-us',
        'title' => ['en' => 'About Us', 'hi' => 'हमारे बारे में', 'gu' => 'અમારા વિશે'],
        'content' => ['en' => 'English content', 'hi' => 'Hindi content', 'gu' => 'Gujarati content'],
    ]);

    expect($page->getTranslation('title', 'en'))->toBe('About Us')
        ->and($page->getTranslation('title', 'hi'))->toBe('हमारे बारे में')
        ->and($page->getTranslation('title', 'gu'))->toBe('અમારા વિશે');
});

test('a post has translatable excerpt and content plus untranslated publish metadata', function () {
    $post = Post::factory()->create([
        'title' => ['en' => 'English Title', 'hi' => 'हिंदी शीर्षक'],
        'featured_image_path' => 'posts/hero.jpg',
    ]);

    expect($post->getTranslation('title', 'en'))->toBe('English Title')
        ->and($post->getTranslation('title', 'hi'))->toBe('हिंदी शीर्षक')
        ->and($post->featured_image_path)->toBe('posts/hero.jpg')
        ->and($post->published_at)->not->toBeNull();
});

test('a testimonial has a non-translated name and a translatable quote', function () {
    $testimonial = Testimonial::factory()->create([
        'name' => 'Priya Shah',
        'quote' => ['en' => 'Wonderful reading!', 'gu' => 'અદ્ભુત વાંચન!'],
    ]);

    expect($testimonial->name)->toBe('Priya Shah')
        ->and($testimonial->getTranslation('quote', 'en'))->toBe('Wonderful reading!')
        ->and($testimonial->getTranslation('quote', 'gu'))->toBe('અદ્ભુત વાંચન!');
});

test('Setting::current() creates a singleton row with sane defaults on first access', function () {
    expect(Setting::query()->count())->toBe(0);

    $settings = Setting::current();

    expect(Setting::query()->count())->toBe(1)
        ->and($settings->supported_languages)->toBe(['en', 'hi', 'gu'])
        ->and($settings->default_currency)->toBe('INR')
        ->and($settings->currencies)->toBe(['INR', 'USD']);

    $again = Setting::current();

    expect(Setting::query()->count())->toBe(1)
        ->and($again->is($settings))->toBeTrue();
});

test('setting contact/social/legal fields round-trip as arrays', function () {
    $settings = Setting::factory()->create([
        'contact' => ['email' => 'hello@example.com', 'phone' => '+91 90000 00000'],
        'social_links' => [['label' => 'Instagram', 'url' => 'https://instagram.com/example']],
        'legal_links' => [['label' => 'Privacy Policy', 'slug' => 'privacy-policy']],
    ]);

    $fresh = $settings->fresh();

    expect($fresh->contact)->toBe(['email' => 'hello@example.com', 'phone' => '+91 90000 00000'])
        ->and($fresh->social_links[0]['label'])->toBe('Instagram')
        ->and($fresh->legal_links[0]['slug'])->toBe('privacy-policy');
});

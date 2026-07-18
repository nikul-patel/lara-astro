<?php

use App\Models\Astrologer;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

test('non-admin users cannot reach course management', function () {
    $editor = User::factory()->create();
    $editor->assignRole('Editor');

    $this->actingAs($editor)->get('/courses')->assertForbidden();
});

test('guests are redirected away from course management', function () {
    $this->get('/courses')->assertRedirect('/signin');
});

test('an admin can list courses', function () {
    $course = Course::factory()->create();

    $this->actingAs($this->admin)
        ->get('/courses')
        ->assertOk()
        ->assertSee($course->title);
});

test('an admin can create a course with a unique slug', function () {
    $astrologer = Astrologer::factory()->create();

    $response = $this->actingAs($this->admin)->post('/courses', [
        'astrologer_id' => $astrologer->id,
        'title' => 'Foundations of Vedic Astrology',
        'description' => 'An introductory course.',
        'type' => 'recorded',
        'price_inr' => 2999,
        'price_usd' => 39,
        'is_active' => '1',
    ]);

    $course = Course::firstOrFail();
    $response->assertRedirect(route('courses.edit', $course));

    expect($course->title)->toBe('Foundations of Vedic Astrology')
        ->and($course->slug)->toBe('foundations-of-vedic-astrology')
        ->and($course->astrologer_id)->toBe($astrologer->id);
});

test('course prices must fit the decimal(10,2) columns', function () {
    $this->actingAs($this->admin)->post('/courses', [
        'title' => 'Overpriced Course',
        'type' => 'recorded',
        'price_inr' => 100000000000,
        'price_usd' => 12.999,
    ])->assertSessionHasErrors(['price_inr', 'price_usd']);
});

test('an admin can update and deactivate a course', function () {
    $course = Course::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)->put("/courses/{$course->id}", [
        'title' => 'Renamed Course',
        'type' => $course->type,
        'price_inr' => 1999,
        'price_usd' => 25,
        // is_active omitted -> false
    ])->assertRedirect(route('courses.edit', $course));

    $fresh = $course->fresh();
    expect($fresh->title)->toBe('Renamed Course')
        ->and($fresh->is_active)->toBeFalse();
});

test('an admin can delete a course', function () {
    $course = Course::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/courses/{$course->id}")
        ->assertRedirect('/courses');

    expect(Course::find($course->id))->toBeNull();
});

test('an admin cannot delete a course with existing enrollments', function () {
    $course = Course::factory()->create();
    Enrollment::factory()->create(['course_id' => $course->id]);

    $this->actingAs($this->admin)
        ->delete("/courses/{$course->id}")
        ->assertRedirect('/courses');

    expect(Course::find($course->id))->not->toBeNull();
});

<?php

use App\Http\Controllers\AstrologerController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AvailabilitySlotController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseLessonController;
use App\Http\Controllers\CourseModuleController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LiveSessionController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;

// authentication pages
Route::get('/signin', function () {
    return view('pages.auth.signin', ['title' => 'Sign In']);
})->middleware('guest')->name('signin');

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Sign Up']);
})->middleware('guest')->name('signup');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Everything below is the admin panel proper — requires an authenticated
// (and, per-route, appropriately-roled) admin panel user. Role gates are
// added alongside each feature as its issue lands (#10-#13); for now every
// admin-panel user (Admin/Astrologer/Editor) can reach these.
Route::middleware('auth')->group(function () {
    // dashboard pages
    Route::get('/', function () {
        return view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard']);
    })->name('dashboard');

    // profile — every admin-panel user manages their own account here.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Astrologer/Service/Availability management — Admin only (PRD §5.2).
    Route::middleware('role:Admin')->group(function () {
        Route::resource('astrologers', AstrologerController::class)->except('show');
        Route::resource('services', ServiceController::class)->except('show');
        Route::resource('availability', AvailabilitySlotController::class)->except('show');
        // Bookings are created by clients via the public API; the admin panel
        // only lists/filters, views detail, and drives status transitions.
        Route::resource('bookings', BookingController::class)->only(['index', 'edit', 'update']);

        // Courses + curriculum builder (modules/lessons/live sessions) — all
        // managed from the course edit screen — and enrollments, which
        // follow the same pending->confirmed UPI workflow as bookings.
        Route::resource('courses', CourseController::class)->except('show');
        Route::post('courses/{course}/modules', [CourseModuleController::class, 'store'])->name('courses.modules.store');
        Route::put('modules/{module}', [CourseModuleController::class, 'update'])->name('modules.update');
        Route::delete('modules/{module}', [CourseModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('modules/{module}/lessons', [CourseLessonController::class, 'store'])->name('modules.lessons.store');
        Route::put('lessons/{lesson}', [CourseLessonController::class, 'update'])->name('lessons.update');
        Route::delete('lessons/{lesson}', [CourseLessonController::class, 'destroy'])->name('lessons.destroy');
        Route::post('courses/{course}/live-sessions', [LiveSessionController::class, 'store'])->name('courses.live-sessions.store');
        Route::put('live-sessions/{liveSession}', [LiveSessionController::class, 'update'])->name('live-sessions.update');
        Route::delete('live-sessions/{liveSession}', [LiveSessionController::class, 'destroy'])->name('live-sessions.destroy');
        Route::resource('enrollments', EnrollmentController::class)->only(['index', 'edit', 'update']);

        // Settings is site-wide config (branding, UPI, SEO defaults) —
        // Admin only, unlike the CMS below.
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // CMS — Admin or Editor (PRD §5.2: "Editor (blog/content only)").
    Route::middleware('role:Admin|Editor')->group(function () {
        Route::resource('pages', PageController::class)->except('show');
        Route::resource('posts', PostController::class)->except('show');
        Route::resource('testimonials', TestimonialController::class)->except('show');
    });
});

// error pages (public — not behind auth)
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

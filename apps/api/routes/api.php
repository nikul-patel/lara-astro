<?php

// Public JSON API v1 (see docs/API_CONTRACT.md). Endpoints land in #14 (read)
// and #15 (write + auth). Routes here are automatically prefixed with
// /api/v1 and given the "api" middleware group (see bootstrap/app.php).

use App\Http\Controllers\Api\AstrologerController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::get('settings', [SettingController::class, 'show']);

Route::get('astrologers', [AstrologerController::class, 'index']);
Route::get('astrologers/{slug}', [AstrologerController::class, 'show']);
Route::get('services', [ServiceController::class, 'index']);
Route::get('availability', [AvailabilityController::class, 'index']);

Route::get('courses', [CourseController::class, 'index']);
Route::get('courses/{slug}', [CourseController::class, 'show']);

Route::get('pages/{slug}', [PageController::class, 'show']);
Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{slug}', [PostController::class, 'show']);
Route::get('testimonials', [TestimonialController::class, 'index']);

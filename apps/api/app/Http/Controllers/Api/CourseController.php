<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $courses = Course::query()
            ->where('is_active', true)
            ->when(
                in_array($request->query('type'), ['recorded', 'live'], true),
                fn ($query) => $query->where('type', $request->query('type'))
            )
            ->with('instructor')
            ->orderBy('title')
            ->paginate(15);

        return CourseResource::collection($courses);
    }

    public function show(string $slug): CourseResource
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['instructor', 'modules.lessons', 'liveSessions'])
            ->firstOrFail();

        return new CourseResource($course);
    }
}

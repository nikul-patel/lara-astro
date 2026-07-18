<?php

namespace App\Http\Controllers;

use App\Models\Astrologer;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->withCount('enrollments')
            ->with('instructor')
            ->latest()
            ->paginate(15);

        return view('pages.courses.index', compact('courses'));
    }

    public function create(): View
    {
        $astrologers = Astrologer::orderBy('name')->get();

        return view('pages.courses.create', compact('astrologers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['title']);

        $course = Course::create($data);

        return redirect()->route('courses.edit', $course)->with('status', 'Course created. Add its curriculum below.');
    }

    public function edit(Course $course): View
    {
        $astrologers = Astrologer::orderBy('name')->get();
        $course->load(['modules.lessons', 'liveSessions']);

        return view('pages.courses.edit', compact('course', 'astrologers'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $course->update($this->validated($request));

        return redirect()->route('courses.edit', $course)->with('status', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        if ($course->enrollments()->exists()) {
            return redirect()->route('courses.index')
                ->with('error', 'Cannot delete a course with existing enrollments. Deactivate it instead.');
        }

        $course->delete();

        return redirect()->route('courses.index')->with('status', 'Course removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'astrologer_id' => ['nullable', 'exists:astrologers,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:recorded,live'],
            'price_inr' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'price_usd' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 1;

        while (Course::where('slug', $slug)->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}

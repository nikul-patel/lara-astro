<?php

namespace App\Http\Controllers;

use App\Models\CourseLesson;
use App\Models\CourseModule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseLessonController extends Controller
{
    public function store(Request $request, CourseModule $module): RedirectResponse
    {
        $module->lessons()->create($this->validated($request));

        return redirect()->route('courses.edit', $module->course_id)->with('status', 'Lesson added.');
    }

    public function update(Request $request, CourseLesson $lesson): RedirectResponse
    {
        $lesson->update($this->validated($request));

        return redirect()->route('courses.edit', $lesson->module->course_id)->with('status', 'Lesson updated.');
    }

    public function destroy(CourseLesson $lesson): RedirectResponse
    {
        $courseId = $lesson->module->course_id;
        $lesson->delete();

        return redirect()->route('courses.edit', $courseId)->with('status', 'Lesson removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        // Field is named "lesson_title" (not "title") so a failed
        // validation doesn't flash into the unrelated course-title field
        // via a shared old('title') on the same edit page.
        $validated = $request->validate([
            'lesson_title' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'order' => ['required', 'integer', 'min:0'],
        ]);

        return [
            'title' => $validated['lesson_title'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'order' => $validated['order'],
        ];
    }
}

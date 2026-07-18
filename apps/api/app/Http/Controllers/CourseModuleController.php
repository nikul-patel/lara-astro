<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseModuleController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $this->validated($request);
        $course->modules()->create($validated);

        return redirect()->route('courses.edit', $course)->with('status', 'Module added.');
    }

    public function update(Request $request, CourseModule $module): RedirectResponse
    {
        $module->update($this->validated($request));

        return redirect()->route('courses.edit', $module->course_id)->with('status', 'Module updated.');
    }

    public function destroy(CourseModule $module): RedirectResponse
    {
        $courseId = $module->course_id;
        $module->delete();

        return redirect()->route('courses.edit', $courseId)->with('status', 'Module removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'order' => ['required', 'integer', 'min:0'],
        ]);
    }
}

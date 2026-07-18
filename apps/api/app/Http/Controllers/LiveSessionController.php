<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LiveSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LiveSessionController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $course->liveSessions()->create($this->validated($request));

        return redirect()->route('courses.edit', $course)->with('status', 'Live session added.');
    }

    public function update(Request $request, LiveSession $liveSession): RedirectResponse
    {
        $liveSession->update($this->validated($request));

        return redirect()->route('courses.edit', $liveSession->course_id)->with('status', 'Live session updated.');
    }

    public function destroy(LiveSession $liveSession): RedirectResponse
    {
        $courseId = $liveSession->course_id;
        $liveSession->delete();

        return redirect()->route('courses.edit', $courseId)->with('status', 'Live session removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'meeting_url' => ['nullable', 'url', 'max:255'],
        ]);

        return $validated;
    }
}

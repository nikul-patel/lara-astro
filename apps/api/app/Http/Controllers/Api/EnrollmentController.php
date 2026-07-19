<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Mail\EnrollmentReceivedMail;
use App\Mail\NewEnrollmentAdminMail;
use App\Models\Client;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $authClient = $request->user('sanctum');

        $rules = [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'guest' => ['sometimes', 'boolean'],
        ];

        if (! $authClient) {
            $rules['client.name'] = ['required', 'string', 'max:255'];
            $rules['client.email'] = ['required', 'email', 'max:255'];
            $rules['client.phone'] = ['required', 'string', 'max:50'];
        }

        $validated = $request->validate($rules);

        $course = Course::query()
            ->where('id', $validated['course_id'])
            ->where('is_active', true)
            ->first();

        if (! $course) {
            throw ValidationException::withMessages(['course_id' => 'This course is not available.']);
        }

        $client = $authClient ?? Client::firstOrCreate(
            ['email' => $validated['client']['email']],
            ['name' => $validated['client']['name'], 'phone' => $validated['client']['phone'] ?? null]
        );

        $enrollment = Enrollment::create([
            'course_id' => $course->id,
            'client_id' => $client->id,
            'status' => 'pending_payment',
        ]);

        // Queued (Mailables implement ShouldQueue): client confirmation with
        // UPI details + admin alert on the new pending enrollment (issue #18).
        Mail::to($client->email)->send(new EnrollmentReceivedMail($enrollment));
        Mail::to(Setting::current()->adminEmail())->send(new NewEnrollmentAdminMail($enrollment));

        return (new EnrollmentResource($enrollment->load('client')))
            ->response()
            ->setStatusCode(201);
    }

    public function mine(Request $request): JsonResponse
    {
        $enrollments = Enrollment::query()
            ->where('client_id', $request->user('sanctum')->id)
            ->with(['client', 'course.modules.lessons', 'course.liveSessions'])
            ->latest()
            ->get();

        // Resolves Settings once and passes it into every resource instead
        // of using EnrollmentResource::collection() (which would call
        // EnrollmentResource::toArray() — and its own Setting::current()
        // lookup — once per enrollment).
        $setting = Setting::current();

        return response()->json(
            $enrollments
                ->map(fn (Enrollment $enrollment) => (new EnrollmentResource($enrollment, $setting))->resolve($request))
                ->values()
        );
    }
}

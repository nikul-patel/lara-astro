@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Courses" />

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-success-500 bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 dark:bg-error-500/10 dark:text-error-400">
                {{ session('error') }}
            </div>
        @endif

        <x-common.component-card title="Courses">
            <div class="mb-4 flex justify-end">
                <a href="{{ route('courses.create') }}"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                    Add Course
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Course</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Instructor</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Type</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Price</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Enrollments</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courses as $course)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $course->title }}</span>
                                    <span class="block text-xs text-gray-400">{{ $course->slug }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">{{ $course->instructor?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 capitalize sm:px-6 dark:text-gray-300">{{ $course->type }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">
                                    ₹{{ number_format((float) $course->price_inr, 2) }} / ${{ number_format((float) $course->price_usd, 2) }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">{{ $course->enrollments_count }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    @if ($course->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('courses.edit', $course) }}" class="text-brand-500 hover:text-brand-600">Manage</a>
                                        <form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('Delete this course?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error-500 hover:text-error-600">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-6 text-center text-sm text-gray-500 sm:px-6">No courses yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $courses->links() }}
            </div>
        </x-common.component-card>
    </div>
@endsection

@extends('layouts.app')

@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

@section('content')
    <x-common.page-breadcrumb pageTitle="Manage Course" />

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-success-500 bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">
                {{ session('status') }}
            </div>
        @endif

        <x-common.component-card title="Edit {{ $course->title }}">
            <form method="POST" action="{{ route('courses.update', $course) }}">
                @csrf
                @method('PUT')
                @include('pages.courses._form')
            </form>
        </x-common.component-card>

        <x-common.component-card title="Curriculum">
            <div class="space-y-6">
                @forelse ($course->modules as $module)
                    <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                        <form method="POST" action="{{ route('modules.update', $module) }}" class="flex flex-wrap items-end gap-3">
                            @csrf
                            @method('PUT')
                            <div class="flex-1">
                                <label class="{{ $labelClass }}">Module Title</label>
                                <input type="text" name="title" value="{{ $module->title }}" required class="{{ $inputClass }}" />
                            </div>
                            <div class="w-24">
                                <label class="{{ $labelClass }}">Order</label>
                                <input type="number" name="order" value="{{ $module->order }}" min="0" required class="{{ $inputClass }}" />
                            </div>
                            <button type="submit" class="bg-brand-500 hover:bg-brand-600 h-10 rounded-lg px-4 text-sm font-medium text-white transition">Save</button>
                        </form>
                        <form method="POST" action="{{ route('modules.destroy', $module) }}" class="inline" onsubmit="return confirm('Delete this module and its lessons?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="mt-2 text-sm text-error-500 hover:text-error-600">Delete module</button>
                        </form>

                        <div class="mt-4 space-y-3 border-t border-gray-100 pt-4 pl-4 dark:border-gray-800">
                            @foreach ($module->lessons as $lesson)
                                <form method="POST" action="{{ route('lessons.update', $lesson) }}" class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex-1">
                                        <label class="{{ $labelClass }}">Lesson Title</label>
                                        <input type="text" name="title" value="{{ $lesson->title }}" required class="{{ $inputClass }}" />
                                    </div>
                                    <div class="w-40">
                                        <label class="{{ $labelClass }}">Video URL</label>
                                        <input type="url" name="video_url" value="{{ $lesson->video_url }}" class="{{ $inputClass }}" />
                                    </div>
                                    <div class="w-28">
                                        <label class="{{ $labelClass }}">Minutes</label>
                                        <input type="number" name="duration_minutes" value="{{ $lesson->duration_minutes }}" min="1" class="{{ $inputClass }}" />
                                    </div>
                                    <div class="w-20">
                                        <label class="{{ $labelClass }}">Order</label>
                                        <input type="number" name="order" value="{{ $lesson->order }}" min="0" required class="{{ $inputClass }}" />
                                    </div>
                                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 h-10 rounded-lg px-4 text-sm font-medium text-white transition">Save</button>
                                    <button type="submit" form="delete-lesson-{{ $lesson->id }}" class="h-10 text-sm text-error-500 hover:text-error-600">Delete</button>
                                </form>
                                <form id="delete-lesson-{{ $lesson->id }}" method="POST" action="{{ route('lessons.destroy', $lesson) }}" onsubmit="return confirm('Delete this lesson?');">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endforeach

                            <form method="POST" action="{{ route('modules.lessons.store', $module) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <div class="flex-1">
                                    <label class="{{ $labelClass }}">New Lesson Title</label>
                                    <input type="text" name="title" required class="{{ $inputClass }}" />
                                </div>
                                <div class="w-40">
                                    <label class="{{ $labelClass }}">Video URL</label>
                                    <input type="url" name="video_url" class="{{ $inputClass }}" />
                                </div>
                                <div class="w-28">
                                    <label class="{{ $labelClass }}">Minutes</label>
                                    <input type="number" name="duration_minutes" min="1" class="{{ $inputClass }}" />
                                </div>
                                <div class="w-20">
                                    <label class="{{ $labelClass }}">Order</label>
                                    <input type="number" name="order" value="0" min="0" required class="{{ $inputClass }}" />
                                </div>
                                <button type="submit" class="h-10 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Add Lesson</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No modules yet. Add one below.</p>
                @endforelse

                <form method="POST" action="{{ route('courses.modules.store', $course) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label class="{{ $labelClass }}">New Module Title</label>
                        <input type="text" name="title" required class="{{ $inputClass }}" />
                    </div>
                    <div class="w-24">
                        <label class="{{ $labelClass }}">Order</label>
                        <input type="number" name="order" value="{{ $course->modules->count() }}" min="0" required class="{{ $inputClass }}" />
                    </div>
                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 h-10 rounded-lg px-4 text-sm font-medium text-white transition">Add Module</button>
                </form>
            </div>
        </x-common.component-card>

        <x-common.component-card title="Live Sessions">
            <div class="space-y-4">
                @forelse ($course->liveSessions as $liveSession)
                    <form method="POST" action="{{ route('live-sessions.update', $liveSession) }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="{{ $labelClass }}">Starts At</label>
                            <input type="datetime-local" name="starts_at" value="{{ $liveSession->starts_at?->format('Y-m-d\TH:i') }}" required class="{{ $inputClass }}" />
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Ends At</label>
                            <input type="datetime-local" name="ends_at" value="{{ $liveSession->ends_at?->format('Y-m-d\TH:i') }}" class="{{ $inputClass }}" />
                        </div>
                        <div class="flex-1">
                            <label class="{{ $labelClass }}">Meeting URL</label>
                            <input type="url" name="meeting_url" value="{{ $liveSession->meeting_url }}" class="{{ $inputClass }}" />
                        </div>
                        <button type="submit" class="bg-brand-500 hover:bg-brand-600 h-10 rounded-lg px-4 text-sm font-medium text-white transition">Save</button>
                        <button type="submit" form="delete-live-session-{{ $liveSession->id }}" class="h-10 text-sm text-error-500 hover:text-error-600">Delete</button>
                    </form>
                    <form id="delete-live-session-{{ $liveSession->id }}" method="POST" action="{{ route('live-sessions.destroy', $liveSession) }}" onsubmit="return confirm('Delete this live session?');">
                        @csrf
                        @method('DELETE')
                    </form>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No live sessions scheduled yet.</p>
                @endforelse

                <form method="POST" action="{{ route('courses.live-sessions.store', $course) }}" class="flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    @csrf
                    <div>
                        <label class="{{ $labelClass }}">Starts At</label>
                        <input type="datetime-local" name="starts_at" required class="{{ $inputClass }}" />
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Ends At</label>
                        <input type="datetime-local" name="ends_at" class="{{ $inputClass }}" />
                    </div>
                    <div class="flex-1">
                        <label class="{{ $labelClass }}">Meeting URL</label>
                        <input type="url" name="meeting_url" class="{{ $inputClass }}" />
                    </div>
                    <button type="submit" class="h-10 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Add Live Session</button>
                </form>
            </div>
        </x-common.component-card>
    </div>
@endsection

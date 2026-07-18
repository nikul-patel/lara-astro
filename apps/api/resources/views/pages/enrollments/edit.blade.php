@extends('layouts.app')

@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

@section('content')
    <x-common.page-breadcrumb pageTitle="Manage Enrollment" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-common.component-card title="Client & Course" class="lg:col-span-1">
            <div class="space-y-4 text-sm">
                <div>
                    <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Reference</p>
                    <p class="text-gray-800 dark:text-white/90">{{ $enrollment->reference_number }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Client</p>
                    <p class="text-gray-800 dark:text-white/90">{{ $enrollment->client?->name ?? '—' }}</p>
                    <p class="text-gray-500 dark:text-gray-400">{{ $enrollment->client?->email }}</p>
                    <p class="text-gray-500 dark:text-gray-400">{{ $enrollment->client?->phone }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Course</p>
                    <p class="text-gray-800 dark:text-white/90">{{ $enrollment->course?->title ?? '—' }}</p>
                    <p class="text-gray-500 capitalize dark:text-gray-400">{{ $enrollment->course?->type }}</p>
                </div>

                @if ($enrollment->course?->type === 'recorded' && $enrollment->course->modules->isNotEmpty())
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Curriculum</p>
                        <ul class="mt-1 list-inside list-disc space-y-1 text-gray-600 dark:text-gray-300">
                            @foreach ($enrollment->course->modules as $module)
                                <li>
                                    {{ $module->title }}
                                    <span class="text-xs text-gray-400">({{ $module->lessons->count() }} lessons)</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-xs text-gray-400">Per-lesson progress tracking isn't recorded yet.</p>
                    </div>
                @endif
            </div>
        </x-common.component-card>

        <x-common.component-card title="Enrollment Status" class="lg:col-span-2">
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-600 dark:bg-error-500/10">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('enrollments.update', $enrollment) }}">
                @csrf
                @method('PUT')

                <div class="space-y-5">
                    <div>
                        <label class="{{ $labelClass }}">Status<span class="text-error-500">*</span></label>
                        <select name="status" required class="{{ $inputClass }}">
                            @foreach (['pending_payment' => 'Pending Payment', 'confirmed' => 'Confirmed'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $enrollment->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">UPI Reference</label>
                        <input type="text" name="upi_reference" value="{{ old('upi_reference', $enrollment->upi_reference) }}"
                            placeholder="Required to mark as confirmed" class="{{ $inputClass }}" />
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Internal Notes</label>
                        <textarea name="admin_notes" rows="4" class="{{ $inputClass }} h-auto">{{ old('admin_notes', $enrollment->admin_notes) }}</textarea>
                    </div>

                    <div>
                        <button type="submit" class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                            Save Changes
                        </button>
                        <a href="{{ route('enrollments.index') }}" class="ml-3 text-sm font-medium text-gray-500 hover:text-gray-700">Cancel</a>
                    </div>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection

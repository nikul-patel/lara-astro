@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Availability" />

    @php
        $weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    @endphp

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-success-500 bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">
                {{ session('status') }}
            </div>
        @endif

        <x-common.component-card title="Weekly Availability Slots" desc="Manual working hours for astrologers not using Google Calendar sync.">
            <div class="mb-4 flex justify-end">
                <a href="{{ route('availability.create') }}"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                    Add Slot
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Astrologer</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Day</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Hours</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($slots as $slot)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4 text-sm font-medium text-gray-800 sm:px-6 dark:text-white/90">{{ $slot->astrologer?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">{{ $weekdays[$slot->weekday] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">
                                    {{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('g:i A') }}
                                    –
                                    {{ \Illuminate\Support\Carbon::parse($slot->end_time)->format('g:i A') }}
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    @if ($slot->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('availability.edit', $slot) }}" class="text-brand-500 hover:text-brand-600">Edit</a>
                                        <form method="POST" action="{{ route('availability.destroy', $slot) }}" onsubmit="return confirm('Delete this slot?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error-500 hover:text-error-600">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-sm text-gray-500 sm:px-6">No availability slots yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $slots->links() }}
            </div>
        </x-common.component-card>
    </div>
@endsection

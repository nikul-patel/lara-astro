@extends('layouts.app')

@php
    $statusFilters = [
        '' => 'All',
        'pending_payment' => 'Pending Payment',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show',
    ];

    $statusBadgeClass = [
        'pending_payment' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400',
        'confirmed' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',
        'completed' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-400',
        'cancelled' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400',
        'no_show' => 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400',
    ];
@endphp

@section('content')
    <x-common.page-breadcrumb pageTitle="Bookings" />

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-success-500 bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">
                {{ session('status') }}
            </div>
        @endif

        <x-common.component-card title="Bookings">
            <div class="mb-4 flex flex-wrap gap-2">
                @foreach ($statusFilters as $value => $label)
                    <a href="{{ route('bookings.index', $value !== '' ? ['status' => $value] : []) }}"
                        class="rounded-full px-3 py-1.5 text-xs font-medium {{ $status === $value ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Client</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Service</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Astrologer</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Slot</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4 text-sm font-medium text-gray-800 sm:px-6 dark:text-white/90">{{ $booking->reference_number }}</td>
                                <td class="px-5 py-4 text-sm sm:px-6">
                                    <span class="block text-gray-800 dark:text-white/90">{{ $booking->client?->name ?? '—' }}</span>
                                    <span class="block text-xs text-gray-400">{{ $booking->client?->email }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">{{ $booking->service?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">{{ $booking->astrologer?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">{{ $booking->slot?->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusBadgeClass[$booking->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $statusFilters[$booking->status] ?? $booking->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm sm:px-6">
                                    <a href="{{ route('bookings.edit', $booking) }}" class="text-brand-500 hover:text-brand-600">Manage</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-6 text-center text-sm text-gray-500 sm:px-6">No bookings yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $bookings->links() }}
            </div>
        </x-common.component-card>
    </div>
@endsection

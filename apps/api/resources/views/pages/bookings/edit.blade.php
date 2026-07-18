@extends('layouts.app')

@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

@section('content')
    <x-common.page-breadcrumb pageTitle="Manage Booking" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-common.component-card title="Client & Birth Details" class="lg:col-span-1">
            <div class="space-y-4 text-sm">
                <div>
                    <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Reference</p>
                    <p class="text-gray-800 dark:text-white/90">{{ $booking->reference_number }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Client</p>
                    <p class="text-gray-800 dark:text-white/90">{{ $booking->client?->name ?? '—' }}</p>
                    <p class="text-gray-500 dark:text-gray-400">{{ $booking->client?->email }}</p>
                    <p class="text-gray-500 dark:text-gray-400">{{ $booking->client?->phone }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Service</p>
                    <p class="text-gray-800 dark:text-white/90">{{ $booking->service?->name ?? '—' }}</p>
                    <p class="text-gray-500 dark:text-gray-400">with {{ $booking->astrologer?->name ?? '—' }}</p>
                </div>

                @if ($booking->birthChart)
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Birth Chart</p>
                        <p class="text-gray-800 dark:text-white/90">{{ $booking->birthChart->name }}</p>
                        <p class="text-gray-500 dark:text-gray-400">{{ $booking->birthChart->dob?->format('d M Y') }} · {{ $booking->birthChart->time }}</p>
                        <p class="text-gray-500 dark:text-gray-400">{{ $booking->birthChart->place }}</p>
                    </div>
                @elseif ($booking->birth_details)
                    <div>
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Birth Details</p>
                        @foreach ($booking->birth_details as $key => $value)
                            <p class="text-gray-500 dark:text-gray-400"><span class="font-medium text-gray-700 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> {{ $value }}</p>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-common.component-card>

        <x-common.component-card title="Booking Status" class="lg:col-span-2">
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-600 dark:bg-error-500/10">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('bookings.update', $booking) }}">
                @csrf
                @method('PUT')

                <div class="space-y-5">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="{{ $labelClass }}">Status<span class="text-error-500">*</span></label>
                            <select name="status" required class="{{ $inputClass }}">
                                @foreach (['pending_payment' => 'Pending Payment', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No Show'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $booking->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Slot<span class="text-error-500">*</span></label>
                            <input type="datetime-local" name="slot"
                                value="{{ old('slot', $booking->slot?->format('Y-m-d\TH:i')) }}"
                                required class="{{ $inputClass }}" />
                        </div>
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">UPI Reference</label>
                        <input type="text" name="upi_reference" value="{{ old('upi_reference', $booking->upi_reference) }}"
                            placeholder="Required to mark as confirmed" class="{{ $inputClass }}" />
                    </div>

                    <div>
                        <label class="{{ $labelClass }}">Internal Notes</label>
                        <textarea name="admin_notes" rows="4" class="{{ $inputClass }} h-auto">{{ old('admin_notes', $booking->admin_notes) }}</textarea>
                    </div>

                    <div>
                        <button type="submit" class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                            Save Changes
                        </button>
                        <a href="{{ route('bookings.index') }}" class="ml-3 text-sm font-medium text-gray-500 hover:text-gray-700">Cancel</a>
                    </div>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection

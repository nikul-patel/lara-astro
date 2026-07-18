@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Astrologers" />

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-success-500 bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">
                {{ session('status') }}
            </div>
        @endif

        <x-common.component-card title="Astrologers">
            <div class="mb-4 flex justify-end">
                <a href="{{ route('astrologers.create') }}"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                    Add Astrologer
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Availability Mode</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Services</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($astrologers as $astrologer)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $astrologer->name }}</span>
                                    <span class="block text-xs text-gray-400">{{ $astrologer->slug }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">
                                    {{ $astrologer->availability_mode === 'manual' ? 'Manual slots' : 'Google Calendar' }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">
                                    {{ $astrologer->services_count }}
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    @if ($astrologer->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('astrologers.edit', $astrologer) }}" class="text-brand-500 hover:text-brand-600">Edit</a>
                                        <form method="POST" action="{{ route('astrologers.destroy', $astrologer) }}" onsubmit="return confirm('Delete this astrologer?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error-500 hover:text-error-600">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-sm text-gray-500 sm:px-6">No astrologers yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $astrologers->links() }}
            </div>
        </x-common.component-card>
    </div>
@endsection

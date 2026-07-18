@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Testimonials" />

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-success-500 bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">
                {{ session('status') }}
            </div>
        @endif

        <x-common.component-card title="Testimonials">
            <div class="mb-4 flex justify-end">
                <a href="{{ route('testimonials.create') }}"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                    Add Testimonial
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Quote</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Rating</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($testimonials as $testimonial)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4 text-sm font-medium text-gray-800 sm:px-6 dark:text-white/90">{{ $testimonial->name }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($testimonial->quote, 60) }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">{{ $testimonial->rating ?? '—' }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    @if ($testimonial->is_active)
                                        <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">Active</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('testimonials.edit', $testimonial) }}" class="text-brand-500 hover:text-brand-600">Edit</a>
                                        <form method="POST" action="{{ route('testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Delete this testimonial?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error-500 hover:text-error-600">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-sm text-gray-500 sm:px-6">No testimonials yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $testimonials->links() }}
            </div>
        </x-common.component-card>
    </div>
@endsection

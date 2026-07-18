@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Blog Posts" />

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-success-500 bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">
                {{ session('status') }}
            </div>
        @endif

        <x-common.component-card title="Blog Posts">
            <div class="mb-4 flex justify-end">
                <a href="{{ route('posts.create') }}"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                    Add Post
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Title</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Slug</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                @php
                                    $displayTitle = $post->getTranslation('title', $defaultLocale, false)
                                        ?: collect($post->getTranslations('title'))->filter()->first();
                                @endphp
                                <td class="px-5 py-4 text-sm font-medium text-gray-800 sm:px-6 dark:text-white/90">{{ $displayTitle }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 sm:px-6 dark:text-gray-300">/{{ $post->slug }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    @if ($post->published_at && $post->published_at->isPast())
                                        <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">Published</span>
                                    @elseif ($post->published_at)
                                        <span class="rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">Scheduled</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">Draft</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('posts.edit', $post) }}" class="text-brand-500 hover:text-brand-600">Edit</a>
                                        <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error-500 hover:text-error-600">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-sm text-gray-500 sm:px-6">No posts yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $posts->links() }}
            </div>
        </x-common.component-card>
    </div>
@endsection

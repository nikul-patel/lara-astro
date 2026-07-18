@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $defaultLocale = array_key_first($locales);
    $post = $post ?? null;
@endphp

@if ($errors->any())
    <div class="mb-5 rounded-lg border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-600 dark:bg-error-500/10">
        <ul class="list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="{{ $labelClass }}">Slug<span class="text-error-500">*</span></label>
            <input type="text" name="slug" value="{{ old('slug', $post?->slug ?? '') }}" required class="{{ $inputClass }}" />
        </div>
        <div>
            <label class="{{ $labelClass }}">Published At</label>
            <input type="datetime-local" name="published_at" value="{{ old('published_at', $post?->published_at?->format('Y-m-d\TH:i')) }}" class="{{ $inputClass }}" />
            <p class="mt-1 text-xs text-gray-400">Leave blank to keep as a draft.</p>
        </div>
    </div>

    <div>
        <label class="{{ $labelClass }}">Featured Image</label>
        @if ($post?->featured_image_path)
            <img src="{{ Storage::url($post->featured_image_path) }}" alt="" class="mb-2 h-32 w-auto rounded-lg object-cover" />
        @endif
        <input type="file" name="featured_image" accept="image/*" class="{{ $inputClass }}" />
    </div>

    @foreach ($locales as $locale => $label)
        <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
            <p class="mb-3 text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">{{ $label }}{{ $locale === $defaultLocale ? ' (default)' : '' }}</p>
            <div class="space-y-4">
                <div>
                    <label class="{{ $labelClass }}">Title{{ $locale === $defaultLocale ? '*' : '' }}</label>
                    <input type="text" name="title[{{ $locale }}]" value="{{ old('title.'.$locale, $post?->getTranslation('title', $locale, false) ?? '') }}"
                        @required($locale === $defaultLocale) class="{{ $inputClass }}" />
                </div>
                <div>
                    <label class="{{ $labelClass }}">Excerpt</label>
                    <input type="text" name="excerpt[{{ $locale }}]" value="{{ old('excerpt.'.$locale, $post?->getTranslation('excerpt', $locale, false) ?? '') }}" class="{{ $inputClass }}" />
                </div>
                <div>
                    <label class="{{ $labelClass }}">Content{{ $locale === $defaultLocale ? '*' : '' }}</label>
                    <textarea name="content[{{ $locale }}]" rows="8" @required($locale === $defaultLocale) class="{{ $inputClass }} h-auto">{{ old('content.'.$locale, $post?->getTranslation('content', $locale, false) ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}">Meta Title</label>
                        <input type="text" name="meta_title[{{ $locale }}]" value="{{ old('meta_title.'.$locale, $post?->getTranslation('meta_title', $locale, false) ?? '') }}" class="{{ $inputClass }}" />
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Meta Description</label>
                        <input type="text" name="meta_description[{{ $locale }}]" value="{{ old('meta_description.'.$locale, $post?->getTranslation('meta_description', $locale, false) ?? '') }}" class="{{ $inputClass }}" />
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div>
        <button type="submit" class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-3 text-sm font-medium text-white transition">
            {{ isset($post) ? 'Save Changes' : 'Create Post' }}
        </button>
        <a href="{{ route('posts.index') }}" class="ml-3 text-sm font-medium text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
</div>

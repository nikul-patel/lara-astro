@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $defaultLocale = array_key_first($locales);
    $testimonial = $testimonial ?? null;
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
            <label class="{{ $labelClass }}">Name<span class="text-error-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $testimonial?->name ?? '') }}" required class="{{ $inputClass }}" />
        </div>
        <div>
            <label class="{{ $labelClass }}">Rating</label>
            <select name="rating" class="{{ $inputClass }}">
                <option value="">No rating</option>
                @foreach ([1, 2, 3, 4, 5] as $value)
                    <option value="{{ $value }}" @selected((string) old('rating', $testimonial?->rating) === (string) $value)>{{ $value }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @foreach ($locales as $locale => $label)
        <div>
            <label class="{{ $labelClass }}">Quote ({{ $label }}){{ $locale === $defaultLocale ? '*' : '' }}</label>
            <textarea name="quote[{{ $locale }}]" rows="3" @required($locale === $defaultLocale) class="{{ $inputClass }} h-auto">{{ old('quote.'.$locale, $testimonial?->getTranslation('quote', $locale, false) ?? '') }}</textarea>
        </div>
    @endforeach

    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1"
            @checked(old('is_active', $testimonial?->is_active ?? true))
            class="h-5 w-5 rounded border-gray-300" />
        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-400">Active</label>
    </div>

    <div>
        <button type="submit" class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-3 text-sm font-medium text-white transition">
            {{ isset($testimonial) ? 'Save Changes' : 'Create Testimonial' }}
        </button>
        <a href="{{ route('testimonials.index') }}" class="ml-3 text-sm font-medium text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
</div>

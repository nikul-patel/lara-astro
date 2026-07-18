@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
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

<div class="space-y-5">
    <div>
        <label class="{{ $labelClass }}">Name<span class="text-error-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $astrologer->name ?? '') }}" required class="{{ $inputClass }}" />
    </div>

    <div>
        <label class="{{ $labelClass }}">Bio</label>
        <textarea name="bio" rows="4" class="{{ $inputClass }} h-auto">{{ old('bio', $astrologer->bio ?? '') }}</textarea>
    </div>

    <div>
        <label class="{{ $labelClass }}">Photo</label>
        <input type="file" name="photo" accept="image/*" class="{{ $inputClass }}" />
        @if (! empty($astrologer?->photo_path))
            <img src="{{ Storage::url($astrologer->photo_path) }}" alt="" class="mt-3 size-16 rounded-full object-cover" />
        @endif
    </div>

    <div>
        <label class="{{ $labelClass }}">Specialties (comma-separated)</label>
        <input type="text" name="specialties" placeholder="Vedic Astrology, Numerology, Tarot"
            value="{{ old('specialties', isset($astrologer) ? implode(', ', $astrologer->specialties ?? []) : '') }}"
            class="{{ $inputClass }}" />
    </div>

    <div>
        <label class="{{ $labelClass }}">Languages (comma-separated)</label>
        <input type="text" name="languages" placeholder="English, Hindi, Gujarati"
            value="{{ old('languages', isset($astrologer) ? implode(', ', $astrologer->languages ?? []) : '') }}"
            class="{{ $inputClass }}" />
    </div>

    <div>
        <label class="{{ $labelClass }}">Availability Mode<span class="text-error-500">*</span></label>
        @php $mode = old('availability_mode', $astrologer->availability_mode ?? 'manual'); @endphp
        <select name="availability_mode" class="{{ $inputClass }}">
            <option value="manual" @selected($mode === 'manual')>Manual slots</option>
            <option value="google_calendar" @selected($mode === 'google_calendar')>Google Calendar sync</option>
        </select>
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1"
            @checked(old('is_active', $astrologer->is_active ?? true))
            class="h-5 w-5 rounded border-gray-300" />
        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-400">Active</label>
    </div>

    <div>
        <button type="submit" class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-3 text-sm font-medium text-white transition">
            {{ isset($astrologer) ? 'Save Changes' : 'Create Astrologer' }}
        </button>
        <a href="{{ route('astrologers.index') }}" class="ml-3 text-sm font-medium text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
</div>

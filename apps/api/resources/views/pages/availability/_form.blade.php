@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
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
        <label class="{{ $labelClass }}">Astrologer<span class="text-error-500">*</span></label>
        @php $selectedAstrologer = old('astrologer_id', $slot->astrologer_id ?? ''); @endphp
        <select name="astrologer_id" required class="{{ $inputClass }}">
            <option value="" disabled @selected(! $selectedAstrologer)>Select an astrologer</option>
            @foreach ($astrologers as $astrologer)
                <option value="{{ $astrologer->id }}" @selected((string) $selectedAstrologer === (string) $astrologer->id)>{{ $astrologer->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="{{ $labelClass }}">Day of Week<span class="text-error-500">*</span></label>
        @php $selectedWeekday = old('weekday', $slot->weekday ?? ''); @endphp
        <select name="weekday" required class="{{ $inputClass }}">
            <option value="" disabled @selected($selectedWeekday === '')>Select a day</option>
            @foreach ($weekdays as $index => $label)
                <option value="{{ $index }}" @selected((string) $selectedWeekday === (string) $index)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="{{ $labelClass }}">Start Time<span class="text-error-500">*</span></label>
            <input type="time" name="start_time" value="{{ old('start_time', isset($slot) ? \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i') : '10:00') }}" required class="{{ $inputClass }}" />
        </div>
        <div>
            <label class="{{ $labelClass }}">End Time<span class="text-error-500">*</span></label>
            <input type="time" name="end_time" value="{{ old('end_time', isset($slot) ? \Illuminate\Support\Carbon::parse($slot->end_time)->format('H:i') : '18:00') }}" required class="{{ $inputClass }}" />
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1"
            @checked(old('is_active', $slot->is_active ?? true))
            class="h-5 w-5 rounded border-gray-300" />
        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-400">Active</label>
    </div>

    <div>
        <button type="submit" class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-3 text-sm font-medium text-white transition">
            {{ isset($slot) ? 'Save Changes' : 'Create Slot' }}
        </button>
        <a href="{{ route('availability.index') }}" class="ml-3 text-sm font-medium text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
</div>

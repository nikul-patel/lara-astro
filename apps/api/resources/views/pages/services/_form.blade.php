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
        <label class="{{ $labelClass }}">Astrologer<span class="text-error-500">*</span></label>
        @php $selectedAstrologer = old('astrologer_id', $service->astrologer_id ?? ''); @endphp
        <select name="astrologer_id" required class="{{ $inputClass }}">
            <option value="" disabled @selected(! $selectedAstrologer)>Select an astrologer</option>
            @foreach ($astrologers as $astrologer)
                <option value="{{ $astrologer->id }}" @selected((string) $selectedAstrologer === (string) $astrologer->id)>{{ $astrologer->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="{{ $labelClass }}">Name<span class="text-error-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $service->name ?? '') }}" required class="{{ $inputClass }}" />
    </div>

    <div>
        <label class="{{ $labelClass }}">Description</label>
        <textarea name="description" rows="3" class="{{ $inputClass }} h-auto">{{ old('description', $service->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div>
            <label class="{{ $labelClass }}">Duration (minutes)<span class="text-error-500">*</span></label>
            <input type="number" name="duration_minutes" min="5" max="480" value="{{ old('duration_minutes', $service->duration_minutes ?? 30) }}" required class="{{ $inputClass }}" />
        </div>
        <div>
            <label class="{{ $labelClass }}">Price (INR)<span class="text-error-500">*</span></label>
            <input type="number" step="0.01" name="price_inr" value="{{ old('price_inr', $service->price_inr ?? '') }}" required class="{{ $inputClass }}" />
        </div>
        <div>
            <label class="{{ $labelClass }}">Price (USD)<span class="text-error-500">*</span></label>
            <input type="number" step="0.01" name="price_usd" value="{{ old('price_usd', $service->price_usd ?? '') }}" required class="{{ $inputClass }}" />
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1"
            @checked(old('is_active', $service->is_active ?? true))
            class="h-5 w-5 rounded border-gray-300" />
        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-400">Active</label>
    </div>

    <div>
        <button type="submit" class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-3 text-sm font-medium text-white transition">
            {{ isset($service) ? 'Save Changes' : 'Create Service' }}
        </button>
        <a href="{{ route('services.index') }}" class="ml-3 text-sm font-medium text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
</div>

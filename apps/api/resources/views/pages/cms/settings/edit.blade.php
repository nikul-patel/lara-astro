@extends('layouts.app')

@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $languageLabels = ['en' => 'English', 'hi' => 'Hindi', 'gu' => 'Gujarati'];
    $selectedLanguages = old('supported_languages', $setting->supported_languages ?? []);
    $selectedCurrencies = old('currencies', $setting->currencies ?? []);
@endphp

@section('content')
    <x-common.page-breadcrumb pageTitle="Settings" />

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-success-500 bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-400">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-600 dark:bg-error-500/10">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <x-common.component-card title="Branding">
                <div class="space-y-5">
                    <div>
                        <label class="{{ $labelClass }}">Site Name<span class="text-error-500">*</span></label>
                        <input type="text" name="site_name" value="{{ old('site_name', $setting->site_name) }}" required class="{{ $inputClass }}" />
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Logo</label>
                        @if ($setting->logo_path)
                            <img src="{{ Storage::url($setting->logo_path) }}" alt="" class="mb-2 h-12 w-auto" />
                        @endif
                        <input type="file" name="logo" accept="image/*" class="{{ $inputClass }}" />
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Languages & Currency">
                <div class="space-y-5">
                    <div>
                        <label class="{{ $labelClass }}">Supported Languages<span class="text-error-500">*</span></label>
                        <div class="flex flex-wrap gap-4">
                            @foreach ($languageLabels as $code => $label)
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="supported_languages[]" value="{{ $code }}" @checked(in_array($code, $selectedLanguages)) class="h-5 w-5 rounded border-gray-300" />
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="{{ $labelClass }}">Default Currency<span class="text-error-500">*</span></label>
                            <select name="default_currency" required class="{{ $inputClass }}">
                                @foreach (['INR', 'USD'] as $currency)
                                    <option value="{{ $currency }}" @selected(old('default_currency', $setting->default_currency) === $currency)>{{ $currency }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Accepted Currencies<span class="text-error-500">*</span></label>
                            <div class="flex flex-wrap gap-4 pt-2.5">
                                @foreach (['INR', 'USD'] as $currency)
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="currencies[]" value="{{ $currency }}" @checked(in_array($currency, $selectedCurrencies)) class="h-5 w-5 rounded border-gray-300" />
                                        {{ $currency }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="UPI Payment">
                <div class="space-y-5">
                    <div>
                        <label class="{{ $labelClass }}">UPI ID</label>
                        <input type="text" name="upi_id" value="{{ old('upi_id', $setting->upi_id) }}" placeholder="yourname@upi" class="{{ $inputClass }}" />
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">UPI QR Code</label>
                        @if ($setting->upi_qr_path)
                            <img src="{{ Storage::url($setting->upi_qr_path) }}" alt="" class="mb-2 h-32 w-32 rounded-lg object-cover" />
                        @endif
                        <input type="file" name="upi_qr" accept="image/*" class="{{ $inputClass }}" />
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Contact & Social">
                <div class="space-y-5">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="{{ $labelClass }}">Contact Email</label>
                            <input type="email" name="contact[email]" value="{{ old('contact.email', $setting->contact['email'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Contact Phone</label>
                            <input type="text" name="contact[phone]" value="{{ old('contact.phone', $setting->contact['phone'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Address</label>
                        <input type="text" name="contact[address]" value="{{ old('contact.address', $setting->contact['address'] ?? '') }}" class="{{ $inputClass }}" />
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="{{ $labelClass }}">Facebook URL</label>
                            <input type="url" name="social_links[facebook]" value="{{ old('social_links.facebook', $setting->social_links['facebook'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Instagram URL</label>
                            <input type="url" name="social_links[instagram]" value="{{ old('social_links.instagram', $setting->social_links['instagram'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">YouTube URL</label>
                            <input type="url" name="social_links[youtube]" value="{{ old('social_links.youtube', $setting->social_links['youtube'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">WhatsApp URL</label>
                            <input type="url" name="social_links[whatsapp]" value="{{ old('social_links.whatsapp', $setting->social_links['whatsapp'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <label class="{{ $labelClass }}">Privacy Policy Page Slug</label>
                            <input type="text" name="legal_links[privacy_policy]" value="{{ old('legal_links.privacy_policy', $setting->legal_links['privacy_policy'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Terms of Service Page Slug</label>
                            <input type="text" name="legal_links[terms_of_service]" value="{{ old('legal_links.terms_of_service', $setting->legal_links['terms_of_service'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Refund Policy Page Slug</label>
                            <input type="text" name="legal_links[refund_policy]" value="{{ old('legal_links.refund_policy', $setting->legal_links['refund_policy'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="SEO Defaults">
                <div class="space-y-5">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="{{ $labelClass }}">Default Meta Title</label>
                            <input type="text" name="seo[default_meta_title]" value="{{ old('seo.default_meta_title', $setting->seo['default_meta_title'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Default Meta Description</label>
                            <input type="text" name="seo[default_meta_description]" value="{{ old('seo.default_meta_description', $setting->seo['default_meta_description'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="{{ $labelClass }}">Google Analytics Measurement ID</label>
                            <input type="text" name="seo[ga_measurement_id]" value="{{ old('seo.ga_measurement_id', $setting->seo['ga_measurement_id'] ?? '') }}" placeholder="G-XXXXXXXXXX" class="{{ $inputClass }}" />
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Search Console Verification</label>
                            <input type="text" name="seo[search_console_verification]" value="{{ old('seo.search_console_verification', $setting->seo['search_console_verification'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="{{ $labelClass }}">Schema.org Business Name</label>
                            <input type="text" name="seo[schema_business_name]" value="{{ old('seo.schema_business_name', $setting->seo['schema_business_name'] ?? '') }}" class="{{ $inputClass }}" />
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Schema.org Business Type</label>
                            <input type="text" name="seo[schema_business_type]" value="{{ old('seo.schema_business_type', $setting->seo['schema_business_type'] ?? '') }}" placeholder="ProfessionalService" class="{{ $inputClass }}" />
                        </div>
                    </div>
                </div>
            </x-common.component-card>

            <div>
                <button type="submit" class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
@endsection

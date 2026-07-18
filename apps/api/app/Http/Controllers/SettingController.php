<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $setting = Setting::current();

        return view('pages.cms.settings.edit', compact('setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = Setting::current();

        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'supported_languages' => ['required', 'array', 'min:1'],
            'supported_languages.*' => ['string', 'in:en,hi,gu'],
            'default_currency' => ['required', 'in:INR,USD'],
            'currencies' => ['required', 'array', 'min:1'],
            'currencies.*' => ['string', 'in:INR,USD'],
            'upi_id' => ['nullable', 'string', 'max:255'],
            'upi_qr' => ['nullable', 'image', 'max:2048'],
            'contact.email' => ['nullable', 'email', 'max:255'],
            'contact.phone' => ['nullable', 'string', 'max:50'],
            'contact.address' => ['nullable', 'string', 'max:500'],
            'social_links.facebook' => ['nullable', 'url', 'max:255'],
            'social_links.instagram' => ['nullable', 'url', 'max:255'],
            'social_links.youtube' => ['nullable', 'url', 'max:255'],
            'social_links.whatsapp' => ['nullable', 'url', 'max:255'],
            'legal_links.privacy_policy' => ['nullable', 'string', 'max:255'],
            'legal_links.terms_of_service' => ['nullable', 'string', 'max:255'],
            'legal_links.refund_policy' => ['nullable', 'string', 'max:255'],
            'seo.default_meta_title' => ['nullable', 'string', 'max:255'],
            'seo.default_meta_description' => ['nullable', 'string', 'max:500'],
            'seo.ga_measurement_id' => ['nullable', 'string', 'max:255'],
            'seo.search_console_verification' => ['nullable', 'string', 'max:255'],
            'seo.schema_business_name' => ['nullable', 'string', 'max:255'],
            'seo.schema_business_type' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('branding', 'public');

            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
        }

        if ($request->hasFile('upi_qr')) {
            $validated['upi_qr_path'] = $request->file('upi_qr')->store('branding', 'public');

            if ($setting->upi_qr_path) {
                Storage::disk('public')->delete($setting->upi_qr_path);
            }
        }

        unset($validated['logo'], $validated['upi_qr']);

        $setting->update($validated);

        return redirect()->route('settings.edit')->with('status', 'Settings updated.');
    }
}

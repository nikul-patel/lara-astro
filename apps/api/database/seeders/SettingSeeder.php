<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds the singleton Settings row for the fictional "Jyotish Path" demo
 * practice (PRD §1). legal_links keys match SettingResource's fixed
 * privacy_policy/terms_of_service/refund_policy mapping and point at the
 * slugs created by PageSeeder. Idempotent: reuses the single settings row.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $setting = Setting::query()->firstOrNew([]);

        $setting->fill([
            'site_name' => 'Jyotish Path',
            'supported_languages' => ['en', 'hi', 'gu'],
            'default_currency' => 'INR',
            'currencies' => ['INR', 'USD'],
            'upi_id' => 'jyotishpath@upi',
            'contact' => [
                'email' => 'hello@jyotishpath.example',
                'phone' => '+91 98250 12345',
                'address' => 'Demo Studio, Ahmedabad, Gujarat 380001, India',
            ],
            'social_links' => [
                'facebook' => 'https://facebook.com/jyotishpath',
                'instagram' => 'https://instagram.com/jyotishpath',
                'youtube' => 'https://youtube.com/@jyotishpath',
                'whatsapp' => 'https://wa.me/919825012345',
            ],
            'legal_links' => [
                'privacy_policy' => 'privacy-policy',
                'terms_of_service' => 'terms-and-conditions',
                'refund_policy' => 'refund-cancellation-policy',
            ],
            'seo' => [
                'default_meta_title' => 'Jyotish Path — Vedic Astrology Consultations & Courses',
                'default_meta_description' => 'Book grounded Vedic astrology consultations and learn chart reading through practical online courses in English, Hindi and Gujarati.',
                'schema_business_name' => 'Jyotish Path',
                'schema_business_type' => 'LocalBusiness',
            ],
            'astrology_western_enabled' => true,
            'astrology_forced_chart_style' => null,
        ]);

        $setting->save();
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Setting */
class SettingResource extends JsonResource
{
    /**
     * Maps our fixed legal_links keys (set from the admin Settings screen,
     * see SettingController) to the {label, slug}[] shape apps/web's
     * SiteFooter expects.
     *
     * @var array<string, string>
     */
    private const LEGAL_LINK_LABELS = [
        'privacy_policy' => 'Privacy Policy',
        'terms_of_service' => 'Terms of Service',
        'refund_policy' => 'Refund Policy',
    ];

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'site_name' => $this->site_name,
            'logo_url' => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null,
            'supported_languages' => $this->supported_languages,
            'upi_id' => $this->upi_id,
            'upi_qr_url' => $this->upi_qr_path ? Storage::disk('public')->url($this->upi_qr_path) : null,
            'default_currency' => $this->default_currency,
            'currencies' => $this->currencies,
            'contact' => $this->contact,
            'social_links' => $this->social_links,
            'legal_links' => collect(self::LEGAL_LINK_LABELS)
                ->filter(fn ($label, $key) => filled($this->legal_links[$key] ?? null))
                ->map(fn ($label, $key) => ['label' => $label, 'slug' => $this->legal_links[$key]])
                ->values(),
            'seo' => $this->seo,
        ];
    }
}

<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected $fillable = [
        'site_name',
        'logo_path',
        'supported_languages',
        'default_currency',
        'currencies',
        'upi_id',
        'upi_qr_path',
        'contact',
        'social_links',
        'legal_links',
        'seo',
        'astrology_western_enabled',
        'astrology_forced_chart_style',
    ];

    protected function casts(): array
    {
        return [
            'supported_languages' => 'array',
            'currencies' => 'array',
            'contact' => 'array',
            'social_links' => 'array',
            'legal_links' => 'array',
            'seo' => 'array',
            'astrology_western_enabled' => 'boolean',
        ];
    }

    /**
     * There is exactly one Settings row per deployment (PRD §10a: one
     * codebase per client). Creates it with sane defaults on first access.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'site_name' => config('app.name', 'Astrology Platform'),
            'supported_languages' => ['en', 'hi', 'gu'],
            'default_currency' => 'INR',
            'currencies' => ['INR', 'USD'],
        ]);
    }
}

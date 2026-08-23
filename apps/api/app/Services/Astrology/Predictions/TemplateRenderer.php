<?php

namespace App\Services\Astrology\Predictions;

/**
 * Fills {slot} placeholders in a curated template string. Shared by every
 * predictor so template authoring stays plain, readable text rather than
 * sprintf-style format strings.
 */
class TemplateRenderer
{
    /**
     * @param  array<string, string|int>  $slots
     */
    public static function render(string $template, array $slots): string
    {
        $replacements = [];
        foreach ($slots as $key => $value) {
            $replacements["{{$key}}"] = (string) $value;
        }

        return strtr($template, $replacements);
    }
}

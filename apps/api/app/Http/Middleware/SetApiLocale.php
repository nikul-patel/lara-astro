<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves ?locale= for the public API (docs/API_CONTRACT.md: "Defaults to
 * `en` if omitted or unsupported"), then sets the app locale so
 * spatie/laravel-translatable's implicit accessors (e.g. $page->title)
 * resolve to the right language without every resource having to know
 * about the query param. The valid locale set mirrors apps/web's
 * routing.locales, not Setting::supported_languages — the latter is which
 * languages the site currently advertises, not which locales the schema
 * can store content in.
 */
class SetApiLocale
{
    public const SUPPORTED_LOCALES = ['en', 'hi', 'gu'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('locale');

        app()->setLocale(in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : 'en');

        return $next($request);
    }
}

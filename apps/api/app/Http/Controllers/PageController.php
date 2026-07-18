<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Page::query()->latest()->paginate(15);
        $defaultLocale = $this->defaultLocale();

        return view('pages.cms.pages.index', compact('pages', 'defaultLocale'));
    }

    public function create(): View
    {
        $locales = $this->locales();

        return view('pages.cms.pages.create', compact('locales'));
    }

    public function store(Request $request): RedirectResponse
    {
        Page::create($this->validated($request));

        return redirect()->route('pages.index')->with('status', 'Page created.');
    }

    public function edit(Page $page): View
    {
        $locales = $this->locales();

        return view('pages.cms.pages.edit', compact('page', 'locales'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page->update($this->validated($request, $page));

        return redirect()->route('pages.index')->with('status', 'Page updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('pages.index')->with('status', 'Page removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Page $page = null): array
    {
        $rules = [
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('pages', 'slug')->ignore($page)],
        ];

        foreach ($this->locales() as $locale => $label) {
            $required = $locale === $this->defaultLocale() ? 'required' : 'nullable';
            $rules["title.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["content.{$locale}"] = [$required, 'string'];
            $rules["meta_title.{$locale}"] = ['nullable', 'string', 'max:255'];
            $rules["meta_description.{$locale}"] = ['nullable', 'string', 'max:500'];
        }

        return $request->validate($rules);
    }

    /**
     * @return array<string, string>
     */
    private function locales(): array
    {
        $supported = Setting::current()->supported_languages;

        return $supported
            ? collect($supported)->mapWithKeys(fn (string $code) => [$code => strtoupper($code)])->all()
            : ['en' => 'EN'];
    }

    private function defaultLocale(): string
    {
        return array_key_first($this->locales());
    }
}

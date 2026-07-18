<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        $testimonials = Testimonial::query()->latest()->paginate(15);

        return view('pages.cms.testimonials.index', compact('testimonials'));
    }

    public function create(): View
    {
        $locales = $this->locales();

        return view('pages.cms.testimonials.create', compact('locales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        Testimonial::create($data);

        return redirect()->route('testimonials.index')->with('status', 'Testimonial created.');
    }

    public function edit(Testimonial $testimonial): View
    {
        $locales = $this->locales();

        return view('pages.cms.testimonials.edit', compact('testimonial', 'locales'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        $testimonial->update($data);

        return redirect()->route('testimonials.index')->with('status', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return redirect()->route('testimonials.index')->with('status', 'Testimonial removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];

        foreach ($this->locales() as $locale => $label) {
            $required = $locale === $this->defaultLocale() ? 'required' : 'nullable';
            $rules["quote.{$locale}"] = [$required, 'string', 'max:1000'];
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

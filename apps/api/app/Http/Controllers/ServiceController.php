<?php

namespace App\Http\Controllers;

use App\Models\Astrologer;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::with('astrologer')->latest()->paginate(15);

        return view('pages.services.index', compact('services'));
    }

    public function create(): View
    {
        $astrologers = Astrologer::orderBy('name')->get();

        return view('pages.services.create', compact('astrologers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        Service::create($data);

        return redirect()->route('services.index')->with('status', 'Service created.');
    }

    public function edit(Service $service): View
    {
        $astrologers = Astrologer::orderBy('name')->get();

        return view('pages.services.edit', compact('service', 'astrologers'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validated($request));

        return redirect()->route('services.index')->with('status', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('services.index')->with('status', 'Service removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'astrologer_id' => ['required', 'exists:astrologers,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'price_inr' => ['required', 'numeric', 'min:0'],
            'price_usd' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (Service::where('slug', $slug)->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}

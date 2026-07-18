<?php

namespace App\Http\Controllers;

use App\Models\Astrologer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AstrologerController extends Controller
{
    public function index(): View
    {
        $astrologers = Astrologer::withCount('services')->latest()->paginate(15);

        return view('pages.astrologers.index', compact('astrologers'));
    }

    public function create(): View
    {
        return view('pages.astrologers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('astrologers', 'public');
        }

        $data['slug'] = $this->uniqueSlug($data['name']);

        Astrologer::create($data);

        return redirect()->route('astrologers.index')->with('status', 'Astrologer created.');
    }

    public function edit(Astrologer $astrologer): View
    {
        return view('pages.astrologers.edit', compact('astrologer'));
    }

    public function update(Request $request, Astrologer $astrologer): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('astrologers', 'public');
        }

        $astrologer->update($data);

        return redirect()->route('astrologers.index')->with('status', 'Astrologer updated.');
    }

    public function destroy(Astrologer $astrologer): RedirectResponse
    {
        if ($astrologer->bookings()->exists()) {
            return redirect()->route('astrologers.index')
                ->with('error', 'Cannot delete an astrologer with existing bookings. Deactivate them instead.');
        }

        $astrologer->delete();

        return redirect()->route('astrologers.index')->with('status', 'Astrologer removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'specialties' => ['nullable', 'string'],
            'languages' => ['nullable', 'string'],
            'availability_mode' => ['required', 'in:manual,google_calendar'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return [
            'name' => $validated['name'],
            'bio' => $validated['bio'] ?? null,
            'specialties' => $this->splitList($validated['specialties'] ?? ''),
            'languages' => $this->splitList($validated['languages'] ?? ''),
            'availability_mode' => $validated['availability_mode'],
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function splitList(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (Astrologer::where('slug', $slug)->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}

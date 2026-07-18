<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()->latest('published_at')->paginate(15);

        return view('pages.cms.posts.index', compact('posts'));
    }

    public function create(): View
    {
        $locales = $this->locales();

        return view('pages.cms.posts.create', compact('locales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('featured_image')) {
            $data['featured_image_path'] = $request->file('featured_image')->store('posts', 'public');
        }

        Post::create($data);

        return redirect()->route('posts.index')->with('status', 'Post created.');
    }

    public function edit(Post $post): View
    {
        $locales = $this->locales();

        return view('pages.cms.posts.edit', compact('post', 'locales'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $this->validated($request, $post);

        if ($request->hasFile('featured_image')) {
            $data['featured_image_path'] = $request->file('featured_image')->store('posts', 'public');
        }

        $post->update($data);

        return redirect()->route('posts.index')->with('status', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('posts.index')->with('status', 'Post removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Post $post = null): array
    {
        $rules = [
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('posts', 'slug')->ignore($post)],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'published_at' => ['nullable', 'date'],
        ];

        foreach ($this->locales() as $locale => $label) {
            $required = $locale === $this->defaultLocale() ? 'required' : 'nullable';
            $rules["title.{$locale}"] = [$required, 'string', 'max:255'];
            $rules["excerpt.{$locale}"] = ['nullable', 'string', 'max:500'];
            $rules["content.{$locale}"] = [$required, 'string'];
            $rules["meta_title.{$locale}"] = ['nullable', 'string', 'max:255'];
            $rules["meta_description.{$locale}"] = ['nullable', 'string', 'max:500'];
        }

        $validated = $request->validate($rules);
        unset($validated['featured_image']);

        return $validated;
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

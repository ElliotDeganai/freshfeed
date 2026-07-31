<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Pages/Index', [
            'pages' => Page::withCount('sections')->orderBy('order')->orderBy('title')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pages/Edit', [
            'page' => null,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'sectionTypes' => PageSection::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePage($request);

        $page = Page::create([
            ...$data,
            'slug' => $data['slug'] ?: Str::slug($data['title']),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.pages.edit', $page)
            ->with('success', 'Page créée. Ajoute maintenant ses sections.');
    }

    public function edit(Page $page): Response
    {
        return Inertia::render('Admin/Pages/Edit', [
            'page' => $page->load(['sections.categories:id,name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'sectionTypes' => PageSection::TYPES,
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validatePage($request, $page->id);

        $page->update([
            ...$data,
            'slug' => $data['slug'] ?: Str::slug($data['title']),
        ]);

        return back()->with('success', 'Page mise à jour.');
    }

    public function destroy(Page $page)
    {
        $page->delete(); // cascade sur les sections
        return redirect()->route('admin.pages.index')->with('success', 'Page supprimée.');
    }

    private function validatePage(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                'unique:pages,slug' . ($ignoreId ? ",$ignoreId" : ''),
            ],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'order' => ['integer'],
        ]);
    }

    // -----------------------------------------------------------------
    // Sections d'une page
    // -----------------------------------------------------------------

    public function storeSection(Request $request, Page $page)
    {
        $data = $this->validateSection($request);

        $section = $page->sections()->create([
            'title' => $data['title'] ?? null,
            'type' => $data['type'],
            'order' => $page->sections()->max('order') + 1,
            'settings' => $data['settings'] ?? [],
            'custom_html' => $data['custom_html'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (! empty($data['category_ids'])) {
            $section->categories()->sync($data['category_ids']);
        }

        return back()->with('success', 'Section ajoutée.');
    }

    public function updateSection(Request $request, Page $page, PageSection $section)
    {
        abort_unless($section->page_id === $page->id, 404);

        $data = $this->validateSection($request);

        $section->update([
            'title' => $data['title'] ?? null,
            'type' => $data['type'],
            'settings' => $data['settings'] ?? [],
            'custom_html' => $data['custom_html'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $section->categories()->sync($data['category_ids'] ?? []);

        return back()->with('success', 'Section mise à jour.');
    }

    public function reorderSections(Request $request, Page $page)
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:page_sections,id'],
        ]);

        foreach ($data['order'] as $index => $sectionId) {
            PageSection::where('id', $sectionId)
                ->where('page_id', $page->id)
                ->update(['order' => $index]);
        }

        return back();
    }

    public function destroySection(Page $page, PageSection $section)
    {
        abort_unless($section->page_id === $page->id, 404);
        $section->delete();

        return back()->with('success', 'Section supprimée.');
    }

    private function validateSection(Request $request): array
    {
        return $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(PageSection::TYPES))],
            'settings' => ['nullable', 'array'],
            'settings.sort' => ['nullable', 'in:recent,popular,random'],
            'settings.limit' => ['nullable', 'integer', 'min:1', 'max:48'],
            'custom_html' => ['nullable', 'string'],
            'category_ids' => ['array'],
            'category_ids.*' => ['exists:categories,id'],
            'is_active' => ['boolean'],
        ]);
    }
}

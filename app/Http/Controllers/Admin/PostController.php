<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();

        $posts = Post::query()
            ->with(['categories:id,name'])
            ->when(
                // Un contributeur ne voit que ses propres posts,
                // un editor/admin (manage-posts) voit tout.
                ! $user->can('manage-posts'),
                fn ($q) => $q->where('user_id', $user->id)
            )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only(['status', 'search']),
            'can' => [
                'publish' => $user->can('publish-posts'),
                'manageAll' => $user->can('manage-posts'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Posts/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'can' => ['publish' => Auth::user()->can('publish-posts')],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'calories' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'calories_unit' => ['nullable', 'in:g,ml'],
            'category_ids' => ['array'],
            'category_ids.*' => ['exists:categories,id'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $canPublish = Auth::user()->can('publish-posts');

        $post = Post::create([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'calories' => $data['calories'] ?? null,
            'calories_unit' => $data['calories_unit'] ?? null,
            'user_id' => Auth::id(),
            // Un contributeur sans droit de publication reste toujours en brouillon,
            // quelle que soit l'action demandée côté front.
            'status' => $canPublish && $request->boolean('publish') ? 'published' : 'draft',
            'published_at' => $canPublish && $request->boolean('publish') ? now() : null,
        ]);

        if (! empty($data['category_ids'])) {
            $post->categories()->sync($data['category_ids']);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $post->update(['image_path' => $path]);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Recette créée.');
    }

    public function edit(Post $post): Response
    {
        $this->authorizeOwnershipOrManage($post);

        return Inertia::render('Admin/Posts/Edit', [
            'post' => $post->load('categories:id,name'),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'can' => ['publish' => Auth::user()->can('publish-posts')],
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorizeOwnershipOrManage($post);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'calories' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'calories_unit' => ['nullable', 'in:g,ml'],
            'category_ids' => ['array'],
            'category_ids.*' => ['exists:categories,id'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $canPublish = Auth::user()->can('publish-posts');

        $post->fill([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'calories' => $data['calories'] ?? null,
            'calories_unit' => $data['calories_unit'] ?? null,
        ]);

        if ($canPublish && $request->has('status')) {
            $post->status = $request->input('status'); // 'draft' | 'published'
            $post->published_at = $post->status === 'published' ? ($post->published_at ?? now()) : null;
        }

        $post->save();

        $post->categories()->sync($data['category_ids'] ?? []);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $post->update(['image_path' => $path]);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Recette mise à jour.');
    }

    public function destroy(Post $post)
    {
        $this->authorizeOwnershipOrManage($post);
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Recette supprimée.');
    }

    /**
     * Un contributeur (manage-own-posts) ne peut agir que sur ses propres posts.
     * Un editor/admin (manage-posts) peut agir sur tout.
     */
    private function authorizeOwnershipOrManage(Post $post): void
    {
        $user = Auth::user();

        if ($user->can('manage-posts')) {
            return;
        }

        abort_unless(
            $user->can('manage-own-posts') && $post->user_id === $user->id,
            403
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    public function index(Request $request): Response
    {
        $categories = Category::withCount('posts')->orderBy('name')->get();

        $posts = Post::query()
            ->published()
            ->with(['user:id,name', 'categories:id,name'])
            ->when(
                $request->filled('category'),
                fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('categories.id', $request->integer('category')))
            )
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Explore', [
            'categories' => $categories,
            'posts' => $posts,
            'activeCategory' => $request->integer('category') ?: null,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function index(): Response
    {
        $posts = Post::query()
            ->published()
            ->with(['user:id,name,avatar_path', 'categories:id,name'])
            ->latest('published_at')
            ->paginate(12);

        return Inertia::render('Feed', [
            'posts' => $posts,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        return Inertia::render('Admin/Dashboard', [
            'can' => [
                'posts' => $user->can('manage-posts') || $user->can('manage-own-posts'),
                'categories' => $user->can('manage-categories'),
                'pages' => $user->can('manage-pages'),
                'users' => $user->can('manage-users'),
                'settings' => $user->can('manage-settings'),
            ],

            'posts' => [
                'total' => Post::count(),
                'published' => Post::where('status', 'published')->count(),
                'draft' => Post::where('status', 'draft')->count(),
                'recent' => Post::query()
                    ->latest()
                    ->limit(4)
                    ->get(['id', 'title', 'status', 'created_at', 'user_id']),
            ],

            'categories' => [
                'total' => Category::count(),
                'recent' => Category::withCount('posts')->latest()->limit(4)->get(['id', 'name']),
            ],

            'pages' => [
                'total' => Page::count(),
                'active' => Page::where('is_active', true)->count(),
            ],

            'users' => [
                'total' => User::count(),
                'by_role' => User::with('roles:name')->get()
                    ->groupBy(fn ($u) => $u->roles->first()->name ?? 'sans rôle')
                    ->map->count(),
            ],
        ]);
    }
}

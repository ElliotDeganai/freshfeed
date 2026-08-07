<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostFavoriteController extends Controller
{
    public function store(Post $post)
    {
        abort_unless($post->status === 'published', 404);

        Auth::user()->favoritePosts()->syncWithoutDetaching([$post->id]);

        return back()->with('success', 'Ajoutée à tes favoris.');
    }

    public function destroy(Post $post)
    {
        Auth::user()->favoritePosts()->detach($post->id);

        return back()->with('success', 'Retirée de tes favoris.');
    }
}

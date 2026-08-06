<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostRatingController extends Controller
{
    public function store(Request $request, Post $post)
    {
        abort_unless($post->status === 'published', 404);
        abort_if($post->user_id === Auth::id(), 403, 'Tu ne peux pas noter ta propre recette.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        PostRating::updateOrCreate(
            ['post_id' => $post->id, 'user_id' => Auth::id()],
            ['rating' => $data['rating']]
        );

        return back()->with('success', 'Merci pour ton avis !');
    }

    public function destroy(Post $post)
    {
        PostRating::where('post_id', $post->id)->where('user_id', Auth::id())->delete();

        return back()->with('success', 'Ton avis a été retiré.');
    }
}

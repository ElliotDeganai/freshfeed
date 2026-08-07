<?php

namespace App\Http\Controllers;

use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileAvatarController extends Controller
{
    public function __construct(private ImageUploadService $imageUploader)
    {
    }

    public function update(Request $request)
    {
        $request->validate([
            // max ici = plafond de sécurité (évite qu'un fichier énorme sature le
            // traitement) — pas la taille finale, ImageUploadService s'en charge.
            'avatar' => ['required', 'mimes:png,jpg,jpeg,svg', 'max:20480'],
        ]);

        $user = Auth::user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update([
            'avatar_path' => $this->imageUploader->store(
                $request->file('avatar'),
                'avatars',
                maxWidth: 512,
                maxHeight: 512,
                targetMaxBytes: 400_000,
            ),
        ]);

        return back()->with('success', 'Photo de profil mise à jour.');
    }

    public function destroy()
    {
        $user = Auth::user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return back()->with('success', 'Photo de profil supprimée.');
    }
}

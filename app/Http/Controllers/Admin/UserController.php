<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::with('roles:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'created_at']),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        // Un seul rôle "principal" par simplicité admin — adapte en syncRoles([...])
        // si tu veux permettre le cumul de rôles.
        $user->syncRoles([$data['role']]);

        return back()->with('success', "Rôle mis à jour pour {$user->name}.");
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403, 'Tu ne peux pas te supprimer toi-même.');

        $user->delete();

        return back()->with('success', 'Utilisateur supprimé.');
    }
}

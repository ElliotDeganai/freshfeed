<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AccountInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
                ->get(['id', 'name', 'email', 'created_at', 'activated_at', 'last_login_at']),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Inscription fermée au public (voir routes/auth.php) — c'est le seul moyen
     * de créer un compte : l'admin invite quelqu'un par email, qui reçoit un lien
     * "définir mon mot de passe" (on réutilise le mécanisme de réinitialisation de
     * mot de passe standard de Laravel, ni plus ni moins).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            // Mot de passe aléatoire, jamais communiqué — l'utilisateur définit le
            // sien via le lien d'activation avant de pouvoir se connecter.
            'password' => Hash::make(Str::random(40)),
        ]);

        $user->assignRole($data['role']);

        $token = Password::broker()->createToken($user);
        $user->notify(new AccountInvitationNotification($token));

        return back()->with('success', "Invitation envoyée à {$user->email}.");
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

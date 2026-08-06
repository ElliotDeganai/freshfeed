<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FreshFeedRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // -----------------------
        // 1. Permissions atomiques
        // -----------------------
        $permissions = [
            'view-admin',
            'manage-posts',        // CRUD + publication sur TOUS les posts
            'manage-own-posts',    // CRUD sur ses propres posts uniquement (contributeur)
            'publish-posts',       // droit de faire passer un post en "published"
            'manage-categories',
            'manage-pages',
            'manage-users',
            'manage-settings',
            'manage-nutrition',    // base de référence pour l'estimation calorique
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // -----------------------
        // 2. Rôles
        // -----------------------
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions); // tout

        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions([
            'manage-posts',
            'publish-posts',
            'manage-categories',
            'manage-pages',
        ]);

        $contributor = Role::firstOrCreate(['name' => 'contributor', 'guard_name' => 'web']);
        $contributor->syncPermissions([
            'manage-own-posts', // pas de publish-posts : reste en brouillon jusqu'à validation
        ]);

        // -----------------------
        // 3. Utilisateur admin par défaut
        // -----------------------
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@freshfeed.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('changeme123'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->syncRoles(['admin']);

        $this->command->info('Rôles admin / editor / contributor créés.');
        $this->command->info('Admin : admin@freshfeed.local / changeme123 — à changer immédiatement.');
    }
}

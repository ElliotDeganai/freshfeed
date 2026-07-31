<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FreshFeedTestUsersSeeder extends Seeder
{
    /**
     * Crée un compte de test par rôle. À lancer APRÈS FreshFeedRolesAndPermissionsSeeder
     * (qui crée les rôles admin/editor/contributor et le compte admin@freshfeed.local).
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin.test@freshfeed.local'],
            [
                'name' => 'Admin Test',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        $editor = User::updateOrCreate(
            ['email' => 'editor.test@freshfeed.local'],
            [
                'name' => 'Éditeur Test',
                'password' => Hash::make('editor123'),
                'email_verified_at' => now(),
            ]
        );
        $editor->syncRoles(['editor']);

        $contributor = User::updateOrCreate(
            ['email' => 'contributor.test@freshfeed.local'],
            [
                'name' => 'Contributeur Test',
                'password' => Hash::make('contributor123'),
                'email_verified_at' => now(),
            ]
        );
        $contributor->syncRoles(['contributor']);

        $this->command->info('3 comptes de test créés :');
        $this->command->info('  Admin       : admin.test@freshfeed.local / admin123');
        $this->command->info('  Editor      : editor.test@freshfeed.local / editor123');
        $this->command->info('  Contributor : contributor.test@freshfeed.local / contributor123');
    }
}

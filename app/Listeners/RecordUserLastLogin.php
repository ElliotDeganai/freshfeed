<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;
use App\Models\LoginLog;

class RecordUserLastLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // updateQuietly évite de déclencher les observers/événements du modèle
        // pour une mise à jour purement technique, sans intérêt métier.
        $event->user->forceFill(['last_login_at' => now()])->saveQuietly();

        // Journal complet (pas juste la dernière date) — nécessaire pour le
        // graphique "connexions par jour" du dashboard analytics.
        LoginLog::create(['user_id' => $event->user->id, 'created_at' => now()]);
    }
}

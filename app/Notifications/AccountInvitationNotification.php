<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(private string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Même token/table que le flux "mot de passe oublié" standard de Laravel —
        // le lien mène donc vers la page reset-password/{token} déjà existante,
        // seul le texte de l'email change pour parler d'activation plutôt que
        // de mot de passe oublié.
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Ton compte ' . config('app.name') . ' est prêt')
            ->greeting('Bienvenue 👋')
            ->line("Un compte vient d'être créé pour toi sur " . config('app.name') . '.')
            ->line('Clique sur le bouton ci-dessous pour définir ton mot de passe et accéder à ton compte.')
            ->action('Définir mon mot de passe', $url)
            ->line('Ce lien expire dans 60 minutes.');
    }
}

<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(private User $newUser)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle inscription — ' . $this->newUser->name)
            ->greeting('Nouvelle inscription 👋')
            ->line("{$this->newUser->name} ({$this->newUser->email}) vient de créer un compte.")
            ->action('Voir les utilisateurs', route('admin.users.index'))
            ->line('Tu reçois cet email car tu es administrateur de ' . config('app.name') . '.');
    }
}

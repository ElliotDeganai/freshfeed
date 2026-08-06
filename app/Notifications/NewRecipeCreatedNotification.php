<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRecipeCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private Post $post)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $status = $this->post->status === 'published' ? 'publiée' : 'enregistrée en brouillon';

        return (new MailMessage)
            ->subject('Nouvelle recette — ' . $this->post->title)
            ->greeting('Nouvelle recette ajoutée 🍽️')
            ->line("\"{$this->post->title}\" vient d'être {$status} par {$this->post->user?->name}.")
            ->action('Voir la recette', route('posts.show', $this->post->id))
            ->line('Tu reçois cet email car tu es administrateur de ' . config('app.name') . '.');
    }
}

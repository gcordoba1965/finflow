<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $temporaryPassword) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenido a FinFlow — Tu cuenta está lista')
            ->greeting("¡Hola {$notifiable->name}!")
            ->line('Tu cuenta en **FinFlow** ha sido creada.')
            ->line("**Email:** {$notifiable->email}")
            ->line("**Contraseña temporal:** `{$this->temporaryPassword}`")
            ->action('Iniciar sesión', route('login'))
            ->line('Deberás cambiar tu contraseña y configurar MFA en tu primer acceso.');
    }
}

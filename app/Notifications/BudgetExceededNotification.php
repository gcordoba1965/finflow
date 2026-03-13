<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetExceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $category,
        private float  $limit,
        private float  $spent
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $labels  = ['needs' => 'Necesidades (50%)', 'wants' => 'Deseos (30%)', 'savings' => 'Ahorro (20%)'];
        $label   = $labels[$this->category] ?? $this->category;
        $excess  = number_format($this->spent - $this->limit, 2);
        $limitFmt = number_format($this->limit, 2);

        return (new MailMessage)
            ->subject("⚠️ Presupuesto excedido — {$label}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Has superado tu límite en **{$label}**.")
            ->line("Límite: **\${$limitFmt}** — Gastado: **\$" . number_format($this->spent, 2) . "** (exceso: \${$excess}).")
            ->action('Ver mi dashboard', route('dashboard'))
            ->line('Recuerda mantener la regla 50/30/20 para alcanzar tus metas financieras.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category,
            'limit'    => $this->limit,
            'spent'    => $this->spent,
            'excess'   => $this->spent - $this->limit,
        ];
    }
}

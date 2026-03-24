<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecoverStoreSlug extends Notification
{
    use Queueable;

    public $slug;

    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url('/loja/' . $this->slug . '/login');

        return (new MailMessage)
                    ->subject('Acesso à sua Loja - Versus TCG')
                    ->greeting('Olá, Lojista!')
                    ->line('Você (ou alguém) solicitou o link de acesso direto ao seu painel.')
                    ->line('Sua loja está registrada no endereço abaixo:')
                    ->action('Acessar Meu Painel', $url)
                    ->line('Se você não solicitou este lembrete, pode ignorar este e-mail em segurança.');
    }
}
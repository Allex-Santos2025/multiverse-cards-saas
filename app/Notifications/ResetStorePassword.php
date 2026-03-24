<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetStorePassword extends Notification
{
    use Queueable;

    public $token;
    public $slug;

    public function __construct($token, $slug)
    {
        $this->token = $token;
        $this->slug = $slug;
    }

    public function via($notifiable) { return ['mail']; }

    public function toMail($notifiable)
    {
        // O link vai levar para uma rota que ainda vamos criar (ex: /loja/{slug}/reset-password/{token})
        $url = url("/loja/{$this->slug}/nova-senha/{$this->token}?email={$notifiable->email}");

        return (new MailMessage)
            ->subject('Recuperação de Senha - Versus TCG')
            ->greeting('Olá!')
            ->line('Você está recebendo este e-mail porque solicitou a redefinição de senha da sua loja.')
            ->action('Redefinir Minha Senha', $url)
            ->line('Este link de redefinição expirará em 60 minutos.')
            ->line('Se você não solicitou isso, ignore este e-mail.');
    }
}
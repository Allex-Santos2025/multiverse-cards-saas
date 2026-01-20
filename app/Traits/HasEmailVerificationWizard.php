<?php

namespace App\Traits;

trait HasEmailVerificationWizard
{
    /**
     * Retorna o e-mail mascarado para o Wizard
     */
    public function getMaskedEmailAttribute()
    {
        $parts = explode("@", $this->email);
        $name = $parts[0];
        $domain = $parts[1];
        
        // Exibe as 4 primeiras letras e mascara o resto
        $visible = substr($name, 0, 4);
        return $visible . '*****@' . $domain;
    }

    /**
     * Retorna a URL do provedor para o botão do Wizard
     */
    public function getEmailProviderUrlAttribute()
    {
        $domain = strtolower(substr(strrchr($this->email, "@"), 1));
        
        $providers = [
            'gmail.com' => 'https://mail.google.com',
            'outlook.com' => 'https://outlook.live.com',
            'hotmail.com' => 'https://outlook.live.com',
            'yahoo.com.br' => 'https://mail.yahoo.com',
            'icloud.com' => 'https://www.icloud.com/mail',
        ];

        return $providers[$domain] ?? "https://{$domain}";
    }
}
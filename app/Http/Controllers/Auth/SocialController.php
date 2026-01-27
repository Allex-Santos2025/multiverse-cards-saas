<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Session;

class SocialController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $socialUser = Socialite::driver($provider)->user();

        // Separando nome e sobrenome
        $nameParts = explode(' ', $socialUser->getName(), 2);
        
        // Guardamos na sessão para o Livewire capturar
        Session::put('social_data', [
            'name' => $nameParts[0] ?? '',
            'surname' => $nameParts[1] ?? '',
            'email' => $socialUser->getEmail(),
            'provider' => $provider,
            'social_id' => $socialUser->getId(),
        ]);

        return redirect()->route('registro.jogador'); // Volta para o seu formulário
    }
}
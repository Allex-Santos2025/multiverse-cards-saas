<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // <--- IMPORTANTE: Adicionado para funcionar a lógica de URL

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // Suas configurações existentes (Mantidas)
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'store.onboarding' => \App\Http\Middleware\RedirectIfStoreOnboarded::class,
            'store.onboarded' => \App\Http\Middleware\RedirectIfNotStoreOnboarded::class,
        ]);

        // --- NOVA LÓGICA DE REDIRECIONAMENTO (Sessão Expirada / Logout) ---
        $middleware->redirectGuestsTo(function (Request $request) {
            
            // 1. Se for API/JSON, ignora
            if ($request->expectsJson()) {
                return null;
            }

            // 2. Se a URL acessada for de uma loja (/loja/...)
            if ($request->is('loja/*')) {
                // Tenta pegar o slug da rota ou, se falhar, pega o segundo segmento da URL
                // Ex: /loja/spellbox/dashboard -> segmento 2 é 'spellbox'
                $slug = $request->route('slug') ?? $request->segment(2);

                if ($slug) {
                    return route('loja.login', ['slug' => $slug]);
                }
            }

            // 3. Padrão: Manda para a home (fallback)
            return route('home');
        });
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
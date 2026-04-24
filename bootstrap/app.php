<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\AuthenticateStoreUserOptional;
use App\Http\Middleware\TrackStoreContext; // <-- Importação do seu novo middleware

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            TrackStoreContext::class, // <-- Middleware de contexto registado aqui
        ]);

        $middleware->alias([
            'store.onboarding' => \App\Http\Middleware\RedirectIfStoreOnboarded::class,
            'store.onboarded' => \App\Http\Middleware\RedirectIfNotStoreOnboarded::class,       
            'auth.store_optional' => AuthenticateStoreUserOptional::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            if ($request->is('loja/*')) {
                $slug = $request->route('slug') ?? $request->segment(2);
                if ($slug) {
                    return route('loja.login', ['slug' => $slug]);
                }
            }

            return route('home');
        });
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
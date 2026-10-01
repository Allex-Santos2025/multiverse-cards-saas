<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckStoreUserSessionExpired
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('store_user')->check()) {
            $user = Auth::guard('store_user')->user();

            // Se atingiu o prazo gravado no banco de dados, expulsa em definitivo
            if ($user->session_expires_at && now()->greaterThan($user->session_expires_at)) {
                
                // Desloga o lojista, invalida a sessão e regenera o token CSRF
                Auth::guard('store_user')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $slug = $request->route('slug') ?? ($user->store->url_slug ?? null);

                if ($slug) {
                    return redirect()->route('loja.login', ['slug' => $slug])
                        ->with('login_error', 'Sua sessão atingiu o prazo máximo de 30 dias. Por favor, faça login novamente.');
                }

                return redirect('/')->with('message', 'Sua sessão expirou.');
            }
        }

        return $next($request);
    }
}
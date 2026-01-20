<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfStoreOnboarded
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('store_user')->check()) {
            $storeUser = Auth::guard('store_user')->user();

            // Verifica se o lojista já tem uma loja E uma assinatura ativa
            // currentStore é um relacionamento que você deve ter no seu modelo StoreUser
            // que aponta para a loja principal do usuário.
            // Se não tiver, pode usar $storeUser->stores()->first()
            if ($storeUser->currentStore && $storeUser->currentStore->subscription_id) {
                // Se sim, redireciona para o dashboard
                return redirect()->route('dashboard.index');
            }
        }

        return $next($request);
    }
}

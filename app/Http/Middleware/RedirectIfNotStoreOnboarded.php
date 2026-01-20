<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotStoreOnboarded
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

            // Se o lojista NÃO tem uma loja OU NÃO tem uma assinatura ativa, redireciona para o onboarding
            // currentStore é um relacionamento que você deve ter no seu modelo StoreUser
            // que aponta para a loja principal do usuário.
            // Se não tiver, pode usar $storeUser->stores()->first()
            if (!$storeUser->currentStore || !$storeUser->currentStore->subscription_id) {
                // Se ele está tentando acessar o dashboard, mas não completou o onboarding, vai para seleção de planos
                if ($request->routeIs('dashboard.*')) {
                    return redirect()->route('onboarding.plans');
                }
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackStoreContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Se estiver numa loja específica
        if ($request->route('slug')) {
            session(['contexto_loja' => $request->route('slug')]);
        } 
        // 2. Se estiver na raiz ou em qualquer página do marketplace (ex: /marketplace/magic)
        // E garantimos que NÃO estamos alterando o contexto se ele já estiver dentro do /lobby
        elseif (!$request->is('lobby*') && !$request->is('loja*')) {
            session(['contexto_loja' => 'versus']);
        }

        return $next($request);
    }
}
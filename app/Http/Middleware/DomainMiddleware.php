<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;

class DomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        $mainDomain = env('APP_URL_DOMAIN', 'versustcg.com.br');

        if ($host !== $mainDomain && $host !== 'www.' . $mainDomain) {
            $store = \App\Models\Store::where('domain', $host)->where('use_custom_domain', true)->first();

            if ($store) {
                // COMPARTILHA a loja com as views
                view()->share('currentStore', $store);

                // A MÁGICA: Define o slug como padrão global para geração de URLs.
                // Isso faz o Laravel "consumir" o parâmetro sem exibi-lo como Query String (?slug=)
                URL::defaults(['slug' => $store->url_slug]);

                // Garante que o parâmetro slug esteja disponível para os Controllers
                $request->route()->setParameter('slug', $store->url_slug);

                return $next($request);
            }
            abort(404);
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HideSlugFromUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $host = $request->getHost();
        $mainDomain = env('APP_URL_DOMAIN', 'versustcg.com.br');

        // Só executa a limpeza se NÃO estivermos no domínio principal
        if ($host !== $mainDomain && $host !== 'www.' . $mainDomain) {
            
            if (method_exists($response, 'getContent')) {
                $content = $response->getContent();
                $slug = $request->route('slug');

                if ($slug) {
                    // Remove "?slug=olhodeleao" e "&slug=olhodeleao" de todos os <a> do HTML
                    $content = str_replace(['?slug=' . $slug, '&slug=' . $slug], '', $content);
                    $response->setContent($content);
                }
            }
        }

        return $response;
    }
}
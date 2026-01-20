<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Store;

class StoreResolver
{
    public function handle(Request $request, Closure $next)
    {
        // Tenta identificar a loja pelo slug na URL
        $storeSlug = $request->route('storeSlug');

        if ($storeSlug) {
            $store = Store::where('url_slug', $storeSlug)
                ->where('is_active', true)
                ->first();

            if ($store) {
                // Compartilha a loja com todo o request
                $request->attributes->set('store', $store);

                // Compartilha com o Inertia (Vue)
                inertia()->share([
                    'store' => $store,
                ]);

                return $next($request);
            }
        }

        // Se a loja não for encontrada, retorna 404
        abort(404, 'Loja não encontrada.');
    }
}

<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $store = $request->attributes->get('store');

        // Busca produtos da loja (por enquanto vazio, depois vai buscar do ItemStock)
        $products = [];

        return Inertia::render('Store/Home', [
            'store' => $store,
            'products' => $products,
        ]);
    }
}

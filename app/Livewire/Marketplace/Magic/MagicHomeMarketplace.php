<?php

namespace App\Livewire\Marketplace\Magic;

use Livewire\Component;
use App\Models\Post;

class MagicHomeMarketplace extends Component
{
    public function render()
{
    // 1. Primeiro buscamos as notícias
    $noticias = Post::orderBy('is_featured', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get();

    // 2. Agora retornamos TUDO em um único comando
    return view('livewire.marketplace.Magic.home-magic-marketplace', [
    'noticias' => $noticias
    ])->layout('layouts.app', ['title' => 'Magic: The Gathering - Versus TCG']);
    }
}
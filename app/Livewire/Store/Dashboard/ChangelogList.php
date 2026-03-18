<?php

namespace App\Livewire\Store\Dashboard;

use App\Models\Changelog;
use Livewire\Component;
use Livewire\WithPagination;

class ChangelogList extends Component
{
    use WithPagination;

    public $slug; // Usamos apenas para os links de navegação

    public function mount($slug = null)
    {
        $this->slug = $slug;
    }

    public function render()
{
    $user = auth('store_user')->user();
    $dataCadastro = $user->created_at;

    // DIAGNÓSTICO: Isso vai parar o código e mostrar as datas na tela.
    // Verifique se a 'Data da Loja' é realmente MAIOR que a 'Data do Log'.
    // dd([
    //    'Data da Loja' => $dataCadastro->format('Y-m-d H:i:s'),
    //    'Data do Primeiro Log no Banco' => Changelog::first()->published_at->format('Y-m-d H:i:s')
    // ]);

    $updates = Changelog::where('is_published', true)
        ->where('published_at', '>=', $dataCadastro)
        ->orderBy('published_at', 'desc')
        ->paginate(10);

    return view('livewire.store.dashboard.changelog-list', [
        'updates' => $updates
    ]);
}
}
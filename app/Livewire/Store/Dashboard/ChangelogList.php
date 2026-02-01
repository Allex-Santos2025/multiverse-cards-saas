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
        // Aqui NÃO filtramos por loja. Pegamos tudo que é global.
        $updates = Changelog::where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('livewire.store.dashboard.changelog-list', [
            'updates' => $updates
        ]);
    }
}
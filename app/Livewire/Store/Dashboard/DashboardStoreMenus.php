<?php

namespace App\Livewire\Store\Dashboard;

use Livewire\Component;
use App\Models\StoreGameMenu;
use App\Models\Game; 

class DashboardStoreMenus extends Component
{
    public $menus, $allGames, $showModal = false, $editingMenuId = null;

    // Propriedades do Formulário
    public $game_id, $is_active = true;
    public $name_singles = 'Cartas Avulsas', $show_singles = true;
    public $name_sealed = 'Produtos Selados', $show_sealed = true;
    public $name_accessories = 'Acessórios', $show_accessories = true;
    public $name_latest = 'Últimos Sets', $show_latest = true;
    public $name_all_sets = 'Todos os Sets', $show_all_sets = true;
    public $name_updates = 'Últimas Atualizações', $show_updates = true;

    protected $rules = [
        'game_id' => 'required|exists:games,id',
        'name_singles' => 'required|string|max:50',
        'name_sealed' => 'required|string|max:50',
        'name_accessories' => 'required|string|max:50',
        'name_latest' => 'required|string|max:50',
        'name_all_sets' => 'required|string|max:50',
        'name_updates' => 'required|string|max:50',
        'show_singles' => 'boolean', 'show_sealed' => 'boolean', 'show_accessories' => 'boolean',
        'show_latest' => 'boolean', 'show_all_sets' => 'boolean', 'show_updates' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function mount() { $this->allGames = Game::where('is_active', true)->get(); $this->loadMenus(); }

    public function loadMenus() {
        $user = auth('store_user')->user();
        if ($user && $user->current_store_id) {
            $this->menus = StoreGameMenu::where('store_id', $user->current_store_id)->with('game')->orderBy('position')->get();
        }
    }

    public function openModal() { $this->resetForm(); $this->showModal = true; }

    public function editMenu($id) {
        $menu = StoreGameMenu::findOrFail($id);
        $this->editingMenuId = $menu->id;
        $this->game_id = $menu->game_id;
        $this->name_singles = $menu->name_singles;
        $this->name_sealed = $menu->name_sealed;
        $this->name_accessories = $menu->name_accessories;
        $this->name_latest = $menu->name_latest;
        $this->name_all_sets = $menu->name_all_sets;
        $this->name_updates = $menu->name_updates ?? 'Últimas Atualizações';
        $this->show_singles = $menu->show_singles;
        $this->show_sealed = $menu->show_sealed;
        $this->show_accessories = $menu->show_accessories;
        $this->show_latest = $menu->show_latest;
        $this->show_all_sets = $menu->show_all_sets;
        $this->show_updates = $menu->show_updates;
        $this->is_active = $menu->is_active;
        $this->showModal = true;
    }

    public function save() {
        $this->validate();
        StoreGameMenu::updateOrCreate(['id' => $this->editingMenuId], [
            'store_id' => auth('store_user')->user()->current_store_id,
            'game_id' => $this->game_id,
            'name_singles' => $this->name_singles,
            'name_sealed' => $this->name_sealed,
            'name_accessories' => $this->name_accessories,
            'name_latest' => $this->name_latest,
            'name_all_sets' => $this->name_all_sets,
            'name_updates' => $this->name_updates,
            'show_singles' => $this->show_singles,
            'show_sealed' => $this->show_sealed,
            'show_accessories' => $this->show_accessories,
            'show_latest' => $this->show_latest,
            'show_all_sets' => $this->show_all_sets,
            'show_updates' => $this->show_updates,
            'is_active' => $this->is_active,
        ]);
        $this->showModal = false;
        $this->loadMenus();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Salvo com sucesso!']);
    }

    public function resetForm() {
        $this->reset(['editingMenuId', 'game_id', 'name_singles', 'name_sealed', 'name_accessories', 'name_latest', 'name_all_sets', 'name_updates']);
    }

    public function render() { return view('livewire.store.dashboard.dashboard-store-menus')->extends('layouts.dashboard')->section('content'); }
}
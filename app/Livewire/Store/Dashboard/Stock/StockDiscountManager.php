<?php

namespace App\Livewire\Store\Dashboard\Stock;

use App\Models\StockItem;
use App\Models\Game;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockDiscountManager extends Component
{
    use WithPagination;

    public $gameSlug;
    public $search = '';

    public function mount($gameSlug)
    {
        $this->gameSlug = $gameSlug;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function saveDiscounts($formData)
    {
        if (!isset($formData['items']) || empty($formData['items'])) {
            $this->dispatch('notify', type: 'error', message: 'Nenhum item para atualizar.');
            return;
        }

        DB::beginTransaction();
        try {
            $updatedCount = 0;

            foreach ($formData['items'] as $id => $data) {
                $percent = isset($data['percent']) && is_numeric($data['percent']) ? (float) $data['percent'] : 0;
                $start = !empty($data['start']) ? $data['start'] : null;
                $end = !empty($data['end']) ? $data['end'] : null;

                $item = StockItem::find($id);

                if ($item) {
                    $item->discount_percent = $percent;
                    $item->discount_start = $start;
                    $item->discount_end = $end;
                    $item->is_promotion = $percent > 0;
                    
                    if ($item->isDirty()) {
                        $item->save();
                        $updatedCount++;
                    }
                }
            }

            DB::commit();

            if ($updatedCount > 0) {
                $this->dispatch('notify', type: 'success', message: "Promoções atualizadas em {$updatedCount} itens!");
            } else {
                $this->dispatch('notify', type: 'info', message: 'Nenhuma alteração detectada.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar descontos em lote: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Erro ao processar as promoções.');
        }
    }

    public function render()
    {
        // Pega o ID da loja igualzinho aos seus outros arquivos
        $storeId = auth('store_user')->user()->current_store_id ?? auth('store_user')->user()->store_id;
        
        // Pega o ID do jogo a partir do slug
        $gameId = Game::where('url_slug', $this->gameSlug)->value('id');

        $items = StockItem::with(['catalogPrint.concept', 'catalogPrint.set', 'catalogProduct'])
            ->withoutGlobalScopes()
            ->where('store_id', $storeId)
            
            // FILTRO DE JOGO: Junta Cartas do Jogo + Produtos do Jogo (ou Universais)
            ->where(function ($query) use ($gameId) {
                // Se for carta, checa pelo SET
                $query->whereHas('catalogPrint.set', function ($q) use ($gameId) {
                    $q->where('game_id', $gameId);
                })
                // Se for produto, checa direto na tabela do produto
                ->orWhereHas('catalogProduct', function ($q) use ($gameId) {
                    $q->where('game_id', $gameId)->orWhereNull('game_id');
                });
            })
            
            // FILTRO DE BUSCA: Por nome da carta ou do produto
            ->when($this->search, function ($query) {
                $term = $this->search;
                $query->where(function($subQuery) use ($term) {
                    // Busca nome em Cartas
                    $subQuery->whereHas('catalogPrint', function ($q) use ($term) {
                        $q->where('printed_name', 'like', "%{$term}%")
                          ->orWhereHas('concept', function ($c) use ($term) {
                              $c->where('name', 'like', "%{$term}%");
                          });
                    })
                    // Busca nome em Produtos
                    ->orWhereHas('catalogProduct', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%");
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('livewire.store.dashboard.stock.stock-discount-manager', [
            'items' => $items
        ]);
    }
}
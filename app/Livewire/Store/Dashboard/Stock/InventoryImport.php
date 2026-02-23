<?php 

namespace App\Livewire\Store\Dashboard\Stock;

use Livewire\Component;
use App\Models\StockItem; 
use App\Models\Print; 
use Illuminate\Support\Facades\DB;

class InventoryImporter extends Component
{
    public $importText = '';
    public $importErrors = [];
    public $limitToFour = 1; 
    public $selectedExtras = []; 

    // Regex Universal: Aceita códigos de edição de 2 a 5 caracteres
    protected $pattern = '/^(\d+)\s+(.+?)\s+\[([A-Z0-9]{2,5})\]\s+(NM|SP|MP|HP|D)\s+(PT|EN|JP|ES|IT)\s+([\d\.]+)$/i';

    public function processImport()
    {
        $this->importErrors = [];
        $lines = explode("\n", str_replace("\r", "", trim($this->importText)));
        
        if (empty($lines[0])) {
            $this->importErrors[] = "A caixa de texto está vazia.";
            return;
        }

        $validData = [];
        $storeId = auth('store_user')->user()->current_store_id;

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match($this->pattern, $line, $matches)) {
                $qtd = (int)$matches[1];
                if ($this->limitToFour == 1 && $qtd > 4) $qtd = 4;

                $validData[] = [
                    'line_number' => $index + 1,
                    'quantity'    => $qtd,
                    'name'        => trim($matches[2]),
                    'edition'     => strtoupper($matches[3]),
                    'condition'   => strtoupper($matches[4]),
                    'language'    => strtoupper($matches[5]),
                    'price'       => (float)$matches[6],
                ];
            } else {
                $this->importErrors[] = "Linha " . ($index + 1) . ": Formato inválido.";
            }
        }

        if (!empty($this->importErrors)) return;

        DB::beginTransaction();
        try {
            foreach ($validData as $item) {
                // Busca no catálogo central (ajuste os nomes das colunas se necessário)
                $print = \App\Models\Print::whereHas('set', function($q) use ($item) {
                    $q->where('code', $item['edition']);
                })
                ->where(function($q) use ($item) {
                    $q->where('printed_name', $item['name'])
                      ->orWhereHas('concept', function($sub) use ($item) {
                          $sub->where('name', $item['name']);
                      });
                })->first();

                if (!$print) {
                    $this->importErrors[] = "Linha {$item['line_number']}: Carta '{$item['name']}' [{$item['edition']}] não encontrada.";
                    continue;
                }

                // Salva ou atualiza somando a quantidade
                \App\Models\StockItem::updateOrCreate(
                    [
                        'store_id'  => $storeId,
                        'print_id'  => $print->id,
                        'condition' => $item['condition'],
                        'language'  => $item['language'],
                        'extras'    => $this->selectedExtras 
                    ],
                    [
                        'quantity' => DB::raw("quantity + {$item['quantity']}"),
                        'price'    => $item['price']
                    ]
                );
            }

            if (!empty($this->importErrors)) {
                DB::rollBack();
                return;
            }

            DB::commit();

            $this->reset(['importText', 'selectedExtras']);
            
            // Feedback e navegação
            $this->dispatch('inventory-updated'); 
            $this->dispatch('mudar-aba', 'lista'); 
            
            // Opcional: Uma notificação visual (se você tiver o pacote de Toasts)
            // session()->flash('message', 'Importação concluída com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->importErrors[] = "Erro crítico: " . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.store.dashboard.stock.inventory-import');
    }
}
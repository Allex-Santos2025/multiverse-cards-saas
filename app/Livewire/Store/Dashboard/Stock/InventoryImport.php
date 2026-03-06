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

    // Regex Universal: Aceita códigos de edição de 2 a 5 caracteres e Extras
    protected $pattern = '/^(?<qtd>\d+)\s+(?<name>.+?)\s+\[(?<set>[A-Z0-9]{2,5})\]\s+(?<cond>M|NM|SP|MP|HP|D)\s+(?<lang>[A-Z]{2,3})(?:\s+\((?<extras>[^\)]+)\))?(?:\s+(?<price>[\d\.,]+))?\s*$/iu';

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

            // --- COLOQUE A ARMADILHA AQUI ---
            dd(
                'Texto exato que o Laravel leu:', 
                $line, 
                'Tamanho (caracteres):', 
                strlen($line),
                'Testando Regex:', 
                preg_match($this->pattern, $line, $matches), 
                'O que ele conseguiu capturar:', 
                $matches
            );
            // --------------------------------

            if (preg_match($this->pattern, $line, $matches)) {
                $qtd = (int)$matches['qtd']; // Puxando pelo nome correto da Regex
                if ($this->limitToFour == 1 && $qtd > 4) $qtd = 4;

                // Lê os extras digitados na linha e junta com os marcados nos botões
                $lineExtras = [];
                if (!empty($matches['extras'])) {
                    $lineExtras = array_map('trim', explode(',', strtolower($matches['extras'])));
                }
                $finalExtras = array_values(array_unique(array_merge($this->selectedExtras, $lineExtras)));
                sort($finalExtras);

                $validData[] = [
                    'line_number' => $index + 1,
                    'quantity'    => $qtd,
                    'name'        => trim($matches['name']),
                    'edition'     => strtoupper($matches['set']),
                    'condition'   => strtoupper($matches['cond']),
                    'language'    => strtoupper($matches['lang']),
                    'extras'      => $finalExtras,
                    'price'       => !empty($matches['price']) ? (float)$matches['price'] : 0,
                ];
            } else {
                $this->importErrors[] = "Linha " . ($index + 1) . ": Formato inválido.Texto lido: [" . $line . "]";
                
            }
        }

        if (!empty($this->importErrors)) return;

        DB::beginTransaction();
        try {
            foreach ($validData as $item) {
                // Busca no catálogo central
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

                // Busca as cartas dessa loja, edição, condição e idioma (sem bater o extras no MySQL)
                $existingItems = \App\Models\StockItem::where('store_id', $storeId)
                    ->where('print_id', $print->id)
                    ->where('condition', $item['condition'])
                    ->where('language', $item['language'])
                    ->get();

                // Filtra os extras direto no PHP, blindando o erro de banco!
                $existing = $existingItems->first(function($si) use ($item) {
                    return $si->extras === $item['extras'];
                });

                if ($existing) {
                    // Soma manual e segura, sem DB::raw
                    $existing->quantity = $existing->quantity + $item['quantity'];
                    if ($this->limitToFour == 1 && $existing->quantity > 4) $existing->quantity = 4;
                    // Se a linha tiver preço, atualiza. Se não tiver, mantém o que já tava no banco.
                    $existing->price = $item['price'] > 0 ? $item['price'] : $existing->price;
                    $existing->save();
                } else {
                    // Inserção de carta nova sem DB::raw para não dar "Expression"
                    \App\Models\StockItem::create([
                        'store_id'  => $storeId,
                        'print_id'  => $print->id,
                        'condition' => $item['condition'],
                        'language'  => $item['language'],
                        'extras'    => $item['extras'],
                        'quantity'  => $item['quantity'],
                        'price'     => $item['price']
                    ]);
                }
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

        } catch (\Exception $e) {
            DB::rollBack();
            $this->importErrors[] = "Erro crítico: " . $e->getMessage();
        }
    }

    public function render()
    {
        dd('BINGO! ELE ESTÁ LENDO O ARQUIVO CERTO!'); // <--- COLOQUE ISSO
        return view('livewire.store.dashboard.stock.inventory-import');
    }
}
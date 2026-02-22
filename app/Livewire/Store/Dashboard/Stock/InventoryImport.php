<?php 

namespace App\Livewire\Store\Dashboard\Stock;

use Livewire\Component;
use App\Models\Stock; // Ou seu modelo de estoque

class InventoryImporter extends Component
{
    public $importText = '';
    public $importErrors = [];
    public $limitToFour = true; // Radio button

    // Padrão rigoroso: [QTD] [NOME] [[SIGLA]] [QUALIDADE] [IDIOMA] [PREÇO]
    protected $pattern = '/^(\d+)\s+(.+?)\s+\[([A-Z0-9]{3,})\]\s+(NM|SP|MP|HP|D)\s+(PT|EN|JP|ES|IT)\s+([\d\.]+)$/i';

    public function processImport()
    {
        $this->importErrors = [];
        $lines = explode("\n", str_replace("\r", "", trim($this->importText)));
        
        if (empty($lines[0])) {
            $this->importErrors[] = "A lista está vazia.";
            return;
        }

        $validData = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match($this->pattern, $line, $matches)) {
                $qtd = (int)$matches[1];
                
                // Aplica a regra de limite se estiver marcado
                if ($this->limitToFour && $qtd > 4) {
                    $qtd = 4;
                }

                $validData[] = [
                    'quantity' => $qtd,
                    'name'     => $matches[2],
                    'edition'  => strtoupper($matches[3]),
                    'condition'=> strtoupper($matches[4]),
                    'language' => strtoupper($matches[5]),
                    'price'    => (float)$matches[6],
                ];
            } else {
                $this->importErrors[] = "Linha " . ($index + 1) . ": Formato inválido.";
            }
        }

        if (!empty($this->importErrors)) return;

        // Persistência no Banco
        foreach ($validData as $item) {
            // Aqui usamos updateOrCreate para não duplicar linhas iguais
            // mas sim somar ou atualizar o preço/estoque
            Stock::updateOrCreate(
                [
                    'name' => $item['name'],
                    'edition_code' => $item['edition'],
                    'condition' => $item['condition'],
                    'language' => $item['language'],
                ],
                [
                    'quantity' => \DB::raw("quantity + {$item['quantity']}"),
                    'price' => $item['price']
                ]
            );
        }

        $this->reset('importText');
        
        // Notifica o sistema para atualizar a tabela e fechar a aba
        $this->dispatch('inventory-updated'); 
        $this->dispatch('mudar-aba', 'lista'); 
    }

    public function render()
    {
        return view('livewire.store.dashboard.stock.inventory-import');
    }
}
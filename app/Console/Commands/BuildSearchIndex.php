<?php

namespace App\Console\Commands;

use App\Models\CardFunctionality;
use App\Models\Card; // <--- ADICIONE ESTE "use"
use Illuminate\Console\Command;

class BuildSearchIndex extends Command
{
    protected $signature = 'scryfall:build-search-index';
    protected $description = 'Populates the searchable_names column for all card functionalities.';

    public function handle()
    {
        $this->info('Iniciando construção do índice de nomes pesquisáveis...');

        // 1. Removemos o ->with('cards') daqui.
        CardFunctionality::chunkById(200, function ($functionalities) {

            foreach ($functionalities as $functionality) {
                
                // 2. EM VEZ de usar a relação, fazemos a query direta (igual a sua View faz)
                $printedNames = Card::where('card_functionality_id', $functionality->id)
                                    ->pluck('printed_name');

                // 3. O resto da lógica continua igual
                $printedNames->push($functionality->name);

                $searchableString = $printedNames
                                    ->filter()
                                    ->unique()
                                    ->implode(' / '); 

                $functionality->searchable_names = $searchableString;
                $functionality->saveQuietly(); 
            }

            $this->output->write('.'); 
        });

        $this->info("\nÍndice de busca construído com sucesso!");
        return self::SUCCESS;
    }
}
<?php

namespace App\Console\Commands;

use App\Models\Set;
use App\Models\Catalog\CatalogPrint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PrepareBaseSlots extends Command
{
    protected $signature = 'pokemon:prepare-slots 
                            {set_code=BASE1 : Código do set} 
                            {--locale=es : Idioma alvo}';

    protected $description = 'Cria a estrutura de pastas e gera um relatório/guia com os nomes exatos dos arquivos para o upload manual.';

    public function handle(): int
    {
        $inputCode = $this->argument('set_code');
        $locale = $this->option('locale');

        $set = Set::where('game_id', 2)
            ->where(function ($q) use ($inputCode) {
                $q->where('code', strtoupper($inputCode))
                  ->orWhere('api_id', strtolower($inputCode));
            })->first();

        if (!$set) {
            $this->error("Set [{$inputCode}] não encontrado.");
            return self::FAILURE;
        }

        $setCode = strtoupper($set->code);
        $directory = public_path("card_images/Pokemon/{$setCode}/{$locale}");
        
        // Garante que a pasta do idioma existe
        File::ensureDirectoryExists($directory);

        $prints = CatalogPrint::where('set_id', $set->id)
            ->where('language_code', 'en')
            ->orderBy('collector_number')
            ->get();

        $this->info("=== ESTRUTURA PREPARADA PARA [{$setCode}] ({$locale}) ===");
        $this->info("Pasta criada/verificada: {$directory}");
        $this->info("Total de cartas esperadas: " . $prints->count());

        $checklistPath = storage_path("app/checklist_{$setCode}_{$locale}.txt");
        $content = "CHECKLIST DE IMAGENS - SET: {$setCode} | IDIOMA: {$locale}\n";
        $content .= "Salve os arquivos no formato webp ou jpg/png dentro de: public/card_images/Pokemon/{$setCode}/{locale}/\n\n";

        foreach ($prints as $print) {
            $number = $print->collector_number;
            $name = $print->printed_name;
            $fileName = strtolower("{$setCode}-{$number}-{$locale}.webp");
            
            $exists = File::exists("{$directory}/{$fileName}") ? "[PRESENTE]" : "[PENDENTE]";
            $content .= "{$number} - {$name} -> Nome do arquivo: {$fileName} {$exists}\n";
        }

        File::put($checklistPath, $content);
        $this->info("Checklist gerado em: {$checklistPath}");
        $this->info("Basta salvar as imagens na pasta com os nomes listados e o sistema fará a leitura automática!");

        return self::SUCCESS;
    }
}

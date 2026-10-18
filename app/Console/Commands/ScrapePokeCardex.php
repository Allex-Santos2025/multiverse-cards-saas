<?php

namespace App\Console\Commands;

use App\Models\Set;
use App\Models\Catalog\CatalogPrint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ScrapePokeCardex extends Command
{
    protected $signature = 'pokemon:scrape-pokecardex
                            {set_code=BASE1 : Código do set a ser raspado (ex: BASE1)}
                            {--locales=pt,es,de,fr,it : Idiomas alvo para busca externa}';

    protected $description = 'Varre o PokeCardex e fontes oficiais alternativas para preencher imagens e dados ausentes em sets clássicos de Pokémon.';

    public function handle(): int
    {
        $inputCode = $this->argument('set_code');
        $locales = array_filter(array_map('trim', explode(',', (string)$this->option('locales'))));

        $set = Set::where('game_id', 2)
            ->where(function ($q) use ($inputCode) {
                $q->where('code', strtoupper($inputCode))
                  ->orWhere('api_id', strtolower($inputCode));
            })->first();

        if (!$set) {
            $this->error("Set [{$inputCode}] não encontrado para Pokémon.");
            return self::FAILURE;
        }

        $setCode = strtoupper($set->code);
        $this->info("=== INICIANDO SCRAPING POKECARDEX: [{$setCode}] {$set->name} ===");

        // Mapeia os prints canônicos em inglês para saber o total de cartas (ex: 102 no Base Set)
        $enPrints = CatalogPrint::where('set_id', $set->id)
            ->where('language_code', 'en')
            ->orderBy('collector_number')
            ->get();

        if ($enPrints->isEmpty()) {
            $this->error("Nenhum print canônico [en] encontrado para o set {$setCode}.");
            return self::FAILURE;
        }

        $this->info("Total de cartas na matriz: " . $enPrints->count());

        foreach ($locales as $locale) {
            $this->line("\n-> Processando buscas externas para o idioma: [{$locale}]");
            $successCount = 0;

            foreach ($enPrints as $enPrint) {
                $number = $enPrint->collector_number;
                
                $print = CatalogPrint::where('set_id', $set->id)
                    ->where('collector_number', $number)
                    ->where('language_code', $locale)
                    ->first();

                if (!$print) {
                    continue;
                }

                // Caminho de destino unificado para a imagem do idioma
                $fileName = Str::slug(strtolower("{$setCode}-{$number}-{$locale}")) . ".webp";
                $targetRelPath = "card_images/Pokemon/{$setCode}/{$locale}/{$fileName}";
                $targetFullPath = public_path($targetRelPath);

                // Se o arquivo já existe e é válido, pula o download
                if (File::exists($targetFullPath) && File::size($targetFullPath) > 0) {
                    continue;
                }

                // Padrões de URL conhecidos do PokeCardex e CDNs regionais para o Base Set (Série BS)
                // Exemplo estruturado por idioma no PokeCardex
                $candidateUrls = [
                    "https://www.pokecardex.com/assets/images/sets/{$locale}/BS/{$number}.jpg",
                    "https://www.pokecardex.com/assets/images/sets_en/BS/HD/{$number}.jpg",
                    "https://www.pokecardex.com/assets/images/sets_{$locale}/BS/HD/{$number}.jpg",
                ];

                $downloaded = false;
                foreach ($candidateUrls as $url) {
                    if ($this->tryDownloadAndConvertToWebp($url, $targetFullPath)) {
                        $print->update(['image_path' => $targetRelPath]);
                        $successCount++;
                        $downloaded = true;
                        break;
                    }
                    usleep(100000); // Respeita rate limit de 100ms entre tentativas
                }

                if ($downloaded) {
                    $this->output->write("\r   [{$locale}] Carta #{$number}: Baixada com sucesso.");
                }
            }

            $this->info("\n   [{$locale}] Total de novas imagens recuperadas via PokeCardex: {$successCount}");
        }

        $this->info("\n=== SCRAPING CONCLUÍDO ===");
        return self::SUCCESS;
    }

    protected function tryDownloadAndConvertToWebp(string $url, string $fullPath): bool
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept'     => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ])->timeout(15)->get($url);

            if ($response->successful() && strlen($response->body()) > 2000) {
                File::ensureDirectoryExists(dirname($fullPath));
                
                // Salva temporariamente e converte/move
                $tempPath = $fullPath . '.tmp';
                File::put($tempPath, $response->body());

                if (File::exists($tempPath) && File::size($tempPath) > 2000) {
                    // Tenta otimizar/converter para Webp usando GD se disponível, ou move direto
                    if (function_exists('imagewebp')) {
                        $info = @getimagesize($tempPath);
                        if ($info) {
                            $img = match ($info[2]) {
                                IMAGEMAGICK => @imagecreatefromstring(File::get($tempPath)),
                                IMAGETYPE_JPEG => @imagecreatefromjpeg($tempPath),
                                IMAGETYPE_PNG => @imagecreatefrompng($tempPath),
                                IMAGETYPE_WEBP => @imagecreatefromwebp($tempPath),
                                default => null,
                            };
                            if ($img) {
                                @imagewebp($img, $fullPath, 85);
                                @imagedestroy($img);
                                @unlink($tempPath);
                                if (File::exists($fullPath) && File::size($fullPath) > 0) {
                                    return true;
                                }
                            }
                        }
                    }
                    // Fallback: move o arquivo baixado diretamente
                    File::move($tempPath, $fullPath);
                    return true;
                }
                if (File::exists($tempPath)) {
                    @unlink($tempPath);
                }
            }
        } catch (\Throwable $e) {
            // Ignora falhas de requisição individuais para continuar a varredura
        }

        return false;
    }
}

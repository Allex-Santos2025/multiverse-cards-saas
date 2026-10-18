<?php

namespace App\Console\Commands;

use App\Models\Set;
use App\Models\Catalog\CatalogPrint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ScrapeWikiDexBaseSet extends Command
{
    protected $signature = 'pokemon:scrape-wikidex-base
                            {--locale=es : Idioma alvo no WikiDex (ex: es)}';

    protected $description = 'Raspa imagens em alta resolução do WikiDex para o Base Set via páginas individuais.';

    public function handle(): int
    {
        $locale = $this->option('locale');
        
        $set = Set::where('game_id', 2)
            ->where(function ($q) {
                $q->where('code', 'BASE1')
                  ->orWhere('api_id', 'base1');
            })->first();

        if (!$set) {
            $this->error("Base Set (BASE1) não encontrado.");
            return self::FAILURE;
        }

        $setCode = strtoupper($set->code);
        $this->info("=== INICIANDO SCRAPING WIKIDEX (Páginas Individuais) PARA [{$setCode}] (Idioma: {$locale}) ===");

        $enPrints = CatalogPrint::where('set_id', $set->id)
            ->where('language_code', 'en')
            ->orderBy('collector_number')
            ->get();

        if ($enPrints->isEmpty()) {
            $this->error("Nenhum print canônico [en] encontrado.");
            return self::FAILURE;
        }

        $successCount = 0;
        $missingCount = 0;

        foreach ($enPrints as $enPrint) {
            $number = $enPrint->collector_number;
            $cardNameEn = $enPrint->printed_name;

            $print = CatalogPrint::where('set_id', $set->id)
                ->where('collector_number', $number)
                ->where('language_code', $locale)
                ->first();

            if (!$print) {
                continue;
            }

            // Padrão rigoroso do seu sistema: base1-1-es.webp
            $fileName = strtolower("base1-{$number}-{$locale}.webp");
            $targetRelPath = "card_images/Pokemon/{$setCode}/{$locale}/{$fileName}";
            $targetFullPath = public_path($targetRelPath);

            if (File::exists($targetFullPath) && File::size($targetFullPath) > 2000) {
                $print->update(['image_path' => $targetRelPath]);
                $successCount++;
                continue;
            }

            $originalUrl = $this->fetchCardPageImage($cardNameEn);
            
            if ($originalUrl && $this->downloadAndSaveImage($originalUrl, $targetFullPath)) {
                $print->update(['image_path' => $targetRelPath]);
                $successCount++;
                $this->line("   [OK] Carta #{$number} ({$cardNameEn}) baixada com sucesso.");
            } else {
                $missingCount++;
                $this->warn("   [AVISO] Não encontrada para a carta #{$number} ({$cardNameEn}).");
            }

            usleep(200000); // 200ms de pausa entre requisições para evitar bloqueio
        }

        $this->info("\n=== SCRAPING CONCLUÍDO ===");
        $this->info("Imagens salvas/verificadas: {$successCount}");
        $this->info("Não localizadas: {$missingCount}");

        return self::SUCCESS;
    }

    protected function fetchCardPageImage(string $cardNameEn): ?string
    {
        try {
            $pageUrl = "https://www.wikidex.net/wiki/" . urlencode("{$cardNameEn} (Base Set TCG)");
            
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
            ])->timeout(15)->get($pageUrl);

            if ($response->successful()) {
                $html = $response->body();
                // Procura por links de imagem na CDN do WikiDex
                preg_match('/src="(https:\/\/images\.wikidexcdn\.net\/mwuploads\/wikidex\/[^"]+\.(?:png|jpg|webp))"/i', $html, $m);
                if (!empty($m[1])) {
                    return $this->convertToOriginalUrl($m[1]);
                }
            }
        } catch (\Throwable $e) {}
        return null;
    }

    protected function convertToOriginalUrl(string $thumbUrl): string
    {
        if (str_contains($thumbUrl, '/thumb/')) {
            $clean = preg_replace('/\/thumb\//', '/', $thumbUrl);
            $clean = preg_replace('/\/[0-9]+px-[^\/]+$/', '', $clean);
            return $clean;
        }
        return $thumbUrl;
    }

    protected function downloadAndSaveImage(string $url, string $fullPath): bool
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            ])->timeout(15)->get($url);

            if ($response->successful() && strlen($response->body()) > 2000) {
                File::ensureDirectoryExists(dirname($fullPath));
                File::put($fullPath, $response->body());
                return File::exists($fullPath) && File::size($fullPath) > 2000;
            }
        } catch (\Throwable $e) {}
        return false;
    }
}

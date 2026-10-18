<?php

namespace App\Console\Commands;

use App\Models\Catalog\CatalogPrint;
use App\Models\Game;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncYugiohScans extends Command
{
    protected $signature = 'yugioh:sync-scans
                            {--set-code= : Código da coleção específica para sincronizar (ex: SDJ, MRD, LOB)}
                            {--force : Força o download e substitui todos os arquivos físicos existentes}
                            {--limit= : Limita a quantidade de prints processados}';

    protected $description = 'Sincroniza scans físicos (1st Edition e Unlimited) da Yugipedia para Yu-Gi-Oh!, substituindo imagens antigas.';

    protected ?Game $game = null;
    protected string $wikiApiUrl = 'https://yugipedia.com/api.php';

    protected array $langMap = [
        'en' => ['EN', 'NA', 'EU', 'E'],
        'pt' => ['PT'],
        'fr' => ['FR', 'F'],
        'de' => ['DE', 'G'],
        'it' => ['IT', 'I'],
        'es' => ['SP', 'S'],
    ];

    public function handle(): int
    {
        $this->game = Game::where('id', 3)->orWhere('name', 'like', '%Yu-Gi-Oh%')->first();

        if (!$this->game) {
            $this->error("Jogo Yu-Gi-Oh! não encontrado na tabela 'games'.");
            return self::FAILURE;
        }

        $query = CatalogPrint::whereHas('concept', function ($q) {
            $q->where('game_id', $this->game->id);
        })->with(['concept', 'set']);

        if ($setCode = $this->option('set-code')) {
            $cleanCode = strtoupper(trim($setCode));
            $query->whereHas('set', function ($q) use ($cleanCode) {
                $q->where('code', $cleanCode)
                  ->orWhere('code', 'like', "{$cleanCode}-%");
            });
            $this->info("Filtrando coleção: [{$cleanCode}].");
        }

        if ($limit = $this->option('limit')) {
            $query->limit((int)$limit);
        }

        $prints = $query->get();

        if ($prints->isEmpty()) {
            $this->warn("Nenhum print de Yu-Gi-Oh! encontrado com os filtros informados.");
            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');
        $this->info("Iniciando sincronização de " . $prints->count() . " print(s)... (Modo Force: " . ($force ? 'SIM' : 'NÃO') . ")");

        $updatedCount = 0;
        $failedCount = 0;

        foreach ($prints as $print) {
            $conceptName = $print->concept?->name ?? '';
            $setCode = $print->set?->code ?? '';
            $collectorNumber = $print->collector_number ?? '';
            $langCode = strtolower($print->language_code ?? 'en');

            if (empty($conceptName) || empty($setCode)) {
                continue;
            }

            $baseSetCode = explode('-', $setCode)[0];
            $cleanConcept = preg_replace('/[^A-Za-z0-9]/', '', $conceptName);
            $safeCollectorNum = Str::slug($collectorNumber ?: 'card');

            $dirPath = public_path("card_images/Yugioh/{$setCode}/{$langCode}");
            File::ensureDirectoryExists($dirPath);

            $standardFile = "{$dirPath}/{$setCode}_{$langCode}_{$safeCollectorNum}.jpg";
            $relativePath = "card_images/Yugioh/{$setCode}/{$langCode}/{$setCode}_{$langCode}_{$safeCollectorNum}.jpg";

            // Se o arquivo já existe e NÃO foi passado --force, apenas assegura o caminho no banco e pula
            if (File::exists($standardFile) && !$force) {
                if ($print->image_path !== $relativePath) {
                    $print->update(['image_path' => $relativePath]);
                }
                continue;
            }

            // Busca os scans reais na Yugipedia
            $scans = $this->fetchAvailableScans($cleanConcept, $baseSetCode, $langCode);

            if (empty($scans)) {
                $this->output->writeln("   <fg=yellow>[SEM SCAN]</> {$conceptName} ({$collectorNumber})");
                $failedCount++;
                continue;
            }

            $primaryScanUrl = null;

            // 1. Baixa 1ª Edição se houver
            if (isset($scans['1e'])) {
                $fullPath1e = "{$dirPath}/{$setCode}_{$langCode}_{$safeCollectorNum}_1e.jpg";
                if (!File::exists($fullPath1e) || $force) {
                    $this->downloadAndSaveImage($scans['1e'], $fullPath1e);
                }
                $primaryScanUrl = $scans['1e'];
            }

            // 2. Baixa Unlimited se houver
            if (isset($scans['ue'])) {
                $fullPathUe = "{$dirPath}/{$setCode}_{$langCode}_{$safeCollectorNum}_ue.jpg";
                if (!File::exists($fullPathUe) || $force) {
                    $this->downloadAndSaveImage($scans['ue'], $fullPathUe);
                }
                if (!$primaryScanUrl) {
                    $primaryScanUrl = $scans['ue'];
                }
            }

            // 3. Fallback para scan padrão se não teve nem 1e nem ue explícitos
            if (!$primaryScanUrl && isset($scans['default'])) {
                $primaryScanUrl = $scans['default'];
            }

            // 4. Grava a imagem canônica (substituindo a antiga obrigatoriamente se for force)
            if ($primaryScanUrl) {
                $this->downloadAndSaveImage($primaryScanUrl, $standardFile);
                $print->update(['image_path' => $relativePath]);
                $updatedCount++;
                $this->output->writeln("   <fg=green>[SUBSTITUÍDO]</> {$conceptName} ({$collectorNumber})");
            }

            usleep(120000); // Cortesia para a API
        }

        $this->newLine();
        $this->info("Concluído! Prints reais sobrescritos e atualizados: {$updatedCount}");
        return self::SUCCESS;
    }

    protected function fetchAvailableScans(string $cleanConcept, string $baseSetCode, string $langCode): array
    {
        $prefix = "{$cleanConcept}-{$baseSetCode}";
        $params = [
            'action'   => 'query',
            'list'     => 'allimages',
            'aiprefix' => $prefix,
            'ailimit'  => 50,
            'aiprop'   => 'url',
            'format'   => 'json',
        ];

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'VersusTCG-Sync/1.0 (contato@versustcg.com.br)'
            ])->timeout(20)->get($this->wikiApiUrl, $params);

            if (!$response->successful()) {
                return [];
            }

            $candidates = $response->json()['query']['allimages'] ?? [];
            if (empty($candidates)) {
                return [];
            }

            $allowedTags = $this->langMap[$langCode] ?? ['EN'];

            // Filtro pelo idioma alvo
            $matches = array_filter($candidates, function ($img) use ($allowedTags) {
                $name = strtoupper($img['name']);
                foreach ($allowedTags as $tag) {
                    if (str_contains($name, "-{$tag}-")) return true;
                }
                return false;
            });

            // Fallback para EN se não houver no idioma específico
            if (empty($matches) && $langCode !== 'en') {
                $matches = array_filter($candidates, function ($img) {
                    $name = strtoupper($img['name']);
                    return str_contains($name, "-EN-") || str_contains($name, "-NA-") || str_contains($name, "-EU-") || str_contains($name, "-E-");
                });
            }

            if (empty($matches)) {
                $matches = $candidates;
            }

            $result = [];
            foreach ($matches as $img) {
                $name = strtoupper($img['name']);
                if (str_contains($name, '1E') && !isset($result['1e'])) {
                    $result['1e'] = $img['url'];
                } elseif (str_contains($name, 'UE') && !isset($result['ue'])) {
                    $result['ue'] = $img['url'];
                } elseif (!isset($result['default'])) {
                    $result['default'] = $img['url'];
                }
            }

            return $result;
        } catch (\Throwable $e) {
            Log::channel('ingest')->warning("Erro Yugipedia: " . $e->getMessage());
            return [];
        }
    }

    protected function downloadAndSaveImage(string $url, string $fullPath): bool
    {
        try {
            $res = Http::withHeaders([
                'User-Agent' => 'VersusTCG-Sync/1.0 (contato@versustcg.com.br)'
            ])->timeout(30)->get($url);

            if ($res->successful()) {
                File::put($fullPath, $res->body());
                return true;
            }
        } catch (\Throwable $e) {
            Log::channel('ingest')->error("Erro download scan: " . $e->getMessage());
        }
        return false;
    }
}

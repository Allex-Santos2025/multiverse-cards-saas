import re

# 1. FIX NO GLOBAL SEARCH (Imagens do estoque + Artistas indevidos)
path_gs = "app/Livewire/GlobalSearch.php"
with open(path_gs, "r", encoding="utf-8") as f:
    gs = f.read()

# Bloco exato do estoque para reconstruir sem erro de imagem ou de artista
old_stock_pattern = r"(\$concept\s*=\s*\$firstPrint->concept;)(.*?)(return \[\s*'status'\s*=>\s*'available',)"
replacement_stock = r'''\1
                    $presenter = \App\Services\GamePresenters\GamePresenterFactory::make($concept->game_id ?? 1);

                    $printPt = $printsCol->first(fn($p) =>
                        in_array(strtolower($p->language_code), ['pt', 'pt-br', 'pt_br']) &&
                        !empty(trim($p->printed_name ?? ''))
                    );

                    $nomeEnBase = $concept->name ?? '';
                    $nomePtBase = $printPt->printed_name ?? ($hit['name_pt'] ?? $nomeEnBase);

                    $isBasicLand = stripos($firstPrint->type_line ?? '', 'Basic Land') !== false;
                    $isVariantSet = in_array(strtoupper($firstPrint->set_code ?? ($firstPrint->set->code ?? '')), ['FEM', 'ALL', 'HML']);
                    $hasLetterInNumber = preg_match('/[a-zA-Z]/', (string)$vNum);
                    $isArtVariant = $isVariantSet && $hasLetterInNumber && !$isBasicLand;

                    $extraArtista = null;
                    if ($isArtVariant && !empty($firstPrint->artist)) {
                        $cacheKey = $cId . '_' . $sid;
                        $nomeArtistaBase = trim($firstPrint->artist);
                        $nomeArtistaFinal = $nomeArtistaBase;
                        if (isset($artistIndexesCache[$cacheKey][$nomeArtistaBase]) && count($artistIndexesCache[$cacheKey][$nomeArtistaBase]) > 1) {
                            $idx = array_search(strtolower(trim($vNum)), $artistIndexesCache[$cacheKey][$nomeArtistaBase]);
                            if ($idx !== false) $nomeArtistaFinal .= ' ' . ($idx + 1);
                        }
                        $extraArtista = $nomeArtistaFinal;
                    }

                    $titles = $presenter->buildDisplayTitles($nomeEnBase, $nomePtBase, $firstPrint, ($vNum !== '' ? $vNum : null), $extraArtista);
                    $nomeEn = $titles['en'];
                    $nomePt = $titles['pt'];
                    $conceptSlug = $presenter->buildProductSlug($concept, $firstPrint, ($vNum !== '' ? $vNum : null), $extraArtista);

                    $printImg = $printsCol->first(fn($p) => !empty($p->image_url) || !empty($p->image_path)) ?? $firstPrint;
                    $imagemBruta = $printImg->image_url ?? $printImg->image_path ?? null;
                    $imagemFinal = $imagemBruta
                        ? (filter_var($imagemBruta, FILTER_VALIDATE_URL) ? $imagemBruta : asset($imagemBruta))
                        : 'https://placehold.co/250x350/eeeeee/999999?text=X';

                    $menorPrecoItem = $printsCol->sortBy('stock_price')->first();
                    $extrasStr      = strtolower($menorPrecoItem->stock_extras ?? '');
                    $isEtched       = str_contains($extrasStr, 'etched');
                    $isFoil         = str_contains($extrasStr, 'foil') && !$isEtched;
                    $desconto       = (float) ($menorPrecoItem->stock_desconto ?? 0);
                    $precoBase      = (float) ($printsCol->min('stock_price') ?? 0);
                    $precoFinal     = $desconto > 0 ? $precoBase * (1 - ($desconto / 100)) : $precoBase;

                    $estoqueResults[] = [
                        'status'          => 'available','''

gs_updated = re.sub(old_stock_pattern, replacement_stock, gs, flags=re.DOTALL)
with open(path_gs, "w", encoding="utf-8") as f:
    f.write(gs_updated)
print("1. GlobalSearch ajustado com imagens e artistas limpos.")

# 2. FIX NO POKEMON PRESENTER (Desmontar slug e evitar 404)
path_pk = "app/Services/GamePresenters/PokemonPresenter.php"
with open(path_pk, "r", encoding="utf-8") as f:
    pk = f.read()

# Garantir que resolveProductData encontre o card pelo slug base mesmo com -num-total na rota
novo_resolve_product = '''    public function resolveProductData(string $conceptSlug): array
    {
        $cleanSlug = $conceptSlug;
        if (preg_match('/^(.*?)-(\\d+[a-zA-Z]?)-(\\d+)$/', $conceptSlug, $m)) {
            $cleanSlug = $m[1];
        } elseif (preg_match('/^(.*?)-(\\d+[a-zA-Z]?)$/', $conceptSlug, $m)) {
            $cleanSlug = $m[1];
        }

        $concept = CatalogConcept::where('game_id', 2)
            ->where(function ($q) use ($cleanSlug, $conceptSlug) {
                $q->where('slug', $cleanSlug)
                  ->orWhere('slug', $conceptSlug)
                  ->orWhere('name', 'like', str_replace('-', ' ', $cleanSlug));
            })
            ->with(['specific', 'prints', 'prints.set'])
            ->first();

        if (!$concept) {
            abort(404, 'Carta de Pokémon não encontrada no catálogo.');
        }

        $this->concept = $concept;
        $matchingPrints = $this->resolveMatchingPrints($concept->prints, $conceptSlug);

        $printPt = $matchingPrints->firstWhere(fn($p) => in_array(strtolower($p->language_code), ['pt', 'pt-br']) && !empty($p->printed_name));
        $this->localizedName = $printPt ? $printPt->printed_name : $concept->name;

        $this->activePrint = $matchingPrints->firstWhere(fn($p) => strtolower($p->language_code) === 'en') ?? $matchingPrints->first();
        $cardDetails = DB::table('pk_prints')->where('id', $this->activePrint?->specific_id)->first();

        return [
            'concept'        => $this->concept,
            'prints'         => $matchingPrints,
            'activePrint'    => $this->activePrint,
            'nomeLocalizado' => $this->localizedName,
            'cardDetails'    => $cardDetails,
        ];
    }'''

pk_updated = re.sub(r'public function resolveProductData.*?return \[\s*\'concept\'.*?\];\s*\}', novo_resolve_product, pk, flags=re.DOTALL)
with open(path_pk, "w", encoding="utf-8") as f:
    f.write(pk_updated)
print("2. PokemonPresenter atualizado para resolver {slug}-{num}-{total}.")

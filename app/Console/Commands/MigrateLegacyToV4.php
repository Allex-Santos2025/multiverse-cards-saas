<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CardFunctionality; // Legado
use App\Models\Card; // Legado
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\Games\Magic\MtgConcept;
use App\Models\Games\Magic\MtgPrint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MigrateLegacyToV4 extends Command
{
    protected $signature = 'migrate:legacy-v4';
    protected $description = 'Copia TODOS os dados das tabelas antigas para a nova estrutura (V4).';

    public function handle()
    {
        $this->info("=== INICIANDO MIGRAÇÃO DE DADOS (LEGADO -> NOVA ESTRUTURA) ===");
        
        // ID 1 = Magic: The Gathering
        $gameId = 1; 

        // Fase 1: Migrar Conceitos (Regras/Oracle)
        $this->migrateConcepts($gameId);

        // Fase 2: Migrar Prints (Cartas Físicas)
        $this->migratePrints($gameId);

        $this->info("=== MIGRAÇÃO CONCLUÍDA COM SUCESSO ===");
        return self::SUCCESS;
    }

    protected function migrateConcepts($gameId)
    {
        // Pega apenas funcionalidades de Magic
        $query = CardFunctionality::where('game_id', $gameId);
        $total = $query->count();
        
        $this->info("Migrando {$total} Conceitos de Magic...");
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // Processa em lotes de 500 para não estourar a memória
        $query->chunk(500, function ($legacies) use ($bar, $gameId) {
            foreach ($legacies as $legacy) {
                DB::transaction(function () use ($legacy, $gameId) {
                    
                    // Verifica se já existe para evitar duplicatas (Idempotência)
                    $exists = CatalogConcept::where('game_id', $gameId)
                        ->where('name', $legacy->mtg_name)
                        ->exists();
                    
                    if ($exists) return;

                    // 1. Cria o Específico (MtgConcept)
                    // Mapeia 1:1 os campos da tabela antiga para a nova
                    $mtgConcept = MtgConcept::create([
                        'oracle_id' => $legacy->mtg_oracle_id,
                        'mana_cost' => $legacy->mtg_mana_cost,
                        'cmc' => $legacy->mtg_cmc ?? 0,
                        'type_line' => $legacy->mtg_type_line,
                        'oracle_text' => $legacy->mtg_rules_text,
                        'power' => $legacy->mtg_power,
                        'toughness' => $legacy->mtg_toughness,
                        'loyalty' => $legacy->mtg_loyalty,
                        'edhrec_rank' => $legacy->mtg_edhrec_rank,
                        'penny_rank' => $legacy->mtg_penny_rank,
                        'max_copies' => $legacy->mtg_max_copies ?? 4,
                        
                        // JSONs
                        'colors' => $legacy->mtg_colors,
                        'color_identity' => $legacy->mtg_color_identity,
                        'color_indicator' => $legacy->mtg_color_indicator,
                        'produced_mana' => $legacy->mtg_produced_mana,
                        'keywords' => $legacy->mtg_keywords,
                        'legalities' => $legacy->mtg_legalities,
                    ]);

                    // 2. Cria o Mestre (CatalogConcept)
                    CatalogConcept::create([
                        'game_id' => $gameId,
                        'name' => $legacy->mtg_name ?? 'Unknown',
                        'slug' => Str::slug($legacy->mtg_name ?? uniqid()),
                        'search_names' => $legacy->mtg_searchable_names ?? (array) $legacy->mtg_name,
                        
                        // Ligação Polimórfica
                        'specific_type' => MtgConcept::class,
                        'specific_id' => $mtgConcept->id,
                    ]);
                });
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
    }

    protected function migratePrints($gameId)
    {
        // Busca cards (prints) que sejam de Magic
        // Considera tanto o game_id no card quanto o game_id do set
        $query = Card::where('game_id', $gameId)
                     ->orWhereHas('set', function($q) { $q->where('game_id', 1); });

        $total = $query->count();
        $this->info("Migrando {$total} Prints de Magic...");
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // CORREÇÃO: Adicionado 'use ($bar)' para a variável ser visível dentro do chunk
        $query->chunk(500, function ($legacyCards) use ($bar) {
            foreach ($legacyCards as $legacy) {
                // Precisamos achar o pai (CatalogConcept) através do Oracle ID antigo
                $oracleId = $legacy->cardFunctionality?->mtg_oracle_id;
                if (!$oracleId) continue;

                $mtgConceptNew = MtgConcept::where('oracle_id', $oracleId)->first();
                if (!$mtgConceptNew) continue; 

                $catalogConcept = $mtgConceptNew->catalogConcept;
                if (!$catalogConcept) continue;

                DB::transaction(function () use ($legacy, $catalogConcept) {
                    
                    // Verifica duplicidade pelo Scryfall ID
                    if ($legacy->mtg_scryfall_id && MtgPrint::where('scryfall_id', $legacy->mtg_scryfall_id)->exists()) {
                        return;
                    }

                    // 1. Cria o Específico (MtgPrint) com TODOS os detalhes
                    $mtgPrint = MtgPrint::create([
                        'scryfall_id' => $legacy->mtg_scryfall_id,
                        'rarity' => $legacy->mtg_rarity,
                        'artist' => $legacy->mtg_artist,
                        'collector_number' => $legacy->mtg_collection_number,
                        'language_code' => $legacy->mtg_language_code ?? 'en',
                        'flavor_text' => $legacy->mtg_flavor_text,
                        'frame' => $legacy->mtg_frame,
                        'border_color' => $legacy->mtg_border_color,
                        'full_art' => $legacy->mtg_full_art ?? false,
                        'textless' => $legacy->mtg_textless ?? false,
                        'promo' => $legacy->mtg_promo ?? false,
                        'reprint' => $legacy->mtg_reprint ?? false,
                        'variation' => $legacy->mtg_variation ?? false,
                        'has_foil' => $legacy->mtg_has_foil ?? false,
                        'nonfoil' => $legacy->mtg_nonfoil ?? false,
                        'etched' => $legacy->mtg_etched ?? false,
                        'oversized' => $legacy->mtg_oversized ?? false,
                        'digital' => $legacy->mtg_digital ?? false,
                        'highres_image' => $legacy->mtg_highres_image ?? false,
                        'security_stamp' => $legacy->mtg_security_stamp,
                        'watermark' => $legacy->mtg_watermark,
                        'card_back_id' => $legacy->mtg_card_back_id,
                        'image_status' => $legacy->mtg_image_status,
                        'released_at' => $legacy->mtg_released_at,
                        'finishes' => $legacy->mtg_finishes,
                        'prices' => $legacy->mtg_prices,
                        'related_uris' => $legacy->mtg_related_uris,
                        'purchase_uris' => $legacy->mtg_purchase_uris,
                        'multiverse_ids' => $legacy->mtg_multiverse_ids,
                        'mtgo_id' => $legacy->mtg_mtgo_id,
                        'mtgo_foil_id' => $legacy->mtg_mtgo_foil_id,
                        'arena_id' => $legacy->mtg_arena_id,
                        'tcgplayer_id' => $legacy->mtg_tcgplayer_id,
                        'tcgplayer_etched_id' => $legacy->mtg_tcgplayer_etched_id,
                        'cardmarket_id' => $legacy->mtg_cardmarket_id,
                        'illustration_id' => $legacy->mtg_illustration_id,
                    ]);

                    // 2. Cria o Mestre (CatalogPrint)
                    CatalogPrint::create([
                        'concept_id' => $catalogConcept->id,
                        'set_id' => $legacy->set_id, // O Set ID é o mesmo
                        
                        // Migra a imagem que existir
                        'image_path' => $legacy->local_image_path_large 
                                     ?? $legacy->local_image_path 
                                     ?? $legacy->mtg_image_url_api,

                        // Ligação Polimórfica
                        'specific_type' => MtgPrint::class,
                        'specific_id' => $mtgPrint->id,
                    ]);
                });

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Sucesso! Todos os dados de Magic foram preservados.");
    }
}
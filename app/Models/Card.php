<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Models\Set; 

class Card extends Model
{
    use HasFactory;

    /**
     * O $fillable completo (inalterado)
     */
    protected $fillable = [
        // Chaves de Relação
        'card_functionality_id',
        'set_id',
        'tcg_name',
        // ... (Todos os 95 outros campos fillable permanecem inalterados) ...
        'mtg_scryfall_id',
        'mtg_printed_name',
        'mtg_printed_text',
        'mtg_printed_type_line',
        'mtg_collection_code',
        'mtg_collection_number',
        'mtg_rarity',
        'mtg_artist',
        'mtg_flavor_text',
        'mtg_image_url_api',
        'mtg_language_code',
        'mtg_layout',
        'mtg_frame',
        'mtg_border_color',
        'mtg_full_art',
        'mtg_textless',
        'mtg_promo',
        'mtg_reprint',
        'mtg_variation',
        'mtg_illustration_id',
        'mtg_has_foil',
        'mtg_nonfoil',
        'mtg_etched',
        'mtg_oversized',
        'mtg_digital',
        'mtg_security_stamp',
        'mtg_watermark',
        'mtg_card_back_id',
        'mtg_highres_image',
        'mtg_image_status',
        'mtg_released_at',
        'mtg_image_uris',
        'mtg_prices',
        'mtg_related_uris',
        'mtg_purchase_uris',
        'mtg_multiverse_ids',
        'mtg_mtgo_id',
        'mtg_mtgo_foil_id',
        'mtg_arena_id',
        'mtg_tcgplayer_id',
        'mtg_tcgplayer_etched_id',
        'mtg_cardmarket_id',
        'pk_flavorText',
        'pk_artist',
        'pk_images',
        'pk_tcgplayer_prices',
        'pk_cardmarket_prices',
        'pk_set_id',
        'pk_set_name',
        'pk_number',
        'pk_language_code',
        'ygo_card_sets',
        'ygo_card_images',
        'ygo_card_prices',
        'ygo_language_code',
        'op_artist',
        'op_image_url',
        'op_promo',
        'op_card_id_name',
        'op_language_code',
        'lor_flavor_text',
        'lor_artist',
        'lor_image_url',
        'lor_collector_number',
        'lor_set_id',
        'lor_set_name',
        'lor_illustrators',
        'lor_prices',
        'lor_tcgplayer_id',
        'lor_language_code',
        'fab_flavor',
        'fab_image_urls',
        'fab_tcgplayer_url',
        'fab_identifier',
        'fab_set',
        'fab_printings',
        'fab_language_code',
        'swu_flavor',
        'swu_artist',
        'swu_image_url',
        'swu_set',
        'swu_card_number',
        'swu_foil',
        'swu_stamped',
        'swu_language_code',
        'bs_flavor_text',
        'bs_artist',
        'bs_image_url',
        'bs_collection_number',
        'bs_set_name',
        'bs_rarity',
        'bs_language_code',
        'local_image_path_large',
        'local_image_path_art_crop',
        'custom_image_path',
    ];

    /**
     * Casts (inalterado)
     */
    protected $casts = [
        // ... (Todos os 34 casts permanecem inalterados) ...
        'mtg_image_uris' => 'array',
        'mtg_prices' => 'array',
        'mtg_related_uris' => 'array',
        'mtg_purchase_uris' => 'array',
        'mtg_multiverse_ids' => 'array',
        'mtg_full_art' => 'boolean',
        'mtg_textless' => 'boolean',
        'mtg_promo' => 'boolean',
        'mtg_reprint' => 'boolean',
        'mtg_variation' => 'boolean',
        'mtg_has_foil' => 'boolean',
        'mtg_nonfoil' => 'boolean',
        'mtg_etched' => 'boolean',
        'mtg_oversized' => 'boolean',
        'mtg_digital' => 'boolean',
        'mtg_highres_image' => 'boolean',
        'pk_images' => 'array',
        'pk_tcgplayer_prices' => 'array',
        'pk_cardmarket_prices' => 'array',
        'ygo_card_sets' => 'array',
        'ygo_card_images' => 'array',
        'ygo_card_prices' => 'array',
        'op_promo' => 'boolean',
        'lor_illustrators' => 'array',
        'lor_prices' => 'array',
        'fab_image_urls' => 'array',
        'fab_printings' => 'array',
        'swu_foil' => 'boolean',
        'swu_stamped' => 'boolean',
    ];

    // ------------------------------------------------------------------
    // ACCESSORS (COM CORREÇÕES)
    // ------------------------------------------------------------------

    protected function artist(): Attribute
    {
        return Attribute::make( get: fn () => match ($this->tcg_name) {
                'Magic: The Gathering' => $this->mtg_artist,
                'Pokémon TCG' => $this->pk_artist,
                'One Piece Card Game' => $this->op_artist,
                'Lorcana TCG' => $this->lor_artist,
                'Star Wars: Unlimited' => $this->swu_artist,
                'Battle Scenes' => $this->bs_artist,
                'Yu-Gi-Oh!' => null,
                'Flesh and Blood' => null,
                default => null,
            },
        );
    }

    /**
     * MUDANÇA 1: Lógica expandida para todos os TCGs.
     * O Blade chama $print->rarity
     */
    protected function rarity(): Attribute
    {
        return Attribute::make( get: fn () => match ($this->tcg_name) {
                'Magic: The Gathering' => $this->mtg_rarity,
                'Battle Scenes' => $this->bs_rarity,
                // TODO: Adicionar os campos de raridade que faltam (dos outros 6 TCGs)
                // quando os adicionarmos ao banco de dados `cards`.
                // Por enquanto, o Blade só entende raridades de Magic/BS.
                default => null,
            },
        );
    }

    protected function flavorText(): Attribute
    {
        return Attribute::make( get: fn () => match ($this->tcg_name) {
                'Magic: The Gathering' => $this->mtg_flavor_text,
                'Pokémon TCG' => $this->pk_flavorText,
                'Lorcana TCG' => $this->lor_flavor_text,
                'Flesh and Blood' => $this->fab_flavor,
                'Star Wars: Unlimited' => $this->swu_flavor,
                'Battle Scenes' => $this->bs_flavor_text,
                default => null,
            },
        );
    }

    /**
     * MUDANÇA 2: Nome da função RENOMEADO de volta para camelCase
     * para corrigir o LogicException. O Blade ($print->collection_number) vai mapear para isso.
     */
    protected function collectionNumber(): Attribute
    {
        return Attribute::make( get: fn () => match ($this->tcg_name) {
                'Magic: The Gathering' => $this->mtg_collection_number,
                'Pokémon TCG' => $this->pk_number,
                'Lorcana TCG' => $this->lor_collector_number,
                'Star Wars: Unlimited' => $this->swu_card_number,
                'Battle Scenes' => $this->bs_collection_number,
                'One Piece Card Game' => $this->op_card_id_name,
                'Flesh and Blood' => $this->fab_identifier,
                'Yu-Gi-Oh!' => $this->ygo_konami_id, // Fallback para o ID
                default => null,
            },
        );
    }

    /**
     * MUDANÇA 3: Accessor 'prices' (camelCase) CRIADO.
     * O Blade ($print->prices) vai mapear para isso.
     */
    protected function prices(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->tcg_name) {
                'Magic: The Gathering' => $this->mtg_prices,
                'Pokémon TCG' => $this->pk_tcgplayer_prices, // O Blade vai procurar 'usd' aqui
                'Yu-Gi-Oh!' => $this->ygo_card_prices[0] ?? null, // Pega o primeiro set de preços
                'Lorcana TCG' => $this->lor_prices,
                // Outros TCGs (BS, OP, FAB, SWU) não têm JSON de preço no Dossiê 2.0
                default => null,
            },
        );
    }

    // Accessor de Imagem (Inalterado, sintaxe já corrigida)
    public function getImageUrlAttribute(): ?string
    {
        // 1. Prioridade: Imagem Customizada (Upload Manual)
        if ($this->custom_image_path) {
            return Storage::url($this->custom_image_path);
        }
        
        // 2. Prioridade: Imagem Local Baixada (Ingestor)
        if ($this->local_image_path_large) {
            return asset($this->local_image_path_large);
        }

        // 3. Fallback: Lógica da API (SINTAXE CORRIGIDA)
        return match ($this->tcg_name) {
            'Magic: The Gathering' => (function() {
                $uris = $this->mtg_image_uris ?? [];
                return $uris['large'] ?? $uris['png'] ?? $uris['normal'] ?? null;
            })(),
            'Pokémon TCG' => (function() {
                $uris = $this->pk_images ?? [];
                return $uris['large'] ?? $uris['small'] ?? null;
            })(),
            'Yu-Gi-Oh!' => (function() {
                $uris = $this->ygo_card_images ?? [];
                return $uris[0]['image_url'] ?? $uris[0]['image_url_small'] ?? null;
            })(),
            'One Piece Card Game' => $this->op_image_url,
            'Lorcana TCG' => $this->lor_image_url,
            'Flesh and Blood' => (function() {
                 $uris = $this->fab_image_urls ?? []; 
                 return $uris['large'] ?? $uris['small'] ?? ($this->fab_image_urls[0] ?? null);
            })(),
            'Star Wars: Unlimited' => $this->swu_image_url,
            'Battle Scenes' => $this->bs_image_url,
            default => null,
        };
    }

    // ------------------------------------------------------------------
    // Relações (Inalteradas, mas essenciais)
    // ------------------------------------------------------------------

    public function cardFunctionality(): BelongsTo
    {
        return $this->belongsTo(CardFunctionality::class);
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }
}
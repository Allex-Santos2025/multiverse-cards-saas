<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CardFunctionality extends Model
{
    use HasFactory;

    /**
     * O $fillable completo.
     * Removemos 'tcg_name' da prioridade lógica, mas mantemos no fillable 
     * caso você decida voltar atrás, mas a lógica principal mudou.
     */
    protected $fillable = [
        'game_id',
        // 'tcg_name', // Não é mais estritamente necessário se usarmos a relação
        'swu_name',
        'fab_name',
        'lor_name',
        'op_name',
        'ygo_name',
        'pk_name',
        'bs_name',
        'bs_alter_ego',
        'swu_title',
        'lor_title',
        'ygo_konami_id',
        'pk_supertype',
        'mtg_oracle_id',
        'mtg_name',
        'mtg_mana_cost',
        'mtg_cmc',
        'mtg_type_line',
        'mtg_rules_text',
        'mtg_searchable_names',
        'mtg_max_copies',
        'mtg_legalities',
        'mtg_power',
        'mtg_toughness',
        'mtg_loyalty',
        'mtg_produced_mana',
        'mtg_edhrec_rank',
        'mtg_penny_rank',
        'mtg_colors',
        'mtg_color_identity',
        'mtg_color_indicator',
        'mtg_keywords',
        'pk_subtypes',
        'pk_types',
        'pk_hp',
        'pk_level',
        'pk_retreatCost',
        'pk_convertedRetreatCost',
        'pk_attacks',
        'pk_abilities',
        'pk_weaknesses',
        'pk_resistances',
        'pk_evolvesFrom',
        'pk_evolvesTo',
        'pk_nationalPokedexNumbers',
        'pk_rules',
        'ygo_type',
        'ygo_race',
        'ygo_attribute',
        'ygo_atk',
        'ygo_def',
        'ygo_level',
        'ygo_scale',
        'ygo_linkval',
        'ygo_linkmarkers',
        'ygo_archetype',
        'ygo_banlist_info',
        'ygo_desc',
        'op_color',
        'op_type',
        'op_cost',
        'op_power',
        'op_life',
        'op_counter',
        'op_attribute',
        'op_traits',
        'op_effect',
        'op_trigger_effect',
        'lor_type',
        'lor_cost',
        'lor_inkable',
        'lor_color',
        'lor_strength',
        'lor_willpower',
        'lor_lore',
        'lor_classifications',
        'lor_abilities_and_effects',
        'fab_pitch',
        'fab_cost',
        'fab_power',
        'fab_defense',
        'fab_health',
        'fab_type',
        'fab_keywords',
        'fab_class',
        'fab_talent',
        'fab_stats',
        'fab_legality',
        'fab_text',
        'swu_is_unique',
        'swu_type',
        'swu_aspects',
        'swu_cost',
        'swu_power',
        'swu_hp',
        'swu_arena',
        'swu_traits',
        'swu_ability_text',
        'swu_keywords',
        'bs_type_line',
        'bs_power',
        'bs_toughness',
        'bs_cost',
        'bs_affiliation',
        'bs_alignment',
        'bs_powers',
        'bs_rules_text',
    ];

    protected $casts = [
        'mtg_legalities' => 'array',
        'mtg_produced_mana' => 'array',
        'mtg_colors' => 'array',
        'mtg_color_identity' => 'array',
        'mtg_keywords' => 'array',
        'mtg_searchable_names' => 'array',
        'pk_subtypes' => 'array',
        'pk_types' => 'array',
        'pk_retreatCost' => 'array',
        'pk_attacks' => 'array',
        'pk_abilities' => 'array',
        'pk_weaknesses' => 'array',
        'pk_resistances' => 'array',
        'pk_evolvesTo' => 'array',
        'pk_nationalPokedexNumbers' => 'array',
        'pk_rules' => 'array',
        'ygo_linkmarkers' => 'array',
        'ygo_banlist_info' => 'array',
        'lor_classifications' => 'array',
        'fab_keywords' => 'array',
        'fab_stats' => 'array',
        'fab_legality' => 'array',
        'swu_aspects' => 'array',
        'swu_traits' => 'array',
        'swu_keywords' => 'array',
        'bs_powers' => 'array',
    ];

    // ------------------------------------------------------------------
    // ACCESSORS INTELIGENTES (Agora usando a Relação GAME)
    // ------------------------------------------------------------------

    /**
     * Helper privado para obter o nome do jogo de forma segura.
     * Isso evita repetir $this->game?->name em todo lugar.
     */
    private function getGameName(): ?string
    {
        return $this->game?->name;
    }

    /**
     * Retorna o NOME principal do card.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->getGameName()) {
                'Magic: The Gathering' => $this->mtg_name,
                'Pokémon TCG' => $this->pk_name,
                'Yu-Gi-Oh!' => $this->ygo_name,
                'One Piece Card Game' => $this->op_name,
                'Lorcana TCG' => $this->lor_name,
                'Flesh and Blood' => $this->fab_name,
                'Star Wars: Unlimited' => $this->swu_name,
                'Battle Scenes' => $this->bs_name,
                default => 'Desconhecido',
            },
        );
    }

    /**
     * Retorna o TEXTO DE REGRAS principal.
     */
    protected function rulesText(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->getGameName()) {
                'Magic: The Gathering' => $this->mtg_rules_text,
                'Pokémon TCG' => $this->pk_rules,
                'Yu-Gi-Oh!' => $this->ygo_desc,
                'One Piece Card Game' => $this->op_effect,
                'Lorcana TCG' => $this->lor_abilities_and_effects,
                'Flesh and Blood' => $this->fab_text,
                'Star Wars: Unlimited' => $this->swu_ability_text,
                'Battle Scenes' => $this->bs_rules_text,
                default => null,
            },
        );
    }

    /**
     * Retorna a LINHA DE TIPO principal.
     */
    protected function typeLine(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->getGameName()) {
                'Magic: The Gathering' => $this->mtg_type_line,
                'Pokémon TCG' => $this->pk_supertype,
                'Yu-Gi-Oh!' => $this->ygo_type,
                'One Piece Card Game' => $this->op_type,
                'Lorcana TCG' => $this->lor_type,
                'Flesh and Blood' => $this->fab_type,
                'Star Wars: Unlimited' => $this->swu_type,
                'Battle Scenes' => $this->bs_type_line,
                default => null,
            },
        );
    }

    /**
     * Retorna o PODER/ATAQUE principal.
     */
    protected function power(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->getGameName()) {
                'Magic: The Gathering' => $this->mtg_power,
                'Yu-Gi-Oh!' => $this->ygo_atk,
                'One Piece Card Game' => $this->op_power,
                'Lorcana TCG' => $this->lor_strength,
                'Flesh and Blood' => $this->fab_power,
                'Star Wars: Unlimited' => $this->swu_power,
                'Battle Scenes' => $this->bs_power, // Energia em BS
                default => null,
            },
        );
    }

    /**
     * Retorna a DEFESA/RESISTÊNCIA principal.
     */
    protected function toughness(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->getGameName()) {
                'Magic: The Gathering' => $this->mtg_toughness,
                'Pokémon TCG' => $this->pk_hp,
                'Yu-Gi-Oh!' => $this->ygo_def,
                'Lorcana TCG' => $this->lor_willpower,
                'Flesh and Blood' => $this->fab_defense,
                'Star Wars: Unlimited' => $this->swu_hp,
                'Battle Scenes' => $this->bs_toughness, // Escudo em BS
                default => null,
            },
        );
    }

    /**
     * Retorna o CUSTO principal.
     */
    protected function cost(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->getGameName()) {
                'Magic: The Gathering' => $this->mtg_mana_cost,
                'One Piece Card Game' => $this->op_cost,
                'Lorcana TCG' => $this->lor_cost,
                'Flesh and Blood' => $this->fab_cost,
                'Star Wars: Unlimited' => $this->swu_cost,
                'Battle Scenes' => $this->bs_cost, // Capacidade em BS
                default => null,
            },
        );
    }
    
    // ------------------------------------------------------------------
    // RELAÇÕES
    // ------------------------------------------------------------------

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function cards(): HasMany 
    { 
        return $this->hasMany(Card::class); 
    }

    public function rulings(): HasMany 
    { 
        return $this->hasMany(Ruling::class); 
    }

    public function stockItems(): HasManyThrough
    {
        return $this->hasManyThrough(StockItem::class, Card::class);
    }
}
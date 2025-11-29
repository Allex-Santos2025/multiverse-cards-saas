<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute; // Adicionado para Accessors
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CardFunctionality extends Model
{
    use HasFactory;

    /**
     * O $fillable completo, sincronizado com o banco de dados e o Superset.
     * Atualizado para o Dossiê Mestre 2.0 (108 campos).
     */
    protected $fillable = [
        'game_id',
        'tcg_name',
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

    /**
     * Casts para os campos JSON (Dossiê Mestre 2.0)
     */
    protected $casts = [
        // Magic: The Gathering
        'mtg_legalities' => 'array',
        'mtg_produced_mana' => 'array',
        'mtg_colors' => 'array',
        'mtg_color_identity' => 'array',
        'mtg_keywords' => 'array',
        'mtg_searchable_names' => 'array',

        // Pokémon TCG
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

        // Yu-Gi-Oh!
        'ygo_linkmarkers' => 'array',
        'ygo_banlist_info' => 'array',

        // Lorcana TCG
        'lor_classifications' => 'array',

        // Flesh and Blood
        'fab_keywords' => 'array',
        'fab_stats' => 'array',
        'fab_legality' => 'array',

        // Star Wars: Unlimited
        'swu_aspects' => 'array',
        'swu_traits' => 'array',
        'swu_keywords' => 'array',

        // Battle Scenes
        'bs_powers' => 'array',
    ];

    // ------------------------------------------------------------------
    // ACCESSORS (Atributos Virtuais Agnósticos)
    // ------------------------------------------------------------------

    /**
     * Retorna o NOME principal do card, independente do TCG.
     * Uso: $model->name
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->tcg_name) {
                'Magic: The Gathering' => $this->mtg_name,
                'Pokémon TCG' => $this->pk_name,
                'Yu-Gi-Oh!' => $this->ygo_name,
                'One Piece Card Game' => $this->op_name,
                'Lorcana TCG' => $this->lor_name,
                'Flesh and Blood' => $this->fab_name,
                'Star Wars: Unlimited' => $this->swu_name,
                'Battle Scenes' => $this->bs_name,
                default => null,
            },
        );
    }

    /**
     * Retorna o TEXTO DE REGRAS principal do card, independente do TCG.
     * Uso: $model->rules_text
     */
    protected function rulesText(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->tcg_name) {
                'Magic: The Gathering' => $this->mtg_rules_text,
                'Pokémon TCG' => $this->pk_rules, // Dossiê 2.0
                'Yu-Gi-Oh!' => $this->ygo_desc, // Dossiê 2.0
                'One Piece Card Game' => $this->op_effect, // Dossiê 2.0
                'Lorcana TCG' => $this->lor_abilities_and_effects, // Dossiê 2.0
                'Flesh and Blood' => $this->fab_text, // Dossiê 2.0
                'Star Wars: Unlimited' => $this->swu_ability_text, // Dossiê 2.0
                'Battle Scenes' => $this->bs_rules_text, // Dossiê 2.0
                default => null,
            },
        );
    }

    /**
     * Retorna a LINHA DE TIPO principal do card, independente do TCG.
     * Uso: $model->type_line
     */
    protected function typeLine(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->tcg_name) {
                'Magic: The Gathering' => $this->mtg_type_line,
                'Pokémon TCG' => $this->pk_supertype, // Dossiê 2.0
                'Yu-Gi-Oh!' => $this->ygo_type, // Dossiê 2.0
                'One Piece Card Game' => $this->op_type, // Dossiê 2.0
                'Lorcana TCG' => $this->lor_type, // Dossiê 2.0
                'Flesh and Blood' => $this->fab_type, // Dossiê 2.0
                'Star Wars: Unlimited' => $this->swu_type, // Dossiê 2.0
                'Battle Scenes' => $this->bs_type_line, // Dossiê 2.0
                default => null,
            },
        );
    }

    /**
     * Retorna o PODER/ATAQUE principal do card, independente do TCG.
     * Uso: $model->power
     */
    protected function power(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->tcg_name) {
                'Magic: The Gathering' => $this->mtg_power,
                'Pokémon TCG' => null, // PK 'attacks' é um array complexo, não um valor simples.
                'Yu-Gi-Oh!' => $this->ygo_atk,
                'One Piece Card Game' => $this->op_power,
                'Lorcana TCG' => $this->lor_strength,
                'Flesh and Blood' => $this->fab_power,
                'Star Wars: Unlimited' => $this->swu_power,
                'Battle Scenes' => $this->bs_power,
                default => null,
            },
        );
    }

    /**
     * Retorna a DEFESA/RESISTÊNCIA principal do card, independente do TCG.
     * Uso: $model->toughness
     */
    protected function toughness(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->tcg_name) {
                'Magic: The Gathering' => $this->mtg_toughness,
                'Pokémon TCG' => $this->pk_hp,
                'Yu-Gi-Oh!' => $this->ygo_def,
                'One Piece Card Game' => null, // OP não tem defesa.
                'Lorcana TCG' => $this->lor_willpower,
                'Flesh and Blood' => $this->fab_defense,
                'Star Wars: Unlimited' => $this->swu_hp,
                'Battle Scenes' => $this->bs_toughness,
                default => null,
            },
        );
    }

    /**
     * Retorna o CUSTO principal do card, independente do TCG.
     * Uso: $model->cost
     */
    protected function cost(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->tcg_name) {
                'Magic: The Gathering' => $this->mtg_mana_cost,
                'Pokémon TCG' => null, // PK 'attacks' tem custos, não o card.
                'Yu-Gi-Oh!' => null, // YGO não tem custo (Nível/Rank não é custo).
                'One Piece Card Game' => $this->op_cost,
                'Lorcana TCG' => $this->lor_cost,
                'Flesh and Blood' => $this->fab_cost,
                'Star Wars: Unlimited' => $this->swu_cost,
                'Battle Scenes' => $this->bs_cost,
                default => null,
            },
        );
    }
    
    // ------------------------------------------------------------------
    // RELAÇÕES (Não alteradas, conforme combinado)
    // ------------------------------------------------------------------

    /**
     * Relação: Um Conceito pertence a um Jogo.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Relação: Um Conceito (Functionality) tem muitas Impressões (Cards).
     */
    public function cards(): HasMany 
    { 
        return $this->hasMany(Card::class); 
    }

    /**
     * Relação: Um Conceito tem muitas Rulings (Regras).
     */
    public function rulings(): HasMany 
    { 
        return $this->hasMany(Ruling::class); 
    }

    /**
     * Relação: Um Conceito tem muitos Itens em Estoque (através dos Cards).
     */
    public function stockItems(): HasManyThrough
    {
        return $this->hasManyThrough(StockItem::class, Card::class);
    }
}
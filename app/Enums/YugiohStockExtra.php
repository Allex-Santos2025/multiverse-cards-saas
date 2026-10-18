<?php

namespace App\Enums;

enum YugiohStockExtra: string
{
    // Edições físicas de lançamento
    case FIRST_EDITION = '1st_edition';
    case UNLIMITED     = 'unlimited';
    case LIMITED       = 'limited';

    // Anomalias de fábrica
    case MISPRINT      = 'misprint';
    case MISCUT        = 'miscut';

    // Acabamentos / Tratamentos Holográficos (Raridades Físicas)
    case COMMON                     = 'common';
    case RARE                       = 'rare';
    case SUPER_RARE                 = 'super_rare';
    case ULTRA_RARE                 = 'ultra_rare';
    case SECRET_RARE                = 'secret_rare';
    case ULTIMATE_RARE              = 'ultimate_rare';
    case GHOST_RARE                 = 'ghost_rare';
    case PRISMATIC_SECRET_RARE      = 'prismatic_secret_rare';
    case STARLIGHT_RARE             = 'starlight_rare';
    case QUARTER_CENTURY_SECRET     = 'quarter_century_secret';
    case COLLECTORS_RARE            = 'collectors_rare';
    case GOLD_RARE                  = 'gold_rare';

    public function label(): string
    {
        return match ($this) {
            self::FIRST_EDITION            => '1ª Edição',
            self::UNLIMITED                => 'Ilimitada',
            self::LIMITED                  => 'Edição Limitada',
            self::MISPRINT                 => 'Misprint (Erro de Impressão)',
            self::MISCUT                   => 'Miscut (Corte Desalinhado)',
            self::COMMON                   => 'Comum (Sem Foil)',
            self::RARE                     => 'Rara (Nome Prateado)',
            self::SUPER_RARE               => 'Super Rara (Arte Foil)',
            self::ULTRA_RARE               => 'Ultra Rara (Nome Dourado + Arte Foil)',
            self::SECRET_RARE              => 'Secret Rare (Holo Diagonal)',
            self::ULTIMATE_RARE            => 'Ultimate Rare (Relevo Texturizado)',
            self::GHOST_RARE               => 'Ghost Rare (Holograma 3D)',
            self::PRISMATIC_SECRET_RARE     => 'Prismatic Secret Rare',
            self::STARLIGHT_RARE           => 'Starlight Rare',
            self::QUARTER_CENTURY_SECRET   => 'Quarter Century Secret Rare (25th)',
            self::COLLECTORS_RARE          => 'Collector\'s Rare',
            self::GOLD_RARE                => 'Gold Rare',
        };
    }

    /**
     * Retorna a lista completa de opções para exibição
     */
    public static function options(): array
    {
        $arr = [];
        foreach (self::cases() as $case) {
            $arr[$case->value] = $case->label();
        }
        return $arr;
    }

    /**
     * Identifica se o extra é um acabamento fixo derivado da estampa
     */
    public function isIntrinsicFinish(): bool
    {
        return !in_array($this, [
            self::FIRST_EDITION,
            self::UNLIMITED,
            self::LIMITED,
            self::MISPRINT,
            self::MISCUT,
        ]);
    }
}

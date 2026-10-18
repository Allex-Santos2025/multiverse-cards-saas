<?php

namespace App\Enums;

enum PokemonStockExtra: string
{
    // --- Acabamentos ---
    case Holofoil = 'holofoil';
    case ReverseHolo = 'reverse_holo';

    // --- Variações de Edição ---
    case FirstEdition = 'first_edition';
    case Shadowless = 'shadowless';

    // --- Eventos / Stamped ---
    case Stamped = 'stamped';
    case Staff = 'staff';
    case Prerelease = 'prerelease';

    // --- Físico / Colecionável ---
    case Oversize = 'oversize';
    case Signed = 'signed';
    case Altered = 'altered';
    case Misprint = 'misprint';
    case Miscut = 'miscut';

    public static function options(): array
    {
        return [
            self::Holofoil->value     => 'Holofoil',
            self::ReverseHolo->value  => 'Reverse Holo',
            self::FirstEdition->value => '1ª Edição (1st Edition)',
            self::Shadowless->value   => 'Shadowless',
            self::Stamped->value      => 'Stamped / Promo',
            self::Staff->value        => 'Staff',
            self::Prerelease->value   => 'Pre-Release',
            self::Oversize->value     => 'Oversize / Jumbo',
            self::Signed->value       => 'Assinada',
            self::Altered->value      => 'Alterada',
            self::Misprint->value     => 'Misprint / Erro',
            self::Miscut->value       => 'Miscut (Corte Errado)',
        ];
    }
}

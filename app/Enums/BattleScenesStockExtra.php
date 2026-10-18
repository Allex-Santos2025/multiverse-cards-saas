<?php

namespace App\Enums;

enum BattleScenesStockExtra: string
{
    // --- Acabamentos ---
    case Foil = 'foil';

    // --- Promos e Especiais ---
    case Promo = 'promo';

    // --- Estado Físico / Alterações ---
    case Signed = 'signed';
    case Altered = 'altered';
    case Misprint = 'misprint';

    public static function options(): array
    {
        return [
            self::Foil->value     => 'Foil',
            self::Promo->value    => 'Promo',
            self::Signed->value   => 'Assinada',
            self::Altered->value  => 'Alterada',
            self::Misprint->value => 'Misprint / Erro',
        ];
    }
}

<?php

namespace App\Enums;

enum StockExtra: string
{
    // --- Acabamentos ---
    case Foil = 'foil';
    case FoilEtched = 'foil_etched';
    
    // --- Estado Físico / Alterações ---
    case Altered = 'altered';
    case Signed = 'signed';
    case Misprint = 'misprint';
    case Miscut = 'miscut';
    
    // --- Promos e Eventos ---
    case Prerelease = 'prerelease'; // Datada
    case Promo = 'promo';           // Promo Pack / Stamped
    case BuyABox = 'buy_a_box';
    case FNM = 'fnm';
    case Textless = 'textless';
    case Oversize = 'oversize';

    // Retorna o texto bonito para o Dropdown
    public static function options(): array
    {
        return [
            self::Foil->value        => 'Foil',
            self::FoilEtched->value  => 'Foil Especial / Etched',
            self::Altered->value     => 'Alterada',
            self::Signed->value      => 'Assinada',
            self::Misprint->value    => 'Misprint / Erro',
            self::Miscut->value      => 'Miscut (Corte Errado)',
            self::Prerelease->value  => 'Pre-Release (Datada)',
            self::Promo->value       => 'Promo / Stamped',
            self::BuyABox->value     => 'Buy-a-Box',
            self::FNM->value         => 'FNM',
            self::Textless->value    => 'Textless',
            self::Oversize->value    => 'Oversize',
        ];
    }
}
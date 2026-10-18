<?php

namespace App\Enums;

enum OnePieceStockExtra: string
{
    // --- Acabamentos e Variantes ---
    case Foil = 'foil';
    case Textured = 'textured';
    case Parallel = 'parallel';
    case Manga = 'manga';

    // --- Promos, Eventos e Torneios ---
    case Prerelease = 'prerelease';
    case TournamentWinner = 'winner';
    case Stamped = 'stamped';
    case Promo = 'promo';

    // --- Estado Físico e Assinaturas ---
    case Signed = 'signed';
    case Altered = 'altered';

    // --- Anomalias de Fábrica ---
    case Misprint = 'misprint';
    case Miscut = 'miscut';

    public static function options(): array
    {
        return [
            self::Foil->value             => 'Foil',
            self::Textured->value         => 'Texturizada / Relevo',
            self::Parallel->value         => 'Parallel (Arte Alternativa)',
            self::Manga->value            => 'Manga Rare',
            self::Prerelease->value       => 'Pre-Release (Carimbada)',
            self::TournamentWinner->value => 'Winner (Torneio)',
            self::Stamped->value          => 'Stamped / Evento',
            self::Promo->value            => 'Promo',
            self::Signed->value           => 'Assinada',
            self::Altered->value          => 'Alterada',
            self::Misprint->value         => 'Misprint (Erro de Impressão)',
            self::Miscut->value           => 'Miscut (Corte Desalinhado)',
        ];
    }
}

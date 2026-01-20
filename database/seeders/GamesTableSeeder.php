<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GamesTableSeeder extends Seeder
{
    public function run(): void
    {
        // LISTA ALINHADA COM O BANCO DE DADOS EXISTENTE (BASEADO NA IMAGEM)
        // IDs 1 a 8 já existem e devem ser respeitados.
        $games = [
            // 1. MAGIC (Já existe)
            
            // 2. POKÉMON TCG (Internacional) - FOCO ATUAL
            [
                'id' => 2,
                'name' => 'Pokémon TCG',
                'url_slug' => 'pokemon',
                'is_active' => true, 
                'ingestor_class' => 'App\Services\PokemonTcgApiService', 
                'api_url' => 'https://api.pokemontcg.io/v2/',
                'rate_limit_ms' => 200, // Validado na documentação oficial para uso sem chave
            ],

            // 3. YU-GI-OH! (TCG Internacional)
            [
                'id' => 3,
                'name' => 'Yu-Gi-Oh!',
                'url_slug' => 'yugioh',
                'is_active' => false,
                'ingestor_class' => null, 
                'api_url' => 'https://db.ygoprodeck.com/api/v7/',
            ],

            // 4. BATTLE SCENES (Já existe)

            // 5. ONE PIECE CARD GAME (Na imagem é o ID 5)
            [
                'id' => 5,
                'name' => 'One Piece Card Game',
                'url_slug' => 'one-piece',
                'is_active' => false,
                'ingestor_class' => null,
                'api_url' => null,
            ],

            // 6. LORCANA TCG (Na imagem é o ID 6)
            [
                'id' => 6,
                'name' => 'Lorcana TCG',
                'url_slug' => 'lorcana',
                'is_active' => false,
                'ingestor_class' => null,
                'api_url' => null,
            ],

            // 7. FLESH AND BLOOD (Na imagem é o ID 7)
            [
                'id' => 7,
                'name' => 'Flesh and Blood',
                'url_slug' => 'flesh-and-blood',
                'is_active' => false,
                'ingestor_class' => null,
                'api_url' => null,
            ],

            // 8. STAR WARS: UNLIMITED (Na imagem é o ID 8)
            [
                'id' => 8,
                'name' => 'Star Wars: Unlimited',
                'url_slug' => 'star-wars-unlimited',
                'is_active' => false,
                'ingestor_class' => null,
                'api_url' => null,
            ],

            // --- NOVOS (VARIAÇÕES ORIENTAIS) ---
            // Estes serão criados agora com IDs seguros (9 e 10).

            // 9. POKÉMON OCG (Japão)
            [
                'id' => 9,
                'name' => 'Pokémon OCG (Japan)',
                'url_slug' => 'pokemon-ocg',
                'is_active' => false,
                'ingestor_class' => null,
                'api_url' => null, 
            ],

            // 10. YU-GI-OH! OCG (Japão/Ásia)
            [
                'id' => 10,
                'name' => 'Yu-Gi-Oh! OCG',
                'url_slug' => 'yugioh-ocg',
                'is_active' => false,
                'ingestor_class' => null,
                'api_url' => null,
            ]
        ];

        foreach ($games as $game) {
            // Proteção contra duplicidade de nome:
            // Se existir um jogo com o mesmo nome mas ID diferente, movemos para o ID certo.
            // (Isso corrige se algum jogo estiver no ID errado).
            $existingGameByName = DB::table('games')->where('name', $game['name'])->first();

            if ($existingGameByName && $existingGameByName->id != $game['id']) {
                // Cuidado: Só atualizamos se o ID de destino estiver livre
                $targetIdExists = DB::table('games')->where('id', $game['id'])->exists();
                if (!$targetIdExists) {
                    DB::table('games')->where('id', $existingGameByName->id)->update(['id' => $game['id']]);
                } else {
                    // Se o ID de destino já existe (conflito), apenas atualizamos o registro existente no ID correto
                    // e ignoramos o registro duplicado no ID errado (terá que ser corrigido manualmente se sobrar lixo)
                }
            }

            // Insere ou Atualiza pelo ID fixo
            DB::table('games')->updateOrInsert(
                ['id' => $game['id']], 
                $game 
            );
        }
    }
}
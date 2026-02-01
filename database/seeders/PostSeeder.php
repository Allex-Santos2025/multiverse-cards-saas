<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DESTAQUE (CAPA) - O Set do Momento (Aetherdrift)
        Post::create([
            'title' => 'Aetherdrift Previews: As Cartas de Veículo que vão Acelerar o Standard',
            'slug' => Str::slug('Aetherdrift Previews: As Cartas de Veículo que vão Acelerar o Standard'),
            'category' => 'SPOILERS',
            'category_color' => 'orange-500', 
            'image' => 'https://scg-static.starcitygames.com/articles/2025/01/8eb9bc2d-explosion-700x525.png', // Estética Magic/Fantasia
            'excerpt' => 'Com o lançamento de Aetherdrift marcado para o próximo mês, analisamos os novos "Drivers" e como a mecânica de customização de veículos promete mudar o ritmo do jogo.',
            'content' => 'A temporada de spoilers de Aetherdrift está a todo vapor na Star City Games. A nova mecânica de "Turbo" permite que veículos ataquem sem tripulação sob certas condições, o que deve punir decks Control que dependem de remoções de feitiço. Analisamos as 10 raras mais fortes reveladas até agora e como elas se encaixam na curva do atual Standard. Na Versus, o mercado de pré-venda já aponta o "Aetherswift Interceptor" como a carta mais desejada do set.',
            'is_featured' => true,
            'published_at' => now(),
        ]);

        // 2. RADAR - Eventos SCG CON 2026
        Post::create([
            'title' => 'SCG CON 2026: Cronograma Oficial do Regional Championship em Fevereiro',
            'slug' => Str::slug('SCG CON 2026: Cronograma Oficial do Regional Championship em Fevereiro'),
            'category' => 'COMPETITIVO',
            'category_color' => 'red-600',
            'image' => 'https://scg-static.starcitygames.com/articles/2025/09/eb5016f6-sl25_12_avatar_atlanta_1920x1080-1-1536x864.jpg',
            'excerpt' => 'A Star City Games confirmou as cidades-sede para o primeiro trimestre de 2026. Saiba onde garantir sua vaga para o Pro Tour.',
            'content' => 'O circuito competitivo começa com força total em fevereiro. Philadelphia e Richmond serão os palcos das primeiras paradas do SCG CON em 2026. O formato principal será Modern, mas os torneios paralelos de Pioneer e Commander prometem premiações recordes. Se você planeja competir, fique atento às listas de deck que começam a surgir pós-banimento. A Versus TCG terá um stand virtual com as principais cartas necessárias para os decks do Tier 1.',
            'is_featured' => false,
            'published_at' => now(),
        ]);

        // 3. RADAR - Análise de Metagame (Modern)
        Post::create([
            'title' => 'O Estado do Modern: Rakdos Midrange ainda é o Deck a ser Batido?',
            'slug' => Str::slug('O Estado do Modern: Rakdos Midrange ainda é o Deck a ser Batido?'),
            'category' => 'ANÁLISE',
            'category_color' => 'blue-600',
            'image' => 'https://cards.scryfall.io/art_crop/front/d/6/d67be074-cdd4-41d9-ac89-0a0456c4e4b2.jpg',
            'excerpt' => 'Analisamos os dados dos últimos torneios da Star City Games para entender a resiliência do Rakdos frente às novas estratégias de Combo.',
            'content' => 'Mesmo com a ascensão de decks baseados em cemitério após Innistrad Remastered, o Rakdos Scam/Midrange continua mantendo uma win rate sólida acima de 54%. Neste artigo, destrinchamos as sideboards mais eficientes para enfrentar esse deck e como a inclusão de "Leyline of the Void" tornou-se obrigatória no meta atual. Confira as cartas chave que você pode encontrar no marketplace da Versus para atualizar sua reserva.',
            'is_featured' => false,
            'published_at' => now(),
        ]);
    }
}
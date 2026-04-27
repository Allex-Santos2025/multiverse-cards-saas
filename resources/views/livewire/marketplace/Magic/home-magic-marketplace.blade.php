<div>
    {{-- CSS Local de Energia (Pode ser movido para um arquivo .css depois) --}}
    <style>
        :root {
            --color-mtg: #ea580c; /* Orange 600 */
            --color-mtg-dark: #c2410c;
        }
        body { background-color: #f8fafc; color: #1e293b; }

        /* Utilitários Específicos */
        .mana-symbol { display: inline-block; width: 16px; height: 16px; border-radius: 50%; box-shadow: 1px 1px 2px rgba(0,0,0,0.3); }
        .mana-w { background: #fdf6d8; border: 1px solid #dcd3ac; }
        .mana-u { background: #c1d7e9; border: 1px solid #a8bfd3; }
        .mana-b { background: #bab1ab; border: 1px solid #a69f99; }
        .mana-r { background: #e49977; border: 1px solid #d28664; }
        .mana-g { background: #9ea491; border: 1px solid #89907f; }

        /* Estilo para o grupo de input + botão */
        .newsletter-row {
            display: flex; 
            gap: 10px; 
            justify-content: center; 
            max-width: 500px; 
            margin: 0 auto;
            width: 100%;
        }

        /* Regra para Celular */
        @media (max-width: 640px) {
            .newsletter-row {
                flex-direction: column; /* Empilha um em cima do outro */
                padding: 0 15px; /* Evita que encostem na borda física do cel */
            }
            .newsletter-input {
                width: 100% !important;
                flex: none !important; /* Desliga o flex:1 que estava "vazando" */
            }
            .newsletter-btn {
                width: 100% !important;
                padding: 15px !important; /* Botão maior no mobile é melhor pro toque */
            }
        }
    </style>    
    
    <div class="pt-36 pb-10 bg-gradient-to-b from-[#0f172a] to-[#f8fafc]">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-3xl md:text-4xl font-black text-white mb-2 italic">ENCONTRE SUA CARTA</h1>
            <p class="text-gray-400 mb-6 text-sm">Compare preços entre várias lojas e compre com segurança.</p>
            
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-orange-500 to-yellow-500 rounded-lg blur opacity-25 group-hover:opacity-75 transition duration-500"></div>
                <div class="relative flex">
                    <input type="text" class="w-full bg-white text-gray-900 border-0 rounded-l-lg px-6 py-4 focus:ring-2 focus:ring-orange-500 focus:outline-none shadow-xl text-lg placeholder-gray-400" placeholder="Digite o nome da carta (ex: Sheoldred, The One Ring)...">
                    <button class="bg-orange-600 hover:bg-orange-500 text-white px-8 rounded-r-lg font-bold uppercase tracking-wider transition shadow-xl">
                        Buscar
                    </button>
                </div>
            </div>
            
            <div class="mt-4 flex flex-wrap justify-center gap-2 text-xs text-gray-400">
                <span>Populares:</span>
                <a href="#" class="hover:text-orange-400 underline">Orcish Bowmasters</a>
                <a href="#" class="hover:text-orange-400 underline">Agatha's Soul Cauldron</a>
                <a href="#" class="hover:text-orange-400 underline">Mana Crypt</a>
            </div>
        </div>
    </div>

<main class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
<div class="lg:col-span-8 space-y-10">
{{-- 1. SEÇÃO DE NOTÍCIAS (Ajustada apenas para ocupar o espaço e ler os dados) --}}
<section class="lg:col-span-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <span class="w-1.5 h-6 bg-orange-500 rounded-full"></span>
            Últimas Notícias
        </h2>
        <a href="/noticias" class="text-xs font-bold text-orange-600 hover:underline">Ver tudo -></a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 h-96">
        
        {{-- ARTIGO DESTAQUE --}}
        @if(isset($noticias[0]))
        <article class="relative rounded-xl overflow-hidden group cursor-pointer md:row-span-2 shadow-sm border border-gray-200">
            <div class="absolute inset-0 bg-gray-800">
                <div class="w-full h-full bg-cover bg-center transition duration-700 group-hover:scale-105 opacity-60" 
                     style="background-image: url('{{ $noticias[0]->image }}')"></div>
            </div>
            <div class="absolute bottom-0 p-6 bg-gradient-to-t from-black via-black/80 to-transparent w-full">
                <span class="bg-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded mb-2 inline-block">
                    {{ strtoupper($noticias[0]->category) }}
                </span>
                <h3 class="text-2xl font-bold text-white leading-tight group-hover:text-orange-400 transition-colors">
                    {{ $noticias[0]->title }}
                </h3>
                <p class="text-gray-300 text-sm mt-2 line-clamp-2">
                    {{ $noticias[0]->excerpt }}
                </p>
            </div>
        </article>
        @endif

        {{-- ARTIGOS MENORES --}}
        @foreach($noticias->slice(1, 2) as $noticia)
        <article class="relative rounded-xl overflow-hidden group cursor-pointer shadow-sm border border-gray-200">
            <div class="absolute inset-0 bg-gray-800">
                <div class="w-full h-full bg-cover bg-center transition duration-700 group-hover:scale-105 opacity-60"
                     style="background-image: url('{{ $noticia->image }}')"></div>
            </div>
            <div class="absolute bottom-0 p-4 bg-gradient-to-t from-black to-transparent w-full">
                @if($noticia->category)
                    <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded mb-1 inline-block">
                        {{ strtoupper($noticia->category) }}
                    </span>
                @endif
                <h3 class="text-sm font-bold text-white leading-tight group-hover:text-orange-400">
                    {{ $noticia->title }}
                </h3>
            </div>
        </article>
        @endforeach
    </div>
</section> 
<section>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <span class="w-1.5 h-6 bg-purple-600 rounded-full"></span>
                Decks em Alta
        </h2>
    
        <div class="flex gap-2">
            <button class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-xs font-bold text-gray-700">Standard</button>
            <button class="px-3 py-1 bg-gray-800 text-white rounded text-xs font-bold shadow-md">Modern</button>
            <button class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-xs font-bold text-gray-700">Pioneer</button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-purple-400 transition cursor-pointer group">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold text-gray-500 uppercase">Modern</span>
                <div class="flex gap-0.5">
                    <span class="mana-symbol mana-u"></span>
                    <span class="mana-symbol mana-r"></span>
                </div>
            </div>
            <h3 class="font-bold text-gray-900 group-hover:text-purple-600">Izzet Murktide</h3>
            <p class="text-xs text-gray-500 mb-3">por <strong>AndreaMengucci</strong></p>
            <div class="flex justify-between items-end border-t border-gray-100 pt-2">
                <span class="text-xs text-green-600 font-bold bg-green-50 px-1.5 py-0.5 rounded">5-0 League</span>
                <span class="font-bold text-gray-900">R$ 3.250</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-purple-400 transition cursor-pointer group">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold text-gray-500 uppercase">Modern</span>
                <div class="flex gap-0.5">
                    <span class="mana-symbol mana-b"></span>
                    <span class="mana-symbol mana-g"></span>
                </div>
            </div>
            <h3 class="font-bold text-gray-900 group-hover:text-purple-600">Golgari Yawgmoth</h3>
            <p class="text-xs text-gray-500 mb-3">por <strong>Xerk</strong></p>
            <div class="flex justify-between items-end border-t border-gray-100 pt-2">
                <span class="text-xs text-blue-600 font-bold bg-blue-50 px-1.5 py-0.5 rounded">1º Challenge</span>
                <span class="font-bold text-gray-900">R$ 2.800</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-purple-400 transition cursor-pointer group">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold text-gray-500 uppercase">Modern</span>
                <div class="flex gap-0.5">
                    <span class="mana-symbol mana-w"></span>
                    <span class="mana-symbol mana-u"></span>
                    <span class="mana-symbol mana-b"></span>
                </div>
            </div>
            <h3 class="font-bold text-gray-900 group-hover:text-purple-600">Esper Control</h3>
            <p class="text-xs text-gray-500 mb-3">por <strong>Wafo-Tapa</strong></p>
            <div class="flex justify-between items-end border-t border-gray-100 pt-2">
                <span class="text-xs text-gray-500 font-bold bg-gray-100 px-1.5 py-0.5 rounded">Top 8</span>
                <span class="font-bold text-gray-900">R$ 4.100</span>
            </div>
        </div>
    </div>
</section>          
</div>    
        <aside class="lg:col-span-4 space-y-8">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-900 p-4 border-b border-gray-800 flex justify-between items-center">
                    <h3 class="text-white font-bold flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        Market Watch
                    </h3>
                    <select class="bg-gray-800 text-xs text-white border-none rounded px-2 py-1">
                        <option>Diário</option>
                        <option>Semanal</option>
                    </select>
                </div>
                
                <div class="flex text-xs font-bold border-b border-gray-100">
                    <button class="flex-1 py-2 bg-green-50 text-green-700 border-b-2 border-green-500">Alta (Up)</button>
                    <button class="flex-1 py-2 text-gray-500 hover:bg-gray-50">Baixa (Down)</button>
                    <button class="flex-1 py-2 text-gray-500 hover:bg-gray-50">Most Viewed</button>
                </div>

                <div class="divide-y divide-gray-100">
                    <div class="p-3 flex items-center justify-between hover:bg-gray-50 cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-200 rounded overflow-hidden">
                                </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 group-hover:text-orange-600 line-clamp-1">Sheoldred, the Apocalypse</p>
                                <p class="text-[10px] text-gray-500">Dominaria United</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">R$ 450,00</p>
                            <p class="text-[10px] font-bold text-green-600 flex items-center justify-end gap-0.5">
                                +12.5% <span class="text-[8px]">▲</span>
                            </p>
                        </div>
                    </div>

                    <div class="p-3 flex items-center justify-between hover:bg-gray-50 cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-200 rounded overflow-hidden"></div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 group-hover:text-orange-600 line-clamp-1">The One Ring</p>
                                <p class="text-[10px] text-gray-500">Tales of Middle-earth</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">R$ 380,00</p>
                            <p class="text-[10px] font-bold text-green-600 flex items-center justify-end gap-0.5">
                                +8.2% <span class="text-[8px]">▲</span>
                            </p>
                        </div>
                    </div>

                     <div class="p-3 flex items-center justify-between hover:bg-gray-50 cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-200 rounded overflow-hidden"></div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 group-hover:text-orange-600 line-clamp-1">Agatha's Soul Cauldron</p>
                                <p class="text-[10px] text-gray-500">Wilds of Eldraine</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">R$ 220,00</p>
                            <p class="text-[10px] font-bold text-green-600 flex items-center justify-end gap-0.5">
                                +5.1% <span class="text-[8px]">▲</span>
                            </p>
                        </div>
                    </div>

                    <a href="#" class="block p-2 text-center text-xs font-bold text-orange-600 hover:bg-orange-50 transition">
                        Ver Lista Completa
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h3 class="font-bold text-gray-900 mb-4 border-l-4 border-blue-500 pl-2">Selados Mais Vendidos</h3>
                <div class="space-y-4">
                    <div class="flex gap-3 group cursor-pointer">
                        <div class="w-16 h-20 bg-gray-200 rounded shrink-0"></div>
                        <div>
                            <p class="text-xs text-gray-500">Commander Deck</p>
                            <h4 class="text-sm font-bold text-gray-900 leading-tight group-hover:text-blue-600">Veloci-ramp-tor (Ixalan)</h4>
                            <p class="text-sm font-bold text-green-600 mt-1">R$ 450,00</p>
                        </div>
                    </div>
                    <div class="flex gap-3 group cursor-pointer">
                        <div class="w-16 h-20 bg-gray-200 rounded shrink-0"></div>
                        <div>
                            <p class="text-xs text-gray-500">Play Booster Box</p>
                            <h4 class="text-sm font-bold text-gray-900 leading-tight group-hover:text-blue-600">Murders at Karlov Manor</h4>
                            <p class="text-sm font-bold text-green-600 mt-1">R$ 890,00</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full h-64 bg-gray-200 rounded-xl flex items-center justify-center text-gray-400 text-sm border border-dashed border-gray-300">
                Publicidade (Espaço Parceiro)
            </div>

        </aside>       

    </main>
    <section class="bg-[#0f172a] text-white py-[60px] px-5 border-t border-white/10 font-sans text-left lg:text-center">
    
    <div class="max-w-[800px] mx-auto">
        
        <div class="inline-block py-1 px-3 rounded-full bg-[#f59e0b]/10 border border-[#f59e0b]/20 mb-5">
            <span class="text-[#f59e0b] text-[10px] font-black uppercase tracking-[2px]">
                Fique à frente do Meta
            </span>
        </div>

        <h2 class="text-[28px] font-black italic uppercase mb-[15px] leading-tight">
            Não perca o próximo <span class="text-[#f59e0b]">Spoiler</span>
        </h2>
        
        <p class="text-[#94a3b8] text-[14px] mb-[30px] leading-[1.6] max-w-[450px] lg:mx-auto">
            Receba alertas de variação de preços e decks que estão dominando o competitivo direto na sua inbox.
        </p>

        <div class="flex items-center gap-2 lg:gap-[10px] justify-start lg:justify-center max-w-[500px] lg:mx-auto">
            
            <input type="email" 
                   placeholder="Seu melhor e-mail..." 
                   class="flex-1 bg-black/30 border border-white/10 px-[15px] py-[12px] rounded-lg text-white outline-none min-w-0 text-sm focus:border-[#f59e0b]/50 transition-colors"
            >
            
            <button class="bg-[#f59e0b] text-black px-4 lg:px-[25px] rounded-lg font-black uppercase text-[11px] lg:text-[12px] cursor-pointer h-[48px] whitespace-nowrap hover:bg-[#f59e0b]/90 transition-colors shadow-lg shadow-[#f59e0b]/10">
                Inscrever-se
            </button>
            
        </div>
    </div>
</section>
</div>
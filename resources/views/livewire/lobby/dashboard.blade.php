<div>
    {{-- CABEÇALHO DO DASHBOARD --}}
    <div class="mb-8">
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Visão Geral da Coleção</h2>
        <p class="text-sm text-slate-500">Acompanhe seu patrimônio, fichários e o desempenho dos seus decks.</p>
    </div>

    {{-- 1. TOP CARDS (Métricas Principais - Ilhas Brancas) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Patrimônio Estimado</p>
            <div class="flex items-end gap-3">
                <h3 class="text-2xl font-black text-slate-900">R$ 14.250,00</h3>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-100 px-1.5 py-0.5 rounded flex items-center mb-1">
                    <i class="ph ph-trend-up mr-1"></i> 5%
                </span>
            </div>
            <p class="text-[10px] text-slate-400 mt-2">Baseado no Mínimo da Liga</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total de Itens</p>
            <div class="flex items-end gap-3">
                <h3 class="text-2xl font-black text-slate-900">2.450</h3>
                <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded mb-1">Cartas</span>
            </div>
            <p class="text-[10px] text-slate-400 mt-2">12 Pastas • 4 Decks</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Valorização (6 Meses)</p>
            <div class="h-12 w-full bg-gradient-to-t from-emerald-500/20 to-transparent border-b-2 border-emerald-500 mt-4 rounded-b-lg"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        {{-- COLUNA ESQUERDA (Maior) --}}
        <div class="xl:col-span-2 space-y-8">
            
            {{-- 2. RESUMO DOS FICHÁRIOS --}}
            <div>
                <div class="flex justify-between items-end mb-4">
                    <h3 class="text-xl font-bold text-slate-900">Meus Fichários</h3>
                    <button class="text-sm font-bold text-orange-500 hover:text-orange-600 transition-colors">+ Nova Pasta</button>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Fichário 9 Bolsos --}}
                    <div class="bg-slate-900 rounded-xl p-5 shadow-sm text-white relative overflow-hidden group cursor-pointer hover:-translate-y-1 transition-transform">
                        <div class="flex justify-between items-start mb-4">
                            <span class="bg-orange-500 text-[10px] font-black px-2 py-1 rounded uppercase tracking-wider">Trocas</span>
                        </div>
                        <h4 class="text-lg font-black">Trade Binder</h4>
                        <p class="text-xs text-slate-400 mt-1">R$ 4.200,00 • 45 Cartas</p>
                    </div>
                    
                    {{-- Fichário 12 Bolsos --}}
                    <div class="bg-blue-950 rounded-xl p-5 shadow-sm text-white relative overflow-hidden group cursor-pointer hover:-translate-y-1 transition-transform">
                        <div class="flex justify-between items-start mb-4">
                            <span class="bg-blue-500 text-[10px] font-black px-2 py-1 rounded uppercase tracking-wider">Pessoal</span>
                        </div>
                        <h4 class="text-lg font-black">Commander Staples</h4>
                        <p class="text-xs text-slate-400 mt-1">R$ 8.500,00 • 120 Cartas</p>
                    </div>

                    {{-- Fichário 4 Bolsos --}}
                    <div class="bg-yellow-950 rounded-xl p-5 shadow-sm text-yellow-50 relative overflow-hidden group cursor-pointer hover:-translate-y-1 transition-transform">
                        <div class="flex justify-between items-start mb-4">
                            <span class="bg-yellow-500 text-yellow-950 text-[10px] font-black px-2 py-1 rounded uppercase tracking-wider">Full Art</span>
                        </div>
                        <h4 class="text-lg font-black">Pokémon 151</h4>
                        <p class="text-xs text-yellow-600 mt-1">R$ 1.550,00 • 80 Cartas</p>
                    </div>
                </div>
            </div>

            {{-- 3. JOIAS DA COROA (Ilha Branca) --}}
            <div>
                <h3 class="text-xl font-bold text-slate-900 mb-4">Joias da Coroa (Top Value)</h3>
                <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-slate-200">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4">Carta</th>
                                <th class="px-6 py-4">Edição / Extra</th>
                                <th class="px-6 py-4 text-right">Valor Mín.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-200 rounded overflow-hidden"><img src="/api/placeholder/40/40" alt="Carta" class="w-full h-full object-cover"></div>
                                    Black Lotus
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">Alpha <span class="bg-slate-200 px-2 py-0.5 rounded text-[10px] ml-2 font-bold text-slate-600">MP</span></td>
                                <td class="px-6 py-4 text-right font-black text-emerald-600">R$ 55.000,00</td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-200 rounded overflow-hidden"><img src="/api/placeholder/40/40" alt="Carta" class="w-full h-full object-cover"></div>
                                    Blue-Eyes White Dragon
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">Legend of Blue Eyes <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px] ml-2 font-bold">1st Ed</span></td>
                                <td class="px-6 py-4 text-right font-black text-emerald-600">R$ 8.500,00</td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-200 rounded overflow-hidden"><img src="/api/placeholder/40/40" alt="Carta" class="w-full h-full object-cover"></div>
                                    Gaea's Cradle
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">Urza's Saga <span class="bg-slate-200 px-2 py-0.5 rounded text-[10px] ml-2 font-bold text-slate-600">NM</span></td>
                                <td class="px-6 py-4 text-right font-black text-emerald-600">R$ 4.500,00</td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-200 rounded overflow-hidden"><img src="/api/placeholder/40/40" alt="Carta" class="w-full h-full object-cover"></div>
                                    Umbreon VMAX
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">Evolving Skies <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded text-[10px] ml-2 font-bold">Alt Art</span></td>
                                <td class="px-6 py-4 text-right font-black text-emerald-600">R$ 3.800,00</td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-200 rounded overflow-hidden"><img src="/api/placeholder/40/40" alt="Carta" class="w-full h-full object-cover"></div>
                                    Charizard Base Set
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">Base Set <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded text-[10px] ml-2 font-bold">Holo</span></td>
                                <td class="px-6 py-4 text-right font-black text-emerald-600">R$ 2.100,00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- COLUNA DIREITA (Menor) --}}
        <div class="space-y-6">
            
            {{-- 4. DESEMPENHO DOS DECKS --}}
            <div>
                <div class="flex justify-between items-end mb-4">
                    <h3 class="text-xl font-bold text-slate-900">Desempenho (Decks)</h3>
                </div>

                {{-- Deck Campeão --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm mb-4">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-[10px] font-black uppercase text-emerald-600 bg-emerald-50 px-2 py-1 rounded">Maior Winrate</span>
                        <i class="ph ph-trophy text-emerald-500 text-xl"></i>
                    </div>
                    <h4 class="font-black text-slate-900 text-lg">Rakdos Scam (Modern)</h4>
                    <div class="flex justify-between items-center mt-4 border-t border-slate-100 pt-4">
                        <div class="text-sm font-bold text-slate-500">65 Partidas</div>
                        <div class="text-xl font-black text-emerald-600">68% <span class="text-xs text-slate-400 font-normal">Win</span></div>
                    </div>
                </div>

                {{-- Deck Lanterna --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-[10px] font-black uppercase text-red-600 bg-red-50 px-2 py-1 rounded">Pior Desempenho</span>
                        <i class="ph ph-warning-circle text-red-500 text-xl"></i>
                    </div>
                    <h4 class="font-black text-slate-900 text-lg">Dimir Faeries (Pauper)</h4>
                    <div class="flex justify-between items-center mt-4 border-t border-slate-100 pt-4">
                        <div class="text-sm font-bold text-slate-500">22 Partidas</div>
                        <div class="text-xl font-black text-red-500">31% <span class="text-xs text-slate-400 font-normal">Win</span></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
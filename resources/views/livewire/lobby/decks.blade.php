<div class="space-y-6">
    
    {{-- CABEÇALHO --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center">
                <i class="ph-fill ph-cards text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900">Meus Decks</h3>
                <p class="text-xs text-slate-500 font-medium">Gerencie suas listas e acompanhe seu desempenho em torneios.</p>
            </div>
        </div>
        
        <button class="bg-slate-900 hover:bg-orange-500 text-white font-black text-xs uppercase px-6 py-3 rounded-xl transition-all shadow-md shadow-slate-900/10 flex items-center justify-center gap-2">
            <i class="ph-bold ph-plus"></i> Novo Deck
        </button>
    </div>

    {{-- GRID DE DECKBOXES --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($meusDecks as $deck)
            @php
                $totalPartidas = $deck['vitorias'] + $deck['derrotas'];
                $winRate = $totalPartidas > 0 ? round(($deck['vitorias'] / $totalPartidas) * 100) : 0;
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col hover:border-slate-300 transition-colors group">
                
                {{-- Topo da Deckbox (Jogo e Formato) --}}
                <div class="{{ $deck['jogo_cor'] }} px-5 py-3 flex justify-between items-center text-white">
                    <span class="text-[9px] font-black uppercase tracking-widest opacity-90">{{ $deck['jogo'] }}</span>
                    <span class="text-[10px] font-bold bg-black/20 px-2 py-0.5 rounded backdrop-blur-sm">{{ $deck['formato'] }}</span>
                </div>

                {{-- Info Principal --}}
                <div class="p-5 flex-1">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="font-black text-slate-900 text-lg leading-tight">{{ $deck['nome'] }}</h4>
                            <div class="flex items-center gap-1 mt-2">
                                @foreach($deck['cores'] as $cor)
                                    <span class="w-3 h-3 rounded-full {{ $cor }} border border-slate-200/50 shadow-sm"></span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Status e Capacidade --}}
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
                        <span class="text-[10px] font-black uppercase px-2 py-1 rounded {{ $deck['status_cor'] }}">
                            {{ $deck['status'] }}
                        </span>
                        <span class="text-xs font-bold text-slate-500">
                            {{ $deck['cartas_atuais'] }} / {{ $deck['cartas_total'] }} Cartas
                        </span>
                    </div>

                    {{-- Estatísticas de Jogo --}}
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="bg-slate-50 rounded-lg p-2">
                            <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Vitórias</p>
                            <p class="text-lg font-black text-emerald-600">{{ $deck['vitorias'] }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-2">
                            <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Derrotas</p>
                            <p class="text-lg font-black text-red-500">{{ $deck['derrotas'] }}</p>
                        </div>
                        <div class="bg-slate-900 rounded-lg p-2 text-white shadow-inner">
                            <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Win Rate</p>
                            <p class="text-lg font-black">{{ $winRate }}%</p>
                        </div>
                    </div>
                </div>

                {{-- Rodapé de Ações --}}
                <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <button class="text-slate-400 hover:text-red-500 transition-colors" title="Excluir Deck">
                        <i class="ph-bold ph-trash text-lg"></i>
                    </button>
                    
                    <button class="flex items-center gap-2 text-xs font-bold text-slate-700 hover:text-orange-600 transition-colors">
                        Abrir Construtor <i class="ph-bold ph-arrow-right"></i>
                    </button>
                </div>

            </div>
        @endforeach
    </div>

</div>
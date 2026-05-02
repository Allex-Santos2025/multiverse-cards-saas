<div class="space-y-10">
    
    {{-- SESSÃO 1: MEUS FICHÁRIOS (A PRATELEIRA) --}}
    <section>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Meus Fichários</h2>
                <p class="text-xs text-slate-500 mt-1">Organize suas cartas fisicamente em pastas ou caixas infinitas.</p>
            </div>
            <button class="bg-slate-900 hover:bg-orange-500 text-white font-black text-xs uppercase px-5 py-2.5 rounded-xl transition-all shadow-md shadow-slate-900/10 flex items-center justify-center gap-2">
                <i class="ph-bold ph-plus"></i> Novo Fichário
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($ficharios as $pasta)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col hover:border-slate-300 transition-all group relative overflow-hidden">
                    
                    {{-- Detalhe da lombada da pasta --}}
                    <div class="absolute left-0 top-0 w-2 h-full {{ $pasta['cor'] }} opacity-80"></div>

                    <div class="pl-4">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-xl {{ $pasta['cor'] }} text-white flex items-center justify-center shadow-sm">
                                <i class="ph-bold {{ $pasta['icone'] }} text-xl"></i>
                            </div>
                            <span class="bg-slate-100 text-slate-500 text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded">
                                {{ $pasta['tipo'] }}
                            </span>
                        </div>

                        <h3 class="font-black text-slate-900 text-lg leading-tight mb-4">{{ $pasta['nome'] }}</h3>

                        <div class="space-y-1.5">
                            <div class="flex justify-between items-end">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lotação</span>
                                <span class="text-xs font-bold text-slate-700">{{ $pasta['cartas_atuais'] }} / {{ $pasta['capacidade'] }}</span>
                            </div>
                            @if($pasta['capacidade'] !== 'Ilimitada')
                                <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $pasta['progresso'] == '100%' ? 'bg-red-500' : 'bg-emerald-500' }} rounded-full" style="width: {{ $pasta['progresso'] }};"></div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-100 pl-4 flex justify-between items-center">
                        <button class="text-slate-400 hover:text-orange-500 transition-colors">
                            <i class="ph-bold ph-gear text-lg"></i>
                        </button>
                        <button class="text-xs font-bold text-slate-700 hover:text-orange-600 transition-colors flex items-center gap-1">
                            Abrir Pasta <i class="ph-bold ph-arrow-right"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- SESSÃO 2: PROGRESSO POR EDIÇÃO (COMPLETAR COLEÇÃO) --}}
    <section>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Completar Edições</h2>
                <p class="text-xs text-slate-500 mt-1">Acompanhe seu progresso para fechar os sets completos.</p>
            </div>
            
            {{-- Toggle 1x / 4x --}}
            <div class="flex items-center bg-white p-1 rounded-lg shadow-sm border border-slate-200">
                <button wire:click="setMeta('1x')" class="px-4 py-1.5 rounded text-xs font-bold transition-colors {{ $metaColecao === '1x' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                    Meta 1x (Singles)
                </button>
                <button wire:click="setMeta('4x')" class="px-4 py-1.5 rounded text-xs font-bold transition-colors {{ $metaColecao === '4x' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                    Meta 4x (Playsets)
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="divide-y divide-slate-100">
                @foreach($progressoEdicoes as $set)
                    @php
                        // Cálculos baseados na meta escolhida
                        if($metaColecao === '1x') {
                            $alvo = $set['total_cartas_set'];
                            $tidas = $set['tidas_1x'];
                        } else {
                            $alvo = $set['total_cartas_set'] * 4;
                            $tidas = $set['tidas_4x'];
                        }
                        $porcentagem = $alvo > 0 ? round(($tidas / $alvo) * 100) : 0;
                        $concluido = $porcentagem == 100;
                    @endphp

                    <div x-data="{ open: false }" class="p-4 flex flex-col hover:bg-slate-50 transition-colors">
                        
                        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                            {{-- Nome do Set e Sigla --}}
                            <div class="flex items-center gap-3 min-w-[300px]">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center font-black text-[10px] uppercase text-slate-500 shrink-0">
                                    {{ $set['set_sigla'] }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-sm leading-tight">{{ $set['set_nome'] }}</h3>
                                    <div class="flex gap-1 mt-1">
                                        @foreach($set['idiomas'] as $lang)
                                            <span class="text-[8px] font-black uppercase bg-slate-200 text-slate-600 px-1 rounded">{{ $lang }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Barra de Progresso --}}
                            <div class="flex-1 w-full lg:px-6">
                                <div class="flex justify-between items-end mb-1">
                                    <span class="text-[10px] font-bold text-slate-500">Progresso ({{ $metaColecao }})</span>
                                    <span class="text-xs font-black {{ $concluido ? 'text-emerald-600' : 'text-slate-900' }}">{{ $porcentagem }}%</span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $concluido ? 'bg-emerald-500' : 'bg-orange-500' }} rounded-full" style="width: {{ $porcentagem }}%;"></div>
                                </div>
                            </div>

                            {{-- Contadores e Botão --}}
                            <div class="flex items-center justify-between lg:justify-end gap-5 min-w-[200px] mt-2 lg:mt-0 pt-2 lg:pt-0 border-t lg:border-none border-slate-100">
                                <div class="text-right">
                                    <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Cartas</p>
                                    <p class="text-sm font-black text-slate-900">{{ $tidas }} / {{ $alvo }}</p>
                                </div>
                                <a href="{{ $set['url_vitrine'] }}" class="flex items-center gap-1.5 bg-slate-900 hover:bg-orange-500 text-white px-4 py-2 rounded-lg text-xs font-bold transition-colors shadow-sm">
                                    Ver Vitrine <i class="ph-bold ph-arrow-right"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    </section>

</div>
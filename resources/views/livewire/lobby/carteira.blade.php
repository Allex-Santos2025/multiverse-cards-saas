<div class="space-y-8">
    
    {{-- ILHA 1: SALDO E AÇÕES RÁPIDAS --}}
    <section class="bg-slate-900 rounded-3xl shadow-xl p-8 text-white relative overflow-hidden">
        {{-- Detalhe visual de fundo --}}
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/5 rounded-full blur-3xl"></div>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-8 relative z-10">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Saldo Total Versus</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-slate-500">R$</span>
                    <h2 class="text-5xl font-black tracking-tighter">{{ number_format($balance, 2, ',', '.') }}</h2>
                </div>
                <p class="text-[10px] text-emerald-400 font-bold mt-4 flex items-center gap-1">
                    <i class="ph ph-shield-check"></i> Saldo Seguro & Unificado
                </p>
            </div>

            <div class="flex flex-wrap gap-3 w-full md:w-auto">
                <button class="flex-1 md:flex-none bg-white text-slate-900 font-black text-xs uppercase px-8 py-4 rounded-2xl hover:bg-orange-500 hover:text-white transition-all shadow-lg">
                    <i class="ph-bold ph-plus mr-2"></i> Adicionar Crédito
                </button>
                <button class="flex-1 md:flex-none bg-slate-800 text-white border border-slate-700 font-black text-xs uppercase px-8 py-4 rounded-2xl hover:bg-slate-700 transition-all">
                    <i class="ph-bold ph-arrow-up-right mr-2"></i> Sacar Saldo
                </button>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        {{-- COLUNA ESQUERDA: EXTRATO (Ilha Branca) --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-black text-slate-900">Extrato de Movimentações</h3>
                    <button class="text-[10px] font-black uppercase text-slate-400 hover:text-orange-500">Ver tudo</button>
                </div>

                <div class="divide-y divide-slate-50">
                    @foreach($movimentacoes as $mov)
                        <div class="p-5 flex items-center justify-between hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $mov['tipo'] == 'entrada' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400' }}">
                                    <i class="ph-bold {{ $mov['tipo'] == 'entrada' ? 'ph-arrow-down-left' : 'ph-arrow-up-right' }} text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-900">{{ $mov['descricao'] }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $mov['loja'] }} • {{ $mov['data'] }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black {{ $mov['tipo'] == 'entrada' ? 'text-emerald-600' : 'text-slate-900' }}">
                                    {{ $mov['tipo'] == 'entrada' ? '+' : '-' }} R$ {{ number_format($mov['valor'], 2, ',', '.') }}
                                </p>
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $mov['status'] == 'Concluído' ? 'bg-slate-100 text-slate-500' : 'bg-orange-100 text-orange-600' }}">
                                    {{ $mov['status'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- COLUNA DIREITA: DADOS DE RECEBIMENTO --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center">
                        <i class="ph-bold ph-lightning text-xl"></i>
                    </div>
                    <h3 class="font-black text-slate-900">Receber via PIX</h3>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Sua Chave PIX</label>
                        <input wire:model.defer="pix_key" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all" placeholder="CPF, E-mail ou Aleatória">
                    </div>
                    <button class="w-full bg-slate-900 text-white font-black text-xs uppercase py-4 rounded-xl hover:bg-orange-600 transition-all">
                        Salvar Chave Pix
                    </button>
                    <p class="text-[10px] text-slate-400 text-center leading-relaxed px-4">
                        Esta chave será usada para todos os seus saques e estornos automáticos no sistema.
                    </p>
                </div>
            </div>

            {{-- Card de Ajuda --}}
            <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-500/20">
                <i class="ph ph-info text-3xl mb-4 opacity-50"></i>
                <h4 class="font-black text-lg leading-tight mb-2">Como funcionam os créditos?</h4>
                <p class="text-xs text-indigo-100 leading-relaxed opacity-80">
                    Você pode ganhar créditos indicando amigos, vendendo cartas (buylist) ou participando de eventos. Eles nunca expiram!
                </p>
            </div>
        </div>
    </div>
</div>
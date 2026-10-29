<div>
    {{-- CABEÇALHO DO DASHBOARD --}}
    <div class="mb-8">
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Visão Geral da Coleção</h2>
        <p class="text-sm text-slate-500">Acompanhe seu patrimônio, fichários e o desempenho dos seus decks.</p>
    </div>

    {{-- 1. TOP CARDS (Métricas Principais - Ilhas Brancas) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        {{-- CARD 1: PATRIMÔNIO ESTIMADO COM TREND REAL --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Patrimônio Estimado</p>
                <div class="flex items-end gap-3">
                    <h3 class="text-2xl font-black text-slate-900 font-mono tracking-tight">
                        R$ {{ number_format($totalMarketValue, 2, ',', '.') }}
                    </h3>
                    
                    @if($portfolioTrend === 'up')
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-100 px-1.5 py-0.5 rounded flex items-center mb-1">
                            <i class="ph ph-trend-up mr-1"></i> +{{ $portfolioDiffPct }}%
                        </span>
                    @elseif($portfolioTrend === 'down')
                        <span class="text-xs font-bold text-rose-600 bg-rose-100 px-1.5 py-0.5 rounded flex items-center mb-1">
                            <i class="ph ph-trend-down mr-1"></i> {{ $portfolioDiffPct }}%
                        </span>
                    @else
                        <span class="text-xs font-bold text-sky-600 bg-sky-100 px-1.5 py-0.5 rounded flex items-center mb-1">
                            <i class="ph ph-arrows-left-right mr-1"></i> 0.0%
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-3 pt-2 border-t border-slate-100">
                <span>Custo Investido: <strong class="text-slate-600 font-mono">R$ {{ number_format($totalInvested, 2, ',', '.') }}</strong></span>
                <span class="{{ $portfolioNominalGain >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-bold font-mono">
                    {{ $portfolioNominalGain >= 0 ? '+' : '' }}R$ {{ number_format($portfolioNominalGain, 2, ',', '.') }}
                </span>
            </div>
        </div>
        
        {{-- CARD 2: TOTAL DE ITENS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total de Itens</p>
                <div class="flex items-end gap-3">
                    <h3 class="text-2xl font-black text-slate-900 font-mono tracking-tight">
                        {{ number_format($totalCardsCount, 0, ',', '.') }}
                    </h3>
                    <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded mb-1">Cartas</span>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 mt-3 pt-2 border-t border-slate-100">
                {{ $totalBindersCount }} {{ $totalBindersCount === 1 ? 'Pasta' : 'Pastas' }} • {{ $totalDecksCount }} {{ $totalDecksCount === 1 ? 'Deck' : 'Decks' }}
            </p>
        </div>

        {{-- CARD 3: GRÁFICO DINÂMICO CLICÁVEL (HISTÓRICO FINANCEIRO) --}}
        <div wire:click="openHistoryModal" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between cursor-pointer hover:border-orange-400 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider group-hover:text-orange-600 transition">
                    Valorização da Coleção
                </p>
                <span class="text-[9px] font-extrabold text-orange-600 bg-orange-50 border border-orange-200 px-1.5 py-0.5 rounded flex items-center gap-1">
                    <i class="ph ph-chart-line-up"></i> Linha do Tempo &rarr;
                </span>
            </div>

            <div class="h-12 w-full mt-4 relative flex items-end">
                @if($portfolioTrend === 'up')
                    <svg class="w-full h-full text-emerald-500" viewBox="0 0 100 35" fill="none" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="grad-dash-up" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.35"/>
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,30 Q25,28 50,15 T100,4 L100,35 L0,35 Z" fill="url(#grad-dash-up)" />
                        <path d="M0,30 Q25,28 50,15 T100,4" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" fill="none" />
                    </svg>
                @elseif($portfolioTrend === 'down')
                    <svg class="w-full h-full text-rose-500" viewBox="0 0 100 35" fill="none" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="grad-dash-down" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#f43f5e" stop-opacity="0.35"/>
                                <stop offset="100%" stop-color="#f43f5e" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,6 Q25,8 50,22 T100,31 L100,35 L0,35 Z" fill="url(#grad-dash-down)" />
                        <path d="M0,6 Q25,8 50,22 T100,31" stroke="#f43f5e" stroke-width="2.5" stroke-linecap="round" fill="none" />
                    </svg>
                @else
                    <svg class="w-full h-full text-sky-500" viewBox="0 0 100 35" fill="none" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="grad-dash-flat" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#0ea5e9" stop-opacity="0.25"/>
                                <stop offset="100%" stop-color="#0ea5e9" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,18 L100,18 L100,35 L0,35 Z" fill="url(#grad-dash-flat)" />
                        <line x1="0" y1="18" x2="100" y2="18" stroke="#0ea5e9" stroke-width="2.5" stroke-dasharray="4 2" />
                    </svg>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. SEÇÃO DE FICHÁRIOS COM O MESMO DESIGN REALISTA DA TELA DE COLEÇÃO --}}
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Meus Fichários</h3>
                <p class="text-xs text-slate-500">Seus álbuns físicos cadastrados com capacidade e lotação atual.</p>
            </div>
            <button wire:click="$parent.switchAba('colecao')" class="text-xs font-bold text-orange-600 hover:text-orange-700 flex items-center gap-1 transition">
                <span>Ver Todos na Coleção</span> &rarr;
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse($binders as $binder)
                @php
                    $isBulk = $binder->pocket_layout === 'bulk';
                    $capacity = $binder->current_capacity;
                    $stored = (int) ($binder->slots_count ?? 0);
                    $percent = (!$isBulk && $capacity > 0) ? min(100, round(($stored / $capacity) * 100)) : 0;

                    $coverBg = match($binder->cover_material) {
                        'cover-blue' => 'bg-gradient-to-br from-blue-700 via-blue-900 to-slate-950 border border-blue-500',
                        'cover-box'  => 'bg-gradient-to-br from-amber-800 via-amber-950 to-stone-950 border border-amber-600',
                        default      => 'bg-gradient-to-br from-slate-800 via-slate-900 to-black border border-slate-700',
                    };

                    $strapBg = match($binder->strap_color) {
                        'cyan' => 'bg-sky-500 shadow-[1px_0_3px_rgba(0,0,0,0.5),-1px_0_3px_rgba(0,0,0,0.5)]',
                        'gold' => 'bg-amber-500 shadow-[1px_0_3px_rgba(0,0,0,0.5),-1px_0_3px_rgba(0,0,0,0.5)]',
                        default => 'bg-orange-500 shadow-[1px_0_3px_rgba(0,0,0,0.5),-1px_0_3px_rgba(0,0,0,0.5)]',
                    };
                @endphp

                <div wire:click="$parent.switchAba('colecao')" class="bg-white rounded-2xl p-4 md:p-5 border border-slate-200 shadow-sm flex flex-col justify-between cursor-pointer relative overflow-hidden group hover:border-slate-300 hover:-translate-y-0.5 hover:shadow-md transition duration-200">
                    <div class="flex gap-4 items-start pl-1">
                        
                        {{-- ÁLBUM FÍSICO COM TEXTURA, IMAGEM, LOMBADA E ELÁSTICO IDÊNTICOS À COLEÇÃO --}}
                        <div class="w-[86px] h-[116px] rounded-[10px] relative shadow-[-4px_6px_14px_rgba(0,0,0,0.35)] shrink-0 overflow-hidden transition-transform duration-300 group-hover:-translate-y-1 group-hover:scale-[1.03] {{ $coverBg }}">
                            @if($binder->cover_image_path)
                                <img src="{{ $binder->cover_image_path }}" 
                                     onerror="this.style.display='none'" 
                                     class="absolute inset-0 w-full h-full object-cover opacity-60">
                            @endif
                            
                            <div class="absolute inset-y-0 left-0 w-3 bg-gradient-to-r from-black/80 via-white/[0.08] to-black/60 border-r border-black/50"></div>
                            <div class="absolute inset-1 border border-dashed border-white/20 rounded-[7px] pointer-events-none"></div>
                            <div class="absolute top-1 right-1 w-2 h-2 border-t border-r border-amber-500 pointer-events-none"></div>
                            <div class="absolute bottom-1 right-1 w-2 h-2 border-b border-r border-amber-500 pointer-events-none"></div>
                            <div class="absolute inset-y-0 right-3 w-[7px] {{ $strapBg }}"></div>
                            
                            <div class="absolute inset-0 flex flex-col items-center justify-center p-1 text-center pointer-events-none">
                                <span class="text-[8px] font-black tracking-widest uppercase text-white bg-black/70 px-1 py-0.5 rounded border border-white/20">
                                    {{ $binder->pocket_layout }}
                                </span>
                                <span class="text-[7px] text-slate-200 font-bold mt-1 line-clamp-2 drop-shadow">
                                    {{ \Illuminate\Support\Str::limit($binder->name, 14) }}
                                </span>
                            </div>
                        </div>

                        {{-- DADOS DO FICHÁRIO --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ strtoupper($binder->pocket_layout) }}
                                </span>

                                @if($binder->is_locked_sheets)
                                    <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                                        <i class="ph-bold ph-lock-key"></i> Fixo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                        <i class="ph-bold ph-lock-key-open"></i> Expansível
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-sm font-extrabold text-slate-900 truncate group-hover:text-orange-600 transition">
                                {{ $binder->name }}
                            </h3>
                            <p class="text-[11px] text-slate-400 truncate">{{ $binder->description ?: 'Pasta do Acervo' }}</p>

                            <div class="mt-2.5 text-[10px] text-slate-500 font-medium">
                                <span class="text-slate-400 block">Capacidade de Fábrica:</span>
                                <strong class="text-slate-700 font-bold">
                                    {{ $isBulk ? 'Ilimitada' : "{$binder->factory_sheets_count} folhas ({$binder->factory_capacity} bolsos)" }}
                                </strong>
                            </div>

                            <div class="mt-2">
                                <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                                    <span class="text-slate-400 uppercase text-[9px]">Lotação</span>
                                    <span class="text-slate-700 font-mono font-extrabold">
                                        {{ $stored }} / {{ $isBulk ? '∞' : $capacity }}
                                    </span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $percent >= 100 ? 'bg-rose-500' : 'bg-emerald-500' }} rounded-full" style="width: {{ $isBulk ? 50 : $percent }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-3 mt-3 border-t border-slate-100 text-xs font-extrabold text-slate-800 group-hover:text-orange-600 transition">
                        <span>Acessar no Acervo</span> <i class="ph-bold ph-arrow-right ml-1"></i>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 rounded-2xl bg-white border border-dashed border-slate-200 text-center text-xs text-slate-400">
                    Nenhum fichário criado ainda. Vá até a aba <button wire:click="$parent.switchAba('colecao')" class="font-bold text-orange-600 underline">Minha Coleção</button> para criar seus álbuns.
                </div>
            @endforelse
        </div>
    </div>

    {{-- 3. JOIAS DA COROA & DESEMPENHO DOS DECKS --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        {{-- JOIAS DA COROA (TOP 5 MAIS VALIOSAS) --}}
        <div class="xl:col-span-2">
            <h3 class="text-xl font-bold text-slate-900 mb-4">Joias da Coroa (Top Value)</h3>
            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-slate-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4">Carta</th>
                            <th class="px-6 py-4">Edição / Extra</th>
                            <th class="px-6 py-4 text-right">Valor Pago</th>
                            <th class="px-6 py-4 text-right">Cotação Atual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($top5Cards as $card)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 flex items-center gap-3">
                                    <div class="w-9 h-12 bg-slate-200 rounded overflow-hidden shadow-sm shrink-0">
                                        <img src="{{ $card['image'] }}" alt="{{ $card['name'] }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 truncate leading-tight">{{ $card['name'] }}</div>
                                        <span class="text-[10px] text-slate-400 font-normal">#{{ $card['number'] }} &bull; {{ $card['quantity'] }}x no acervo</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-slate-700">{{ $card['set_code'] }}</span>
                                        <span class="bg-slate-100 px-1.5 py-0.5 rounded text-[10px] font-bold text-slate-600">
                                            {{ $card['condition'] }}
                                        </span>
                                        @foreach($card['extras'] as $extra)
                                            <span class="bg-amber-50 text-amber-600 border border-amber-200 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase">
                                                {{ $extra }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-xs text-slate-400 font-bold">
                                    R$ {{ number_format($card['paid_price'], 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono">
                                    <div class="font-black text-slate-900 text-sm">
                                        R$ {{ number_format($card['market_price'], 2, ',', '.') }}
                                    </div>
                                    @if($card['diff_trend'] === 'up')
                                        <span class="text-[10px] font-bold text-emerald-600 flex items-center justify-end gap-0.5">
                                            <i class="ph ph-trend-up"></i> +{{ $card['diff_pct'] }}%
                                        </span>
                                    @elseif($card['diff_trend'] === 'down')
                                        <span class="text-[10px] font-bold text-rose-600 flex items-center justify-end gap-0.5">
                                            <i class="ph ph-trend-down"></i> {{ $card['diff_pct'] }}%
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-400 flex items-center justify-end gap-0.5">
                                            <i class="ph ph-arrows-left-right"></i> 0.0%
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-400 text-xs font-bold">
                                    Nenhuma carta cadastrada no acervo ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- DESEMPENHO DOS DECKS (COM FALLBACK) --}}
        <div class="space-y-6">
            <div>
                <div class="flex justify-between items-end mb-4">
                    <h3 class="text-xl font-bold text-slate-900">Desempenho (Decks)</h3>
                </div>

                @if($totalDecksCount > 0)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm mb-4">
                        <span class="text-xs font-bold text-slate-700">Decks ativos cadastrados</span>
                    </div>
                @else
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm text-center flex flex-col items-center justify-center min-h-[260px]">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                            <i class="ph ph-cards text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm">Nenhum Deck Registrado</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-[220px]">
                            O módulo de montagem e métricas de winrate de decks estará disponível em breve.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL INTERATIVO: LINHA DO TEMPO FINANCEIRA --}}
    @if($modalHistoryOpen)
        <div class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-4xl w-full p-6 md:p-8 shadow-2xl relative border border-slate-200 max-h-[92vh] overflow-y-auto">
                <button type="button" wire:click="closeHistoryModal" class="absolute top-5 right-5 text-slate-400 hover:text-slate-700">
                    <i class="ph ph-x text-lg"></i>
                </button>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-orange-600 bg-orange-50 px-2 py-0.5 rounded border border-orange-200">
                            Linha do Tempo Patrimonial
                        </span>
                        <h3 class="text-xl font-black text-slate-900 mt-1">Evolução do Valor do Acervo</h3>
                        <p class="text-xs text-slate-500">Histórico de compras vs. cotação atualizada do mercado.</p>
                    </div>

                    {{-- SELETOR DE PERÍODO --}}
                    <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs font-bold">
                        <button type="button" wire:click="setPeriod('1M')" class="px-3 py-1.5 rounded-lg transition {{ $historyPeriod === '1M' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">1M</button>
                        <button type="button" wire:click="setPeriod('3M')" class="px-3 py-1.5 rounded-lg transition {{ $historyPeriod === '3M' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">3M</button>
                        <button type="button" wire:click="setPeriod('6M')" class="px-3 py-1.5 rounded-lg transition {{ $historyPeriod === '6M' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">6M</button>
                        <button type="button" wire:click="setPeriod('1Y')" class="px-3 py-1.5 rounded-lg transition {{ $historyPeriod === '1Y' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">1A</button>
                        <button type="button" wire:click="setPeriod('ALL')" class="px-3 py-1.5 rounded-lg transition {{ $historyPeriod === 'ALL' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">TUDO</button>
                    </div>
                </div>

                {{-- MÉTRICAS RESUMIDAS --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 my-6">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Custo Total de Entrada (Pago)</span>
                        <span class="text-lg font-black text-slate-800 font-mono">
                            R$ {{ number_format($totalInvested, 2, ',', '.') }}
                        </span>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Valor de Mercado Atual</span>
                        <span class="text-lg font-black text-slate-900 font-mono">
                            R$ {{ number_format($totalMarketValue, 2, ',', '.') }}
                        </span>
                    </div>

                    <div class="p-4 rounded-2xl border {{ $portfolioNominalGain >= 0 ? 'bg-emerald-50/70 border-emerald-200' : 'bg-rose-50/70 border-rose-200' }}">
                        <span class="text-[10px] font-bold {{ $portfolioNominalGain >= 0 ? 'text-emerald-600' : 'text-rose-600' }} uppercase block">Lucro / Prejuízo Teórico</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-lg font-black font-mono {{ $portfolioNominalGain >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $portfolioNominalGain >= 0 ? '+' : '' }}R$ {{ number_format($portfolioNominalGain, 2, ',', '.') }}
                            </span>
                            <span class="text-xs font-extrabold font-mono {{ $portfolioNominalGain >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                ({{ $portfolioDiffPct }}%)
                            </span>
                        </div>
                    </div>
                </div>

                {{-- GRÁFICO APEXCHARTS EM LINHA DO TEMPO --}}
                <div class="bg-white p-3 rounded-2xl border border-slate-200"
                     x-data="{
                        dates: @js($timelineDates),
                        invested: @js($timelineInvested),
                        market: @js($timelineMarket),
                        chart: null,
                        init() {
                            const options = {
                                series: [
                                    { name: 'Valor de Mercado', data: this.market },
                                    { name: 'Custo Pago (Investido)', data: this.invested }
                                ],
                                chart: {
                                    type: 'area',
                                    height: 320,
                                    fontFamily: 'inherit',
                                    toolbar: { show: false }
                                },
                                colors: ['#10b981', '#64748b'],
                                dataLabels: { enabled: false },
                                stroke: { curve: 'smooth', width: [3, 2] },
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        shadeIntensity: 1,
                                        opacityFrom: [0.4, 0.1],
                                        opacityTo: [0.05, 0.0],
                                        stops: [0, 95, 100]
                                    }
                                },
                                xaxis: {
                                    categories: this.dates,
                                    labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 600 } }
                                },
                                yaxis: {
                                    labels: {
                                        formatter: (val) => 'R$ ' + Number(val).toLocaleString('pt-BR', { minimumFractionDigits: 2 }),
                                        style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 600 }
                                    }
                                },
                                tooltip: {
                                    y: { formatter: (val) => 'R$ ' + Number(val).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }
                                },
                                legend: {
                                    position: 'top',
                                    horizontalAlign: 'right',
                                    fontSize: '12px',
                                    fontWeight: 700
                                }
                            };
                            this.chart = new ApexCharts(this.$refs.timelineChart, options);
                            this.chart.render();
                        }
                     }">
                    <div x-ref="timelineChart" class="w-full"></div>
                </div>

                <div class="mt-6 flex justify-end pt-4 border-t border-slate-100">
                    <button type="button" wire:click="closeHistoryModal" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-orange-600 text-white text-xs font-black transition shadow-sm">
                        Fechar Linha do Tempo
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

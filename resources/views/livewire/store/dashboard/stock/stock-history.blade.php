<div>
    @push('scripts')
    <script>
    function detailStockChart(dataRaw, selectedGames) {
        return {
            chart: null,
            init() {
                const isDark = document.documentElement.classList.contains('dark');
                
                if (!dataRaw || dataRaw.length === 0) return;

                // 1. MAPEAMENTO DE CORES (Identidade Visual do Versus)
                const colorMap = {
                    'Total Geral': '#10b981', // Verde
                    'Magic': '#ea580c',       // Laranja
                    'Pokémon': '#facc15',     // Amarelo
                    'Pokémon TCG': '#facc15',
                    'Yu-Gi-Oh!': '#a855f7',   // Roxo
                    'Yugioh': '#a855f7',
                    'Battle Scenes': '#dc2626'// Vermelho
                };

                // Helper do Tooltip (Padrão do Painel Principal: "Quarta, 15 Abr")
                const formatTooltipDate = (dateObj) => {
                    // Extrai o dia da semana ("quarta-feira") e o mês ("abril") por extenso
                    let wd = new Intl.DateTimeFormat('pt-BR', { weekday: 'long' }).format(dateObj);
                    let mm = new Intl.DateTimeFormat('pt-BR', { month: 'long' }).format(dateObj);
                    
                    // Pega o dia com dois dígitos ("15") e o ano com quatro ("2026")
                    let dd = String(dateObj.getDate()).padStart(2, '0');
                    let yyyy = dateObj.getFullYear();
                    
                    // Capitaliza a primeira letra do dia da semana
                    wd = wd.charAt(0).toUpperCase() + wd.slice(1);
                    
                    // Monta a string final exatamente igual ao seu print
                    return `${wd}, ${dd} de ${mm} de ${yyyy}`;
                };

                // Ordena os dados do banco do mais antigo pro mais novo para construir a linha do tempo
                dataRaw.sort((a, b) => a.snapshot_date.localeCompare(b.snapshot_date));

                // 2. CRIAÇÃO DA LINHA DO TEMPO CONTÍNUA (O Segredo do Gráfico de Ações)
                let startDate = new Date(dataRaw[0].snapshot_date.substring(0, 10) + 'T12:00:00');
                let endDate = new Date(); // Vai até o dia de hoje
                endDate.setHours(12, 0, 0, 0);

                let dict = {};
                dataRaw.forEach(s => { dict[s.snapshot_date.substring(0, 10)] = s; });

                let timeline = [];
                let lastKnown = { total: 0, games: {} };

                // Inicia o valor base
                let firstSnap = dataRaw[0];
                lastKnown.total = parseFloat(firstSnap.total_items) || 0;
                selectedGames.forEach(g => {
                    let bk = typeof firstSnap.game_breakdown === 'string' ? JSON.parse(firstSnap.game_breakdown) : firstSnap.game_breakdown;
                    lastKnown.games[g] = (bk && bk[g]) ? parseFloat(bk[g].qty) : 0;
                });

                // Anda dia a dia preenchendo os buracos com linha reta
                for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                    let iso = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');

                    if (dict[iso]) {
                        let s = dict[iso];
                        lastKnown.total = parseFloat(s.total_items) || 0;
                        selectedGames.forEach(g => {
                            let bk = typeof s.game_breakdown === 'string' ? JSON.parse(s.game_breakdown) : s.game_breakdown;
                            lastKnown.games[g] = (bk && bk[g]) ? parseFloat(bk[g].qty) : 0;
                        });
                    }

                    timeline.push({
                        dateObj: new Date(d),
                        iso: iso,
                        total: lastKnown.total,
                        games: { ...lastKnown.games }
                    });
                }

                // 3. MONTAGEM DAS LINHAS DO GRÁFICO (Séries e Cores)
                let series = [];
                let colors = [];

                series.push({ name: 'Total Geral', data: timeline.map(t => t.total) });
                colors.push(colorMap['Total Geral']);

                selectedGames.forEach(game => {
                    series.push({ name: game, data: timeline.map(t => t.games[game]) });
                    let matchedColor = '#64748b'; // Cinza genérico se for um jogo novo sem cor mapeada
                    Object.keys(colorMap).forEach(k => { if(game.includes(k)) matchedColor = colorMap[k]; });
                    colors.push(matchedColor);
                });

                let xLabels = timeline.map(t => String(t.dateObj.getDate()).padStart(2,'0') + '/' + String(t.dateObj.getMonth()+1).padStart(2,'0'));

                // 4. OPÇÕES DO APEXCHARTS
                const options = {
                    series: series,
                    colors: colors,
                    chart: { 
                        type: 'line', 
                        height: 350, 
                        background: 'transparent', 
                        fontFamily: 'Inter, sans-serif',
                        animations: { enabled: false }, // Desliga animação pesada para rolar gráfico de muitos meses suave
                        toolbar: { 
                            show: true, // Ativa a barra de Zoom e Pan (Navegação no tempo)
                            tools: { zoom: true, pan: true, reset: true, download: false }
                        }
                    },
                    stroke: { curve: 'straight', width: 3 }, // Linhas Retas (Step)
                    markers: { size: 0, hover: { size: 6 } }, // Esconde bolinhas, igual bolsa de valores
                    xaxis: { 
                        categories: xLabels,
                        tickAmount: 10, // Evita que os dias fiquem espremidos se houver meses de dados
                        labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontWeight: 600 } }
                    },
                    yaxis: { labels: { formatter: (v) => v.toLocaleString('pt-BR'), style: { colors: isDark ? '#94a3b8' : '#64748b', fontWeight: 600 } } },
                    theme: { mode: isDark ? 'dark' : 'light' },
                    grid: { borderColor: isDark ? '#334155' : '#e2e8f0', strokeDashArray: 4 },
                    
                    // TOOLTIP PERSONALIZADO (Padrão Versus TCG)
                    tooltip: { 
                        shared: true, 
                        intersect: false,
                        theme: isDark ? 'dark' : 'light',
                        custom: function({series, seriesIndex, dataPointIndex, w}) {
                            let pointData = timeline[dataPointIndex];
                            let dateStr = formatTooltipDate(pointData.dateObj);

                            let html = `<div class="p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl rounded-md text-xs font-medium min-w-[180px] z-50">`;
                            html += `<div class="text-slate-900 dark:text-white mb-2 pb-1 border-b border-slate-100 dark:border-slate-700">${dateStr}</div>`;

                            w.globals.seriesNames.forEach((sName, i) => {
                                let val = series[i][dataPointIndex];
                                let color = w.globals.colors[i];
                                html += `<div class="flex items-center justify-between gap-6 mt-1.5">
                                    <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                                        <span class="w-2 h-2 rounded-full shadow-sm" style="background-color: ${color}"></span> ${sName}
                                    </span>
                                    <span class="font-bold text-slate-900 dark:text-white">${val.toLocaleString('pt-BR')} un</span>
                                </div>`;
                            });

                            html += `</div>`;
                            return html;
                        }
                    }
                };

                this.chart = new ApexCharts(this.$refs.chartCanvas, options);
                this.chart.render();
            }
        }
    }
    </script>
    @endpush

    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        
        {{-- CABEÇALHO --}}
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Histórico Consolidado</h1>
                <p class="text-sm text-slate-500">Filtragem detalhada por jogo e evolução de estoque.</p>
            </div>
            <a href="{{ route('store.dashboard', ['slug' => $slug]) }}" wire:navigate class="px-3 py-1.5 text-xs font-bold text-sky-500 hover:text-sky-600 transition-colors flex items-center gap-1 bg-sky-50 dark:bg-sky-500/10 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Voltar
            </a>
        </div>

        {{-- FILTROS DE JOGOS DINÂMICOS (Na linha de baixo, alinhados à esquerda) --}}
        <div class="flex flex-wrap items-center justify-start gap-2 pt-2">
            @foreach($availableGames as $game)
                <button wire:click="toggleGame('{{ $game }}')" 
                    class="px-3 py-1.5 rounded-full text-xs font-bold transition-all border {{ in_array($game, $selectedGames) ? 'bg-sky-500 border-sky-500 text-white shadow-md shadow-sky-500/20' : 'bg-white dark:bg-[#1e293b] border-slate-200 dark:border-slate-700 text-slate-500 hover:border-sky-300' }}">
                    {{ $game }}
                </button>
            @endforeach
        </div>

        {{-- ÁREA DO GRÁFICO (Evolução Contínua) --}}
        @if(count($historico) > 0)
        <div x-data="detailStockChart({{ $historico->getCollection()->reverse()->values()->toJson() }}, {{ json_encode($selectedGames) }})" 
             class="bg-white dark:bg-[#1e293b] rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] transition-colors relative">
             {{-- Ícone indicando que tem navegação/zoom --}}
             <div class="absolute top-4 right-6 text-slate-300 dark:text-slate-600 pointer-events-none">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
             </div>
            <div x-ref="chartCanvas" class="-ml-2"></div>
        </div>
        @endif

        {{-- LÓGICA DE DENSIDADE DA TABELA --}}
        @php
            $gameCount = count($selectedGames);
            
            if ($gameCount <= 2) {
                $textSize = 'text-sm';
                $headSize = 'text-[11px]';
                $pad = 'px-6 py-4';
            } elseif ($gameCount <= 5) {
                $textSize = 'text-xs';
                $headSize = 'text-[10px]';
                $pad = 'px-4 py-3';
            } else {
                $textSize = 'text-[11px]';
                $headSize = 'text-[9px]';
                $pad = 'px-3 py-2';
            }
        @endphp

        {{-- TABELA ESTILO LIGA COM SCROLL HORIZONTAL --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-xl shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] border border-slate-200 dark:border-slate-700 overflow-hidden transition-all duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left {{ $textSize }} whitespace-nowrap transition-all duration-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 {{ $headSize }} uppercase font-black tracking-widest text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 transition-all duration-300">
                        <tr>
                            <th class="{{ $pad }} border-r border-slate-200 dark:border-slate-700 transition-all">Data da Foto</th>
                            @foreach($selectedGames as $game)
                                <th class="{{ $pad }} text-right border-l border-slate-200 dark:border-slate-700/50 transition-all" title="{{ $game }}">{{ mb_substr($game, 0, 3) }} (un)</th>
                                <th class="{{ $pad }} text-right transition-all" title="{{ $game }}">{{ mb_substr($game, 0, 3) }} (R$)</th>
                            @endforeach
                            <th class="{{ $pad }} text-right border-l-2 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white transition-all">TOTAL ITENS</th>
                            <th class="{{ $pad }} text-right text-slate-900 dark:text-white transition-all">VALOR TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($historico as $log)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="{{ $pad }} font-bold text-slate-900 dark:text-white border-r border-slate-100 dark:border-slate-700 transition-all">
                                    {{ \Carbon\Carbon::parse($log->snapshot_date)->format('d/m/Y') }}
                                </td>
                                
                                @foreach($selectedGames as $game)
                                    @php
                                        $breakdown = is_string($log->game_breakdown) ? json_decode($log->game_breakdown, true) : $log->game_breakdown;
                                        $gameData = $breakdown[$game] ?? ['qty' => 0, 'value' => 0];
                                    @endphp
                                    <td class="{{ $pad }} text-right border-l border-slate-100 dark:border-slate-700/30 text-slate-600 dark:text-slate-400 transition-all">
                                        {{ number_format($gameData['qty'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="{{ $pad }} text-right text-slate-400 dark:text-slate-500 transition-all">
                                        R$ {{ number_format($gameData['value'] ?? 0, 2, ',', '.') }}
                                    </td>
                                @endforeach

                                <td class="{{ $pad }} text-right font-black text-slate-900 dark:text-white border-l-2 border-slate-100 dark:border-slate-700 transition-all">
                                    {{ number_format($log->total_items, 0, ',', '.') }}
                                </td>
                                <td class="{{ $pad }} text-right font-black text-emerald-500 transition-all">
                                    R$ {{ number_format($log->total_value, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="{{ $pad }} py-12 text-center text-slate-400">
                                    Nenhum registro de estoque consolidado ainda. Volte ao Dashboard e clique em "Atualizar Gráficos".
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($historico->hasPages())
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700">
                    {{ $historico->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
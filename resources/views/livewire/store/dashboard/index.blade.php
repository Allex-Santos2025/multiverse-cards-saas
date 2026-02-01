@extends('layouts.dashboard')

@section('content')
@push('cards')
<style>
    /* Card Styles */
    .fi-card { 
        background-color: #ffffff; /* Branco Puro */
        border-radius: 0.75rem; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        
        /* Mantendo sua lógica de bordas finas nos 3 lados */
        border-top: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    .fi-card:hover {
        background-color: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .dark .fi-card { 
        background-color: #1e293b; 
        border-top: 1px solid #334155;
        border-right: 1px solid #334155;
        border-bottom: 1px solid #334155;; border-radius: 0.75rem; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
    }
    .game-btn.fi-card {
        width: 100% !important;
        max-width: none !important;
    }
     /* Estilo para a linha da tabela */
    .fi-table-row {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9; /* slate-100 */
        background-color: transparent;
    }

    /* Hover no Tema Claro */
    .fi-table-row:hover {
        background-color: rgba(100, 116, 139, 0.12) !important;
        cursor: pointer;
    }

    /* Ajustes para o Tema Escuro */
    .dark .fi-table-row {
        border-bottom-color: #334155; /* slate-700 */
    }

    /* Hover no Tema Escuro - Usando transparência para não "gritar" */
    .dark .fi-table-row:hover {
        background-color: rgba(51, 65, 85, 0.4); /* slate-700 com 40% opacidade */
    }
    /* Impedir que o card pai "levante" se você quiser focar só na linha */
    .fi-card-static:hover {
        transform: none !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
    }
    .apexcharts-datalabel-value {
        fill: #0f172a !important;   /* A "tinta" da fonte (Preto) */
        font-family: 'Inter';       /* A "família" da fonte */
        font-weight: 900;           /* O "peso" da fonte */
    }
    .dark .apexcharts-datalabel-value {
    fill: #fff !important;   /* A "tinta" da fonte (Preto) */
    font-family: 'Inter';       /* A "família" da fonte */
    font-weight: 900;           /* O "peso" da fonte */
}
/* Aplica a cor da borda baseada na variável do botão */
    .game-btn:hover {
        border-color: var(--game-color) !important; 
        box-shadow: 0 0 8px -2px var(--game-color);       
        background-color: rgba(var(--game-color-rgb), 0.05); /* Um leve fundo colorido opcional */
    }

    /* Ajuste para o tema claro: garante que o texto e a borda tenham contraste */
    .game-btn span {
        transition: color 0.3s;
    }   
    
</style>
@endpush
@push('scripts')
<script>
function salesAnalytics() {
    return {
        chart: null,
        init() {
            // Renderiza o gráfico ao carregar a página
            this.renderChart();

            // Escuta o evento de troca de tema para atualizar o gráfico sem dar refresh
            window.addEventListener('theme-changed', () => {
                if (this.chart) {
                    this.chart.destroy();
                }
                this.renderChart();
            });
        },
        renderChart() {
            const isDark = document.documentElement.classList.contains('dark');
            
            const options = {
                series: [
                    { name: 'Versus Marketplace', data: [31, 40, 28, 51, 42, 109, 100] },
                    { name: 'Loja Própria', data: [11, 32, 45, 32, 34, 52, 41] }
                ],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: { show: false },
                    background: 'transparent',
                    fontFamily: 'Inter, sans-serif'
                },
                colors: ['#ea580c', '#3b82f6'], // Suas cores: Laranja e Azul
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.2,
                        opacityTo: 0,
                        stops: [0, 90, 100]
                    }
                },
                grid: {
                    borderColor: isDark ? '#334155' : '#e2e8f0',
                    strokeDashArray: 4,
                    padding: { left: 20 }
                },
                theme: { mode: isDark ? 'dark' : 'light' },
                xaxis: {
                    categories: ["Seg", "Ter", "Qua", "Qui", "Sex", "Sab", "Dom"],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: isDark ? '#94a3b8' : '#64748b' } }
                },
                yaxis: {
                    labels: { style: { colors: isDark ? '#94a3b8' : '#64748b' } }
                },
                legend: { show: false }, // Escondemos porque você já fez a legenda no HTML
                tooltip: { x: { show: false } }
            };

            this.chart = new ApexCharts(document.querySelector("#salesChart"), options);
            this.chart.render();
        }
    }
}

function stockEvolution() {
        return {
            init() {
                const isDark = document.documentElement.classList.contains('dark');
                const options = {
                    series: [{
                        name: 'Estoque',
                        data: [85200, 85450, 85100, 85800, 85950, 86020, 86097]
                    }],
                    chart: {
                        type: 'line',
                        height: 250,
                        toolbar: { show: false },
                        background: 'transparent'
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 4,
                        colors: ['#10b981']
                    },
                    {{-- 
                        FOCO AQUI: 
                        shared: false e followCursor: true fazem o balão flutuar exatamente
                        onde o seu ponteiro está, sem "imposto" de ficar travado no topo.
                    --}}
                    tooltip: {
                        enabled: true,
                        theme: 'dark',
                        followCursor: true, 
                        shared: false,     
                        intersect: false,  
                        y: {
                            formatter: (v) => v.toLocaleString('pt-BR') + ' un',
                            title: { formatter: () => '' }
                        }
                    },
                    grid: {
                        borderColor: isDark ? '#334155' : '#f1f5f9',
                        yaxis: { lines: { show: true } },
                        xaxis: { lines: { show: false } }
                    },
                    xaxis: {
                        categories: ['25', '26', '27', '28', '29', '30', '31'],
                        labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontWeight: 600 } }
                    },
                    yaxis: {
                        min: 85000,
                        max: 86500,
                        tickAmount: 4,
                        labels: {
                            formatter: (v) => v.toLocaleString('pt-BR'),
                            style: { colors: isDark ? '#94a3b8' : '#64748b', fontWeight: 600 }
                        }
                    },
                    markers: {
                        size: 0,
                        hover: { size: 7 }
                    },
                    colors: ['#10b981']
                };

                new ApexCharts(this.$refs.evolutionCanvas, options).render();
            }
        }
    }
    function stockPieChart() {
        return {
            init() {
                const isDark = document.documentElement.classList.contains('dark');
                const options = {
                    series: [65, 20, 10, 5],
                    chart: {
                        type: 'donut',
                        height: 250,
                        background: 'transparent'
                    },
                    colors: ['#f97316', '#facc15', '#a855f7', '#64748b'],
                    labels: ['Magic', 'Pokémon', 'Yu-Gi-Oh!', 'Outros'],
                    stroke: { show: false },
                    dataLabels: { enabled: false },
                    legend: { show: false },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '78%',
                                labels: {
                                    show: true,
                                    name: { show: false },
                                    value: {
                                        show: true,
                                        fontSize: '30px',
                                        fontWeight: '900',
                                        {{-- CORRIGIDO: Preto no claro, Branco no escuro --}}
                                        color: isDark ? '#ffffff' : '#0f172a',
                                        offsetY: 10,
                                        formatter: () => '86k'
                                    },
                                    total: {
                                        show: true,
                                        {{-- CORRIGIDO: Sincronizado com o gráfico de evolução --}}
                                        formatter: () => '86k'
                                    }
                                }
                            }
                        }
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: (val) => val + '%'
                        }
                    }
                };

                new ApexCharts(this.$refs.pieCanvas, options).render();
            }
        }
    }
</script>

@endpush


<main class="flex-1 p-6 md:p-8 max-w-[1600px] mx-auto w-full space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
        <div class="fi-card p-5 border-l-4 border-l-orange-500 relative overflow-hidden group hover:bg-[#253045] transition-colors cursor-pointer">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aguardando Envio</p>
                    <h3 class="text-3xl font-black mt-1 text-slate-900 dark:text-white">12</h3>
                    <p class="text-xs text-orange-400 mt-1 font-medium">8 Marketplace / 4 Loja</p>
                </div>
                <div class="p-2 bg-orange-500/10 rounded-lg text-orange-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </div>
            </div>
        </div>
        <div class="fi-card p-5 border-l-4 border-l-blue-500 relative overflow-hidden group hover:bg-[#253045] transition-colors cursor-pointer">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Retirada Balcão</p>
                    <h3 class="text-3xl font-black mt-1 text-slate-900 dark:text-white">3</h3>
                    <p class="text-xs text-blue-400 mt-1 font-medium">Clientes na loja hoje</p>
                </div>
                <div class="p-2 bg-blue-500/10 rounded-lg text-blue-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
            </div>
        </div>
        <div class="fi-card p-5 border-l-4 border-l-purple-500 relative overflow-hidden group hover:bg-[#253045] transition-colors cursor-pointer">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Buylist (Aprovar)</p>
                        <h3 class="text-3xl font-black mt-1 text-slate-900 dark:text-white">5</h3>
                        <p class="text-xs text-purple-400 mt-1 font-medium">R$ 1.200,00 em compra</p>
                    </div>
                    <div class="p-2 bg-purple-500/10 rounded-lg text-purple-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
            <div class="fi-card p-5 border-l-4 border-l-green-500 relative overflow-hidden group hover:bg-[#253045] transition-colors cursor-pointer">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pré-Vendas</p>
                        <h3 class="text-3xl font-black mt-1 text-slate-900 dark:text-white">28</h3>
                        <p class="text-xs text-green-400 mt-1 font-medium">Foundations (Release: 15/11)</p>
                    </div>
                    <div class="p-2 bg-green-500/10 rounded-lg text-green-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="salesAnalytics()">
            <div class="lg:col-span-2 fi-card p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-slate-900 dark:text-white">Desempenho de Vendas (7 Dias)</h3>
                    <div class="flex gap-4 text-xs font-bold">
                        <span class="flex items-center gap-1 text-orange-500"><span class="w-2 h-2 rounded-full bg-orange-500"></span> Versus Marketplace</span>
                        <span class="flex items-center gap-1 text-blue-500"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Loja Própria</span>
                    </div>
                </div>
                <div id="salesChart" class="h-[300px]"></div>
            </div>
        
            <div class="lg:col-span-1 fi-card overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-transparent">
                    <h3 class="font-bold text-slate-900 dark:text-white uppercase text-xs tracking-widest">Últimos Pedidos</h3>
                    <a href="#" class="text-xs text-slate-400 hover:text-white">Ver Todos</a>
                </div>
                <div class="flex-1 overflow-y-auto max-h-[300px]">
                    <table class="w-full text-left text-sm text-slate-400">
                        <tbody>
                            <tr class="fi-table-row border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition-colors duration-200 group">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-orange-500" title="Marketplace"></span>
                                        <span class="font-mono text-slate-900 dark:text-white font-bold text-base">#9785</span>
                                    </div>
                                    <span class="text-[10px] text-slate-500">Há 5 min</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-bold text-slate-900 dark:text-white text-base">R$ 63,46</div>
                                    <span class="text-[10px] text-green-500 bg-green-500/10 px-1.5 rounded">Pago</span>
                                </td>
                            </tr>
                            <tr class="fi-table-row border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-colors group">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-blue-500" title="Loja Própria"></span>
                                        <span class="font-mono text-slate-900 dark:text-white font-bold text-base">#9784</span>
                                    </div>
                                    <span class="text-[10px] text-slate-500">Há 12 min</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-bold text-slate-900 dark:text-white text-base">R$ 150,00</div>
                                    <span class="text-[10px] text-orange-500 bg-orange-500/10 px-1.5 rounded">Processando</span>
                                </td>
                            </tr>
                            <tr class="fi-table-row border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-colors group">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-blue-500" title="Loja Própria"></span>
                                        <span class="font-mono text-slate-900 dark:text-white font-bold text-base">#9783</span>
                                    </div>
                                    <span class="text-[10px] text-slate-500">Há 45 min</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-bold text-slate-900 dark:text-white text-base">R$ 25,00</div>
                                    <span class="text-[10px] text-slate-500 bg-slate-700 px-1.5 rounded">Enviado</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Card 1: Evolução do Estoque --}}
            <div x-data="stockEvolution()" class="fi-card p-6 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-[#1e293b] transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <h3 class="font-bold text-slate-900 dark:text-white mb-4 uppercase text-xs tracking-widest">Evolução do Estoque (Itens)</h3>
                <div x-ref="evolutionCanvas" class="h-[250px]"></div>
            </div>
    
            {{-- Card 2: Estoque por Jogo --}}
            <div x-data="stockPieChart()" class="fi-card p-6 flex flex-col md:flex-row items-center gap-8 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-[#1e293b] transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="flex-1 w-full">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4 uppercase text-xs tracking-widest">Estoque por Jogo</h3>
                    {{-- Gráfico Donut --}}
                    <div x-ref="pieCanvas" class="h-[250px]"></div>
                </div>
    
            <div class="w-full md:w-48 space-y-3 text-sm">
                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                    <span class="flex items-center gap-2 font-medium">
                        <span class="w-3 h-3 rounded-full bg-orange-500"></span> Magic
                    </span>
                    <span class="font-bold text-slate-900 dark:text-white">65%</span>
                </div>
        
                {{-- CORRIGIDO: Bolinha amarela para Pokémon --}}
                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                    <span class="flex items-center gap-2 font-medium">
                        <span class="w-3 h-3 rounded-full bg-yellow-400"></span> Pokémon
                    </span>
                    <span class="font-bold text-slate-900 dark:text-white">20%</span>
                </div>

                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                    <span class="flex items-center gap-2 font-medium">
                        <span class="w-3 h-3 rounded-full bg-purple-500"></span> Yu-Gi-Oh!
                    </span>
                    <span class="font-bold text-slate-900 dark:text-white">10%</span>
                </div>

                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                    <span class="flex items-center gap-2 font-medium">
                        <span class="w-3 h-3 rounded-full bg-gray-500"></span> Outros
                    </span>
                    <span class="font-bold text-slate-900 dark:text-white">5%</span>
                </div>
            </div>
        </div>
    </div>
    <div class="pt-4">
        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Cadastrar Estoque (Entrada Rápida)
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <button class="game-btn fi-card p-6 flex flex-col items-center justify-center gap-3 transition-all border border-transparent group"
            style="--game-color: #ea580c">
                <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center shadow-lg shadow-orange-600/20 group-hover:scale-110 transition-transform">
                    <span class="font-black text-white text-xs">MTG</span>
                </div>
                <span class="font-bold text-slate-300 group-hover:text-white">Magic</span>
            </button>

            <button class="game-btn fi-card p-6 flex flex-col items-center justify-center gap-3 transition-all border border-transparent group" style="--game-color: #facc15">
                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center shadow-lg shadow-yellow-500/20 group-hover:scale-110 transition-transform">
                    <span class="font-black text-black text-xs">PKM</span>
                </div>
                <span class="font-bold text-slate-300 group-hover:text-white">Pokémon</span>
            </button>

            <button class="game-btn fi-card p-6 flex flex-col items-center justify-center gap-3 transition-all border border-transparent group" style="--game-color: #a855f7">
                <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center shadow-lg shadow-purple-600/20 group-hover:scale-110 transition-transform">
                    <span class="font-black text-white text-xs">YGO</span>
                </div>
                <span class="font-bold text-slate-300 group-hover:text-white">Yu-Gi-Oh!</span>
            </button>

            <button class="game-btn fi-card p-6 flex flex-col items-center justify-center gap-3 transition-all border border-transparent group" style="--game-color: #dc2626">
                <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center shadow-lg shadow-red-600/20 group-hover:scale-110 transition-transform">
                    <span class="font-black text-white text-xs">BS</span>
                </div>
                <span class="font-bold text-slate-300 group-hover:text-white">Battle Scenes</span>
            </button>

            <button class="game-btn fi-card p-6 flex flex-col items-center justify-center gap-3 transition-all border border-transparent group" style="--game-color: #64748b">
                <div class="w-12 h-12 bg-slate-600 rounded-full flex items-center justify-center shadow-lg shadow-slate-600/20 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                <span class="font-bold text-slate-300 group-hover:text-white">Outros Games</span>
            </button>
        </div>
    </div>
</main>
@endsection
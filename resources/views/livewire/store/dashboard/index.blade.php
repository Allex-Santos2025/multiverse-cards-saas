<div>
@push('scripts')
<script>
function salesAnalytics() {
    return {
        chart: null,
        init() {
            this.renderChart();
            window.addEventListener('theme-changed', () => {
                if (this.chart) this.chart.destroy();
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
                chart: { type: 'area', height: 300, toolbar: { show: false }, background: 'transparent', fontFamily: 'Inter, sans-serif' },
                colors: ['#ea580c', '#3b82f6'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.2, opacityTo: 0, stops: [0, 90, 100] } },
                grid: { borderColor: isDark ? '#334155' : '#e2e8f0', strokeDashArray: 4, padding: { left: 20 } },
                theme: { mode: isDark ? 'dark' : 'light' },
                xaxis: { categories: ["Seg", "Ter", "Qua", "Qui", "Sex", "Sab", "Dom"], axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { colors: isDark ? '#94a3b8' : '#64748b' } } },
                yaxis: { labels: { style: { colors: isDark ? '#94a3b8' : '#64748b' } } },
                legend: { show: false },
                tooltip: { x: { show: false } }
            };

            this.chart = new ApexCharts(document.querySelector("#salesChart"), options);
            this.chart.render();
        }
    }
}

function stockEvolution(snapshotsRaw) {
    return {
        chart: null,
        mode: 'items',
        shortLabels: [],
        currItems: [], prevItems: [],
        currVals: [], prevVals: [],
        currDates: [], prevDates: [],
        dateRangeCurr: '', dateRangePrev: '',

        init() {
            const formatDateFull = (dateObj) => {
                let ext = new Intl.DateTimeFormat('pt-BR', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' }).format(dateObj);
                return ext.charAt(0).toUpperCase() + ext.slice(1);
            };

            let dict = {};
            if (snapshotsRaw && snapshotsRaw.length > 0) {
                snapshotsRaw.forEach(s => {
                    let dStr = s.snapshot_date.substring(0, 10);
                    dict[dStr] = { items: parseFloat(s.total_items) || 0, val: parseFloat(s.total_value) || 0 };
                });
            }

            let days = [];
            let today = new Date();
            today.setHours(12, 0, 0, 0); 
            for(let i = 13; i >= 0; i--) {
                let d = new Date(today);
                d.setDate(today.getDate() - i);
                let iso = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                days.push({ date: d, iso: iso });
            }

            let lastKnown = { items: 0, val: 0 };
            if (snapshotsRaw && snapshotsRaw.length > 0) {
                let sorted = [...snapshotsRaw].sort((a,b) => a.snapshot_date.localeCompare(b.snapshot_date));
                lastKnown = { items: parseFloat(sorted[0].total_items) || 0, val: parseFloat(sorted[0].total_value) || 0 };
            }

            let processed14 = [];
            days.forEach(dayInfo => {
                if (dict[dayInfo.iso]) {
                    lastKnown = dict[dayInfo.iso]; 
                }
                processed14.push({
                    dateObj: dayInfo.date,
                    fullStr: formatDateFull(dayInfo.date),
                    items: lastKnown.items,
                    val: lastKnown.val
                });
            });

            let prev = processed14.slice(0, 7);
            let curr = processed14.slice(7, 14);

            this.shortLabels = curr.map(d => String(d.dateObj.getDate()).padStart(2, '0') + '/' + String(d.dateObj.getMonth()+1).padStart(2, '0'));
            
            this.currDates = curr.map(d => d.fullStr);
            this.prevDates = prev.map(d => d.fullStr);
            this.currItems = curr.map(d => d.items);
            this.prevItems = prev.map(d => d.items);
            this.currVals = curr.map(d => d.val);
            this.prevVals = prev.map(d => d.val);

            this.dateRangeCurr = curr[0].dateObj.toLocaleDateString('pt-BR') + ' — ' + curr[6].dateObj.toLocaleDateString('pt-BR');
            this.dateRangePrev = prev[0].dateObj.toLocaleDateString('pt-BR') + ' — ' + prev[6].dateObj.toLocaleDateString('pt-BR');

            this.renderChart();

            window.addEventListener('theme-changed', () => {
                if (this.chart) { this.chart.destroy(); this.renderChart(); }
            });
        },

        toggleMode() {
            this.mode = this.mode === 'items' ? 'value' : 'items';
            const newCurr = this.mode === 'items' ? this.currItems : this.currVals;
            const newPrev = this.mode === 'items' ? this.prevItems : this.prevVals;
            
            const validData = [...newCurr, ...newPrev];
            let newMin = 0, newMax = 100;
            
            let min = Math.min(...validData);
            let max = Math.max(...validData);
            
            if (min === max) {
                newMin = Math.floor(min * 0.95);
                newMax = Math.ceil(max * 1.05) || 10;
            } else {
                newMin = Math.floor(min * 0.98);
                newMax = Math.ceil(max * 1.02);
            }
            
            this.chart.updateSeries([{ data: newCurr }, { data: newPrev }]);
            this.chart.updateOptions({
                yaxis: {
                    min: newMin, max: newMax,
                    labels: { formatter: (v) => this.mode === 'items' ? v.toLocaleString('pt-BR') : 'R$ ' + v.toLocaleString('pt-BR', {minimumFractionDigits: 2}) }
                }
            });
        },

        renderChart() {
            const isDark = document.documentElement.classList.contains('dark');
            const dataCurr = this.mode === 'items' ? this.currItems : this.currVals;
            const dataPrev = this.mode === 'items' ? this.prevItems : this.prevVals;
            
            const validData = [...dataCurr, ...dataPrev];
            let minVal = 0, maxVal = 100;
            
            let min = Math.min(...validData);
            let max = Math.max(...validData);
            
            if (min === max) {
                minVal = Math.floor(min * 0.95);
                maxVal = Math.ceil(max * 1.05) || 10;
            } else {
                minVal = Math.floor(min * 0.98);
                maxVal = Math.ceil(max * 1.02);
            }

            const self = this;
            
            // As Cores Mágicas: Verde pro Atual, Cinza Dinâmico pro Fantasma
            const colorCurr = '#10b981'; // emerald-500
            const colorPrev = isDark ? '#475569' : '#cbd5e1'; // slate-600 para Dark, slate-300 para Light

            const options = {
                series: [
                    { name: 'Atual', data: dataCurr },
                    { name: 'Anterior', data: dataPrev }
                ],
                chart: { type: 'line', height: 260, toolbar: { show: false }, background: 'transparent', fontFamily: 'Inter, sans-serif' },
                stroke: { curve: 'straight', width: [3, 3], colors: [colorCurr, colorPrev] },
                markers: { size: [4, 4], colors: [colorCurr, colorPrev], strokeColors: isDark ? '#1e293b' : '#ffffff', strokeWidth: 2, hover: { size: 6 } },
                grid: { borderColor: isDark ? '#334155' : '#f1f5f9', yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
                xaxis: { categories: this.shortLabels, labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontWeight: 600 } } },
                yaxis: {
                    min: minVal, max: maxVal, tickAmount: 4,
                    labels: { formatter: (v) => v.toLocaleString('pt-BR'), style: { colors: isDark ? '#94a3b8' : '#64748b', fontWeight: 600 } }
                },
                legend: { show: false },
                
                tooltip: {
                    shared: true, intersect: false, theme: isDark ? 'dark' : 'light',
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        let cDate = self.currDates[dataPointIndex];
                        let cVal = series[0][dataPointIndex];
                        let pDate = self.prevDates[dataPointIndex];
                        let pVal = series[1][dataPointIndex];
                        
                        let formatVal = (v) => self.mode === 'items' ? v.toLocaleString('pt-BR') : 'R$ ' + v.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        let labelTxt = self.mode === 'items' ? 'Itens:' : 'Valor:';

                        return `<div class="p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl rounded-md text-xs font-medium z-50 min-w-[200px]">
                            <div class="text-slate-900 dark:text-white mb-1">${cDate}</div>
                            <div class="flex items-center justify-between gap-4 text-emerald-500 mb-2">
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full" style="background-color: ${colorCurr}"></span> Atual</span>
                                <span class="font-bold">${formatVal(cVal)}</span>
                            </div>
                            <div class="text-slate-900 dark:text-white mb-1 pt-2 border-t border-slate-100 dark:border-slate-700">${pDate}</div>
                            <div class="flex items-center justify-between gap-4 text-slate-500 dark:text-slate-400">
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full" style="background-color: ${colorPrev}"></span> Anterior</span>
                                <span class="font-bold">${formatVal(pVal)}</span>
                            </div>
                        </div>`;
                    }
                }
            };

            this.chart = new ApexCharts(this.$refs.evolutionCanvas, options);
            this.chart.render();
        }
    }
}

function stockPieChart(breakdownData) {
    return {
        labels: [], seriesQty: [], seriesVal: [], colors: [],
        
        init() {
            const isDark = document.documentElement.classList.contains('dark');
            const colorMap = { 'Magic': '#ea580c', 'Pokémon': '#facc15', 'Pokémon TCG': '#facc15', 'Yu-Gi-Oh!': '#a855f7', 'Yugioh': '#a855f7', 'Battle Scenes': '#dc2626' };

            if (breakdownData && Object.keys(breakdownData).length > 0) {
                for (const [game, data] of Object.entries(breakdownData)) {
                    this.labels.push(game);
                    this.seriesQty.push(parseFloat(data.qty) || 0); 
                    this.seriesVal.push(parseFloat(data.value) || 0);
                    let matchedColor = '#64748b';
                    Object.keys(colorMap).forEach(k => { if(game.includes(k)) matchedColor = colorMap[k]; });
                    this.colors.push(matchedColor);
                }
            }

            const self = this;
            const options = {
                series: this.seriesQty, labels: this.labels, colors: this.colors,
                chart: { type: 'pie', height: 280, background: 'transparent', fontFamily: 'Inter, sans-serif', dropShadow: { enabled: true, top: 2, left: 2, blur: 3, opacity: isDark ? 0.3 : 0.1 } },
                stroke: { show: false }, dataLabels: { enabled: false }, legend: { show: false }, 
                tooltip: {
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        let gameName = w.globals.labels[seriesIndex];
                        let items = self.seriesQty[seriesIndex];
                        let val = self.seriesVal[seriesIndex];
                        return `<div class="p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl rounded-md text-sm z-50">
                            <div class="font-black text-slate-900 dark:text-white mb-2 border-b border-slate-100 dark:border-slate-700 pb-1">${gameName}</div>
                            <div class="text-slate-600 dark:text-slate-300">Valor Total: <span class="font-bold text-sky-500">R$ ${val.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span></div>
                            <div class="text-slate-600 dark:text-slate-300">Itens Total: <span class="font-bold">${items.toLocaleString('pt-BR')} un</span></div>
                        </div>`;
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
    
    {{-- OS 4 CARDS SUPERIORES --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] transition-all duration-300 ease-in-out hover:-translate-y-[2px] hover:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1)] hover:bg-[#f8fafc] dark:hover:bg-[#253045] p-5 border-l-4 border-l-orange-500 relative overflow-hidden group cursor-pointer">
            <div class="flex justify-between items-start">
                <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aguardando Envio</p><h3 class="text-3xl font-black mt-1 text-slate-900 dark:text-white">12</h3><p class="text-xs text-orange-400 mt-1 font-medium">8 Marketplace / 4 Loja</p></div>
                <div class="p-2 bg-orange-500/10 rounded-lg text-orange-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg></div>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] transition-all duration-300 ease-in-out hover:-translate-y-[2px] hover:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1)] hover:bg-[#f8fafc] dark:hover:bg-[#253045] p-5 border-l-4 border-l-blue-500 relative overflow-hidden group cursor-pointer">
            <div class="flex justify-between items-start">
                <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Retirada Balcão</p><h3 class="text-3xl font-black mt-1 text-slate-900 dark:text-white">3</h3><p class="text-xs text-blue-400 mt-1 font-medium">Clientes na loja hoje</p></div>
                <div class="p-2 bg-blue-500/10 rounded-lg text-blue-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] transition-all duration-300 ease-in-out hover:-translate-y-[2px] hover:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1)] hover:bg-[#f8fafc] dark:hover:bg-[#253045] p-5 border-l-4 border-l-purple-500 relative overflow-hidden group cursor-pointer">
            <div class="flex justify-between items-start">
                <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Buylist (Aprovar)</p><h3 class="text-3xl font-black mt-1 text-slate-900 dark:text-white">5</h3><p class="text-xs text-purple-400 mt-1 font-medium">R$ 1.200,00 em compra</p></div>
                <div class="p-2 bg-purple-500/10 rounded-lg text-purple-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] transition-all duration-300 ease-in-out hover:-translate-y-[2px] hover:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1)] hover:bg-[#f8fafc] dark:hover:bg-[#253045] p-5 border-l-4 border-l-green-500 relative overflow-hidden group cursor-pointer">
            <div class="flex justify-between items-start">
                <div><p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pré-Vendas</p><h3 class="text-3xl font-black mt-1 text-slate-900 dark:text-white">28</h3><p class="text-xs text-green-400 mt-1 font-medium">Foundations (Release: 15/11)</p></div>
                <div class="p-2 bg-green-500/10 rounded-lg text-green-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            </div>
        </div>
    </div>

    {{-- BLOCO 2: VENDAS E PEDIDOS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="salesAnalytics()">
        <div class="lg:col-span-2 bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] p-6 transition-all duration-300 ease-in-out hover:-translate-y-[2px] hover:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1)]">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-slate-900 dark:text-white">Desempenho de Vendas (7 Dias)</h3>
                <div class="flex gap-4 text-xs font-bold">
                    <span class="flex items-center gap-1 text-orange-500"><span class="w-2 h-2 rounded-full bg-orange-500"></span> Versus Marketplace</span>
                    <span class="flex items-center gap-1 text-blue-500"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Loja Própria</span>
                </div>
            </div>
            <div id="salesChart" class="h-[300px]"></div>
        </div>
    
        <div class="lg:col-span-1 bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] overflow-hidden flex flex-col transition-all duration-300 ease-in-out hover:-translate-y-[2px] hover:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1)]">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-transparent">
                <h3 class="font-bold text-slate-900 dark:text-white uppercase text-xs tracking-widest">Últimos Pedidos</h3>
                <a href="#" class="text-xs text-slate-400 hover:text-white transition-colors">Ver Todos</a>
            </div>
            <div class="flex-1 overflow-y-auto max-h-[300px]">
                <table class="w-full text-left text-sm text-slate-400">
                    <tbody>
                        <tr class="border-b border-slate-100 dark:border-slate-700 bg-transparent transition-all duration-200 hover:bg-slate-100 dark:hover:bg-slate-700/40 cursor-pointer group">
                            <td class="px-4 py-3"><div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-orange-500"></span><span class="font-mono text-slate-900 dark:text-white font-bold text-base">#9785</span></div><span class="text-[10px] text-slate-500">Há 5 min</span></td>
                            <td class="px-4 py-3 text-right"><div class="font-bold text-slate-900 dark:text-white text-base">R$ 63,46</div><span class="text-[10px] text-green-500 bg-green-500/10 px-1.5 rounded">Pago</span></td>
                        </tr>
                        <tr class="border-b border-slate-100 dark:border-slate-700 bg-transparent transition-all duration-200 hover:bg-slate-100 dark:hover:bg-slate-700/40 cursor-pointer group">
                            <td class="px-4 py-3"><div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-500"></span><span class="font-mono text-slate-900 dark:text-white font-bold text-base">#9784</span></div><span class="text-[10px] text-slate-500">Há 12 min</span></td>
                            <td class="px-4 py-3 text-right"><div class="font-bold text-slate-900 dark:text-white text-base">R$ 150,00</div><span class="text-[10px] text-orange-500 bg-orange-500/10 px-1.5 rounded">Processando</span></td>
                        </tr>
                        <tr class="border-b border-slate-100 dark:border-slate-700 bg-transparent transition-all duration-200 hover:bg-slate-100 dark:hover:bg-slate-700/40 cursor-pointer group">
                            <td class="px-4 py-3"><div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-500"></span><span class="font-mono text-slate-900 dark:text-white font-bold text-base">#9783</span></div><span class="text-[10px] text-slate-500">Há 45 min</span></td>
                            <td class="px-4 py-3 text-right"><div class="font-bold text-slate-900 dark:text-white text-base">R$ 25,00</div><span class="text-[10px] text-slate-500 bg-slate-700 px-1.5 rounded">Enviado</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- BLOCO 3: ESTOQUE --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Card 1: Evolução do Estoque (Verde de volta e Cinza Dinâmico) --}}
        <div x-data="stockEvolution({{ isset($snapshots) ? $snapshots->toJson() : 'null' }})" class="bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] p-6 transition-all duration-300 ease-in-out hover:-translate-y-[2px] hover:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1)] flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm tracking-wide border-l-4 border-sky-500 pl-3">Evolução do Estoque</h3>
                    <div class="text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></div>
                </div>
                <div x-ref="evolutionCanvas" class="h-[260px] -ml-2"></div>
                
                <div class="flex flex-col items-center justify-center mt-2 gap-1 text-xs font-bold font-mono">
                    <div class="flex items-center gap-2 text-emerald-500">
                        <span class="flex items-center relative"><span class="w-3 h-3 rounded-full border-2 border-emerald-500 bg-white dark:bg-slate-800 z-10"></span><span class="absolute w-8 h-[2px] bg-emerald-500 -left-2.5"></span></span>
                        <span x-text="dateRangeCurr"></span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                        <span class="flex items-center relative"><span class="w-3 h-3 rounded-full border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 z-10"></span><span class="absolute w-8 h-[2px] bg-slate-300 dark:bg-slate-600 -left-2.5"></span></span>
                        <span x-text="dateRangePrev"></span>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <button @click="toggleMode()" type="button" class="px-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-600 text-[10px] uppercase font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-sky-500 dark:hover:text-sky-400 transition-colors">
                        <span x-text="mode === 'items' ? 'Gráfico valor estoque (R$)' : 'Gráfico Quantidade (un)'"></span>
                    </button>
                    <a href="{{ route('store.dashboard.stock.history', ['slug' => $slug]) }}" class="px-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-600 text-[10px] uppercase font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-sky-500 dark:hover:text-sky-400 transition-colors">Histórico</a>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-medium text-slate-400">Dados consolidados em: {{ isset($snapshots) && $snapshots->isNotEmpty() ? $snapshots->first()->updated_at->format('d/m/Y H:i') : '--' }}</span>
                    <button wire:click="refreshStockData" class="text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors" title="Forçar Atualização Agora">
                        <svg class="w-4 h-4" wire:loading.class="animate-spin text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Card 2: Estoque por Categoria (PIZZA CENTRALIZADA E FONTE MENOR) --}}
        <div x-data="stockPieChart({{ isset($snapshots) && $snapshots->isNotEmpty() ? json_encode($snapshots->first()->game_breakdown) : 'null' }})" class="bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] p-6 flex flex-col items-start transition-all duration-300 ease-in-out hover:-translate-y-[2px] hover:shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1)]">
            
            <div class="flex justify-between items-center mb-6 w-full">
                <h3 class="font-semibold text-slate-900 dark:text-white text-sm tracking-wide border-l-4 border-sky-500 pl-3">Estoque por Categoria</h3>
                <div class="text-slate-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg></div>
            </div>

            <div class="w-full flex-1 flex flex-col md:flex-row items-center justify-center gap-8 lg:gap-12 px-4">
                
                {{-- Div da Pizza (Ocupa 50% e joga pro meio) --}}
                <div class="w-full md:w-1/2 flex justify-center md:justify-end">
                    <div x-ref="pieCanvas" class="w-full max-w-[280px]"></div>
                </div>

                {{-- Div da Legenda (Ocupa 50% com font-xs) --}}
                <div class="w-full md:w-1/2 flex flex-col items-center md:items-start space-y-3 text-xs font-bold text-slate-600 dark:text-slate-300">
                    <template x-for="(label, index) in labels" :key="index">
                        <div class="flex justify-between items-center w-full max-w-[220px]">
                            <span class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full shadow-sm" :style="'background-color: ' + colors[index % colors.length]"></span> 
                                <span x-text="label"></span>
                            </span>
                            <span class="text-slate-900 dark:text-white" x-text="((seriesQty[index] / seriesQty.reduce((a,b)=>a+b,0)) * 100).toFixed(0) + '%'"></span>
                        </div>
                    </template>
                </div>
                
            </div>
        </div>
    </div>

    {{-- BLOCO 4: BOTÕES RÁPIDOS --}}
    <div class="pt-4">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Cadastrar Estoque (Entrada Rápida)
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <a href="{{ route('store.dashboard.stock.index', ['slug' => $slug, 'game_slug' => 'magic']) }}" wire:navigate class="w-full bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] p-6 flex flex-col items-center justify-center gap-3 transition-all duration-300 ease-in-out group hover:-translate-y-[2px] hover:bg-orange-500/5 hover:border-orange-500 hover:shadow-[0_0_8px_-2px_#ea580c]">
                <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center shadow-lg shadow-orange-600/20 group-hover:scale-110 transition-transform duration-300"><span class="font-black text-white text-xs">MTG</span></div>
                <span class="font-bold text-slate-500 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">Magic</span>
            </a>
            <a href="{{ route('store.dashboard.stock.index', ['slug' => $slug, 'game_slug' => 'pokemon']) }}" wire:navigate class="w-full bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] p-6 flex flex-col items-center justify-center gap-3 transition-all duration-300 ease-in-out group hover:-translate-y-[2px] hover:bg-yellow-400/5 hover:border-yellow-400 hover:shadow-[0_0_8px_-2px_#facc15]">
                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center shadow-lg shadow-yellow-500/20 group-hover:scale-110 transition-transform duration-300"><span class="font-black text-black text-xs">PKM</span></div>
                <span class="font-bold text-slate-500 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">Pokémon</span>
            </a>
            <button class="w-full bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] p-6 flex flex-col items-center justify-center gap-3 transition-all duration-300 ease-in-out group hover:-translate-y-[2px] hover:bg-purple-500/5 hover:border-purple-500 hover:shadow-[0_0_8px_-2px_#a855f7]">
                <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center shadow-lg shadow-purple-600/20 group-hover:scale-110 transition-transform duration-300"><span class="font-black text-white text-xs">YGO</span></div>
                <span class="font-bold text-slate-500 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">Yu-Gi-Oh!</span>
            </button>
            <button class="w-full bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] p-6 flex flex-col items-center justify-center gap-3 transition-all duration-300 ease-in-out group hover:-translate-y-[2px] hover:bg-red-600/5 hover:border-red-600 hover:shadow-[0_0_8px_-2px_#dc2626]">
                <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center shadow-lg shadow-red-600/20 group-hover:scale-110 transition-transform duration-300"><span class="font-black text-white text-xs">BS</span></div>
                <span class="font-bold text-slate-500 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">Battle Scenes</span>
            </button>
            <button class="w-full bg-white dark:bg-[#1e293b] rounded-xl border-t border-r border-b border-slate-200 dark:border-slate-700 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.05)] dark:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] p-6 flex flex-col items-center justify-center gap-3 transition-all duration-300 ease-in-out group hover:-translate-y-[2px] hover:bg-slate-500/5 hover:border-slate-500 hover:shadow-[0_0_8px_-2px_#64748b]">
                <div class="w-12 h-12 bg-slate-600 rounded-full flex items-center justify-center shadow-lg shadow-slate-600/20 group-hover:scale-110 transition-transform duration-300"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg></div>
                <span class="font-bold text-slate-500 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition-colors">Outros Games</span>
            </button>
        </div>
    </div>
</main>
</div>
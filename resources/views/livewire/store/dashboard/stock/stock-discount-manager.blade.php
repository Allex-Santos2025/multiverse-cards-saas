<div>
    <div x-data="stockPreview()">
    <div class="space-y-6">
        {{-- 1. CABEÇALHO E BUSCA --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-colors duration-300">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <i class="ph-fill ph-tag text-orange-500 text-3xl"></i>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">Central de Promoções</h2>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-0.5">Gestão de descontos em massa</p>
                    </div>
                </div>
                
                {{-- BARRA DE INPUT --}}
                <div class="flex items-center gap-3 w-full md:w-96 relative z-10">
                    <div class="relative flex-grow">
                        <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-lg"></i>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar no estoque..." 
                            class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 dark:text-white placeholder-gray-400 transition-all shadow-sm text-sm">
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. BARRA DE APLICAÇÃO EM LOTE --}}
        <div x-data="{ massPercent: '', massStart: '', massEnd: '' }" 
            class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-wrap gap-6 items-end transition-colors duration-300">
            
            <div class="flex flex-wrap items-center gap-6">
                <div class="flex flex-col">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Desconto (%)</label>
                    <div class="flex items-center gap-2">
                        <input type="number" x-model="massPercent" placeholder="0" class="w-20 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-center font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                    </div>
                </div>
                <div class="flex flex-col">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Período de Validade</label>
                    <div class="flex items-center gap-2">
                        <input type="date" x-model="massStart" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all w-36">
                        <span class="text-gray-400 font-bold px-1">até</span>
                        <input type="date" x-model="massEnd" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all w-36">
                    </div>
                </div>
            </div>
            
            <button type="button" @click="$dispatch('mass-update', { percent: massPercent, start: massStart, end: massEnd })" 
                class="bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition-colors shadow-sm ml-auto md:ml-0 lg:ml-auto">
                Aplicar na Tela
            </button>
        </div>

        {{-- 3. TABELA DE ITENS --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-colors duration-300">
            <form x-data="{
                submitDiscounts() {
                    let fd = new FormData($el);
                    let data = { items: {} };
                    for (let [k, v] of fd.entries()) {
                        let match = k.match(/items\[(\d+)\]\[(\w+)\]/);
                        if (match) {
                            let id = match[1]; let field = match[2];
                            if (!data.items[id]) data.items[id] = {};
                            data.items[id][field] = v;
                        }
                    }
                    $wire.saveDiscounts(data);
                }
            }" @submit.prevent="submitDiscounts()">
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Desconto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Período</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estoque</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Preço Final</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Info</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Extras</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-full">Produto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($items as $item)
                                @php
                                    $isCard = (bool)$item->catalog_print_id;
                                    $print = $item->catalogPrint;
                                    $product = $item->catalogProduct;
                                    $rarity = strtolower($print->rarity ?? 'common');
                                    $textColor = match($rarity) {
                                        'common' => 'text-gray-500 dark:text-gray-400',
                                        'uncommon' => 'text-slate-500 dark:text-slate-400',
                                        'rare' => 'text-yellow-600 dark:text-yellow-500',
                                        'mythic' => 'text-orange-600 dark:text-orange-500',
                                        default => 'text-gray-500 dark:text-gray-400',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                    x-data="{ 
                                        percent: {{ (float)$item->discount_percent }}, 
                                        price: {{ $item->price }},
                                        start: '{{ $item->discount_start ? \Carbon\Carbon::parse($item->discount_start)->format('Y-m-d') : '' }}',
                                        end: '{{ $item->discount_end ? \Carbon\Carbon::parse($item->discount_end)->format('Y-m-d') : '' }}',
                                        get finalPrice() { return (this.price - (this.price * (this.percent / 100))).toFixed(2); }
                                    }"
                                    @mass-update.window="percent = $event.detail.percent; start = $event.detail.start; end = $event.detail.end;">
                                    
                                    {{-- Desconto Input --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="relative rounded-md shadow-sm w-20">
                                            <input type="number" step="0.1" name="items[{{ $item->id }}][percent]" x-model="percent" 
                                                class="block w-full rounded-md border-0 py-1.5 pr-6 pl-2 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:text-sm dark:bg-gray-700 dark:text-white dark:ring-gray-600 text-center font-medium">
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                <span class="text-gray-500 sm:text-sm">%</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Período Inputs --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <input type="date" name="items[{{ $item->id }}][start]" x-model="start" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 text-sm text-gray-900 dark:text-white outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 w-32">
                                            <input type="date" name="items[{{ $item->id }}][end]" x-model="end" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-2 py-1.5 text-sm text-gray-900 dark:text-white outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 w-32">
                                        </div>
                                    </td>

                                    {{-- Estoque --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-300 font-medium">
                                        {{ $item->quantity }}
                                    </td>

                                    {{-- Preço Final --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-xs text-gray-500 dark:text-gray-400 line-through" x-show="percent > 0">R$ {{ number_format($item->price, 2, ',', '.') }}</span>
                                            <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="'R$ ' + finalPrice.replace('.', ',')"></span>
                                        </div>
                                    </td>

                                    {{-- Info (Idioma/Condição) --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">{{ $item->language ?? 'EN' }}</span>
                                            <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase mt-0.5">{{ $item->condition }}</span>
                                        </div>
                                    </td>

                                    {{-- Extras --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex flex-wrap justify-center gap-1.5">
                                            @if($isCard && $item->extras)
                                                @foreach($item->extras as $extra)
                                                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 px-2 py-0.5 rounded uppercase">{{ $extra }}</span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Produto/Card Info --}}                                
                                    <td class="px-6 py-4" :class="sidebarCollapsed ? 'max-w-[250px] lg:max-w-[400px]' : 'max-w-[160px] lg:max-w-[280px]'">
                                        @if($isCard)
                                            <div class="flex items-center gap-3 {{ $textColor }} min-w-0">
                                                {{-- Ícone Protegido (shrink-0 impede de amassar) --}}
                                                <div class="relative w-6 h-6 flex items-center justify-center shrink-0">
                                                    <x-set-symbol :path="''" :code="$print->set->code ?? ''" :rarity="$rarity" size="w-6 h-6" />
                                                </div>
                                                
                                                {{-- Textos --}}
                                                <div class="flex items-baseline gap-2 min-w-0 w-full">
                                                    {{-- O nome da carta ganha o truncate (os três pontinhos) --}}
                                                    <span class="text-blue-600 dark:text-blue-400 font-medium tracking-tight text-[15px] cursor-help hover:underline transition-all truncate block" 
                                                        @mouseenter="showPreview('{{ asset($print->image_path) }}', 'card', '', $event)" 
                                                        @mouseleave="hidePreview()" @mousemove="movePreview($event)">
                                                        {{ $print->printed_name ?? $print->concept->name }}
                                                    </span>
                                                    {{-- O número da coleção fica protegido --}}
                                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 font-normal shrink-0">({{ $print->set->code }} #{{ $print->collector_number }})</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-3 min-w-0">
                                                {{-- Imagem Protegida --}}
                                                <div class="w-8 h-8 bg-white dark:bg-gray-900 rounded flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-700 shrink-0">
                                                    <img src="{{ asset($product->image_path) }}" class="w-full h-full object-contain p-0.5">
                                                </div>
                                                
                                                {{-- O nome do produto ganha os três pontinhos --}}
                                                <span class="text-blue-600 dark:text-blue-400 font-medium text-[15px] cursor-help hover:underline transition-all truncate block w-full"
                                                    @mouseenter="showPreview('{{ asset($product->image_path) }}', 'product', '{{ $product->name }}', $event)" 
                                                    @mouseleave="hidePreview()" @mousemove="movePreview($event)">
                                                    {{ $product->name }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="ph ph-ghost text-4xl mb-2 text-gray-300 dark:text-gray-600"></i>
                                            <span>Nenhum item encontrado.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 4. RODAPÉ COM PAGINAÇÃO E BOTÃO SALVAR --}}
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center bg-gray-50 dark:bg-gray-800 gap-4">
                    <div class="w-full md:w-auto text-sm text-gray-600 dark:text-gray-400">
                        {{ $items->links() }}
                    </div>
                    
                    <button type="submit" wire:loading.attr="disabled" class="w-full md:w-auto relative inline-flex items-center justify-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition-all text-sm">
                        <span wire:loading.remove wire:target="saveDiscounts">Salvar Promoções</span>
                        <span wire:loading wire:target="saveDiscounts" class="flex items-center gap-2">
                            <i class="ph ph-circle-notch animate-spin"></i> Salvando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 5. PREVIEWS FLUTUANTES (Mantido idêntico) --}}
    <div x-ref="previewContainer" class="fixed pointer-events-none z-[99999] opacity-0 transition-opacity duration-200" style="display: none;">
        <template x-if="previewType === 'card'">
            <img :src="previewUrl" class="w-64 h-auto rounded-[12px] border-[3px] border-[#ea580c] shadow-[0_0_40px_rgba(234,88,12,0.5)] bg-black">
        </template>
        <template x-if="previewType === 'product'">
            <div class="w-60 p-3 bg-white dark:bg-[#1e293b] rounded-2xl shadow-2xl border border-gray-200 dark:border-slate-700">
                <div class="aspect-square bg-gray-50 dark:bg-[#0f172a] rounded-xl flex items-center justify-center overflow-hidden p-4">
                    <img :src="previewUrl" class="w-full h-full object-contain drop-shadow-2xl">
                </div>
                <p class="mt-3 text-center text-[11px] font-black text-gray-900 dark:text-white leading-tight uppercase tracking-tighter" x-text="previewName"></p>
            </div>
        </template>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('stockPreview', () => ({
        show: false,
        previewUrl: '',
        previewType: '',
        previewName: '',
        showPreview(url, type, name, event) {
            this.previewUrl = url;
            this.previewType = type;
            this.previewName = name;
            this.show = true;
            this.$refs.previewContainer.style.display = 'block';
            this.$refs.previewContainer.style.opacity = '1';
            this.movePreview(event);
        },
        hidePreview() {
            this.show = false;
            this.$refs.previewContainer.style.opacity = '0';
            setTimeout(() => { if(!this.show) this.$refs.previewContainer.style.display = 'none'; }, 200);
        },
        movePreview(event) {
            const container = this.$refs.previewContainer;
            const offset = 25;
            let top = event.clientY + offset;
            let left = event.clientX + offset;
            if (left + 280 > window.innerWidth) left = event.clientX - 290;
            if (top + 380 > window.innerHeight) top = window.innerHeight - 390;
            container.style.top = `${top}px`;
            container.style.left = `${left}px`;
        }
    }))
});
</script>
</div>
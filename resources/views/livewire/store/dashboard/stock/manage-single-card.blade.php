<div class="p-4 md:p-8 min-h-screen bg-white dark:bg-[#0f172a] text-gray-900 dark:text-gray-100 transition-colors duration-300"
     x-data="{ showModal: @entangle('showModal') }">

    {{-- Estilo global para esconder os scrolls mantendo a rolagem ativa --}}
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- Header / Breadcrumb (Mantido Original) --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-[10px] uppercase font-black text-gray-400 dark:text-zinc-500 mb-4 tracking-widest font-sans">
            <a href="{{ route('store.dashboard', ['slug' => $slug]) }}" class="hover:text-orange-500 transition-colors">Administração</a>
            <i class="ph ph-caret-right"></i>
            <span>Estoque</span>
            <i class="ph ph-caret-right"></i>
            <span class="text-orange-500">{{ $concept->name }}</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50 dark:bg-[#1e293b]/50 p-6 rounded-2xl border border-gray-200 dark:border-white/5 backdrop-blur-sm shadow-sm transition-all">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white italic uppercase tracking-tighter leading-none font-sans">
                    Gerenciar <span class="text-orange-500">Cards</span>
                </h1>
                <p class="text-[10px] text-gray-400 dark:text-zinc-500 mt-2 font-bold uppercase tracking-widest font-sans">{{ $concept->name }} / {{ $concept->prints->count() }} versões</p>
            </div>
            <button wire:click="$set('showModal', true)" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl font-black uppercase text-[10px] tracking-widest shadow-lg flex items-center justify-center gap-2 transition-all font-sans">
                <i class="ph-bold ph-plus-circle text-lg"></i> Adicionar Novo
            </button>
        </div>
    </div>

    {{-- Tabela (Mantida Original) --}}
    <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-white/5 shadow-xl overflow-hidden transition-all">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 dark:bg-black/20 border-b border-gray-200 dark:border-white/5">
                    <tr class="text-[9px] font-black uppercase text-gray-500 dark:text-zinc-500 tracking-widest font-sans">
                        <th class="px-6 py-4">Est.</th>
                        <th class="px-4 py-4 text-center">Desconto</th>
                        <th class="px-4 py-4 text-center">Validade</th>
                        <th class="px-6 py-4">Preço</th>
                        <th class="px-4 py-4 text-center">Idioma</th>
                        <th class="px-4 py-4">Qualidade</th>
                        <th class="px-4 py-4 text-center">Extras</th>
                        <th class="px-6 py-4">Edição / Print</th>
                        <th class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($stockItems as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors group text-[11px] font-sans">
                            <td class="px-6 py-4 font-black text-blue-600 dark:text-blue-500 text-base">{{ $item->quantity }}</td>
                            <td class="px-4 py-4 text-center">
                                @if($item->discount_percent > 0)
                                    <span class="bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 px-2 py-1 rounded text-[10px] font-black">-{{ $item->discount_percent }}%</span>
                                @else
                                    <span class="opacity-20 font-bold">0%</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($item->discount_start)
                                    <div class="flex flex-col text-[9px] font-bold text-gray-400 leading-tight uppercase">
                                        <span>{{ \Carbon\Carbon::parse($item->discount_start)->format('d/m/y') }}</span>
                                        <span>{{ \Carbon\Carbon::parse($item->discount_end)->format('d/m/y') }}</span>
                                    </div>
                                @else
                                    <span class="opacity-10 text-[9px]">--/--</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-green-600 dark:text-green-500 italic">R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                            <td class="px-4 py-4 text-center uppercase font-black text-gray-400 bg-gray-100 dark:bg-white/5 px-2 py-1 rounded text-[9px]">{{ $item->language }}</td>
                            <td class="px-4 py-4 font-bold text-gray-700 dark:text-zinc-400">{{ $item->condition }}</td>
                            <td class="px-4 py-4 text-center italic text-gray-400 text-[10px]">{{ is_array($item->extras) ? implode(', ', $item->extras) : $item->extras }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 dark:text-white leading-none">#{{ $item->catalogPrint->collector_number }} - {{ $item->nome_da_edicao }}</span>
                                    <span class="text-[9px] text-zinc-500 font-black uppercase mt-1 tracking-widest">{{ $item->catalogPrint->set->code }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="edit({{ $item->id }})" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-white/5 text-blue-500 hover:bg-blue-600 hover:text-white transition-all shadow-sm"><i class="ph-bold ph-note-pencil"></i></button>
                                    <button type="button" wire:click="deleteItem({{ $item->id }})" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-white/5 text-red-500 hover:bg-red-600 hover:text-white transition-all shadow-sm"><i class="ph-bold ph-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-6 py-20 text-center italic opacity-30 font-bold uppercase tracking-widest text-xs text-gray-400 font-sans">Nenhum card cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL ULTRA-RESPONSIVO --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-2 md:p-4 bg-black/90 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#0f172a] w-full max-w-4xl rounded-[2rem] md:rounded-[2.5rem] shadow-2xl flex flex-col md:flex-row overflow-hidden max-h-[95vh] border border-gray-200 dark:border-white/10 transition-all duration-300">

            {{-- SIDEBAR: TOPO NO MOBILE / LATERAL NO DESKTOP --}}
            <div class="w-full md:w-56 bg-gray-50 dark:bg-black/40 border-b md:border-b-0 md:border-r border-gray-200 dark:border-white/10 p-4 flex flex-row md:flex-col items-center gap-4 md:gap-0 shrink-0">
                <div class="w-24 md:w-full aspect-[2.5/3.5] rounded-xl md:rounded-2xl bg-slate-200 dark:bg-slate-800 shadow-lg overflow-hidden md:mb-4 border-2 border-white dark:border-white/5 shrink-0">
                    @if($currentPrintImage)
                        <img src="{{ asset($currentPrintImage) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center italic text-gray-400 text-[8px] md:text-[10px] text-center p-4 opacity-30 uppercase font-black font-sans">Aguardando Print</div>
                    @endif
                </div>

                <div class="flex-1 md:w-full space-y-2">
                    <div class="bg-white dark:bg-slate-800 p-2 rounded-xl md:rounded-2xl border border-gray-100 dark:border-white/5 text-center shadow-sm">
                        <span class="text-[7px] font-black text-gray-400 dark:text-zinc-500 uppercase tracking-widest leading-none font-sans">Sugestão Mercado</span>
                        <div class="text-base md:text-lg font-black text-green-600 dark:text-green-500 mt-1 font-sans">R$ {{ $marketPrices['mid'] > 0 ? number_format($marketPrices['mid'], 2, ',', '.') : '--' }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-white dark:bg-slate-800 p-1.5 rounded-xl border border-gray-100 dark:border-white/5 text-center shadow-sm">
                            <span class="text-[6px] font-black text-gray-400 uppercase font-sans">Min Versus</span>
                            <div class="text-[10px] font-black dark:text-gray-200 font-sans">R$ {{ $marketPrices['min'] > 0 ? number_format($marketPrices['min'], 2, ',', '.') : '--' }}</div>
                        </div>
                        <div class="bg-white dark:bg-slate-800 p-1.5 rounded-xl border border-gray-100 dark:border-white/5 text-center shadow-sm">
                            <span class="text-[6px] font-black text-gray-400 uppercase font-sans">Max Versus</span>
                            <div class="text-[10px] font-black dark:text-gray-200 font-sans">R$ {{ $marketPrices['max'] > 0 ? number_format($marketPrices['max'], 2, ',', '.') : '--' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FORM DIREITO --}}
            <div class="flex-1 p-4 md:p-6 flex flex-col bg-white dark:bg-transparent overflow-hidden">
                <div class="flex justify-between items-start mb-4 md:mb-5 flex-shrink-0">
                    <div>
                        <h2 class="text-lg md:text-xl font-black text-blue-600 dark:text-blue-500 uppercase italic tracking-tighter leading-none font-sans">
                            {{ $nomePT }}
                        </h2>
                        @if($nomePT !== $concept->name)
                            <p class="text-[9px] md:text-[10px] font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-widest leading-none mt-1 font-sans">
                                {{ $concept->name }}
                            </p>
                        @endif
                    </div>
                    <button wire:click="resetForm" class="text-gray-300 hover:text-red-500 transition-colors p-1 -mt-2">
                        <i class="ph-bold ph-x text-2xl"></i>
                    </button>
                </div>

                {{-- Aplicado no-scrollbar no grid do formulário (Modal responsivo) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3 overflow-y-auto pr-2 no-scrollbar overflow-x-visible">
                    {{-- VERSÃO BUSCÁVEL --}}
                    <div class="md:col-span-2 relative" x-data="{ open: false }">
                        <label class="text-[8px] font-black text-gray-400 dark:text-zinc-600 uppercase tracking-widest ml-1 mb-1 block font-sans">Versão do Print (Busca Rápida)</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.250ms="searchPrint" x-on:focus="open = true; $wire.set('searchPrint', '')" @click.away="open = false" placeholder="Clique ou digite..."
                                class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-lg py-2 px-3 text-xs font-bold text-gray-900 dark:text-white outline-none focus:ring-1 focus:ring-blue-500 transition-all font-sans">
                            <i class="ph-bold ph-magnifying-glass absolute right-3 top-2.5 text-gray-400 text-[10px]"></i>
                            
                            {{-- Aplicado no-scrollbar no dropdown de Busca Rápida --}}
                            <div x-show="open" class="absolute z-[210] w-full mt-1 bg-white dark:bg-slate-800 border border-gray-200 dark:border-white/20 rounded-xl shadow-2xl overflow-hidden max-h-48 overflow-y-auto no-scrollbar">
                                @forelse($availablePrints as $p)
                                    <button type="button" wire:click="selectPrint({{ $p->id }}, '{{ addslashes($p->label_dropdown) }}')" @click="open = false"
                                        class="w-full text-left px-3 py-2.5 text-[11px] font-bold border-b dark:border-white/5 hover:bg-blue-600 hover:text-white transition-colors flex justify-between items-center group font-sans">
                                        <span>{{ $p->label_dropdown }}</span>
                                        <span class="text-[8px] font-black opacity-30 group-hover:opacity-100 uppercase tracking-tighter">{{ $p->set->code }}</span>
                                    </button>
                                @empty
                                    <div class="p-4 text-[10px] italic text-gray-500 text-center font-sans">Nenhum print encontrado.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Idioma e Qualidade --}}
                    <div>
                        <label class="text-[8px] font-black text-gray-400 dark:text-zinc-600 uppercase tracking-widest ml-1 mb-1 block font-sans">Idioma Físico</label>
                        <select wire:model.live="language" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-lg py-2 text-xs font-bold text-gray-800 dark:text-white outline-none font-sans">
                            @forelse($availableLanguages as $lang)
                                <option value="{{ $lang['code'] }}">{{ $lang['label'] }}</option>
                            @empty
                                <option value="pt">Português</option>
                            @endforelse
                        </select>
                    </div>

                    <div>
                        <label class="text-[8px] font-black text-gray-400 dark:text-zinc-600 uppercase tracking-widest ml-1 mb-1 block font-sans">Qualidade (Filtra Min/Max)</label>
                        <select wire:model.live="quality" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-lg py-2 text-xs font-bold text-gray-800 dark:text-white outline-none font-sans">
                            <option value="M">Mint (M)</option>
                            <option value="NM">Near Mint (NM)</option>
                            <option value="SP">Slightly Played (SP)</option>
                            <option value="MP">Moderately Played (MP)</option>
                            <option value="HP">Heavily Played (HP)</option>
                            <option value="D">Damaged (D)</option>
                        </select>
                    </div>

                    {{-- EXTRAS TELEPORTADO --}}
                    <div class="md:col-span-2 relative" 
                         x-data="{ 
                            open: false,
                            localExtras: @js($selectedExtras),
                            style: { left: '0px', top: '0px', width: '0px' },
                            init() {
                                this.localExtras = @js($selectedExtras);
                                $wire.$watch('selectedExtras', (val) => {
                                    const incoming = val.map(i => i.toLowerCase());
                                    const local = this.localExtras.map(i => i.toLowerCase());
                                    const same = incoming.length === local.length && incoming.every(v => local.includes(v));
                                    if (!same) this.localExtras = [...val];
                                });
                            },
                            position() {
                                if(!this.$refs.button) return;
                                const rect = this.$refs.button.getBoundingClientRect();
                                this.style.left = rect.left + 'px';
                                this.style.top = (rect.bottom + 4) + 'px';
                                this.style.width = rect.width + 'px';
                            },
                            toggleExtra(val) {
                                const v = val.toLowerCase();
                                let current = [...this.localExtras];

                                const lowers = current.map(i => i.toLowerCase());
                                const has = lowers.includes(v);

                                if (has) {
                                    current = current.filter(i => i.toLowerCase() !== v);
                                } else {
                                    if (v === 'foil') {
                                        current = current.filter(i => i.toLowerCase() !== 'etched');
                                    }
                                    if (v === 'etched') {
                                        current = current.filter(i => i.toLowerCase() !== 'foil');
                                    }
                                    current.push(val);
                                }

                                this.localExtras = current;
                                $wire.set('selectedExtras', current);
                            },
                            isChecked(val) {
                                const v = val.toLowerCase();
                                return this.localExtras
                                    .map(i => i.toLowerCase())
                                    .includes(v);
                            }
                         }"
                         @scroll.window="open = false"
                         @resize.window="open = false">

                        <label class="text-[8px] font-black text-gray-400 dark:text-zinc-600 uppercase tracking-widest ml-1 mb-1 block font-sans">Extras (Foil vs Etched)</label>

                        <button x-ref="button" @click="position(); open = !open" type="button" 
                                class="w-full flex items-center justify-between bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-lg py-2 px-3 text-xs transition-all active:scale-[0.98] font-sans">
                            <span class="font-bold text-blue-600 uppercase truncate pr-4" 
                                  x-text="localExtras.length > 0 ? localExtras.length + ' selecionado(s)' : 'Nenhum extra'"></span>
                            <i class="ph-bold ph-caret-down text-gray-400"></i>
                        </button>

                        <template x-teleport="body">
                            <div x-show="open" @click.outside="open = false" 
                                 class="fixed z-[9999] bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/20 rounded-xl shadow-[0_20px_50px_rgba(0,0,0,0.4)] overflow-hidden"
                                 :style="`left: ${style.left}; top: ${style.top}; width: ${style.width};`"
                                 style="display: none;">

                                <div class="max-h-40 overflow-y-auto no-scrollbar p-1.5 bg-white dark:bg-[#1e293b]">
                                    @php
                                        $selectedExtrasLower = array_map('strtolower', $selectedExtras);
                                        $disabledExtrasLower = array_map('strtolower', $disabledExtras);
                                    @endphp

                                    @foreach($availableExtras as $value => $label)
                                        @php
                                            $lowerValue   = strtolower($value);
                                            // Lógica agnóstica: verifica se tá bloqueado e se tá ativo
                                            $isDisabled   = in_array($lowerValue, $disabledExtrasLower, true);
                                            $isActive     = in_array($lowerValue, $selectedExtrasLower, true);
                                        @endphp

                                        <label class="flex items-center gap-3 p-2.5 rounded-lg transition-colors"
                                            :class="'{{ $isDisabled ? 'opacity-60 cursor-not-allowed pointer-events-none' : 'cursor-pointer hover:bg-blue-600 hover:text-white' }}'"
                                            @if(!$isDisabled) @click.prevent="toggleExtra('{{ $value }}')" @endif>

                                            <input
                                                type="checkbox"
                                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-0 shrink-0 pointer-events-none transition-all"
                                                @if($isDisabled)
                                                    @if($isActive)
                                                        checked disabled
                                                    @else
                                                        disabled
                                                    @endif
                                                @else
                                                    :checked="isChecked('{{ $value }}')"
                                                @endif
                                            >

                                            <span class="text-[11px] font-bold select-none font-sans">{{ $label }}</span>

                                            @if($isDisabled)
                                                <span class="ml-auto text-[8px] font-black uppercase {{ $isActive ? 'text-blue-500' : 'opacity-40' }}">
                                                    {{ $isActive ? 'Fixo' : 'Incompatível' }}
                                                </span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Preço e Estoque --}}
                    <div>
                        <label class="text-[8px] font-black text-gray-400 dark:text-zinc-600 uppercase tracking-widest ml-1 mb-1 block font-sans">Preço Venda (R$)</label>
                        <input type="number" step="0.01" wire:model="price" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl py-1.5 px-3 font-black text-green-600 dark:text-green-500 text-sm focus:ring-1 focus:ring-green-500 outline-none font-sans">
                    </div>

                    <div>
                        <label class="text-[8px] font-black text-gray-400 dark:text-zinc-600 uppercase tracking-widest ml-1 mb-1 block font-sans">Estoque Qtd</label>
                        <input type="number" wire:model="quantity" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl py-1.5 px-3 font-black text-gray-800 dark:text-white text-sm outline-none font-sans">
                    </div>

                    {{-- DESCONTO --}}
                    <div class="md:col-span-2 bg-blue-50/50 dark:bg-white/5 p-3 rounded-2xl grid grid-cols-3 gap-3 border border-blue-100/50 dark:border-white/5 transition-all">
                        <div>
                            <label class="text-[7px] font-black text-blue-400 dark:text-zinc-500 uppercase font-sans">Desc %</label>
                            <input type="number" wire:model="discount_percent" class="w-full bg-transparent border-none p-0 text-red-500 font-black text-xs focus:ring-0 font-sans">
                        </div>
                        <div>
                            <label class="text-[7px] font-black text-blue-400 dark:text-zinc-500 uppercase tracking-tighter font-sans">Início</label>
                            <input type="date" wire:model="discount_start" class="w-full bg-transparent border-none p-0 text-[9px] text-gray-600 dark:text-gray-300 focus:ring-0 font-sans">
                        </div>
                        <div>
                            <label class="text-[7px] font-black text-blue-400 dark:text-zinc-500 uppercase tracking-tighter font-sans">Término</label>
                            <input type="date" wire:model="discount_end" class="w-full bg-transparent border-none p-0 text-[9px] text-gray-600 dark:text-gray-300 focus:ring-0 font-sans">
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="mt-auto pt-4 border-t border-gray-100 dark:border-white/5 flex items-center justify-between flex-shrink-0 transition-all">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" wire:model="keepOpen" class="w-3 h-3 rounded border-gray-300 text-blue-600">
                        <span class="text-[8px] font-black text-gray-400 dark:text-zinc-500 uppercase tracking-widest group-hover:text-blue-500 transition-colors font-sans">Manter Aberto</span>
                    </label>
                    <button wire:click="save" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-2.5 rounded-xl font-black uppercase text-[10px] tracking-widest shadow-lg transition-all active:scale-95 font-sans">
                        {{ $isEditing ? 'Atualizar' : 'Cadastrar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
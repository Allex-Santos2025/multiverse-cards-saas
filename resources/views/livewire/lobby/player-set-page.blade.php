<div class="space-y-6">
    {{-- BARRA SUPERIOR DA EDIÇÃO --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <button wire:click="$parent.closeSetPage" class="flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 transition">
                <i class="ph-bold ph-arrow-left"></i>
                <span>Voltar ao Resumo</span>
            </button>
            <div class="h-4 w-px bg-slate-200"></div>
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center font-black text-xs">
                    {{ $set->code }}
                </span>
                <div>
                    <h1 class="text-base font-black text-slate-900 leading-none">
                        {{ $set->nome_localizado }}
                        <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-slate-800 text-white">
                            {{ strtoupper($language) }}
                        </span>
                        @if($isFoil)
                            <span class="px-2 py-0.5 rounded text-[9px] ml-1 border border-amber-300 font-extrabold text-slate-900 bg-gradient-to-r from-amber-200 via-pink-300 to-sky-300">Foil</span>
                        @else
                            <span class="text-blue-600 font-bold ml-1 text-xs">&bull; Regular</span>
                        @endif
                    </h1>
                    <p class="text-[11px] text-slate-400 mt-0.5">Catálogo Oficial &bull; {{ $set->card_count }} cartas numeradas na coleção</p>
                </div>
            </div>
        </div>

        {{-- CONTROLES: FILTROS, EXIBIÇÃO E MODO GRID/LISTA --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <input type="text" wire:model.live.debounce.250ms="search" placeholder="Filtrar por nome ou nº..." class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-800 font-bold focus:outline-none focus:border-slate-400">
            </div>

            <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200">
                <button wire:click="$set('filterStatus', 'all')" class="px-2.5 py-1 rounded-lg text-xs font-bold transition {{ $filterStatus === 'all' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-900' }}">Todas</button>
                <button wire:click="$set('filterStatus', 'owned')" class="px-2.5 py-1 rounded-lg text-xs font-bold transition {{ $filterStatus === 'owned' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-900' }}">No Acervo</button>
                <button wire:click="$set('filterStatus', 'missing')" class="px-2.5 py-1 rounded-lg text-xs font-bold transition {{ $filterStatus === 'missing' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-900' }}">Faltantes</button>
            </div>

            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs">
                <span class="text-[10px] text-slate-400 font-bold uppercase px-1">Exibir:</span>
                <select wire:model.live="perPage" class="bg-white border-0 rounded-lg text-xs font-bold text-slate-800 py-1 pl-2 pr-6 focus:ring-0 cursor-pointer">
                    <option value="30">30</option>
                    <option value="60">60</option>
                    <option value="90">90</option>
                    <option value="120">120</option>
                </select>
            </div>

            <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200">
                <button wire:click="setViewType('album')" class="px-2.5 py-1 rounded-lg text-xs font-bold flex items-center gap-1.5 transition {{ $viewType === 'album' ? 'bg-orange-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900' }}" title="Visualizar em Grid">
                    <i class="ph-bold ph-squares-four"></i> Grid
                </button>
                <button wire:click="setViewType('list')" class="px-2.5 py-1 rounded-lg text-xs font-bold flex items-center gap-1.5 transition {{ $viewType === 'list' ? 'bg-orange-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900' }}" title="Visualizar como Lista">
                    <i class="ph-bold ph-list-dashes"></i> Lista
                </button>
            </div>
        </div>
    </div>

    {{-- BARRA DE PAGINAÇÃO SUPERIOR --}}
    @php
        $firstItem = $paginatedPrints->firstItem() ?? 0;
        $lastItem = $paginatedPrints->lastItem() ?? 0;
        $totalItems = $paginatedPrints->total();
        $currentPage = $paginatedPrints->currentPage();
        $lastPage = $paginatedPrints->lastPage();
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs font-bold border-b border-gray-200 dark:border-white/10 pb-3 text-gray-500 font-sans">
        <span class="uppercase tracking-wider">
            {{ $firstItem }}-{{ $lastItem }} DE <strong class="text-rose-600">{{ $totalItems }}</strong> CARDS
        </span>

        @if($lastPage > 1)
            <div class="flex items-center gap-1.5">
                @if($currentPage > 1)
                    <button wire:click="previousPage" class="px-2 py-1 rounded text-xs font-bold text-gray-400 hover:text-gray-800 transition">
                        &laquo; ANT
                    </button>
                @else
                    <span class="px-2 py-1 text-xs font-bold text-gray-300 opacity-60 cursor-not-allowed">
                        &laquo; ANT
                    </span>
                @endif

                @for($p = max(1, $currentPage - 2); $p <= min($lastPage, $currentPage + 2); $p++)
                    @if($p == $currentPage)
                        <span class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-600 text-white text-xs font-black shadow-sm">
                            {{ $p }}
                        </span>
                    @else
                        <button wire:click="gotoPage({{ $p }})" class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition">
                            {{ $p }}
                        </button>
                    @endif
                @endfor

                @if($currentPage < $lastPage)
                    <button wire:click="nextPage" class="px-2 py-1 rounded text-xs font-bold text-gray-400 hover:text-gray-800 transition">
                        Próx &raquo;
                    </button>
                @else
                    <span class="px-2 py-1 text-xs font-bold text-gray-300 opacity-60 cursor-not-allowed">
                        Próx &raquo;
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- MODO 1: GRID --}}
    @if($viewType === 'album')
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @forelse($items as $item)
                <div class="rounded-2xl p-3 border transition flex flex-col justify-between group relative overflow-hidden {{ $item['is_owned'] ? 'bg-white border-slate-200 shadow-sm hover:border-orange-400 hover:shadow-md' : 'bg-slate-100/70 border-dashed border-slate-300 opacity-60' }}">
                    
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[9px] font-mono font-black uppercase px-1.5 py-0.5 rounded {{ $item['is_owned'] ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-500' }}">
                            #{{ $item['collector_number'] }}
                        </span>

                        @if($item['is_owned'])
                            <span class="text-[9px] font-black uppercase text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                {{ $item['owned_count'] }}x No Acervo
                            </span>
                        @else
                            <span class="text-[9px] font-black uppercase text-slate-400">
                                Não possui
                            </span>
                        @endif
                    </div>

                    <div class="aspect-[2.5/3.5] rounded-xl overflow-hidden bg-black/10 relative {{ $item['is_owned'] ? '' : 'grayscale' }}">
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                    </div>

                    <div class="mt-2.5">
                        <h4 class="text-xs font-black text-slate-900 truncate leading-tight" title="{{ $item['name'] }}">{{ $item['name'] }}</h4>
                        <span class="text-[10px] text-slate-400 block truncate">{{ $item['english_name'] }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-12 text-center text-xs font-bold text-slate-400 bg-white rounded-2xl border border-slate-200">
                    Nenhuma carta encontrada para este filtro.
                </div>
            @endforelse
        </div>

    {{-- MODO 2: LISTA COM AÇÕES DE EDITAR E EXCLUIR --}}
    @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 font-black uppercase text-[9px] tracking-wider">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Carta</th>
                            <th class="px-4 py-3">Raridade</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Total</th>
                            <th class="px-4 py-3">Lançamentos / Extras</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                        @forelse($items as $item)
                            <tr class="hover:bg-slate-50/80 transition {{ $item['is_owned'] ? '' : 'opacity-50 bg-slate-50/30' }}">
                                <td class="px-4 py-3 font-mono font-bold text-slate-400">#{{ $item['collector_number'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <img src="{{ $item['image'] }}" class="w-6 h-8 object-cover rounded bg-black shrink-0 {{ $item['is_owned'] ? '' : 'grayscale' }}">
                                        <div>
                                            <div class="font-extrabold text-slate-900">{{ $item['name'] }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $item['english_name'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 capitalize text-slate-500">{{ $item['rarity'] ?: 'Comum' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($item['is_owned'])
                                        <span class="text-[9px] font-black uppercase text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Possui</span>
                                    @else
                                        <span class="text-[9px] font-black uppercase text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Falta</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-black text-slate-900">
                                    {{ $item['owned_count'] }}x
                                </td>
                                <td class="px-4 py-3">
                                    @if($item['is_owned'])
                                        <div class="space-y-1">
                                            @foreach($item['owned_records'] as $record)
                                                <div class="flex items-center gap-2 text-[10px]">
                                                    <span class="font-black bg-slate-100 text-slate-800 px-1.5 py-0.5 rounded">{{ $record->quantity }}x</span>
                                                    <span class="uppercase text-slate-500 font-bold">{{ $record->language }} &bull; {{ $record->condition }}</span>
                                                    @if(!empty($record->extras))
                                                        <span class="text-amber-600 font-bold">({{ implode(', ', (array)$record->extras) }})</span>
                                                    @endif
                                                    @if($record->acquisition_price > 0)
                                                        <span class="text-emerald-600 font-bold">R$ {{ number_format($record->acquisition_price, 2, ',', '.') }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">--</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($item['is_owned'])
                                        <div class="flex items-center justify-end gap-1.5">
                                            @foreach($item['owned_records'] as $record)
                                                <button wire:click="openEditCardModal({{ $record->id }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition" title="Editar este lançamento">
                                                    <i class="ph-bold ph-pencil-simple text-xs"></i>
                                                </button>
                                                <button wire:click="deleteCard({{ $record->id }})" wire:confirm="Deseja realmente remover esta carta do seu acervo?" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 transition" title="Excluir do acervo">
                                                    <i class="ph-bold ph-trash text-xs"></i>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400 font-bold">Nenhuma carta encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- BARRA DE PAGINAÇÃO INFERIOR --}}
    @if($lastPage > 1)
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs font-bold border-t border-gray-200 dark:border-white/10 pt-4 text-gray-500 font-sans">
            <span class="uppercase tracking-wider">
                {{ $firstItem }}-{{ $lastItem }} DE <strong class="text-rose-600">{{ $totalItems }}</strong> CARDS
            </span>

            <div class="flex items-center gap-1.5">
                @if($currentPage > 1)
                    <button wire:click="previousPage" class="px-2 py-1 rounded text-xs font-bold text-gray-400 hover:text-gray-800 transition">
                        &laquo; ANT
                    </button>
                @else
                    <span class="px-2 py-1 text-xs font-bold text-gray-300 opacity-60 cursor-not-allowed">
                        &laquo; ANT
                    </span>
                @endif

                @for($p = max(1, $currentPage - 2); $p <= min($lastPage, $currentPage + 2); $p++)
                    @if($p == $currentPage)
                        <span class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-600 text-white text-xs font-black shadow-sm">
                            {{ $p }}
                        </span>
                    @else
                        <button wire:click="gotoPage({{ $p }})" class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition">
                            {{ $p }}
                        </button>
                    @endif
                @endfor

                @if($currentPage < $lastPage)
                    <button wire:click="nextPage" class="px-2 py-1 rounded text-xs font-bold text-gray-400 hover:text-gray-800 transition">
                        Próx &raquo;
                    </button>
                @else
                    <span class="px-2 py-1 text-xs font-bold text-gray-300 opacity-60 cursor-not-allowed">
                        Próx &raquo;
                    </span>
                @endif
            </div>
        </div>
    @endif

    {{-- MODAL: EDITAR CARTA DO ACERVO (COM EXTRAS REAIS) --}}
    @if($modalEditCard && $editCardData)
        <div class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative border border-slate-200 max-h-[90vh] overflow-y-auto">
                <button type="button" wire:click="$set('modalEditCard', false)" class="absolute top-5 right-5 text-slate-400 hover:text-slate-700">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>

                <h3 class="text-base font-black text-slate-900">Editar Carta no Acervo</h3>
                <p class="text-xs text-slate-500 mt-0.5">Corrija os dados físicos, acabamentos ou valor pago deste registro.</p>

                <div class="mt-4 p-3 rounded-2xl bg-slate-50 border border-slate-200 flex gap-3 items-center">
                    <img src="{{ $editCardData['image'] }}" class="w-12 h-16 object-cover rounded-lg shadow-sm bg-black">
                    <div>
                        <span class="text-[9px] font-black uppercase text-slate-400">{{ $editCardData['code'] }} #{{ $editCardData['number'] }}</span>
                        <h4 class="text-xs font-extrabold text-slate-900 leading-tight">{{ $editCardData['name'] }}</h4>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Idioma Físico</label>
                            <select wire:model="editLanguage" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900">
                                @foreach($availableLanguages as $lang)
                                    <option value="{{ $lang['code'] }}">{{ $lang['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Conservação</label>
                            <select wire:model="editCondition" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900">
                                <option value="NM">Near Mint (NM)</option>
                                <option value="SP">Slightly Played (SP)</option>
                                <option value="MP">Moderately Played (MP)</option>
                                <option value="HP">Heavily Played (HP)</option>
                                <option value="D">Damaged (D)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Extras / Acabamentos Reais (Enum / Presenter) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Acabamentos & Detalhes Físicos (Extras)</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($availableExtras as $value => $label)
                                @php
                                    $isSel = in_array(strtolower($value), array_map('strtolower', $editSelectedExtras));
                                @endphp
                                <button type="button" wire:click="toggleEditExtra('{{ $value }}')" class="px-2.5 py-1.5 rounded-xl text-xs font-bold border transition text-left flex items-center justify-between {{ $isSel ? 'bg-orange-500 text-white border-orange-500 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                    <span class="truncate">{{ $label }}</span>
                                    @if($isSel)
                                        <i class="ph-bold ph-check text-[10px]"></i>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Quantidade de Cópias</label>
                            <input type="number" min="1" wire:model="editQuantity" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Preço Pago (R$)</label>
                            <input type="text" wire:model="editPrice" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900" placeholder="0,00">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('modalEditCard', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500">Cancelar</button>
                    <button type="button" wire:click="updateCard" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-orange-600 text-white text-xs font-black transition shadow-sm">
                        Salvar Alterações
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

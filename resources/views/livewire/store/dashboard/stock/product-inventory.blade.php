<div>
    {{-- ÁREA DE BUSCA (IDÊNTICA AOS CARDS) --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-8 transition-colors duration-300">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                Busca de Produtos
            </h2>
        </div>

        {{-- TIPO DE BUSCA --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-2 pl-1">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Origem da Busca</label>
            </div>
            <div class="bg-gray-100 dark:bg-gray-700 p-1 rounded-lg inline-flex w-full md:w-auto">
                @foreach(['padrao' => 'Catálogo Global', 'minhaLoja' => 'Minha Loja'] as $key => $label)
                    <button type="button" 
                            @click="$wire.set('searchType', '{{ $key }}').then(() => { $wire.applyFilters() })" 
                            class="flex-1 md:flex-none px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 {{ $searchType === $key ? 'bg-white dark:bg-gray-600 text-orange-600 dark:text-orange-400 shadow-sm ring-1 ring-gray-200 dark:ring-gray-500' : 'text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- BARRA DE INPUT + BOTÃO --}}
        <div class="flex items-center gap-3 relative z-10">
            <div class="relative flex-grow">
                <input type="text" wire:model="search" wire:keydown.enter="applyFilters" placeholder="Digite o nome do produto (Ex: Booster Box)..." class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 dark:text-white placeholder-gray-400 transition-all shadow-sm">
            </div>
            <button wire:click="applyFilters" class="hidden md:flex shrink-0 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium px-6 py-3 rounded-lg transition-colors items-center gap-2 shadow-sm">
                <span wire:loading.remove wire:target="applyFilters">Buscar</span>
                <span wire:loading wire:target="applyFilters" class="flex items-center gap-2"><i class="ph ph-circle-notch animate-spin"></i></span>
            </button>
        </div>
    </div>

    {{-- TABELA DE RESULTADOS --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm dark:shadow-lg border border-gray-200 dark:border-none transition-colors duration-300">
        <div class="flex items-center justify-between mb-4 flex-wrap">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Cadastrar Produtos</h2>
            <div class="flex items-center space-x-4 mt-4 md:mt-0">
                
                <button type="button" 
                        x-data
                        @click="
                            const form = document.getElementById('productInventoryForm');
                            const fd = new FormData(form);
                            const data = { items: {} };
                            for (let [k, v] of fd.entries()) {
                                const match = k.match(/items\[([^\]]+)\]\[([^\]]+)\]/);
                                if (match) {
                                    const id = match[1];
                                    const field = match[2];
                                    if (!data.items[id]) data.items[id] = {};
                                    data.items[id][field] = v;
                                }
                            }
                            $wire.saveForm(data);
                        "
                        wire:loading.attr="disabled"
                        class="relative inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition-all">
                    <span wire:loading.remove wire:target="saveForm">Salvar</span>
                    <span wire:loading wire:target="saveForm"><i class="ph ph-circle-notch animate-spin"></i> Salvando...</span>
                </button>

                <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                    Mostrando {{ $pagination->firstItem() ?? 0 }}-{{ $pagination->lastItem() ?? 0 }} de {{ number_format($pagination->total(), 0, ',', '.') }}
                </div>
            </div>
        </div>

        <form id="productInventoryForm" onsubmit="event.preventDefault();">
            <div class="overflow-x-visible overflow-y-visible">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estoque</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Preço</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Idioma</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qualidade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Produto Oficial</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($items as $item)
                            @php
                                $product = $item['product'];
                                $stock = $item['stock'];
                            @endphp
                            <tr wire:key="prod-{{ $product->id }}">
                                
                                {{-- COLUNA ESTOQUE --}}
                                <td class="px-6 py-4 whitespace-nowrap">       
                                    <div x-data="{ qty: {{ $stock?->quantity ?? 0 }} }" class="flex items-center space-x-2">
                                        <button type="button" @click="qty > 0 ? qty-- : 0" class="text-gray-400 hover:text-orange-500 focus:outline-none"><i class="ph ph-minus-circle text-xl"></i></button>
                                        <input type="number" name="items[{{ $product->id }}][qty]" x-model.number="qty" class="w-16 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm text-center text-gray-900 dark:text-white focus:outline-none focus:border-orange-500">
                                        <button type="button" @click="qty++" class="text-gray-400 hover:text-orange-500 focus:outline-none"><i class="ph ph-plus-circle text-xl"></i></button>
                                    </div>
                                </td>
                                
                                {{-- COLUNA PREÇO --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="relative rounded-md shadow-sm">
                                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2 text-gray-500 sm:text-sm">R$</span>
                                        <input type="number" name="items[{{ $product->id }}][price]" value="{{ number_format($stock?->price ?? 0, 2, '.', '') }}" step="0.01" class="block w-24 rounded-md border-0 py-1.5 pl-8 pr-2 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:text-sm dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                    </div>
                                </td>

                                {{-- COLUNA IDIOMA (Para Selados) --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select name="items[{{ $product->id }}][language]" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-2 py-2 text-sm focus:outline-none focus:border-orange-500 text-gray-900 dark:text-white transition-colors">
                                        <option value="EN" {{ ($stock?->language ?? 'EN') == 'EN' ? 'selected' : '' }}>🇺🇸 EN</option>
                                        <option value="PT" {{ ($stock?->language ?? '') == 'PT' ? 'selected' : '' }}>🇧🇷 PT</option>
                                        <option value="JP" {{ ($stock?->language ?? '') == 'JP' ? 'selected' : '' }}>🇯🇵 JP</option>
                                        <option value="ANY" {{ ($stock?->language ?? '') == 'ANY' ? 'selected' : '' }}>N/A (Acessório)</option>
                                    </select>
                                </td>

                                {{-- COLUNA QUALIDADE --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select name="items[{{ $product->id }}][condition]" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-2 py-2 text-sm focus:outline-none focus:border-orange-500 text-gray-900 dark:text-white transition-colors">
                                        <option value="NM" {{ ($stock?->condition ?? 'NM') == 'NM' ? 'selected' : '' }}>Selado / Novo</option>
                                        <option value="SP" {{ ($stock?->condition ?? '') == 'SP' ? 'selected' : '' }}>Caixa Danificada</option>
                                        <option value="MP" {{ ($stock?->condition ?? '') == 'MP' ? 'selected' : '' }}>Aberto / Sem lacre</option>
                                    </select>
                                </td>

                                {{-- COLUNA PRODUTO INFO --}}
                                <td class="px-6 py-4" style="overflow: visible !important;">
                                    <div class="flex items-center gap-3" x-data="{ showPreview: false, position: { x: 0, y: 0 } }">
                                        
                                        {{-- MINIATURA ESTOQUE --}}
                                        <div class="w-10 h-10 shrink-0 bg-gray-100 dark:bg-gray-800 rounded border border-gray-300 dark:border-gray-700">
                                            @if($product->image_path)
                                                <img src="{{ asset($product->image_path) }}" class="w-full h-full object-cover rounded">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                                    <i class="ph ph-package"></i>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- TEXTOS E GATILHO --}}
                                        <div class="flex flex-col">
                                            {{-- Usamos $event para capturar a posição do mouse e posicionar o preview perfeitamente --}}
                                            <span class="text-blue-600 dark:text-blue-400 font-bold text-sm cursor-help w-max hover:underline"
                                                @mouseenter="showPreview = true; position.x = $event.clientX + 20; position.y = $event.clientY - 100" 
                                                @mousemove="position.x = $event.clientX + 20; position.y = $event.clientY - 100"
                                                @mouseleave="showPreview = false">
                                                {{ $product->name }}
                                            </span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400 uppercase">{{ $product->type }}</span>

                                            {{-- PREVIEW FLUTUANTE (FIXED) --}}
                                            @if($product->image_path)
                                            <template x-teleport="body"> {{-- O teleport joga o HTML pro final do <body>, eliminando qualquer corte --}}
                                                <div x-show="showPreview" 
                                                    x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 scale-95"
                                                    x-transition:enter-end="opacity-100 scale-100"
                                                    :style="`position: fixed; left: ${position.x}px; top: ${position.y}px; display: block;`" 
                                                    class="z-[99999] w-56 p-2 bg-white dark:bg-gray-800 rounded-xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-gray-200 dark:border-gray-600 pointer-events-none">
                                                    
                                                    <img src="{{ asset($product->image_path) }}" class="w-full h-auto rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                                                    <div class="mt-2 text-center">
                                                        <p class="text-[12px] font-black text-gray-900 dark:text-white leading-tight">{{ $product->name }}</p>
                                                        <span class="text-[9px] text-orange-500 font-bold uppercase tracking-widest">{{ $product->type }}</span>
                                                    </div>
                                                </div>
                                            </template>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                {{-- AÇÕES --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button type="button" wire:click="$set('editingProductId', {{ $product->id }})" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Upload Foto e Detalhes">
                                        <i class="ph ph-camera-plus text-xl"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="ph ph-ghost text-4xl mb-2 text-gray-300 dark:text-gray-600"></i>
                                        @if($searchType === 'minhaLoja')
                                            <span>Você não tem este tipo de produto no seu estoque.</span>
                                        @else
                                            <span>Nenhum produto oficial encontrado.</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
            {{ $pagination->links() }}
        </div>
    </div>
    {{-- MODAL DE FOTO E DETALHES --}}
    @if($showModal)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg p-6 relative">
                <button wire:click="$set('showModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <i class="ph ph-x text-2xl"></i>
                </button>
                
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="ph ph-camera text-orange-500 text-xl"></i>
                    Detalhes do Produto
                </h3>
                
                <form wire:submit.prevent="saveDetails">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Foto real do produto (Opcional)</label>
                        
                        @if($existingPhotoUrl && !$modalPhoto)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $existingPhotoUrl) }}" class="h-32 rounded object-cover border border-gray-300 dark:border-gray-600 shadow-sm">
                            </div>
                        @endif
                        
                        @if($modalPhoto)
                            <div class="mb-3">
                                <img src="{{ $modalPhoto->temporaryUrl() }}" class="h-32 rounded object-cover border border-gray-300 dark:border-gray-600 shadow-sm">
                            </div>
                        @endif
                        
                        <input type="file" wire:model="modalPhoto" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 dark:file:bg-gray-700 dark:file:text-gray-300 dark:hover:file:bg-gray-600 transition-colors">
                        <div wire:loading wire:target="modalPhoto" class="text-xs font-bold text-orange-500 mt-2 animate-pulse">Carregando visualização...</div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Comentários / Observações</label>
                        <textarea wire:model="modalComment" rows="3" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 dark:text-white shadow-sm transition-all" placeholder="Ex: A caixa apresenta um leve amassado no canto superior direito..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-700 pt-4">
                        <button type="button" wire:click="$set('showModal', false)" class="px-5 py-2.5 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-bold text-sm transition-colors">Cancelar</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-sm shadow-sm transition-colors flex items-center gap-2">
                            <span wire:loading.remove wire:target="saveDetails">Salvar Detalhes</span>
                            <span wire:loading wire:target="saveDetails"><i class="ph ph-circle-notch animate-spin"></i> Processando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
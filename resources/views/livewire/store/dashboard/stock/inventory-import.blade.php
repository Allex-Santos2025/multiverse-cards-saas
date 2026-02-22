<div class="mt-4 p-10 bg-white dark:bg-[#2d3748] rounded-lg shadow-xl w-full animate-in fade-in duration-300 border border-gray-200 dark:border-transparent">
    <h1 class="text-2xl font-semibold text-gray-800 dark:text-[#e2e8f0] mb-6 uppercase tracking-tight">
        Importar arquivo .txt
    </h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-start">
        
        <div class="flex flex-col gap-6">
            
            <div class="flex flex-col">
                <label class="text-[0.9rem] font-bold text-gray-500 dark:text-[#a0aec0] mb-2 uppercase">Arquivo .txt</label>
                <div class="flex gap-3 items-center">
                    <input type="text" 
                        class="flex-grow bg-gray-100 dark:bg-[#4a5568] border border-gray-300 dark:border-[#4a5568] text-gray-800 dark:text-[#e2e8f0] px-4 py-3 rounded-md text-sm outline-none" 
                        placeholder="Escolher Arquivo" readonly>
                    
                    <label class="bg-[#4a90e2] hover:bg-[#3a7bd5] text-white px-6 py-3 rounded-md font-semibold text-xs uppercase cursor-pointer transition-colors whitespace-nowrap">
                        Procurar
                        <input type="file" class="hidden" accept=".txt">
                    </label>
                </div>
            </div>
            {{-- Depois (Correto) --}}
            @if(!empty($importErrors) && count($importErrors) > 0)
                <div class="alert-erro"> {{-- Use a classe de erro que você já tiver no dashboard --}}
                    <ul>
                        @foreach($importErrors as $erro)
                            <li class="text-red-500">{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="flex flex-col">
                <label class="text-[0.9rem] font-bold text-gray-500 dark:text-[#a0aec0] mb-2 uppercase text-xs">Lista de Cards (Edite se necessário)</label>
                <textarea wire:model="importText"
                    class="w-full h-64 bg-gray-100 dark:bg-[#4a5568] border border-gray-300 dark:border-[#4a5568] text-gray-800 dark:text-[#e2e8f0] p-4 rounded-md text-sm font-mono no-scrollbar outline-none resize-none"
                    placeholder="1 Veneno [4ED] NM PT 0.25"></textarea>
            </div>

            <div class="flex flex-col">
                <label class="text-[0.9rem] font-bold text-gray-500 dark:text-[#a0aec0] mb-2 uppercase text-xs">Limitar estoque</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-[#e2e8f0]">
                        <input type="radio" value="1" wire:model="limitToFour" name="limit" class="accent-[#4a90e2] w-4 h-4" checked>
                        <span>Limitar em 4 unidades</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-[#e2e8f0]">
                        <input type="radio" value="0" wire:model="limitToFour" name="limit" class="accent-[#4a90e2] w-4 h-4">
                        <span>Não limitar</span>
                    </label>
                </div>
            </div>

            <<div class="relative w-full" x-data="{ open: false }">
    
    <label class="text-[0.9rem] font-bold text-gray-500 dark:text-[#a0aec0] mb-2 uppercase text-xs">Extras do Lote</label>

    {{-- BOTÃO GATILHO (Estilo idêntico ao seu input de arquivo) --}}
    <button 
        @click="open = !open" 
        type="button"
        class="w-full flex items-center justify-between bg-gray-100 dark:bg-[#4a5568] border border-gray-300 dark:border-[#4a5568] text-gray-800 dark:text-[#e2e8f0] px-4 py-3 rounded-md text-sm outline-none transition-all hover:border-blue-500"
    >
        <span class="truncate select-none">
            <template x-if="selectedExtras.length > 0">
                <span class="text-blue-500 dark:text-blue-400 font-bold" x-text="selectedExtras.length + ' item(s) selecionado(s)'"></span>
            </template>
            <template x-if="selectedExtras.length === 0">
                <span class="opacity-60">Nenhum Extra Selecionado</span>
            </template>
        </span>
        <svg class="w-4 h-4 text-gray-500 dark:text-gray-300 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    {{-- MENU FLUTUANTE (Com fundo e sombra) --}}
    <div 
        x-show="open" 
        @click.away="open = false" 
        class="absolute z-50 mt-1 w-full bg-white dark:bg-[#2d3748] border border-gray-300 dark:border-gray-600 rounded shadow-2xl flex flex-col overflow-hidden"
        style="display: none;" 
    >
        <div class="overflow-y-auto max-h-60 p-1 custom-scrollbar">
            @php
                try {
                    $options = \App\Enums\StockExtra::options();
                } catch (\Throwable $e) {
                    $options = ['foil' => 'Foil', 'etched' => 'Etched', 'promo' => 'Promo', 'textless' => 'Textless'];
                }
            @endphp

            @foreach($options as $value => $label)
                <label class="flex items-center gap-3 px-3 py-2.5 rounded cursor-pointer hover:bg-blue-600 hover:text-white group transition-colors">
                    <input type="checkbox" 
                           value="{{ $value }}" 
                           wire:model="selectedExtras"
                           class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-500 dark:bg-gray-600">
                    <span class="text-sm font-medium select-none text-gray-700 dark:text-gray-200 group-hover:text-white">
                        {{ $label }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>
</div>

            <button type="button" wire:click="processImport" wire:loading.attr="disabled" class="w-fit bg-[#4a90e2] hover:bg-[#3a7bd5] text-white px-6 py-3.5 rounded-md font-bold text-sm uppercase transition-all shadow-md">
                Importar Arquivo
            </button>
        </div>

        <div class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-[#e2e8f0]">Modelo de Importação</h2>
            <div class="bg-gray-200 dark:bg-[#2a4365] border border-gray-300 dark:border-[#2a4365] text-gray-700 dark:text-[#e2e8f0] p-6 rounded-md font-mono text-sm leading-relaxed min-h-[300px] shadow-inner">
[QTD] [NOME] [[SIGLA-EDIÇÃO]] [QUALIDADE] [IDIOMA] [PREÇO]

Exemplo:
1 Veneno [4ED] NM PT 0.25
4 Counterspell [7ED] SP PT 12.50
1 Black Lotus [LEA] NM EN 50000.00
10 Lightning Bolt [CLB] NM EN 1.00

Dicionário:
Qualidade: NM, SP, MP, HP, D
Idioma: PT, EN, JP, ES, IT
            </div>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold italic">
                * Se uma linha estiver fora do padrão, nada será salvo.
            </p>
        </div>
    </div>
</div>
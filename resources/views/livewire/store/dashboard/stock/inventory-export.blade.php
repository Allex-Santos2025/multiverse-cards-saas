<div>
    <div class="mt-4 p-10 bg-white dark:bg-[#2d3748] rounded-lg shadow-xl w-full animate-in fade-in duration-300 border border-gray-200 dark:border-transparent">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-[#e2e8f0] mb-6 uppercase tracking-tight">
            Exportar Cards e Produtos
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-start">
            
            <div class="flex flex-col gap-6">
                
                {{-- Edições --}}
                <div class="flex flex-col">
                    <label class="text-[0.9rem] font-bold text-gray-500 dark:text-[#a0aec0] mb-2 uppercase text-xs">Edições</label>
                    <div class="relative w-full">
                        <select wire:model="exportSet" class="w-full bg-gray-100 dark:bg-[#4a5568] border border-gray-300 dark:border-[#4a5568] text-gray-800 dark:text-[#e2e8f0] px-4 py-3 rounded-md text-sm outline-none transition-all focus:border-[#4a90e2] appearance-none cursor-pointer">
                            <option value="all">Todas as Edições</option>
                            
                            @foreach($availableSets as $set)
                                <option value="{{ $set->id }}">{{ $set->name }} ({{ $set->code }})</option>
                            @endforeach

                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 dark:text-gray-300">
                            <i class="ph ph-caret-up-down text-sm"></i>
                        </div>
                    </div>
                </div>

                {{-- Formato de Saída (Agrupar) --}}
                <div class="flex flex-col">
                    <label class="text-[0.9rem] font-bold text-gray-500 dark:text-[#a0aec0] mb-2 uppercase text-xs">Formato do Relatório</label>
                    <div class="flex flex-col gap-4">
                        <label class="flex items-center gap-3 cursor-pointer text-sm text-gray-700 dark:text-[#e2e8f0] group">
                            <input type="radio" value="yes" wire:model="groupCards" name="group_cards" class="accent-[#4a90e2] w-4 h-4">
                            <div class="flex flex-col">
                                <span class="font-bold">Simples (Agrupado)</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Soma cartas iguais. Retorna com qualidade NM e R$ 0,00.</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer text-sm text-gray-700 dark:text-[#e2e8f0] group">
                            <input type="radio" value="no" wire:model="groupCards" name="group_cards" class="accent-[#4a90e2] w-4 h-4" checked>
                            <div class="flex flex-col">
                                <span class="font-bold">Completo (Detalhado)</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Linha por linha. Ideal para backup e re-importação.</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Botão de Ação --}}
                <button type="button" 
                        wire:click="processExport" 
                        wire:loading.attr="disabled" 
                        class="mt-2 w-fit bg-[#4a90e2] hover:bg-[#3a7bd5] text-white px-6 py-3.5 rounded-md font-bold text-sm uppercase transition-all shadow-md flex items-center gap-2">
                    <span wire:loading.remove wire:target="processExport">Exportar Arquivo</span>
                    <span wire:loading wire:target="processExport" class="flex items-center gap-2">
                        <i class="ph ph-circle-notch animate-spin"></i> Gerando Lista...
                    </span>
                </button>
            </div>

            <div class="flex flex-col gap-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-[#e2e8f0]">Como ficará o arquivo?</h2>
                
                <div class="bg-gray-200 dark:bg-[#2a4365] border border-gray-300 dark:border-[#2a4365] text-gray-700 dark:text-[#e2e8f0] p-6 rounded-md font-mono text-sm leading-relaxed min-h-[300px] shadow-inner overflow-x-auto">
                    <div x-data="{ groupCards: @entangle('groupCards') }">
                        <div x-show="groupCards === 'no'">
    1 Veneno [4ED] NM PT 0.25
    4 Counterspell [7ED] SP PT (Foil) 12.50
    1 Black Lotus [LEA] NM EN 50000.00
    10 Lightning Bolt [CLB] NM EN (Foil, Promo) 1.00
                        </div>

                        <div x-show="groupCards === 'yes'" x-cloak>
    1 Veneno [4ED] NM PT 0.00
    4 Counterspell [7ED] NM PT 0.00
    1 Black Lotus [LEA] NM EN 0.00
    10 Lightning Bolt [CLB] NM EN 0.00
                        </div>
                    </div>
                </div>
                
                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold italic">
                    * O formato gerado é 100% compatível com a nossa ferramenta de Importação.
                </p>
            </div>
            
        </div>
    </div>
</div>
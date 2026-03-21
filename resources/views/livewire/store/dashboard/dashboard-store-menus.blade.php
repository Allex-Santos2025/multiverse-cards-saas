<div class="p-6 md:p-10 max-w-7xl mx-auto" x-data="{ modalOpen: @entangle('showModal') }">
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Menus e Card Games</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Gerencie os jogos ativos e a estrutura de navegação da sua vitrine.</p>
        </div>
        <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-sm transition-colors duration-200 flex items-center gap-2">
            <i class="ph ph-plus-circle text-xl"></i> Ativar Novo Jogo
        </button>
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors duration-300">
        @if($menus && $menus->count() > 0)
            <ul class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach($menus as $menu)
                    <li class="p-5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            {{-- Ícone dinâmico com as 2 primeiras letras do nome do jogo --}}
                            <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-lg uppercase border border-blue-100 dark:border-blue-800/50">
                                {{ substr($menu->game->name ?? '??', 0, 2) }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                                    {{-- Nome do Jogo vindo direto da tabela games --}}
                                    {{ $menu->game->name ?? 'Jogo não identificado' }}
                                </h3>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @if($menu->show_singles) <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-xs text-slate-600 dark:text-slate-300 rounded-md">{{ $menu->name_singles }}</span> @endif
                                    @if($menu->show_sealed) <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-xs text-slate-600 dark:text-slate-300 rounded-md">{{ $menu->name_sealed }}</span> @endif
                                    @if($menu->show_accessories) <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-xs text-slate-600 dark:text-slate-300 rounded-md">{{ $menu->name_accessories }}</span> @endif
                                    @if($menu->show_latest) <span class="px-2.5 py-1 bg-green-50 dark:bg-green-900/20 text-xs text-green-700 dark:text-green-400 font-medium rounded-md border border-green-200 dark:border-green-800/50"><i class="ph ph-robot mr-1"></i> {{ $menu->name_latest }}</span> @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $menu->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ $menu->is_active ? 'Ativo na Loja' : 'Oculto' }}
                            </span>
                            <button wire:click="editMenu({{ $menu->id }})" class="text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-800">
                                <i class="ph ph-pencil-simple text-xl"></i>
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            {{-- Estado vazio --}}
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400 dark:text-slate-500">
                    <i class="ph ph-game-controller text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-slate-900 dark:text-white">Nenhum jogo configurado</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">Sua vitrine está vazia. Comece ativando o primeiro Card Game para gerar os menus da sua loja.</p>
            </div>
        @endif
    </div>

    {{-- Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="modalOpen = false"></div>

            <div x-show="modalOpen" x-transition.scale.origin.bottom class="relative inline-block align-bottom bg-white dark:bg-[#1e293b] rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-slate-200 dark:border-slate-700">
                
                <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                        {{ $editingMenuId ? 'Editar Configurações do Jogo' : 'Ativar Novo Card Game' }}
                    </h3>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"><i class="ph ph-x text-xl"></i></button>
                </div>

                <div class="px-6 py-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    
                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Qual jogo deseja ativar?</label>
                        {{-- Select Dinâmico: Busca da variável $allGames do PHP --}}
                        <select wire:model="game_id" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-[#0f172a] text-slate-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/20 {{ $editingMenuId ? 'opacity-60 cursor-not-allowed' : '' }}" {{ $editingMenuId ? 'disabled' : '' }}>
                            <option value="">Selecione um jogo...</option>
                            @foreach($allGames as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                        @error('game_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-4">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 pb-2 mb-4">Estrutura do Menu</h4>
                        
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Defina os nomes das abas que aparecerão na sua vitrine. Desative o que você não vende.</p>

                        @php
                            $opcoes = [
                                ['label' => 'Cartas Avulsas (Singles)', 'model' => 'name_singles', 'show' => 'show_singles', 'bg' => 'bg-slate-50 dark:bg-slate-800/50'],
                                ['label' => 'Produtos Selados', 'model' => 'name_sealed', 'show' => 'show_sealed', 'bg' => 'bg-slate-50 dark:bg-slate-800/50'],
                                ['label' => 'Acessórios', 'model' => 'name_accessories', 'show' => 'show_accessories', 'bg' => 'bg-slate-50 dark:bg-slate-800/50'],
                                
                                // Nome da seção de Sets no menu
                                ['label' => 'Título da Seção de Sets', 'model' => 'name_latest', 'show' => 'show_latest', 'bg' => 'bg-slate-50 dark:bg-slate-800/50'],
                                
                                // BOTÃO DO ROBÔ (O que você quer mudar o nome)
                                ['label' => 'Botão do WhatsApp (Robô)', 'model' => 'name_updates', 'show' => 'show_updates', 'bg' => 'bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800/30'],
                                
                                ['label' => 'Link: Todos os Sets', 'model' => 'name_all_sets', 'show' => 'show_all_sets', 'bg' => 'bg-slate-50 dark:bg-slate-800/50'],
                            ];
                        @endphp

                        @foreach($opcoes as $op)
                            <div class="flex items-center gap-4 p-3 rounded-lg {{ $op['bg'] }}">
                                <div class="flex-1">
                                    <label class="block text-[11px] font-bold {{ str_contains($op['model'], 'latest') ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }} uppercase tracking-wider mb-1.5">{{ $op['label'] }}</label>
                                    <input type="text" wire:model="{{ $op['model'] }}" class="w-full rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-[#0f172a] text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring focus:ring-blue-500/20 py-2">
                                </div>
                                <div class="w-24 flex flex-col items-end justify-center pt-5">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model="{{ $op['show'] }}" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 dark:after:border-slate-600 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/50 px-6 py-4 border-t border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                    
                    <label class="relative inline-flex items-center cursor-pointer w-full sm:w-auto">
                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-red-400 dark:bg-red-500/50 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                        <span class="ml-3 text-sm font-bold text-slate-800 dark:text-slate-200">Jogo Ativo na Loja</span>
                    </label>

                    <div class="flex gap-3 w-full sm:w-auto">
                        <button type="button" @click="modalOpen = false" class="flex-1 sm:flex-none px-4 py-2 bg-white dark:bg-[#0f172a] border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancelar</button>
                        <button type="button" wire:click="save" class="flex-1 sm:flex-none px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 shadow-sm transition-colors flex justify-center items-center gap-2">
                            <span wire:loading.remove wire:target="save">Salvar</span>
                            <span wire:loading wire:target="save"><i class="ph ph-spinner animate-spin"></i> Salvando...</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
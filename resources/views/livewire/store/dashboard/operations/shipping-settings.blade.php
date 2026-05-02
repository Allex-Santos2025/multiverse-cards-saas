<div class="space-y-6 relative" x-data="{ openSettings: null }">
    
    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    {{-- CABEÇALHO E BOTÃO SALVAR --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter">Envios & Retiradas</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">Gerencie os métodos de entrega e integrações logísticas da sua loja.</p>
        </div>
        <button wire:click="salvarConfiguracoes" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-6 py-2.5 rounded-md transition-all shadow-sm flex items-center justify-center gap-2">
            Salvar Tudo
        </button>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/50 text-emerald-600 dark:text-emerald-400 font-bold px-4 py-3 rounded-md text-sm flex items-center gap-2 mb-6">
            <i class="ph-fill ph-check-circle text-lg"></i> {{ session('message') }}
        </div>
    @endif

    {{-- BLOCO 1: INTEGRAÇÕES GLOBAIS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        {{-- Card Melhor Envio --}}
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-md bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-500 flex items-center justify-center"><i class="ph-fill ph-truck"></i></div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Melhor Envio</h3>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="is_active_melhor_envio" class="sr-only peer">
                    <div class="w-9 h-5 bg-gray-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-orange-500"></div>
                </label>
            </div>
            @if($is_active_melhor_envio)
                <input type="password" wire:model="melhor_envio_token" placeholder="Cole seu Token API aqui" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-xs focus:ring-1 focus:ring-orange-500 focus:border-orange-500 outline-none transition-all font-mono">
            @else
                <p class="text-xs text-slate-500 dark:text-slate-400">Correios e transportadoras sem contrato próprio.</p>
            @endif
        </div>

        {{-- Card Correios CWS --}}
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-md bg-amber-100 dark:bg-amber-500/10 text-amber-600 dark:text-amber-500 flex items-center justify-center"><i class="ph-fill ph-mailbox"></i></div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Correios CWS</h3>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="is_active_correios" class="sr-only peer">
                    <div class="w-9 h-5 bg-gray-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-orange-500"></div>
                </label>
            </div>
            @if($is_active_correios)
                <div class="space-y-2">
                    <input type="text" wire:model="correios_cartao_postagem" placeholder="Cartão de Postagem" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-1.5 text-xs focus:ring-1 focus:ring-orange-500 focus:border-orange-500 outline-none transition-all">
                    <input type="password" wire:model="correios_senha" placeholder="Senha da API" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-1.5 text-xs focus:ring-1 focus:ring-orange-500 focus:border-orange-500 outline-none transition-all">
                </div>
            @else
                <p class="text-xs text-slate-500 dark:text-slate-400">Use sua tabela particular do Meu Correios.</p>
            @endif
        </div>

        {{-- Card Frenet --}}
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-md bg-indigo-100 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center"><i class="ph-fill ph-package"></i></div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Frenet</h3>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="is_active_frenet" class="sr-only peer">
                    <div class="w-9 h-5 bg-gray-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-orange-500"></div>
                </label>
            </div>
            @if($is_active_frenet)
                <input type="password" wire:model="frenet_token" placeholder="Cole seu Token Frenet aqui" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-xs focus:ring-1 focus:ring-orange-500 focus:border-orange-500 outline-none transition-all font-mono">
            @else
                <p class="text-xs text-slate-500 dark:text-slate-400">Gateway para contratos terceirizados.</p>
            @endif
        </div>
    </div>

    {{-- BLOCO 2: TABELA DE SERVIÇOS --}}
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50 flex justify-between items-center">
            <h3 class="font-bold text-slate-900 dark:text-white">Serviços Oferecidos</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Ative e configure os envios que aparecerão no checkout.</p>
        </div>

        <div class="overflow-x-auto hide-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-slate-800/80 border-b border-gray-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold">
                        <th class="px-6 py-4 w-20 text-center">Status</th>
                        <th class="px-6 py-4">Serviço</th>
                        <th class="px-6 py-4">Integração</th>
                        <th class="px-6 py-4">Resumo de Regras</th>
                        <th class="px-6 py-4 w-24 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700/50 text-sm">
                    
                    {{-- Serviço: PAC --}}
                    @if($is_active_correios)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors animate-in fade-in">
                            <td class="px-6 py-4 text-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="correios_pac" class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $correios_pac_nome_exibicao }}</td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400">Correios CWS</td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    @if($taxa_seguro_percentual > 0)
                                        <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Seguro {{ $taxa_seguro_percentual }}%</span>
                                    @endif
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">Até 30kg</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openSettings = 'pac'" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors bg-blue-50 dark:bg-blue-900/20 p-2 rounded-md">
                                    <i class="ph-bold ph-gear text-lg"></i>
                                </button>
                            </td>
                        </tr>
                    @endif

                    {{-- Serviço: Carta Registrada --}}
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active_carta_registrada" class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $cr_nome_exibicao }}</td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400">Manual / Fixo</td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Fixo R$ {{ $cr_valor_fixo }}</span>
                                <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">Max {{ $cr_limite_cartas }} un.</span>
                                @if($cr_apenas_singles)
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Apenas Singles</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="openSettings = 'carta_registrada'" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors bg-blue-50 dark:bg-blue-900/20 p-2 rounded-md">
                                <i class="ph-bold ph-gear text-lg"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- Serviço: Retirada --}}
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active_retirada" class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $retirada_nome_exibicao }}</td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400">Manual / Local</td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                @if($retirada_apenas_local)
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">Trava: Mesmo Estado</span>
                                @else
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">Sem Trava Geográfica</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="openSettings = 'retirada'" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors bg-blue-50 dark:bg-blue-900/20 p-2 rounded-md">
                                <i class="ph-bold ph-gear text-lg"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- SLIDE-OVER PANELS --}}
    {{-- ========================================================== --}}

    {{-- Fundo escuro do Modal --}}
    <div x-show="openSettings !== null" 
         x-transition.opacity 
         class="fixed inset-0 bg-black/60 z-[200] backdrop-blur-sm" 
         @click="openSettings = null" 
         style="display: none;">
    </div>

    {{-- Painel Lateral Direita --}}
    <div x-show="openSettings !== null" 
         x-transition:enter="transform transition ease-in-out duration-300" 
         x-transition:enter-start="translate-x-full" 
         x-transition:enter-end="translate-x-0" 
         x-transition:leave="transform transition ease-in-out duration-300" 
         x-transition:leave-start="translate-x-0" 
         x-transition:leave-end="translate-x-full" 
         class="fixed inset-y-0 right-0 w-full max-w-md bg-white dark:bg-slate-800 shadow-2xl z-[210] flex flex-col border-l border-gray-200 dark:border-slate-700" 
         style="display: none;">
        
        {{-- Header do Painel Lateral --}}
        <div class="px-6 py-5 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between bg-gray-50 dark:bg-slate-900">
            <h2 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider" x-text="
                openSettings === 'pac' ? 'Regras: Correios PAC' : 
                (openSettings === 'carta_registrada' ? 'Regras: Carta Registrada' : 
                (openSettings === 'retirada' ? 'Regras: Retirada' : 'Configurações'))
            "></h2>
            <button @click="openSettings = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors bg-gray-200 dark:bg-slate-800 rounded-full p-1.5">
                <i class="ph-bold ph-x text-lg"></i>
            </button>
        </div>

        {{-- Conteúdo do Painel Lateral (Sem destruição de DOM) --}}
        <div class="p-6 flex-1 overflow-y-auto hide-scrollbar space-y-6">
            
            {{-- Formulário para PAC --}}
            <div x-show="openSettings === 'pac'" style="display: none;">
                <div class="space-y-5 animate-in fade-in">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Nome de Exibição</label>
                        <input type="text" wire:model="correios_pac_nome_exibicao" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Descrição / Instruções</label>
                        <textarea wire:model="correios_pac_descricao" rows="2" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none"></textarea>
                    </div>

                    <hr class="border-gray-200 dark:border-slate-700">
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Acréscimo de Seguro (%)</label>
                        <input type="number" step="0.1" wire:model="taxa_seguro_percentual" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                        <p class="text-[10px] text-slate-400 mt-1">Gordura de segurança contra extravios.</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Prazo de Separação / Manuseio (Dias)</label>
                        <input type="number" wire:model="prazo_manuseio_dias" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                    </div>
                </div>
            </div>

            {{-- Formulário para CARTA REGISTRADA --}}
            <div x-show="openSettings === 'carta_registrada'" style="display: none;">
                <div class="space-y-5 animate-in fade-in">
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Nome de Exibição (Checkout)</label>
                        <input type="text" wire:model="cr_nome_exibicao" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="Ex: Carta Registrada, Impresso Nacional...">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Descrição / Instruções</label>
                        <textarea wire:model="cr_descricao" rows="2" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="Ex: Rastreio simples. Apenas para cartas."></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Este texto aparecerá para o cliente logo abaixo do nome do frete na hora do pagamento.</p>
                    </div>

                    <label class="flex items-start gap-3 cursor-pointer group p-4 bg-blue-50 dark:bg-blue-900/10 rounded-lg border border-blue-100 dark:border-blue-900/30">
                        <div class="pt-0.5">
                            <input type="checkbox" wire:model="cr_apenas_singles" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-blue-900 dark:text-blue-400">Permitir Apenas para Cartas Avulsas</span>
                            <span class="block text-[11px] text-blue-700 dark:text-blue-500 mt-1">Se houver caixas ou acessórios no carrinho, esta opção será ocultada.</span>
                        </div>
                    </label>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Valor Base Fixo (R$)</label>
                            <input type="number" step="0.01" wire:model="cr_valor_fixo" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Acréscimo / Seguro (%)</label>
                            <input type="number" step="0.1" wire:model="cr_taxa_percentual" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1 relative group">
                            <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Limite (Qtd. Cartas)</label>
                            <input type="number" wire:model="cr_limite_cartas" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="Ex: 80">
                            <span class="block text-[10px] text-slate-400 mt-1">Acima desta quantia, a opção some.</span>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Prazo Fixo (Dias)</label>
                            <input type="number" wire:model="cr_prazo_dias" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulário para RETIRADA --}}
            <div x-show="openSettings === 'retirada'" style="display: none;">
                <div class="space-y-5 animate-in fade-in">
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Nome de Exibição</label>
                        <input type="text" wire:model="retirada_nome_exibicao" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="Ex: Retirada no Balcão, Entrega no Metrô...">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Instruções para o Jogador</label>
                        <textarea wire:model="retirada_instrucoes" rows="3" class="w-full bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-4 py-3 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="Onde o jogador deve ir? Em quais horários?"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Dica: Use este espaço para avisar sobre prazos de agendamento prévio.</p>
                    </div>

                    <label class="flex items-start gap-3 cursor-pointer group p-4 bg-orange-50 dark:bg-orange-900/10 rounded-lg border border-orange-100 dark:border-orange-900/30 mt-4">
                        <div class="pt-0.5">
                            <input type="checkbox" wire:model="retirada_apenas_local" class="w-4 h-4 text-orange-500 bg-white border-gray-300 rounded focus:ring-orange-500 dark:bg-slate-700 dark:border-slate-600">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-orange-900 dark:text-orange-400">Trava Geográfica (Estado)</span>
                            <span class="block text-[11px] text-orange-700 dark:text-orange-500 mt-1">Exige que o CEP do jogador seja do mesmo Estado da sua loja.</span>
                        </div>
                    </label>
                </div>
            </div>

        </div>

        {{-- Footer do Painel Lateral (Botão Fechar/Concluir) --}}
        <div class="p-6 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900">
            <button @click="openSettings = null" class="w-full bg-slate-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 dark:text-slate-900 text-white font-bold text-sm px-6 py-3 rounded-md transition-all shadow-sm">
                Concluir Edição
            </button>
            <p class="text-center text-[10px] text-slate-400 mt-3">Lembre-se de clicar no botão "Salvar Tudo" na tela principal.</p>
        </div>
    </div>

</div>
<form wire:submit="salvarPerfil" class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-6 relative">
    
    {{-- CABEÇALHO --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter">Perfil da Loja</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">Gerencie as informações de contato, dados fiscais e o endereço do seu negócio.</p>
        </div>
        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm px-6 py-2.5 rounded-md transition-all shadow-sm">
            Salvar Alterações
        </button>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/50 text-emerald-600 dark:text-emerald-400 font-bold px-4 py-3 rounded-md text-sm flex items-center gap-2 mb-6">
            <i class="ph-fill ph-check-circle text-lg"></i> {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/50 text-red-600 dark:text-red-400 font-bold px-4 py-3 rounded-md text-sm flex items-center gap-2 mb-6">
            <i class="ph-fill ph-warning-circle text-lg"></i> {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6">

        {{-- BLOCO 1: IDENTIFICAÇÃO E DOMÍNIO --}}
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg p-6 shadow-sm">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="ph-fill ph-storefront text-lg text-orange-500"></i> Identificação e Domínio
            </h3>
            
            <div class="space-y-6">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Nome da Loja</label>
                    <input type="text" wire:model="name" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none" required>
                </div>

                <div class="space-y-3 pt-2 border-t border-gray-100 dark:border-slate-700/50">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Possui domínio de internet próprio?</label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="use_custom_domain" value="0" class="text-orange-600 focus:ring-orange-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Não (Usar link do Versus)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="use_custom_domain" value="1" class="text-orange-600 focus:ring-orange-500">
                            <span class="text-sm text-slate-700 dark:text-slate-300">Sim (Domínio Próprio)</span>
                        </label>
                    </div>
                </div>

                @if(!$use_custom_domain)
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Link da sua Loja (Slug)</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-slate-700 bg-gray-100 dark:bg-slate-950 text-slate-500 dark:text-slate-400 sm:text-sm">
                                versustcg.com.br/loja/
                            </span>
                            <input type="text" wire:model="url_slug" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                        </div>
                    </div>
                @else
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Qual o seu domínio?</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-slate-700 bg-gray-100 dark:bg-slate-950 text-slate-500 dark:text-slate-400 sm:text-sm">
                                https://
                            </span>
                            <input type="text" wire:model="domain" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="www.suamarca.com.br">
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- BLOCO 2: DADOS FISCAIS --}}
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg p-6 shadow-sm">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="ph-fill ph-buildings text-lg text-orange-500"></i> Dados Fiscais
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="space-y-1 lg:col-span-1">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">CNPJ ou CPF (Opcional)</label>
                    <input type="text" wire:model="document" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="Apenas números">
                </div>
                <div class="space-y-1 lg:col-span-2">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Razão Social / Nome</label>
                    <input type="text" wire:model="corporate_name" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                </div>
                
                <div class="space-y-1 lg:col-span-1 mt-2">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider flex justify-between items-center">
                        Inscrição Estadual (IE)
                    </label>
                    <input type="text" wire:model="state_registration" @if($is_ie_exempt) readonly @endif class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none @if($is_ie_exempt) opacity-60 cursor-not-allowed @endif">
                    <label class="flex items-center gap-2 mt-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="is_ie_exempt" class="text-orange-600 rounded border-gray-300 focus:ring-orange-500">
                        <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">Isento</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- BLOCO 3: CONTATO E REDES SOCIAIS (DINÂMICO) --}}
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg p-6 shadow-sm">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="ph-fill ph-headset text-lg text-orange-500"></i> Atendimento e Redes Sociais
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8 border-b border-gray-100 dark:border-slate-700/50 pb-6">
                <div class="space-y-1 sm:col-span-1">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Telefone / WhatsApp</label>
                    <input type="text" wire:model="phone" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="(00) 00000-0000">
                </div>
                <div class="space-y-1 sm:col-span-2">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">E-mail de Suporte</label>
                    <input type="email" wire:model="support_email" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="contato@sualoja.com.br">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-4">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Links das Redes Sociais</label>
                    <button type="button" wire:click="addSocial" class="text-xs font-bold text-orange-600 hover:text-orange-700 flex items-center gap-1">
                        <i class="ph-bold ph-plus"></i> Adicionar Rede
                    </button>
                </div>

                <div class="space-y-3">
                    @foreach($socials as $index => $social)
                        <div class="flex gap-2 items-start" wire:key="social-{{ $index }}">
                            <div class="w-1/3">
                                <select wire:model="socials.{{ $index }}.platform" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none">
                                    @foreach($availablePlatforms as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1 relative">
                                <input type="text" wire:model="socials.{{ $index }}.url" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="Link completo ou @usuario">
                            </div>
                            <button type="button" wire:click="removeSocial({{ $index }})" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-colors" title="Remover">
                                <i class="ph-bold ph-trash"></i>
                            </button>
                        </div>
                    @endforeach

                    @if(count($socials) === 0)
                        <div class="text-center py-6 bg-gray-50 dark:bg-slate-900/50 rounded-md border border-dashed border-gray-300 dark:border-slate-700">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Nenhuma rede social cadastrada.</p>
                            <button type="button" wire:click="addSocial" class="text-xs font-bold text-orange-600 mt-2">Clique aqui para adicionar</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- BLOCO 4: ENDEREÇO LOGÍSTICO --}}
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg p-6 shadow-sm">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="ph-fill ph-map-pin-line text-lg text-orange-500"></i> Endereço Base / Remetente
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div class="space-y-1 sm:col-span-1">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">CEP *</label>
                    <input type="text" wire:model.blur="store_zip_code" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none" required placeholder="00000-000">
                </div>
                <div class="space-y-1 sm:col-span-2">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Rua / Avenida *</label>
                    <input type="text" wire:model="street" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div class="space-y-1 sm:col-span-1">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Número *</label>
                    <input type="text" wire:model="number" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none" required>
                </div>
                <div class="space-y-1 sm:col-span-2">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Complemento</label>
                    <input type="text" wire:model="complement" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none" placeholder="Ex: Sala 204, Bloco B">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Bairro *</label>
                    <input type="text" wire:model="neighborhood" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none" required>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Cidade *</label>
                    <input type="text" wire:model="city" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none" required>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Estado (UF) *</label>
                    <input type="text" wire:model="store_state_code" maxlength="2" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-orange-500 outline-none uppercase" required placeholder="Ex: SP">
                </div>
            </div>
        </div>

    </div>
</form>
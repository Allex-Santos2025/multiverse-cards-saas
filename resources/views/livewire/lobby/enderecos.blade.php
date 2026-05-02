<div class="space-y-6">

    {{-- MENSAGENS DE SUCESSO/ERRO --}}
    @if (session()->has('message'))
        <div class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2">
            <i class="ph-fill ph-check-circle text-lg"></i> {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-50 text-red-600 border border-red-200 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2">
            <i class="ph-fill ph-warning-circle text-lg"></i> {{ session('error') }}
        </div>
    @endif

    {{-- CONTROLE DE EXIBIÇÃO: FORMULÁRIO vs LISTA --}}
    @if($showForm)
        
        {{-- MODO FORMULÁRIO --}}      
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-black text-slate-900">{{ $endereco_id ? 'Editar Endereço' : 'Novo Endereço' }}</h3>
                <button wire:click="cancelar" class="text-xs font-bold text-slate-400 hover:text-slate-900 transition-colors">Voltar para lista</button>
            </div>

            <div class="p-6 space-y-6">
                
                {{-- Linha 1: Título e Destinatário --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Identificação (Ex: Casa, Trabalho)</label>
                        <input wire:model="title" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                        @error('title') <span class="text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Nome do Recebedor</label>
                        <input wire:model="receiver_name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                        @error('receiver_name') <span class="text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr class="border-slate-100">

                {{-- Linha 2: CEP e Rua --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">CEP</label>
                        {{-- O .live.blur é o gatilho perfeito para o Livewire 3 --}}
                        <input wire:model.live.blur="zip_code" type="text" placeholder="00000-000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                        @error('zip_code') <span class="text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Endereço (Rua, Avenida)</label>
                        <input wire:key="street-{{ $street }}" wire:model="street" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                        @error('street') <span class="text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Linha 3: Número, Complemento e Bairro --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Número</label>
                        <input wire:model="number" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                        @error('number') <span class="text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Complemento</label>
                        <input wire:model="complement" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all" placeholder="Apto, Bloco...">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Bairro</label>
                        <input wire:key="neighborhood-{{ $neighborhood }}" wire:model="neighborhood" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                        @error('neighborhood') <span class="text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Linha 4: Cidade e Estado --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Cidade</label>
                        <input wire:key="city-{{ $city }}" wire:model="city" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                        @error('city') <span class="text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Estado (UF)</label>
                        <input wire:key="state-{{ $state }}" wire:model="state" type="text" maxlength="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all uppercase">
                        @error('state') <span class="text-[10px] font-bold text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Checkbox de Oficial --}}
                <div class="pt-4 flex items-center gap-3">
                    <input wire:model="is_official" type="checkbox" id="is_official" class="w-5 h-5 text-orange-500 bg-slate-50 border-slate-200 rounded focus:ring-orange-500 focus:ring-2 cursor-pointer">
                    <label for="is_official" class="text-sm font-bold text-slate-700 cursor-pointer">
                        Definir como meu endereço principal de entrega
                    </label>
                </div>

            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button wire:click="cancelar" class="bg-white border border-slate-200 text-slate-600 hover:text-slate-900 font-black text-xs uppercase px-8 py-4 rounded-xl transition-all shadow-sm">
                    Cancelar
                </button>
                <button wire:click="salvarEndereco" class="bg-slate-900 hover:bg-orange-600 text-white font-black text-xs uppercase px-8 py-4 rounded-xl transition-all shadow-lg flex items-center gap-2">
                    <i class="ph-bold ph-check"></i> Salvar Endereço
                </button>
            </div>
        </div>

    @else
        
        {{-- MODO LISTA DE ENDEREÇOS --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Meus Endereços</h2>
                <p class="text-xs text-slate-500 mt-1">Gerencie os locais para onde enviaremos suas cartas ({{ count($enderecos) }}/3).</p>
            </div>
            @if(count($enderecos) < 3)
                <button wire:click="novoEndereco" class="bg-slate-900 hover:bg-orange-500 text-white font-black text-xs uppercase px-5 py-2.5 rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                    <i class="ph-bold ph-plus"></i> Novo Endereço
                </button>
            @endif
        </div>

        @if(count($enderecos) === 0)
            <div class="bg-white border-2 border-dashed border-slate-200 rounded-2xl p-10 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-4">
                    <i class="ph-fill ph-map-pin-line text-3xl"></i>
                </div>
                <h3 class="font-black text-slate-900 text-lg mb-1">Nenhum endereço cadastrado</h3>
                <p class="text-sm text-slate-500 mb-6 max-w-sm">Você precisa de pelo menos um endereço cadastrado para finalizar compras no marketplace.</p>
                <button wire:click="novoEndereco" class="bg-orange-500 hover:bg-orange-600 text-white font-black text-xs uppercase px-6 py-3 rounded-xl transition-all shadow-md shadow-orange-500/20">
                    Cadastrar Meu Primeiro Endereço
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($enderecos as $endereco)
                    <div class="bg-white rounded-2xl shadow-sm border {{ $endereco->is_official ? 'border-orange-400' : 'border-slate-200' }} p-6 flex flex-col relative group transition-colors hover:border-slate-300">
                        
                        {{-- Tag de Principal --}}
                        @if($endereco->is_official)
                            <div class="absolute -top-3 -right-3 bg-emerald-500 text-white text-[9px] font-black uppercase tracking-wider px-3 py-1.5 rounded-lg shadow-sm shadow-emerald-500/30 flex items-center gap-1">
                                <i class="ph-fill ph-star"></i> Oficial
                            </div>
                        @endif

                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl {{ $endereco->is_official ? 'bg-orange-50 text-orange-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center">
                                <i class="ph-fill {{ $endereco->title == 'Casa' ? 'ph-house' : ($endereco->title == 'Trabalho' ? 'ph-buildings' : 'ph-map-pin') }} text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-900 leading-tight">{{ $endereco->title }}</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">A/C: {{ $endereco->receiver_name }}</p>
                            </div>
                        </div>

                        <div class="text-sm font-medium text-slate-600 leading-relaxed flex-1 mb-6">
                            <p>{{ $endereco->street }}, {{ $endereco->number }}</p>
                            @if($endereco->complement)
                                <p class="text-slate-400 text-xs">{{ $endereco->complement }}</p>
                            @endif
                            <p>{{ $endereco->neighborhood }} - {{ $endereco->city }}/{{ $endereco->state }}</p>
                            
                            {{-- Linha do CEP com o Botão do Google Maps --}}
                            <div class="flex items-center justify-between mt-3">
                                <p class="font-bold text-slate-900">CEP: {{ $endereco->zip_code }}</p>
                                
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($endereco->street . ', ' . $endereco->number . ' ' . $endereco->neighborhood . ', ' . $endereco->city . ' - ' . $endereco->state . ' ' . $endereco->zip_code) }}" 
                                   target="_blank" 
                                   class="text-[10px] font-black uppercase text-blue-600 hover:text-white bg-blue-50 hover:bg-blue-500 border border-blue-200 hover:border-blue-500 transition-all px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm">
                                    <i class="ph-bold ph-map-trifold text-sm"></i> Ver no Mapa
                                </a>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <div class="flex gap-2">
                                <button wire:click="editarEndereco({{ $endereco->id }})" class="text-xs font-bold text-slate-500 hover:text-orange-600 transition-colors">Editar</button>
                                <span class="text-slate-200">•</span>
                                <button onclick="confirm('Tem certeza que deseja apagar este endereço?') || event.stopImmediatePropagation()" wire:click="excluirEndereco({{ $endereco->id }})" class="text-xs font-bold text-slate-400 hover:text-red-500 transition-colors">Excluir</button>
                            </div>
                            
                            @if(!$endereco->is_official)
                                <button wire:click="tornarOficial({{ $endereco->id }})" class="text-[10px] font-black uppercase text-slate-400 hover:text-emerald-600 transition-colors flex items-center gap-1">
                                    <i class="ph-bold ph-check"></i> Tornar Principal
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
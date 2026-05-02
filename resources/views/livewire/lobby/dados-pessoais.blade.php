<div class="space-y-6">
    
    {{-- BLOCO: Avatar de Jogador --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-black text-slate-900">Avatar de Jogador</h3>
        </div>
        <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center gap-6">
            
            @php
                $inicialPreview = mb_strtoupper(mb_substr($name ?? $nickname ?? 'U', 0, 1));
            @endphp
            
            <div class="relative w-24 h-24 rounded-full bg-slate-50 border-4 border-white shadow-md overflow-hidden shrink-0 flex items-center justify-center">
                @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                @elseif ($avatar)
                    <img src="{{ asset($avatar) }}" class="w-full h-full object-cover">
                @else
                    <span class="font-black text-4xl text-slate-400">{{ $inicialPreview }}</span>
                @endif
                
                <div wire:loading wire:target="photo" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                    <i class="ph-bold ph-spinner animate-spin text-2xl text-orange-500"></i>
                </div>
            </div>
            
            <div class="space-y-4 flex-1 w-full">
                <div>
                    <label class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-orange-500 text-white text-xs font-black uppercase rounded-lg cursor-pointer transition-colors shadow-sm">
                        <i class="ph-bold ph-upload-simple"></i> Escolher foto
                        <input type="file" wire:model="photo" class="hidden" accept="image/*">
                    </label>
                    @error('photo') <span class="text-[10px] font-bold text-red-500 block mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div class="space-y-2">
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Ou escolha um avatar de Magic:</p>
                    <div class="flex flex-wrap gap-4 items-start">
                        @foreach([
                            ['nome' => 'Gideon Jura', 'url' => '/store_images/magic/avatar/Gideon Jura.jpg'],
                            ['nome' => 'Jace Beleren', 'url' => '/store_images/magic/avatar/Jace Beleren.jpg'],
                            ['nome' => 'Liliana Vess', 'url' => '/store_images/magic/avatar/Liliana Vess.jpg'],
                            ['nome' => 'Chandra Nalaar', 'url' => '/store_images/magic/avatar/Chandra Nalaar.jpg'],
                            ['nome' => 'Nissa Revane', 'url' => '/store_images/magic/avatar/Nissa Revane.jpg']
                        ] as $p)
                            <div class="flex flex-col items-center gap-1.5 w-[52px]">
                                <button type="button" wire:click="selecionarAvatar('{{ $p['url'] }}')" class="w-12 h-12 rounded-full border-2 overflow-hidden transition-all {{ $avatar === $p['url'] && !$photo ? 'border-orange-500 scale-110 shadow-md shadow-orange-500/30' : 'border-slate-200 opacity-60 hover:opacity-100' }}">
                                    <img src="{{ asset($p['url']) }}" alt="{{ $p['nome'] }}" class="w-full h-full object-cover">
                                </button>
                                <span class="text-[8px] font-bold text-slate-500 text-center leading-tight">{{ $p['nome'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BLOCO ORIGINAL: Informações Básicas --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-black text-slate-900">Informações Básicas</h3>
            @if (session()->has('message'))
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full animate-bounce">
                    {{ session('message') }}
                </span>
            @endif
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Nome</label>
                    <input wire:model.defer="name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Sobrenome</label>
                    <input wire:model.defer="surname" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Nickname (Exibido no Marketplace)</label>
                    <input wire:model.defer="nickname" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">E-mail</label>
                    <input wire:model.defer="email" type="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                </div>
            </div>
        </div>
    </div>

    {{-- BLOCO ORIGINAL: Documentação e Contato --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="font-black text-slate-900">Documentação e Contato</h3>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">CPF</label>
                    <input wire:model.defer="document_number" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">RG</label>
                    <input wire:model.defer="id_document_number" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Data de Nascimento</label>
                    <input wire:model.defer="birth_date" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                </div>
            </div>

            <div class="mt-6">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">WhatsApp / Telefone</label>
                    <input wire:model.defer="phone_number" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all" placeholder="(00) 00000-0000">
                </div>
            </div>
        </div>
    </div>

    {{-- BLOCO: Segurança da Conta --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="font-black text-slate-900">Segurança da Conta</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Senha Atual</label>
                    <input wire:model="current_password" type="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                    @error('current_password') <span class="text-[10px] font-bold text-red-500 block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Nova Senha</label>
                    <input wire:model="new_password" type="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                    @error('new_password') <span class="text-[10px] font-bold text-red-500 block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Confirmar Nova Senha</label>
                    <input wire:model="new_password_confirmation" type="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                </div>
            </div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
            <button wire:click="salvar" class="bg-slate-900 hover:bg-orange-600 text-white font-black text-xs uppercase px-8 py-4 rounded-xl transition-all shadow-lg flex items-center gap-2">
                <i class="ph-bold ph-check"></i> Salvar Alterações
            </button>
        </div>
    </div>
</div>
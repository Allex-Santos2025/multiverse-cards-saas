<div class="flex flex-col min-h-screen" style="font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #1f2937;">
    
    {{-- BREADCRUMB CONDICIONAL --}}
    @if(isset($loja))
        <div class="py-2" style="background-color: var(--cor-secundaria); color: var(--cor-texto-secundaria);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="text-xs font-bold flex gap-2 items-center">
                    <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" class="hover:underline opacity-90">Home</a>
                    <span class="opacity-50">></span>
                    <span>Minha Conta</span>
                </nav>
            </div>
        </div>
    @else
        <div class="h-10 sm:h-16 w-full"></div>
    @endif

    <div class="py-10 max-w-7xl mx-auto w-full px-4">
        
        <h1 class="text-3xl font-black text-gray-900 mb-8 tracking-tighter">
            Minha Conta
        </h1>

        <div class="flex flex-col lg:flex-row gap-8">
            
            {{-- MENU LATERAL (ASIDE) --}}
            <aside class="w-full lg:w-64 flex-shrink-0">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col overflow-hidden">
                    
                    {{-- ÁREA DE PERFIL --}}
                    <div class="p-6 flex flex-col items-center border-b border-slate-100">
                        <div class="w-20 h-20 rounded-full bg-orange-50 border border-orange-200 flex items-center justify-center mb-3 relative shadow-sm">
                            {{-- Lógica da Foto vs Emoji implementada aqui --}}
                            @if ($playerAvatar)
                                <img src="{{ asset($playerAvatar) }}" alt="Avatar" class="w-full h-full object-cover rounded-full">
                            @else
                                <span class="text-3xl">😎</span>
                            @endif
                            <div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></div>
                        </div>
                        <h2 class="text-lg font-black text-slate-900 mb-1 text-center leading-tight">
                            {{ strtoupper($playerName ?? 'Player') }}
                        </h2>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Crédito: R$ 150,00</span>
                        </div>
                    </div>

                    {{-- NAVEGAÇÃO --}}
                    <nav class="flex flex-col gap-1 p-3">
                        <button wire:click="switchAba('dashboard')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition-all w-full text-left {{ $abaAtiva === 'dashboard' ? 'text-orange-600 bg-orange-50 shadow-sm shadow-orange-500/5' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i class="ph ph-squares-four text-lg"></i> Visão Geral
                        </button>

                        <button wire:click="switchAba('dados')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition-all w-full text-left {{ $abaAtiva === 'dados' ? 'text-orange-600 bg-orange-50 shadow-sm shadow-orange-500/5' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i class="ph ph-user text-lg"></i> Meus Dados
                        </button>

                        <button wire:click="switchAba('enderecos')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition-all w-full text-left {{ $abaAtiva === 'enderecos' ? 'text-orange-600 bg-orange-50 shadow-sm shadow-orange-500/5' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i class="ph ph-map-pin text-lg"></i> Endereços
                        </button>
                        
                        <hr class="border-slate-100 my-1">
                        
                        <button wire:click="switchAba('pedidos')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition-all w-full text-left {{ $abaAtiva === 'pedidos' ? 'text-orange-600 bg-orange-50 shadow-sm shadow-orange-500/5' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i class="ph ph-shopping-bag text-lg"></i> Meus Pedidos
                        </button>

                        <button wire:click="switchAba('vendas')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition-all w-full text-left {{ $abaAtiva === 'vendas' ? 'text-orange-600 bg-orange-50 shadow-sm shadow-orange-500/5' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i class="ph ph-handshake text-lg"></i> Minhas Vendas
                        </button>
                        
                        <button wire:click="switchAba('colecao')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition-all w-full text-left {{ $abaAtiva === 'colecao' ? 'text-orange-600 bg-orange-50 shadow-sm shadow-orange-500/5' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i class="ph ph-archive text-lg"></i> Minha Coleção
                        </button>

                        <button wire:click="switchAba('decks')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition-all w-full text-left {{ $abaAtiva === 'decks' ? 'text-orange-600 bg-orange-50 shadow-sm shadow-orange-500/5' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i class="ph ph-cards text-lg"></i> Meus Decks
                        </button>

                        <button wire:click="switchAba('wantlist')" class="flex items-center justify-between px-4 py-2.5 rounded-xl text-sm font-bold transition-all w-full text-left {{ $abaAtiva === 'wantlist' ? 'text-orange-600 bg-orange-50 shadow-sm shadow-orange-500/5' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                            <div class="flex items-center gap-3">
                                <i class="ph ph-heart text-lg"></i> Want List
                            </div>
                            <span class="bg-red-100 text-red-600 text-[9px] uppercase tracking-wider px-1.5 py-0.5 rounded-full font-black">Alertas</span>
                        </button>
                        
                        <hr class="border-slate-100 my-1">

                        <button wire:click="switchAba('carteira')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition-all w-full text-left {{ $abaAtiva === 'carteira' ? 'text-orange-600 bg-orange-50 shadow-sm shadow-orange-500/5' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}">
                            <i class="ph ph-wallet text-lg"></i> Carteira
                        </button>
                    </nav>
                </div>
            </aside>

            {{-- ÁREA DE CONTEÚDO DINÂMICO --}}
            <main class="flex-1">
                @if($abaAtiva === 'dashboard')
                    <livewire:lobby.dashboard :loja="$loja" />
                @elseif($abaAtiva === 'dados')
                    <livewire:lobby.dados-pessoais />
                @elseif($abaAtiva === 'enderecos')
                    <livewire:lobby.enderecos />
                @elseif($abaAtiva === 'pedidos')
                    <livewire:lobby.meus-pedidos />
                @elseif($abaAtiva === 'vendas')
                    <livewire:lobby.minhas-vendas />
                @elseif($abaAtiva === 'colecao')
                    <livewire:lobby.colecao />
                @elseif($abaAtiva === 'decks')
                    <livewire:lobby.decks />
                @elseif($abaAtiva === 'wantlist')
                    <livewire:lobby.wantlist />
                @elseif($abaAtiva === 'carteira')
                    <livewire:lobby.carteira />
                @else
                    {{-- Caso de segurança para abas não mapeadas --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8 min-h-[600px]">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 capitalize">Minha {{ $abaAtiva }}</h2>
                        <p class="text-sm text-gray-500">Conteúdo em desenvolvimento...</p>
                    </div>
                @endif
            </main>

        </div>
    </div>
</div>
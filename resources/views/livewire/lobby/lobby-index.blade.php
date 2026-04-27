<div class="flex flex-col min-h-screen" style="font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #1f2937;">
    
    {{-- BREADCRUMB CONDICIONAL APENAS PARA A LOJA --}}
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
        {{-- COMPENSAÇÃO DE MENU PARA O MARKETPLACE --}}
        <div class="h-10 sm:h-16 w-full"></div>
    @endif

    <div class="py-10 max-w-7xl mx-auto w-full px-4">
        
        <h1 class="text-3xl font-black text-gray-900 mb-8 tracking-tighter">
            Minha Conta
        </h1>

        <div class="flex flex-col lg:flex-row gap-8">
            
            {{-- MENU LATERAL (ASIDE) --}}
            <aside class="w-full lg:w-64 flex-shrink-0 space-y-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <nav class="flex flex-col space-y-1">
                        <button wire:click="switchAba('dados')" 
                            class="text-left px-4 py-2 rounded-lg font-bold text-sm transition-colors {{ $abaAtiva === 'dados' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                            Meus Dados
                        </button>
                        <button wire:click="switchAba('enderecos')" 
                            class="text-left px-4 py-2 rounded-lg font-bold text-sm transition-colors {{ $abaAtiva === 'enderecos' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                            Endereços
                        </button>
                        <button wire:click="switchAba('pedidos')" 
                            class="text-left px-4 py-2 rounded-lg font-bold text-sm transition-colors {{ $abaAtiva === 'pedidos' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                            Meus Pedidos
                        </button>
                        <button wire:click="switchAba('carteira')" 
                            class="text-left px-4 py-2 rounded-lg font-bold text-sm transition-colors {{ $abaAtiva === 'carteira' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
                            Carteira
                        </button>
                    </nav>
                </div>
            </aside>

            {{-- ÁREA DE CONTEÚDO DINÂMICO --}}
            <main class="flex-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 min-h-[400px]">
                    
                    @if($abaAtiva === 'dados')
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Meus Dados Pessoais</h2>
                        <p class="text-sm text-gray-500">Formulários de Nome, CPF e Telefone serão inseridos aqui.</p>
                    
                    @elseif($abaAtiva === 'enderecos')
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Meus Endereços</h2>
                        <p class="text-sm text-gray-500">Lista e cadastro de CEPs para cálculo de frete.</p>
                    
                    @elseif($abaAtiva === 'pedidos')
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Histórico de Pedidos</h2>
                        <p class="text-sm text-gray-500">Suas compras recentes aparecerão aqui.</p>
                    
                    @elseif($abaAtiva === 'carteira')
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Minha Carteira</h2>
                        <p class="text-sm text-gray-500">Extrato financeiro e saldos.</p>
                    @endif

                </div>
            </main>

        </div>
    </div>
</div>
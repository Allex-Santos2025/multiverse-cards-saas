<div class="flex flex-col min-h-screen" style="font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #1f2937;">
    
    {{-- BREADCRUMB --}}
    @if(isset($loja))
        <div class="py-2" style="background-color: var(--cor-secundaria); color: var(--cor-texto-secundaria);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="text-xs font-bold flex gap-2 items-center">
                    <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" class="hover:underline opacity-90">Home</a>
                    <span class="opacity-50">></span>
                    <a href="{{ route('store.cart', ['slug' => $loja->url_slug]) }}" class="hover:underline opacity-90">Carrinho</a>
                    <span class="opacity-50">></span>
                    <span>Checkout</span>
                </nav>
            </div>
        </div>
    @else
        <div class="h-10 sm:h-20 w-full"></div>
    @endif

    <div class="py-10 max-w-7xl mx-auto w-full px-4">
        
        <h1 class="text-3xl font-black text-gray-900 mb-8 flex items-center gap-3">
            <i class="ph ph-shield-check text-blue-600"></i>
            Finalizar Pedido
        </h1>

        @if(!$pedidoFinalizado)
            <div class="flex flex-col lg:flex-row gap-8">
                
                {{-- LADO ESQUERDO: ETAPAS DO PEDIDO --}}
                <div class="flex-1 space-y-6">
                    
                    {{-- ETAPA 1: DADOS PESSOAIS --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">1</div>
                            <h2 class="font-black text-gray-900 uppercase tracking-tight text-sm">Dados do Comprador</h2>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2 space-y-1">
                                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Nome Completo</label>
                                <input type="text" wire:model="nome" class="w-full border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm font-bold text-gray-700 bg-gray-50">
                                @error('nome') <span class="text-red-500 text-[10px] font-bold uppercase">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">E-mail</label>
                                <input type="email" wire:model="email" class="w-full border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm font-bold text-gray-700 bg-gray-50">
                                @error('email') <span class="text-red-500 text-[10px] font-bold uppercase">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest">CPF / Documento</label>
                                <input type="text" wire:model="cpf" class="w-full border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm font-bold text-gray-700 bg-gray-50">
                                @error('cpf') <span class="text-red-500 text-[10px] font-bold uppercase">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ETAPA 2: ENDEREÇO DE ENTREGA --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">2</div>
                                <h2 class="font-black text-gray-900 uppercase tracking-tight text-sm">Onde entregar?</h2>
                            </div>
                            
                            {{-- Botão para adicionar endereço (Sessão Bumerangue) --}}
                            @if(count($enderecos) < 3)
                                <button wire:click="irParaCadastroEndereco" class="text-blue-600 hover:text-blue-700 text-[10px] font-black uppercase flex items-center gap-1 transition-all">
                                    <i class="ph ph-plus-circle text-sm"></i>
                                    Novo Endereço
                                </button>
                            @endif
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @forelse($enderecos as $end)
                                    <div wire:click="selecionarEndereco({{ $end->id }})" 
                                         class="cursor-pointer border-2 p-4 rounded-xl transition-all relative {{ $enderecoSelecionadoId == $end->id ? 'border-blue-600 bg-blue-50' : 'border-gray-100 hover:border-gray-200' }}">
                                        
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-[10px] font-black uppercase {{ $enderecoSelecionadoId == $end->id ? 'text-blue-600' : 'text-gray-400' }} tracking-widest">
                                                {{ $end->title }}
                                            </span>
                                            @if($enderecoSelecionadoId == $end->id)
                                                <i class="ph-fill ph-check-circle text-blue-600 text-xl"></i>
                                            @endif
                                        </div>

                                        <p class="text-xs font-bold text-gray-800 leading-tight">{{ $end->street }}, {{ $end->number }}</p>
                                        <p class="text-[10px] text-gray-500 uppercase mt-1">
                                            {{ $end->neighborhood }} • {{ $end->city }}/{{ $end->state }}
                                        </p>
                                    </div>
                                @empty
                                    <div class="col-span-full py-8 text-center border-2 border-dashed border-gray-200 rounded-xl">
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-4">Você ainda não tem endereços salvos.</p>
                                        <button wire:click="irParaCadastroEndereco" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-black text-xs uppercase italic">
                                            Cadastrar Primeiro Endereço
                                        </button>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- ETAPA 3: OPÇÕES DE FRETE (Dinâmico por Loja - Dropdown) --}}
                    @if($enderecoSelecionadoId && !empty($fretesPorLoja))
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-5 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">3</div>
                                <h2 class="font-black text-gray-900 uppercase tracking-tight text-sm">Escolha o Frete</h2>
                            </div>
                            <div class="p-6 space-y-6">
                                @foreach($fretesPorLoja as $storeId => $opcoes)
                                    @php $store = $cartByStore[$storeId]['items']->first()->stockItem->store ?? null; @endphp
                                    
                                    @if($store)
                                        <div class="border border-gray-100 rounded-xl p-4">
                                            <div class="flex items-center gap-2 mb-4">
                                                <i class="ph ph-storefront text-gray-400"></i>
                                                <span class="text-[10px] font-black uppercase text-gray-500 tracking-widest">Loja: {{ $store->name }}</span>
                                            </div>
                                            
                                            <div class="w-full">
                                                {{-- DROPDOWN COM WIRE:MODEL.LIVE PARA CALCULAR EM TEMPO REAL --}}
                                                <select wire:model.live="selectedShipping.{{ $storeId }}" class="w-full border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm font-bold text-gray-700 bg-gray-50 py-3 px-4 uppercase">
                                                    @foreach($opcoes as $key => $opcao)
                                                        <option value="{{ $key }}">
                                                            {{ $opcao['nome'] }} - R$ {{ number_format($opcao['valor'], 2, ',', '.') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                
                                                {{-- Exibe a descrição do frete selecionado --}}
                                                @if(isset($selectedShipping[$storeId]) && isset($opcoes[$selectedShipping[$storeId]]['descricao']) && !empty($opcoes[$selectedShipping[$storeId]]['descricao']))
                                                    <p class="text-[10px] text-gray-500 leading-tight mt-3 italic px-1">
                                                        <i class="ph ph-info mr-1"></i>{{ $opcoes[$selectedShipping[$storeId]]['descricao'] }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ETAPA 4: PAGAMENTO --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">4</div>
                            <h2 class="font-black text-gray-900 uppercase tracking-tight text-sm">Pagamento</h2>
                        </div>
                        <div class="p-6">
                            <div class="border-2 border-blue-600 bg-blue-50/50 p-4 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-white rounded-lg border border-blue-200 flex items-center justify-center">
                                        <i class="ph ph-qr-code text-blue-600 text-2xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-gray-900 text-sm italic uppercase tracking-tighter">PIX</p>
                                        <p class="text-xs text-gray-500 italic">Aprovação imediata • O QR Code será gerado ao finalizar.</p>
                                    </div>
                                </div>
                                <i class="ph-fill ph-check-circle text-blue-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- LADO DIREITO: RESUMO FIXO --}}
                <div class="w-full lg:w-96 flex-shrink-0">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 sticky top-24">
                        <h2 class="text-lg font-black text-gray-900 mb-6 uppercase italic tracking-tighter">Resumo da Compra</h2>

                        @if (session()->has('error'))
                            <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-lg text-red-600 text-xs font-bold flex items-start gap-2 italic">
                                <i class="ph ph-warning-circle text-lg"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="space-y-3 mb-6 border-b border-gray-100 pb-6">
                            <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                                <span>Itens</span>
                                <span class="text-gray-900">R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                                <span>Frete Total</span>
                                <span class="text-gray-900">R$ {{ number_format($shippingTotal, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-end mb-8">
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Geral</span>
                            <div class="text-right">
                                <span class="block text-3xl font-black text-gray-900">R$ {{ number_format($totalGeral, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <button wire:click="processarPagamento" wire:loading.attr="disabled" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-lg shadow-lg shadow-blue-600/20 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3 uppercase italic tracking-tighter">
                            <span wire:loading.remove>Finalizar e Gerar PIX</span>
                            <span wire:loading><i class="ph ph-circle-notch animate-spin text-xl"></i> Processando...</span>
                        </button>
                        
                        <p class="mt-4 text-[9px] text-gray-400 text-center font-bold uppercase tracking-widest">
                            Ambiente Seguro • Versus TCG
                        </p>
                    </div>
                </div>

            </div>
        @else
            {{-- TELA DE SUCESSO / PIX --}}
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden text-center">
                    <div class="bg-blue-600 p-8 text-white">
                        <i class="ph ph-qr-code text-5xl mb-4"></i>
                        <h2 class="text-2xl font-black italic uppercase tracking-tighter">Quase lá!</h2>
                        <p class="text-sm opacity-90">Efetue o pagamento do PIX para confirmar seu pedido.</p>
                    </div>

                    <div class="p-10 flex flex-col items-center">
                        <div class="p-4 bg-white border-2 border-gray-100 rounded-2xl shadow-inner mb-8">
                            @if($qrCodeBase64)
                                <img src="data:image/png;base64, {{ $qrCodeBase64 }}" class="w-64 h-64">
                            @endif
                        </div>

                        <div class="w-full space-y-4 text-left">
                            <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1 italic">Código Copia e Cola</label>
                            <div class="flex gap-2">
                                <input type="text" readonly value="{{ $qrCodeCopiaCola }}" class="w-full bg-gray-50 border-gray-200 rounded-lg text-xs font-mono py-3 px-4 text-gray-600">
                                <button onclick="navigator.clipboard.writeText('{{ $qrCodeCopiaCola }}')" class="bg-gray-900 text-white px-5 rounded-lg font-bold text-xs hover:bg-black transition-colors uppercase italic">Copiar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
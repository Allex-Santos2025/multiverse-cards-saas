<div class="flex flex-col min-h-screen" style="font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #1f2937;">
    <style>
        .card-thumb {
            transition: transform 0.2s;
        }
        .card-thumb:hover {
            transform: scale(1.1) rotate(2deg);
            z-index: 10;
        }
    </style>

    {{-- BREADCRUMB CONDICIONAL APENAS PARA A LOJA --}}
    @if(isset($loja))
        <div class="py-2" style="background-color: var(--cor-secundaria); color: var(--cor-texto-secundaria);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="text-xs font-bold flex gap-2 items-center">
                    <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" class="hover:underline opacity-90">Home</a>
                    <span class="opacity-50">></span>
                    <span>Carrinho</span>
                </nav>
            </div>
        </div>
    @else
        {{-- COMPENSAÇÃO DE MENU PARA O MARKETPLACE --}}
        {{-- Altura fixa para criar o mesmo distanciamento visual que o breadcrumb cria na loja --}}
        <div class="h-10 sm:h-20 w-full"></div>
    @endif

    <div class="py-10 max-w-7xl mx-auto w-full px-4">
        
        {{-- TÍTULO E CONTAGEM --}}
        <h1 class="text-3xl font-black text-gray-900 mb-8 flex items-center gap-3">
            Seu Carrinho
            <span class="text-sm font-medium text-gray-500 bg-gray-200 px-3 py-1 rounded-full">
                {{ $totalItems ?? 0 }} Itens
            </span>
        </h1>

        <div class="flex flex-col lg:flex-row gap-8">

            {{-- LADO ESQUERDO: LISTA DE LOJAS E PRODUTOS --}}
            <div class="flex-1 space-y-8">

                @forelse($cartByStore ?? [] as $storeId => $storeCart)
                    {{-- BLOCO DA LOJA --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden group hover:border-[var(--cor-1,#60a5fa)]/30 transition-colors">
                        
                        {{-- Header da Loja condicionado ao Marketplace --}}
                        @if($isMarketplace)
                            <div class="bg-white p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div class="flex items-center gap-5 cursor-pointer hover:opacity-80 transition-opacity">
                                    {{-- Logo com Inversão de Cor Dinâmica --}}
                                    <div class="flex items-center justify-center overflow-hidden">
                                        @if(isset($storeCart['store']->visual->logo_main))
                                            <img src="{{ asset('store_images/' . $storeCart['store']->url_slug . '/' . $storeCart['store']->visual->logo_main) }}" 
                                                alt="Logo" 
                                                class="max-h-12 w-auto object-contain transition-all duration-300 invert"
                                                style="filter: brightness(0);">
                                        @else
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-zinc-900 text-white font-black text-xs italic">
                                                {{ substr($storeCart['store']->name ?? 'VS', 0, 2) }}
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 tracking-tight">
                                            {{ $storeCart['store']->name ?? 'Nome da Loja' }}
                                            
                                            @if(isset($storeCart['store']->is_oficial) && $storeCart['store']->is_oficial)
                                                <i class="ph-fill ph-seal-check text-blue-500 text-lg" title="Loja Oficial"></i>
                                            @endif
                                        </h3>
                                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                            <i class="ph ph-map-pin"></i>
                                            {{ $storeCart['store']->cidade ?? 'Cidade' }} - {{ $storeCart['store']->estado ?? 'UF' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Ações da Loja - Mais enxutas --}}
                                <div class="flex items-center gap-3 w-full sm:w-auto">
                                    <a href="{{ route('store.view', ['slug' => $storeCart['store']->url_slug ?? '#']) }}" 
                                    class="text-[11px] font-black uppercase tracking-widest text-gray-500 hover:text-gray-900 transition-colors">
                                        Ver vitrine
                                    </a>
                                    <button class="flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-white px-5 py-2.5 rounded-lg transition-all shadow-md shadow-blue-500/10 hover:scale-105 active:scale-95"
                                            style="background-color: var(--cor-1, #2563eb);">
                                        <i class="ph ph-plus-circle text-sm"></i>
                                        Aproveitar Frete
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Lista de Itens da Loja --}}
                        <div class="p-5 space-y-4">
                            
                            {{-- CABEÇALHO DAS COLUNAS DA TABELA --}}
                            <div class="hidden sm:flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-100 mb-2 text-[10px] font-black uppercase tracking-widest text-gray-500">
                                <div class="w-1/2 pl-2">Produto</div>
                                <div class="w-1/4 text-center">Quantidade</div>
                                <div class="w-1/4 text-right pr-2">Preço / Subtotal</div>
                            </div>

                            @foreach($storeCart['items'] ?? [] as $item)
                                {{-- LINHA DO ITEM: Alinhamento horizontal em 3 colunas --}}
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 py-2">
                                    
                                    {{-- COLUNA 1: PRODUTO (50%) --}}
                                    <div class="flex items-start gap-4 w-full sm:w-1/2">
                                        <div class="w-12 h-16 bg-gray-200 rounded shrink-0 overflow-hidden relative border border-gray-200 card-thumb cursor-pointer">
                                            <img src="{{ $item->imagem_final ?? 'https://cards.scryfall.io/large/front/5/6/567ab685-6123-4560-9d0d-b4b66df87271.jpg' }}" class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex flex-col justify-start">
                                            <h4 class="font-bold text-gray-900 text-sm hover:text-blue-600 cursor-pointer">{{ $item->nome_localizado ?? 'Nome da Carta' }}</h4>
                                            <div class="flex gap-2 mt-1 mb-2">
                                                <span class="text-[10px] bg-gray-100 border border-gray-200 px-1.5 rounded text-gray-600">{{ $item->edicao ?? 'Edição' }}</span>
                                                <span class="text-[10px] bg-green-100 border border-green-200 px-1.5 rounded text-green-700 font-bold">{{ $item->condicao ?? 'NM' }}</span>
                                                <span class="text-[10px] bg-gray-100 border border-gray-200 px-1.5 rounded text-gray-600">{{ $item->idioma ?? 'PT' }}</span>
                                            </div>
                                            {{-- Botão remover fixo abaixo do nome e das tags --}}
                                            <button wire:click="removeItem({{ $item->id }})" class="text-[10px] text-red-500 hover:text-red-700 font-bold text-left w-max flex items-center gap-1 uppercase tracking-tighter">
                                                <i class="ph ph-trash"></i> Remover
                                            </button>
                                        </div>
                                    </div>

                                    {{-- COLUNA 2: QUANTIDADE (25%) --}}
                                    <div class="w-full sm:w-1/4 flex flex-col items-center justify-center">
                                        <div class="flex items-center border border-gray-300 rounded bg-white shadow-sm mb-1">
                                            <button wire:click="decrementQuantity({{ $item->id }})" class="px-2 py-0.5 text-gray-500 hover:bg-gray-50 transition-colors">-</button>
                                            <span class="px-3 text-xs font-black text-gray-900">{{ $item->quantity }}</span>
                                            <button wire:click="incrementQuantity({{ $item->id }})" class="px-2 py-0.5 text-gray-500 hover:bg-gray-50 transition-colors">+</button>
                                        </div>
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">
                                            {{ $item->quantity }} de {{ $item->estoque_maximo ?? 'X' }} un.
                                        </span>
                                    </div>

                                    {{-- COLUNA 3: PREÇO / SUBTOTAL (25%) --}}
                                    <div class="w-full sm:w-1/4 flex flex-col items-end justify-center pr-2">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter mb-0.5">
                                            Un: R$ {{ number_format($item->price, 2, ',', '.') }}
                                        </span>
                                        <span class="font-black text-gray-900 text-sm">
                                            R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}
                                        </span>
                                    </div>

                                </div>

                                @if(!$loop->last)
                                    <hr class="border-gray-100 my-2">
                                @endif
                            @endforeach
                        </div>

                        {{-- Footer da Loja (Frete e Subtotal) --}}
                        <div class="bg-gray-50 p-4 border-t border-gray-200">
                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    {{-- Select condicionado ao CEP Global e vinculado ao Livewire --}}
                                    @if($cep)
                                        <label class="text-[10px] font-bold text-gray-500 uppercase flex justify-between">
                                            <span>Entrega para {{ $cep }}</span>
                                        </label>
                                        <div class="mt-1 relative">
                                            <select wire:model.live="selectedShipping.{{ $storeId }}" class="w-full text-xs border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 bg-white">
                                                <option value="">Selecione o frete...</option>
                                                <option value="balcao">Retirada no Balcão - R$ 0,00</option>
                                                <option value="sedex">SEDEX - R$ 24,90</option>
                                                <option value="pac">PAC - R$ 12,50</option>
                                            </select>
                                        </div>
                                    @else
                                        <div class="text-[10px] font-bold text-gray-400 uppercase mt-1">
                                            Informe seu CEP no resumo para calcular o frete.
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-500 uppercase font-bold">Total Loja</p>
                                    <p class="text-lg font-bold text-gray-900">R$ {{ number_format($storeCart['total'] ?? 0, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- ESTADO VAZIO --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 flex flex-col items-center justify-center text-center">
                        <i class="ph ph-shopping-cart text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-black text-gray-900">Seu carrinho está vazio</h3>
                        <p class="text-sm text-gray-500 mt-2">Navegue pelas lojas ou pelo marketplace para adicionar cartas.</p>
                        @if(isset($loja))
                            <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" class="mt-6 text-white font-bold px-6 py-3 rounded-lg transition" style="background-color: var(--cor-cta); color: var(--cor-cta-txt);">
                                Voltar para a Loja
                            </a>
                        @else
                            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}" class="mt-6 bg-blue-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                                Voltar Anterior
                            </a>
                        @endif
                    </div>
                @endforelse

            </div>

            {{-- LADO DIREITO: RESUMO DO PEDIDO --}}
            @if(($totalItems ?? 0) > 0)
                <div class="w-full lg:w-96 flex-shrink-0">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 sticky top-24">
                        <h2 class="text-lg font-bold text-gray-900 mb-6">Resumo do Pedido</h2>
                        
                        {{-- Input Global de CEP injetado no Resumo --}}
                        <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-500 uppercase block mb-2">CEP de Entrega</label>
                            <div class="flex gap-2">
                                <input type="text" wire:model.blur="cep" placeholder="00000-000" class="w-full text-xs border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <button class="bg-gray-800 text-white text-xs px-3 rounded font-bold hover:bg-gray-900 transition-colors">OK</button>
                            </div>
                        </div>

                        <div class="space-y-3 mb-6 border-b border-gray-100 pb-6">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Produtos ({{ $totalItems ?? 0 }} itens)</span>
                                <span>R$ {{ number_format($subtotalGeral ?? 0, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Fretes ({{ count($cartByStore ?? []) }} envios)</span>
                                <span>R$ {{ number_format($fretesGeral ?? 0, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-green-600 font-medium">
                                <span>Desconto</span>
                                <span>R$ {{ number_format($descontoGeral ?? 0, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-end mb-6">
                            <span class="text-sm font-bold text-gray-500 uppercase">Total Geral</span>
                            <div class="text-right">
                                <span class="block text-3xl font-black text-gray-900">R$ {{ number_format($totalGeral ?? 0, 2, ',', '.') }}</span>
                                <span class="text-xs text-gray-400">Pix ou Cartão</span>
                            </div>
                        </div>

                        @if(count($cartByStore ?? []) > 1)
                            {{-- Aviso de pagamento único apenas se houver mais de uma loja --}}
                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-6 flex gap-3">
                                <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-[10px] text-blue-800 leading-tight">
                                    Pagamento único. O sistema repassa os valores para 
                                    @foreach($cartByStore as $storeId => $storeCart)
                                        <strong>{{ $storeCart['store']->name }}</strong>{{ !$loop->last ? ($loop->remaining == 1 ? ' e ' : ', ') : '' }}
                                    @endforeach
                                    automaticamente.
                                </p>
                            </div>
                        @endif

                        <button class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-4 rounded-lg shadow-lg shadow-green-600/20 transition-all transform hover:-translate-y-1 mb-4">
                            IR PARA PAGAMENTO
                        </button>

                        <div class="flex justify-center gap-2 grayscale opacity-50">
                            <div class="h-6 w-10 bg-gray-100 rounded border flex items-center justify-center text-[8px] font-bold">PIX</div>
                            <div class="h-6 w-10 bg-gray-100 rounded border flex items-center justify-center text-[8px] font-bold">CARD</div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
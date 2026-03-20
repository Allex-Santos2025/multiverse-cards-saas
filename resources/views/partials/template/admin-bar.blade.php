{{-- Verifica se está logado COMO LOJISTA e se a loja acessada é a DELE --}}
@if(auth('store_user')->check() && auth('store_user')->user()->current_store_id == $loja->id)
    <div class="bg-[#0a0a0a] text-gray-300 py-2 px-4 border-b-2 border-[var(--cor-1)] text-xs z-50 relative">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            
            {{-- Lado Esquerdo: Identificação --}}
            <div class="flex items-center space-x-3">
                <span class="bg-[var(--cor-1)] text-white px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider flex items-center shadow-[0_0_10px_var(--cor-1)]">
                    <i class="ph ph-storefront mr-1"></i> Modo Lojista
                </span>
                <span class="font-medium hidden sm:inline">
                    Logado como: <span class="text-white">{{ auth('store_user')->user()->name ?? 'Staff' }}</span>
                </span>
            </div>

            {{-- Lado Direito: Ferramentas (Isso vai mudar dependendo da página) --}}
            <div class="flex items-center space-x-5 font-medium">
                
                {{-- Placeholder: Estes botões vão aparecer quando criarmos a página da carta --}}
                @if(isset($isCardPage) && $isCardPage)
                    <a href="#" class="text-yellow-500 hover:text-yellow-400 flex items-center transition-colors" title="Comparar preços no Versus TCG">
                        <i class="ph ph-scales mr-1 text-base"></i> Comparar Preço
                    </a>
                    <a href="#" class="text-green-500 hover:text-green-400 flex items-center transition-colors" title="Adicionar esta carta ao seu estoque">
                        <i class="ph ph-plus-circle mr-1 text-base"></i> Adicionar ao Estoque
                    </a>
                    <span class="text-gray-600">|</span>
                @endif

                {{-- Botão Fixo: Voltar pro Painel --}}
                <a href="{{ url('/loja/' . $loja->url_slug . '/dashboard') }}" class="hover:text-white flex items-center transition-colors">
                    <i class="ph ph-gauge mr-1 text-base"></i> Painel de Controle
                </a>
            </div>

        </div>
    </div>
@endif
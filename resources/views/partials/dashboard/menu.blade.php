<nav x-data="{ openMenu: null, mobileOpen: false }" class="relative z-[100] px-6 border-t border-zinc-100 dark:border-white/5 bg-white dark:bg-[#0f172a]">
    
    <div class="flex items-center justify-between">
        
        {{-- 1. BLOCO DESKTOP (HORIZONTAL) --}}
        <div class="hidden lg:flex items-center gap-8">
            {{-- Loop para o Desktop para manter o código limpo e completo --}}
            @foreach([
                'dashboard' => ['icon' => 'ph-chart-pie', 'label' => 'Dashboard'],
                'catalogo' => ['icon' => 'ph-package', 'label' => 'Catálogo'],
                'operacoes' => ['icon' => 'ph-shopping-cart', 'label' => 'Operações'],
                'eventos' => ['icon' => 'ph-ticket', 'label' => 'Eventos'],
                'layout' => ['icon' => 'ph-paint-brush-broad', 'label' => 'Layout'],
                'config' => ['icon' => 'ph-gear', 'label' => 'Configurações'],
                'gerenciais' => ['icon' => 'ph-shield-check', 'label' => 'Gerenciais']
            ] as $key => $item)
                <div class="relative">
                    <button @click.stop="openMenu = (openMenu === '{{ $key }}') ? null : '{{ $key }}'" 
                        class="h-12 flex items-center gap-2 text-sm font-bold outline-none cursor-pointer transition-all"
                        :class="openMenu === '{{ $key }}' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-zinc-500 dark:text-zinc-400 border-b-2 border-transparent hover:text-zinc-900 dark:hover:text-white'">
                        <i class="ph {{ $item['icon'] }}"></i> {{ $item['label'] }} <i class="ph ph-caret-down text-[10px]"></i>
                    </button>

                    <div x-show="openMenu === '{{ $key }}'" @click.away="openMenu = null" x-transition class="absolute top-[48px] left-0 w-52 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-white/10 shadow-2xl rounded-b-lg py-2 z-[110]" style="display: none;">
                        @if($key === 'dashboard')
                            <a href="{{ route('store.dashboard', ['slug' => auth('store_user')->user()->store->url_slug]) }}" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Painel Geral</a>
                            <a href="{{ route('store.view', ['slug' => auth('store_user')->user()->store->url_slug]) }}" target="_blank" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Ver Minha Loja ↗</a>
                            <a href="{{ route('store.dashboard.logs', ['slug' => auth('store_user')->user()->store->url_slug]) }}" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Logs do Sistema</a>
                            <a href="{{ route('store.dashboard.novidades', ['slug' => auth('store_user')->user()->store->url_slug]) }}" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Novidades</a>
                        @elseif($key === 'catalogo')
                            <a href="{{ route('store.dashboard.stock.index', ['slug' => auth('store_user')->user()->store->url_slug, 'game_slug' => 'magic']) }}" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Estoque</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Buylist</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Regras de Preço</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Categorias</a>
                        @elseif($key === 'operacoes')
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Pedidos</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Envios & Retiradas</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Clientes & Créditos</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Caixa</a>
                        @elseif($key === 'eventos')
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Torneios</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Ingressos</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Artigos (Blog)</a>
                        @elseif($key === 'layout')
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Identidade Visual</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Banners & Vitrine</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Configurações de UI</a>
                        @elseif($key === 'config')
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Checkout</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Métodos de Envio</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Integrações</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Institucional</a>
                        @elseif($key === 'gerenciais')
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Minha Equipe</a>
                            <a href="#" class="block px-6 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400">Assinatura do Plano</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 2. BOTÃO HAMBURGUER (DIREITA) --}}
        <div class="lg:hidden flex ml-auto py-2">
            <button @click="mobileOpen = !mobileOpen" class="text-zinc-600 dark:text-zinc-400 p-2 outline-none">
                <i class="ph-bold" :class="mobileOpen ? 'ph-x' : 'ph-list'" style="font-size: 26px;"></i>
            </button>
        </div>
    </div>

    {{-- 3. MENU MOBILE (VERTICAL - ESQUERDA) --}}
    <div x-show="mobileOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         @click.away="mobileOpen = false"
         class="absolute left-0 top-[48px] z-[120] w-72 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 shadow-2xl rounded-br-2xl p-2 lg:hidden"
         x-cloak>
        
        <div class="flex flex-col max-h-[80vh] overflow-y-auto">
            {{-- Loop idêntico para garantir que todos os menus apareçam --}}
            @foreach([
                'dashboard' => ['icon' => 'ph-chart-pie', 'label' => 'Dashboard'],
                'catalogo' => ['icon' => 'ph-package', 'label' => 'Catálogo'],
                'operacoes' => ['icon' => 'ph-shopping-cart', 'label' => 'Operações'],
                'eventos' => ['icon' => 'ph-ticket', 'label' => 'Eventos'],
                'layout' => ['icon' => 'ph-paint-brush-broad', 'label' => 'Layout'],
                'config' => ['icon' => 'ph-gear', 'label' => 'Configurações'],
                'gerenciais' => ['icon' => 'ph-shield-check', 'label' => 'Gerenciais']
            ] as $key => $item)
                <div class="w-full">
                    <button @click="openMenu = (openMenu === 'm-{{ $key }}') ? null : 'm-{{ $key }}'" 
                        class="w-full px-4 py-4 flex items-center justify-between text-sm font-bold transition-all border-b border-zinc-50 dark:border-white/5"
                        :class="openMenu === 'm-{{ $key }}' ? 'text-orange-600' : 'text-zinc-500'">
                        <span class="flex items-center gap-2"><i class="ph {{ $item['icon'] }}"></i> {{ $item['label'] }}</span>
                        <i class="ph ph-caret-down text-[10px] transition-transform" :class="openMenu === 'm-{{ $key }}' ? 'rotate-180' : ''"></i>
                    </button>
                    
                    <div x-show="openMenu === 'm-{{ $key }}'" x-transition class="bg-zinc-50 dark:bg-black/20 py-2">
                        @if($key === 'dashboard')
                            <a href="{{ route('store.dashboard', ['slug' => auth('store_user')->user()->store->url_slug]) }}" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Painel Geral</a>
                            <a href="{{ route('store.view', ['slug' => auth('store_user')->user()->store->url_slug]) }}" target="_blank" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Ver Minha Loja ↗</a>
                            <a href="{{ route('store.dashboard.logs', ['slug' => auth('store_user')->user()->store->url_slug]) }}" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Logs do Sistema</a>
                            <a href="{{ route('store.dashboard.novidades', ['slug' => auth('store_user')->user()->store->url_slug]) }}" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Novidades</a>
                        @elseif($key === 'catalogo')
                            <a href="{{ route('store.dashboard.stock.index', ['slug' => auth('store_user')->user()->store->url_slug, 'game_slug' => 'magic']) }}" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Estoque</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Buylist</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Regras de Preço</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Categorias</a>
                        @elseif($key === 'operacoes')
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Pedidos</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Envios & Retiradas</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Clientes & Créditos</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Caixa</a>
                        @elseif($key === 'eventos')
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Torneios</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Ingressos</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Artigos (Blog)</a>
                        @elseif($key === 'layout')
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Identidade Visual</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Banners & Vitrine</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Configurações de UI</a>
                        @elseif($key === 'config')
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Checkout</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Métodos de Envio</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Integrações</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Institucional</a>
                        @elseif($key === 'gerenciais')
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Minha Equipe</a>
                            <a href="#" class="block px-10 py-3 text-xs font-semibold text-zinc-500">Assinatura do Plano</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</nav>
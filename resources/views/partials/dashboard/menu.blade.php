{{-- Menu Superior com Dropdowns - Substitua seu <nav> anterior por este --}}
<nav class="relative z-[100] px-6 flex items-center gap-8 border-t border-zinc-100 dark:border-white/5 bg-white dark:bg-[#0f172a]" x-data="{ openMenu: null }">
    
    {{-- 1. Dashboard --}}
    <div class="relative">
        <button @click.stop="openMenu = (openMenu === 'dashboard') ? null : 'dashboard'" 
            class="h-12 flex items-center gap-2 text-sm font-bold outline-none cursor-pointer transition-all"
            :class="openMenu === 'dashboard' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-zinc-500 dark:text-zinc-400 border-b-2 border-transparent hover:text-zinc-900 dark:hover:text-white'">
            <i class="ph ph-chart-pie"></i> Dashboard <i class="ph ph-caret-down text-[10px]"></i>
        </button>
        <div x-show="openMenu === 'dashboard'" @click.away="openMenu = null" x-transition class="absolute top-[48px] left-0 w-52 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-white/10 shadow-2xl rounded-b-lg py-2 z-[110]" style="display: none;">
            <a href="{{ route('store.dashboard', ['slug' => auth('store_user')->user()->store->url_slug]) }}" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-orange-600 dark:hover:text-orange-500">Painel Geral</a>
            <a href="{{ route('store.view', ['slug' => auth('store_user')->user()->store->url_slug]) }}" 
            target="_blank" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-orange-600 dark:hover:text-orange-500">Ver Minha Loja ↗</a>
            <a href="{{ route('store.dashboard.logs', ['slug' => auth('store_user')->user()->store->url_slug]) }}" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-orange-600 dark:hover:text-orange-500">Logs do Sistema</a>
            <a href="{{ route('store.dashboard.novidades', ['slug' => auth('store_user')->user()->store->url_slug]) }}" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-orange-600 dark:hover:text-orange-500">Novidades</a>
        </div>
    </div>

    {{-- 2. Catálogo --}}
    <div class="relative">
        <button @click.stop="openMenu = (openMenu === 'catalogo') ? null : 'catalogo'"
            class="h-12 flex items-center gap-2 text-sm font-bold outline-none cursor-pointer transition-all"
            :class="openMenu === 'catalogo' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-zinc-500 dark:text-zinc-400 border-b-2 border-transparent hover:text-zinc-900 dark:hover:text-white'">
            <i class="ph ph-package"></i> Catálogo <i class="ph ph-caret-down text-[10px]"></i>
        </button>
        <div x-show="openMenu === 'catalogo'" @click.away="openMenu = null" x-transition class="absolute top-[48px] left-0 w-52 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-white/10 shadow-2xl rounded-b-lg py-2 z-[110]" style="display: none;">
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Vender (Estoque)</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Comprar (Buylist)</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Regras de Preço</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Categorias</a>
        </div>
    </div>

    {{-- 3. Operações --}}
    <div class="relative">
        <button @click.stop="openMenu = (openMenu === 'operacoes') ? null : 'operacoes'"
            class="h-12 flex items-center gap-2 text-sm font-bold outline-none cursor-pointer transition-all"
            :class="openMenu === 'operacoes' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-zinc-500 dark:text-zinc-400 border-b-2 border-transparent hover:text-zinc-900 dark:hover:text-white'">
            <i class="ph ph-shopping-cart"></i> Operações <i class="ph ph-caret-down text-[10px]"></i>
        </button>
        <div x-show="openMenu === 'operacoes'" @click.away="openMenu = null" x-transition class="absolute top-[48px] left-0 w-52 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-white/10 shadow-2xl rounded-b-lg py-2 z-[110]" style="display: none;">
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Pedidos</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Envios & Retiradas</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Clientes & Créditos</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Caixa</a>
        </div>
    </div>

    {{-- 4. Eventos --}}
    <div class="relative">
        <button @click.stop="openMenu = (openMenu === 'eventos') ? null : 'eventos'"
            class="h-12 flex items-center gap-2 text-sm font-bold outline-none cursor-pointer transition-all"
            :class="openMenu === 'eventos' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-zinc-500 dark:text-zinc-400 border-b-2 border-transparent hover:text-zinc-900 dark:hover:text-white'">
            <i class="ph ph-ticket"></i> Eventos <i class="ph ph-caret-down text-[10px]"></i>
        </button>
        <div x-show="openMenu === 'eventos'" @click.away="openMenu = null" x-transition class="absolute top-[48px] left-0 w-52 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-white/10 shadow-2xl rounded-b-lg py-2 z-[110]" style="display: none;">
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Torneios</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Ingressos</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Artigos (Blog)</a>
        </div>
    </div>

    {{-- 5. Layout --}}
    <div class="relative">
        <button @click.stop="openMenu = (openMenu === 'layout') ? null : 'layout'"
            class="h-12 flex items-center gap-2 text-sm font-bold outline-none cursor-pointer transition-all"
            :class="openMenu === 'layout' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-zinc-500 dark:text-zinc-400 border-b-2 border-transparent hover:text-zinc-900 dark:hover:text-white'">
            <i class="ph ph-paint-brush-broad"></i> Layout <i class="ph ph-caret-down text-[10px]"></i>
        </button>
        <div x-show="openMenu === 'layout'" @click.away="openMenu = null" x-transition class="absolute top-[48px] left-0 w-52 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-white/10 shadow-2xl rounded-b-lg py-2 z-[110]" style="display: none;">
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Identidade Visual</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Banners & Vitrine</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Configurações de UI</a>
        </div>
    </div>

    {{-- 6. Configurações --}}
    <div class="relative">
        <button @click.stop="openMenu = (openMenu === 'config') ? null : 'config'"
            class="h-12 flex items-center gap-2 text-sm font-bold outline-none cursor-pointer transition-all"
            :class="openMenu === 'config' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-zinc-500 dark:text-zinc-400 border-b-2 border-transparent hover:text-zinc-900 dark:hover:text-white'">
            <i class="ph ph-gear"></i> Configurações <i class="ph ph-caret-down text-[10px]"></i>
        </button>
        <div x-show="openMenu === 'config'" @click.away="openMenu = null" x-transition class="absolute top-[48px] left-0 w-52 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-white/10 shadow-2xl rounded-b-lg py-2 z-[110]" style="display: none;">
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Checkout</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Métodos de Envio</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Integrações</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Institucional</a>
        </div>
    </div>

    {{-- 7. Gerenciais --}}
    <div class="relative">
        <button @click.stop="openMenu = (openMenu === 'gerenciais') ? null : 'gerenciais'"
            class="h-12 flex items-center gap-2 text-sm font-bold outline-none cursor-pointer transition-all"
            :class="openMenu === 'gerenciais' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-zinc-500 dark:text-zinc-400 border-b-2 border-transparent hover:text-zinc-900 dark:hover:text-white'">
            <i class="ph ph-shield-check"></i> Gerenciais <i class="ph ph-caret-down text-[10px]"></i>
        </button>
        <div x-show="openMenu === 'gerenciais'" @click.away="openMenu = null" x-transition class="absolute top-[48px] left-0 w-52 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-white/10 shadow-2xl rounded-b-lg py-2 z-[110]" style="display: none;">
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Minha Equipe</a>
            <a href="#" class="block px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Assinatura do Plano</a>
        </div>
    </div>

</nav>
<header class="w-full" x-data="{ mobileMenuOpen: false }">
    {{-- Top Bar (Contatos) --}}
    <div class="bg-top-bar-custom text-xs py-2 px-4 transition-colors duration-300">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <span class="flex items-center"><i class="ph ph-whatsapp-logo mr-1 text-base"></i> (11) 99999-9999</span>
                <span class="hidden sm:flex items-center"><i class="ph ph-envelope mr-1 text-base"></i> contato@{{ $loja->url_slug }}.com.br</span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="#" class="hover:opacity-75 transition-opacity"><i class="ph ph-instagram-logo text-lg"></i></a>
                <a href="#" class="hover:opacity-75 transition-opacity"><i class="ph ph-facebook-logo text-lg"></i></a>
            </div>
        </div>
    </div>

    {{-- Área Principal (Logo, Busca e Ícones) --}}
    <div class="bg-header-custom border-b border-gray-100/10 py-6 px-4 transition-colors duration-300">
        <div class="max-w-7xl mx-auto flex flex-wrap lg:flex-nowrap justify-between items-center gap-4">

            {{-- Logo --}}
            <div class="w-full lg:w-1/4 flex justify-center lg:justify-start">
                <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" class="flex items-center hover:opacity-90 transition-opacity">
                    @if($loja->visual && $loja->visual->logo_main)
                        <img src="{{ asset('store_images/' . $loja->url_slug . '/' . $loja->visual->logo_main) }}" 
                            alt="{{ $loja->name }}" 
                            class="max-h-16">
                    @else
                        <span class="text-3xl font-black tracking-tighter uppercase" style="color: var(--cor-texto-header);">
                            {{ $loja->name }}
                        </span>
                    @endif
                </a>
            </div>

            {{-- Barra de Busca --}}
            <div class="w-full lg:w-2/4 order-3 lg:order-none">
                @livewire('global-search', ['storeSlug' => $loja->url_slug])
            </div>

            {{-- Ícones do Usuário e Carrinho --}}
            <div class="w-full lg:w-1/4 flex justify-center lg:justify-end items-center space-x-6 order-2 lg:order-none" style="color: var(--cor-texto-header);">
                <a href="#" class="flex flex-col items-center hover:opacity-75 transition-opacity">
                    <i class="ph ph-user text-2xl"></i>
                    <span class="text-xs font-medium mt-1 uppercase">ENTRAR</span>
                </a>
                <a href="#" class="relative flex flex-col items-center hover:opacity-75 transition-opacity">
                    <i class="ph ph-shopping-cart text-2xl"></i>
                    <span class="text-xs font-medium mt-1 uppercase">CARRINHO</span>
                    <span class="absolute -top-1 -right-2 bg-accent-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm">0</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Barra de Menu de Categorias --}}
    <div class="w-full border-b border-gray-200 bg-white relative z-50">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between">

            {{-- Menu Desktop --}}
            <ul class="hidden lg:flex items-center space-x-1 py-2 text-sm font-bold uppercase tracking-tighter text-gray-800">
                <li class="relative">
                    <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" 
                       class="px-4 py-2 rounded-md block font-bold uppercase menu-link-custom">
                        PÁGINA INICIAL
                    </a>
                </li>

                @if(isset($loja->gameMenus) && $loja->gameMenus->count() > 0)
                    @foreach($loja->gameMenus as $menu)
                        <li class="relative group">
                            <button class="px-4 py-2 rounded-md flex items-center gap-1 font-bold uppercase menu-link-custom">
                                {{ strtoupper($menu->game->name) }} 
                                <i class="ph ph-caret-down text-xs transition-transform group-hover:rotate-180"></i>
                            </button>

                            <ul class="absolute top-full left-0 hidden group-hover:block bg-white shadow-2xl border border-gray-100 rounded-b-xl py-3 w-64 z-[100] animate-in fade-in slide-in-from-top-2">

                                @php
                                    $linkStyle = "block px-5 py-2.5 font-bold uppercase text-xs border-l-4 border-transparent hover:border-black/5 submenu-link-custom";
                                    $subItemStyle = "block px-8 py-1.5 uppercase text-[11px] font-semibold flex items-center submenu-link-custom";
                                @endphp

                                @if($menu->show_singles)
                                    <li>
                                        <a href="{{ route('store.catalog.singles', ['slug' => $loja->url_slug, 'gameSlug' => $menu->game->url_slug]) }}" class="{{ $linkStyle }}">
                                            {{ strtoupper($menu->name_singles) }}
                                        </a>
                                    </li>
                                @endif

                                @if($menu->show_latest && $menu->recent_sets->count() > 0)
                                    <li>
                                        <span class="block px-5 py-2.5 font-bold uppercase text-xs cursor-default bg-gray-50/50 border-l-4" style="color: var(--menu-txt); border-color: var(--menu-txt);">
                                            {{ strtoupper($menu->name_latest) }}
                                        </span>
                                    </li>
                                    <div class="py-1">
                                        @foreach($menu->recent_sets as $set)
                                            <li>
                                                <a href="{{ route('store.catalog.set', ['slug' => $loja->url_slug, 'gameSlug' => $menu->game->url_slug, 'setCode' => $set->code]) }}" class="{{ $subItemStyle }}">
                                                    <i class="ph ph-caret-right mr-1.5 text-[10px] opacity-50"></i> {{ strtoupper($set->nome_localizado) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </div>
                                @endif

                                @if($menu->show_all_sets)
                                    <li>
                                        <a href="{{ route('store.catalog.sets', ['slug' => $loja->url_slug, 'gameSlug' => $menu->game->url_slug]) }}" class="{{ $linkStyle }} border-t border-gray-50">
                                            {{ strtoupper($menu->name_all_sets) }} &rarr;
                                        </a>
                                    </li>
                                @endif

                                @if($menu->show_sealed)
                                    <li><a href="#" class="{{ $linkStyle }}">{{ strtoupper($menu->name_sealed) }}</a></li>
                                @endif

                                @if($menu->show_accessories)
                                    <li><a href="#" class="{{ $linkStyle }}">{{ strtoupper($menu->name_accessories) }}</a></li>
                                @endif

                                <li class="px-3 mt-3 pb-2 pt-2 border-t border-gray-50">
                                    <a href="#" class="flex items-center justify-center gap-2 py-2.5 px-2 rounded-lg text-[10px] font-black transition-all shadow-md group/boot uppercase btn-updates-custom">
                                        <i class="ph ph-robot text-lg group-hover/boot:animate-bounce"></i>
                                        {{ strtoupper($menu->name_updates ?? 'ÚLTIMAS ATUALIZAÇÕES') }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endforeach
                @endif

                <li class="relative">
                    <a href="#" class="px-4 py-2 rounded-md transition-all duration-300 hover:scale-110 hover:bg-[var(--cor-3)] hover:text-gray-900 text-[var(--cor-3)] flex items-center gap-1 font-bold uppercase">
                        <i class="ph ph-tag-simple font-bold"></i> OFERTAS
                    </a>
                </li>
            </ul>

            {{-- Lado direito da barra de menu (Mobile) --}}
            <div class="flex items-center justify-end flex-1 lg:hidden py-2 space-x-2">
                {{-- Ofertas NO MOBILE: aparece só aqui, não entra no dropdown --}}
                <a href="#" class="px-3 py-2 text-xs font-bold uppercase flex items-center gap-1 text-[var(--cor-3)]">
                    <i class="ph ph-tag-simple font-bold"></i> OFERTAS
                </a>

                {{-- Botão Hambúrguer na barra branca (só mobile) --}}
                <button
                    class="flex items-center justify-center w-10 h-10 rounded-md focus:outline-none"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    aria-label="Abrir menu"
                >
                    <i :class="mobileMenuOpen ? 'ph ph-x' : 'ph ph-list'" class="text-2xl"></i>
                </button>
            </div>
        </div>

        {{-- Dropdown Mobile preso na barra --}}
        <div
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="lg:hidden border-t border-gray-200 bg-white"
            x-cloak
        >
            <nav class="max-w-7xl mx-auto px-4 py-2">
                <ul class="text-sm font-bold uppercase tracking-tighter text-gray-800">

                    {{-- PÁGINA INICIAL --}}
                    <li>
                        <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" 
                           class="block px-2 py-2 menu-link-custom border-b border-gray-50">
                            PÁGINA INICIAL
                        </a>
                    </li>

                    {{-- Jogos com submenus (escadinha) --}}
                    @if(isset($loja->gameMenus) && $loja->gameMenus->count() > 0)
                        @foreach($loja->gameMenus as $menu)
                            <li x-data="{ openGame: false }" class="border-b border-gray-50">
                                {{-- Nível 1: Jogo --}}
                                <button
                                    @click="openGame = !openGame"
                                    class="w-full flex items-center justify-between px-2 py-2 menu-link-custom"
                                >
                                    <span>{{ strtoupper($menu->game->name) }}</span>
                                    <i class="ph ph-caret-down text-xs transition-transform duration-200" :class="openGame ? 'rotate-180' : ''"></i>
                                </button>

                                {{-- Submenu do jogo --}}
                                <div x-show="openGame" x-collapse class="bg-gray-50/50">
                                    @php
                                        $mobileLinkStyle = "block px-4 py-2.5 font-bold uppercase text-xs border-l-4 border-transparent submenu-link-custom";
                                        $mobileSubItemStyle = "flex items-center px-7 py-2 uppercase text-[11px] font-semibold submenu-link-custom";
                                    @endphp

                                    {{-- Singles --}}
                                    @if($menu->show_singles)
                                        <a href="{{ route('store.catalog.singles', ['slug' => $loja->url_slug, 'gameSlug' => $menu->game->url_slug]) }}" 
                                           class="{{ $mobileLinkStyle }} border-b border-gray-100">
                                            {{ strtoupper($menu->name_singles) }}
                                        </a>
                                    @endif

                                    {{-- Sets Recentes com escadinha --}}
                                    @if($menu->show_latest && $menu->recent_sets->count() > 0)
                                        <div x-data="{ openSets: false }" class="border-b border-gray-100">
                                            {{-- Label nível 2 --}}
                                            <button
                                                @click="openSets = !openSets"
                                                class="w-full flex items-center justify-between px-4 py-2.5 font-bold uppercase text-xs"
                                                style="color: var(--menu-txt);"
                                            >
                                                <span>{{ strtoupper($menu->name_latest) }}</span>
                                                <i class="ph ph-caret-down text-xs transition-transform duration-200" :class="openSets ? 'rotate-180' : ''"></i>
                                            </button>

                                            {{-- Nível 3: sets --}}
                                            <div x-show="openSets" x-collapse class="bg-white border-t border-gray-50">
                                                @foreach($menu->recent_sets as $set)
                                                    <a href="{{ route('store.catalog.set', ['slug' => $loja->url_slug, 'gameSlug' => $menu->game->url_slug, 'setCode' => $set->code]) }}" 
                                                       class="{{ $mobileSubItemStyle }} border-b border-gray-50">
                                                        <i class="ph ph-caret-right mr-1.5 text-[10px] opacity-50"></i> {{ strtoupper($set->nome_localizado) }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Ver todos os sets --}}
                                    @if($menu->show_all_sets)
                                        <a href="{{ route('store.catalog.sets', ['slug' => $loja->url_slug, 'gameSlug' => $menu->game->url_slug]) }}" 
                                           class="{{ $mobileLinkStyle }} border-b border-gray-100">
                                            {{ strtoupper($menu->name_all_sets) }} →
                                        </a>
                                    @endif

                                    {{-- Selados --}}
                                    @if($menu->show_sealed)
                                        <a href="#" class="{{ $mobileLinkStyle }} border-b border-gray-100">{{ strtoupper($menu->name_sealed) }}</a>
                                    @endif

                                    {{-- Acessórios --}}
                                    @if($menu->show_accessories)
                                        <a href="#" class="{{ $mobileLinkStyle }} border-b border-gray-100">{{ strtoupper($menu->name_accessories) }}</a>
                                    @endif

                                    {{-- Robô de Atualizações --}}
                                    <div class="px-2 py-3">
                                        <a href="#" class="flex items-center justify-center gap-2 py-2.5 px-2 rounded-lg text-[10px] font-black transition-all shadow-md group/boot uppercase btn-updates-custom">
                                            <i class="ph ph-robot text-lg group-hover/boot:animate-bounce"></i>
                                            {{ strtoupper($menu->name_updates ?? 'ÚLTIMAS ATUALIZAÇÕES') }}
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    @endif

                    {{-- NÃO colocamos OFERTAS aqui para evitar duplicação no mobile --}}

                </ul>
            </nav>
        </div>
    </div>
</header>
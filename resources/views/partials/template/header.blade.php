<header class="w-full">
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
                    {{-- Verificamos se a loja tem o registro visual e se a logo_main está preenchida --}}
                    @if($loja->visual && $loja->visual->logo_main)
                        <img src="{{ asset('store_images/' . $loja->url_slug . '/' . $loja->visual->logo_main) }}" 
                            alt="{{ $loja->name }}" 
                            class="max-h-16">
                    @else
                        {{-- Fallback original que você já tinha: apenas o nome da loja --}}
                        <span class="text-3xl font-black tracking-tighter uppercase" style="color: var(--cor-texto-header);">
                            {{ $loja->name }}
                        </span>
                    @endif
                </a>
            </div>

            {{-- Barra de Busca --}}
            <div class="w-full lg:w-2/4 order-3 lg:order-none">
                <form action="#" class="relative flex w-full shadow-sm rounded-md">
                    <input type="text" placeholder="Buscar cartas, coleções ou produtos..." class="w-full pl-4 pr-12 py-3 rounded-l-md border-2 border-r-0 border-main-1 text-gray-900 bg-white focus:outline-none focus:ring-0">
                    <button type="submit" class="bg-main-1 px-6 rounded-r-md transition-all flex items-center justify-center">
                        <i class="ph ph-magnifying-glass text-xl"></i>
                    </button>
                </form>
            </div>

            {{-- Ícones do Usuário e Carrinho --}}
            <div class="w-full lg:w-1/4 flex justify-center lg:justify-end items-center space-x-6 order-2 lg:order-none" style="color: var(--cor-texto-header);">
                <a href="#" class="flex flex-col items-center hover:opacity-75 transition-opacity">
                    <i class="ph ph-user text-2xl"></i>
                    <span class="text-xs font-medium mt-1 uppercase">Entrar</span>
                </a>
                <a href="#" class="relative flex flex-col items-center hover:opacity-75 transition-opacity">
                    <i class="ph ph-shopping-cart text-2xl"></i>
                    <span class="text-xs font-medium mt-1 uppercase">Carrinho</span>
                    <span class="absolute -top-1 -right-2 bg-accent-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm">0</span>
                </a>
            </div>
        </div>
    </div>
        
    {{-- Menu de Categorias Dinâmico --}}
    <div class="w-full border-b border-gray-200 bg-white relative z-50">
        <div class="max-w-7xl mx-auto px-4">
            <ul class="flex items-center space-x-1 py-2 text-sm font-bold uppercase tracking-tighter lg:overflow-visible text-gray-800">
            
                <li class="relative">
                    <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" 
                       class="px-4 py-2 rounded-md block font-bold uppercase menu-link-custom">
                        Página Inicial
                    </a>
                </li>

                @if(isset($loja->gameMenus) && $loja->gameMenus->count() > 0)
                    @foreach($loja->gameMenus as $menu)
                        <li class="relative group">
                            <button class="px-4 py-2 rounded-md flex items-center gap-1 font-bold uppercase menu-link-custom">
                                {{ $menu->game->name }} 
                                <i class="ph ph-caret-down text-xs transition-transform group-hover:rotate-180"></i>
                            </button>

                            <ul class="absolute top-full left-0 hidden group-hover:block bg-white shadow-2xl border border-gray-100 rounded-b-xl py-3 w-64 z-[100] animate-in fade-in slide-in-from-top-2">
                                
                                @php
                                    $linkStyle = "block px-5 py-2.5 font-bold uppercase text-xs border-l-4 border-transparent hover:border-black/5 submenu-link-custom";
                                    $subItemStyle = "block px-8 py-1.5 uppercase text-[11px] font-semibold flex items-center submenu-link-custom";
                                @endphp

                                @if($menu->show_singles)
                                    <li><a href="#" class="{{ $linkStyle }}">{{ $menu->name_singles }}</a></li>
                                @endif

                                @if($menu->show_latest && $menu->recent_sets->count() > 0)
                                    <li>
                                        <span class="block px-5 py-2.5 font-bold uppercase text-xs cursor-default bg-gray-50/50 border-l-4" style="color: var(--menu-txt); border-color: var(--menu-txt);">
                                            {{ $menu->name_latest }}
                                        </span>
                                    </li>
                                    <div class="py-1">
                                        @foreach($menu->recent_sets as $set)
                                            <li>
                                                <a href="#" class="{{ $subItemStyle }}">
                                                    <i class="ph ph-caret-right mr-1.5 text-[10px] opacity-50"></i> {{ $set->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </div>
                                @endif

                                @if($menu->show_all_sets)
                                    <li><a href="#" class="{{ $linkStyle }} border-t border-gray-50">{{ $menu->name_all_sets }} &rarr;</a></li>
                                @endif

                                @if($menu->show_sealed)
                                    <li><a href="#" class="{{ $linkStyle }}">{{ $menu->name_sealed }}</a></li>
                                @endif

                                @if($menu->show_accessories)
                                    <li><a href="#" class="{{ $linkStyle }}">{{ $menu->name_accessories }}</a></li>
                                @endif

                                {{-- 6. O MENU DE ATUALIZAÇÕES (WhatsApp/Robô) --}}
                                <li class="px-3 mt-3 pb-2 pt-2 border-t border-gray-50">
                                    <a href="#" class="flex items-center justify-center gap-2 py-2.5 px-2 rounded-lg text-[10px] font-black transition-all shadow-md group/boot uppercase btn-updates-custom">
                                        <i class="ph ph-robot text-lg group-hover/boot:animate-bounce"></i>
                                        {{ $menu->name_updates ?? 'ÚLTIMAS ATUALIZAÇÕES' }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endforeach
                @endif

                {{-- Ofertas (MANTIDO EXATAMENTE COMO VOCÊ MANDOU) --}}
                <li class="relative">
                    <a href="#" class="px-4 py-2 rounded-md transition-all duration-300 hover:scale-110 hover:bg-[var(--cor-3)] hover:text-gray-900 text-[var(--cor-3)] flex items-center gap-1 font-bold uppercase">
                        <i class="ph ph-tag-simple font-bold"></i> Ofertas
                    </a>
                </li>

            </ul>
        </div>
    </div>
</header>
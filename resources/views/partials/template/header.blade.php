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
    <div class="bg-header-custom border-b border-gray-100 py-4 px-4 transition-colors duration-300">
        <div class="max-w-7xl mx-auto flex flex-wrap lg:flex-nowrap justify-between items-center gap-4">
            
            {{-- Logo --}}
            <div class="w-full lg:w-1/4 flex justify-center lg:justify-start">
                <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" class="flex items-center hover:opacity-90 transition-opacity">
                    @if($loja->logo_path)
                        {{-- O Laravel renderiza a imagem --}}
                        <img src="{{ asset('store_images/' . $loja->url_slug . '/' . $loja->logo_path) }}" alt="{{ $loja->name }}" class="h-12 w-auto object-contain">
                    @else
                        {{-- Fallback: Nome da Loja estilizado --}}
                        <span class="text-3xl font-black tracking-tighter uppercase">
                            <span class="text-main-1">{{ $loja->name }}</span>
                        </span>
                    @endif
                </a>
            </div>

            {{-- Barra de Busca --}}
            <div class="w-full lg:w-2/4 order-3 lg:order-none">
                <form action="#" class="relative flex w-full shadow-sm rounded-md">
                    {{-- Input com texto fixo escuro para não sumir independente do fundo do header --}}
                    <input type="text" placeholder="Buscar cartas, coleções ou produtos..." class="w-full pl-4 pr-12 py-3 rounded-l-md border-2 border-r-0 border-main-1 text-gray-800 focus:outline-none focus:ring-0">
                    <button type="submit" class="bg-main-1 hover:opacity-90 px-6 rounded-r-md transition-all flex items-center justify-center">
                        <i class="ph ph-magnifying-glass text-xl"></i>
                    </button>
                </form>
            </div>

            {{-- Ícones do Usuário e Carrinho --}}
            <div class="w-full lg:w-1/4 flex justify-center lg:justify-end items-center space-x-6 order-2 lg:order-none">
                <a href="#" class="flex flex-col items-center hover:opacity-75 transition-opacity">
                    <i class="ph ph-user text-2xl"></i>
                    <span class="text-xs font-medium mt-1">Entrar</span>
                </a>
                <a href="#" class="relative flex flex-col items-center hover:opacity-75 transition-opacity">
                    <i class="ph ph-shopping-cart text-2xl"></i>
                    <span class="text-xs font-medium mt-1">Carrinho</span>
                    <span class="absolute -top-1 -right-2 bg-[var(--cor-3)] text-gray-900 text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm">3</span>
                </a>
            </div>
        </div>
    </div>
    
    {{-- Menu de Categorias Dinâmico --}}
    {{-- Mantido branco por padrão, mas pode virar uma variável de "bg-menu" no futuro --}}
    <div class="w-full border-b border-gray-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 flex justify-center lg:justify-start">
            <ul class="flex space-x-8 overflow-x-auto whitespace-nowrap py-3 hide-scrollbar text-sm font-semibold text-gray-700">
                
                {{-- 1. Fixo: Página Inicial --}}
                <li>
                    <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" class="hover:text-main-1 transition-colors">
                        Página Inicial
                    </a>
                </li>

                {{-- 2. Lógica: O Lojista tem menus cadastrados? --}}
                @if(isset($loja->menus) && $loja->menus->count() > 0)
                    @foreach($loja->menus as $menu)
                        <li class="relative group cursor-pointer">
                            <a href="#" class="flex items-center hover:text-main-1 transition-colors">
                                {{ $menu->name }} 
                                @if($menu->submenus && $menu->submenus->count() > 0)
                                    <i class="ph ph-caret-down ml-1"></i>
                                @endif
                            </a>

                            @if($menu->submenus && $menu->submenus->count() > 0)
                                <ul class="absolute top-full left-0 hidden group-hover:block bg-white shadow-lg border border-gray-100 rounded-md py-2 w-48 z-50">
                                    @foreach($menu->submenus as $submenu)
                                        <li>
                                            <a href="#" class="block px-4 py-2 hover:bg-gray-50 hover:text-main-1">
                                                {{ $submenu->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                @else
                    {{-- 3. Fallback: Vitrine padrão --}}
                    <li class="relative group cursor-pointer">
                        <span class="flex items-center hover:text-main-1 transition-colors">
                            Magic: The Gathering <i class="ph ph-caret-down ml-1"></i>
                        </span>
                        <ul class="absolute top-full left-0 hidden group-hover:block bg-white shadow-lg border border-gray-100 rounded-md py-2 w-48 z-50">
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-50 hover:text-main-1">Cartas Avulsas (Singles)</a></li>
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-50 hover:text-main-1">Boosters & Caixas</a></li>
                        </ul>
                    </li>
                    <li><a href="#" class="hover:text-main-1 transition-colors">Pokémon TCG</a></li>
                    <li><a href="#" class="hover:text-main-1 transition-colors">Yu-Gi-Oh!</a></li>
                    <li><a href="#" class="hover:text-main-1 transition-colors">Acessórios</a></li>
                @endif

                {{-- 4. Fixo: Ofertas do Dia (Usando a Cor 3 de destaque) --}}
                <li>
                    <a href="#" class="text-[var(--cor-3)] flex items-center hover:opacity-80 transition-opacity">
                        <i class="ph ph-tag mr-1 text-lg"></i> Ofertas do Dia
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>
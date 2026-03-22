<footer class="bg-secondary-1 text-gray-200 mt-12 pt-12 pb-6 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 border-b border-white/10 pb-8">
        
        {{-- Coluna 1: Sobre a Loja --}}
        <div>
            <h3 class="mb-4 tracking-tighter uppercase">
                {{-- 
                    1. Verificamos o visual (usando a relação que criamos)
                    2. Se não quiser usar a relação agora, mantenha seu $loja->logo_path, 
                    mas adicione as travas de tamanho abaixo:
                --}}
                @if($loja->visual && $loja->visual->logo_main)
                    <img src="{{ asset('store_images/' . $loja->url_slug . '/' . $loja->visual->logo_main) }}" 
                        alt="{{ $loja->name }}" 
                        {{-- 
                            AJUSTES PRÁTICOS:
                            - h-8: Reduzi um pouco (32px). No footer fica mais elegante.
                            - max-w-[200px]: Se a logo for muito larga, ela trava aqui e não quebra o layout.
                            - object-left: Garante que ela alinhe com o texto abaixo.
                        --}}
                        class="h-12 w-auto max-w-[200px] object-contain object-left brightness-0 invert opacity-80 hover:opacity-100 transition-opacity">
                @else
                    {{-- Seu fallback original com a cor de destaque --}}
                    <span class="text-white text-xl font-bold">
                        {{ $loja->name }} <span class="text-accent-1">TCG</span>
                    </span>
                @endif
            </h3>
            
            <p class="text-sm leading-relaxed mb-4 text-gray-300">
                A sua principal loja de Card Games. Encontre as melhores cartas, acessórios e participe dos nossos torneios semanais.
            </p>

            <div class="flex space-x-3">
                <a href="#" class="bg-white/10 p-2 rounded-full hover:bg-accent-1 transition-colors">
                    <i class="ph ph-instagram-logo"></i>
                </a>
                <a href="#" class="bg-white/10 p-2 rounded-full hover:bg-accent-1 transition-colors">
                    <i class="ph ph-facebook-logo"></i>
                </a>
            </div>
        </div>

        {{-- Coluna 2: Departamentos --}}
        <div>
            <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Departamentos</h4>
            <ul class="space-y-2 text-sm text-gray-300">
                <li><a href="#" class="hover:text-accent-1 transition-colors">Magic: The Gathering</a></li>
                <li><a href="#" class="hover:text-accent-1 transition-colors">Pokémon TCG</a></li>
                <li><a href="#" class="hover:text-accent-1 transition-colors">Acessórios</a></li>
            </ul>
        </div>

        {{-- Coluna 3: Atendimento --}}
        <div>
            <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Atendimento</h4>
            <ul class="space-y-2 text-sm text-gray-300">
                <li><a href="#" class="hover:text-accent-1 transition-colors">Rastrear Pedido</a></li>
                <li><a href="#" class="hover:text-accent-1 transition-colors">Políticas de Envio</a></li>
                <li><a href="#" class="hover:text-accent-1 transition-colors">Fale Conosco</a></li>
            </ul>
        </div>

        {{-- Coluna 4: Contato --}}
        <div>
            <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Contato</h4>
            <ul class="space-y-2 text-sm mb-6 text-gray-300">
                <li class="flex items-start">
                    <i class="ph ph-map-pin mr-2 text-accent-1 text-xl shrink-0"></i>
                    <span>Av. Exemplo, 1234 - Loja 5<br>Centro - Cidade/UF</span>
                </li>
            </ul>
            <h4 class="text-white font-bold mb-3 uppercase text-xs tracking-wider">Pague de forma segura</h4>
            <div class="flex space-x-2">
                <div class="w-10 h-6 bg-white/20 rounded flex items-center justify-center hover:bg-white/30 transition-colors cursor-pointer"><i class="ph ph-credit-card text-white"></i></div>
                <div class="w-10 h-6 bg-white/20 rounded flex items-center justify-center hover:bg-white/30 transition-colors cursor-pointer"><i class="ph ph-barcode text-white"></i></div>
            </div>
        </div>
    </div>

    {{-- Bottom Footer --}}
    <div class="max-w-7xl mx-auto px-4 mt-6 flex flex-col md:flex-row justify-between items-center text-xs text-gray-400">
        <p>&copy; {{ date('Y') }} {{ $loja->name }}. Todos os direitos reservados.</p>
        <p class="mt-2 md:mt-0 flex items-center">
            Powered by <a href="#" class="ml-1 text-white font-bold hover:text-accent-1 transition-colors tracking-wide">VERSUS TCG</a>
        </p>
    </div>
</footer>
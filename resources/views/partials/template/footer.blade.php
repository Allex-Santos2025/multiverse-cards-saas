<footer class="bg-secondary-1 text-gray-200 mt-0 pt-12 pb-6 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 border-b border-white/10 pb-8">
        
        {{-- Coluna 1: Sobre a Loja --}}
        <div>
            <h3 class="mb-4 tracking-tighter uppercase">
                @if($loja->visual && $loja->visual->logo_main)
                    <img src="{{ asset('store_images/' . $loja->url_slug . '/' . $loja->visual->logo_main) }}" 
                        alt="{{ $loja->name }}" 
                        class="h-12 w-auto max-w-[200px] object-contain object-left brightness-0 invert opacity-80 hover:opacity-100 transition-opacity">
                @else
                    <span class="text-white text-xl font-bold">
                        {{ $loja->name }} <span class="text-accent-1">TCG</span>
                    </span>
                @endif
            </h3>
            
            <p class="text-sm leading-relaxed mb-4 text-gray-300">
                A sua principal loja de Card Games. Encontre as melhores cartas, acessórios e participe dos nossos torneios semanais.
            </p>

            {{-- REDES SOCIAIS DINÂMICAS COM AUTO-COMPLETAR --}}
            <div class="flex space-x-3">
                @if(isset($loja->socials) && $loja->socials->count() > 0)
                    @foreach($loja->socials as $social)
                        @php
                            $rawUrl = $social->url;
                            if (!str_starts_with($rawUrl, 'http')) {
                                $clean = ltrim($rawUrl, '@/');
                                $bases = [
                                    'instagram' => 'https://instagram.com/',
                                    'facebook'  => 'https://facebook.com/',
                                    'youtube'   => 'https://youtube.com/@',
                                    'tiktok'    => 'https://tiktok.com/@',
                                    'twitter'   => 'https://twitter.com/',
                                    'twitch'    => 'https://twitch.tv/',
                                    'linkedin'  => 'https://linkedin.com/in/',
                                ];
                                $url = isset($bases[$social->platform]) ? $bases[$social->platform] . $clean : 'https://' . $clean;
                            } else {
                                $url = $rawUrl;
                            }
                        @endphp
                        <a href="{{ $url }}" target="_blank" class="bg-white/10 p-2 rounded-full hover:bg-accent-1 transition-colors" title="{{ ucfirst($social->platform) }}">
                            <i class="ph ph-{{ $social->platform }}-logo text-lg"></i>
                        </a>
                    @endforeach
                @endif
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
            <ul class="space-y-3 text-sm mb-6 text-gray-300">
                
                @if(!empty($loja->street))
                    <li class="flex items-start">
                        <i class="ph ph-map-pin mr-2 text-accent-1 text-xl shrink-0"></i>
                        <span>
                            {{ $loja->street }}, {{ $loja->number }} 
                            @if(!empty($loja->complement)) - {{ $loja->complement }} @endif
                            <br>
                            {{ $loja->neighborhood }} - {{ $loja->city }}/{{ strtoupper($loja->store_state_code) }}
                        </span>
                    </li>
                @endif

                @if(!empty($loja->phone))
                    <li class="flex items-center">
                        <i class="ph ph-whatsapp-logo mr-2 text-accent-1 text-xl shrink-0"></i>
                        <span>{{ $loja->phone }}</span>
                    </li>
                @endif

                @if(!empty($loja->support_email))
                    <li class="flex items-center">
                        <i class="ph ph-envelope mr-2 text-accent-1 text-xl shrink-0"></i>
                        <span>{{ $loja->support_email }}</span>
                    </li>
                @endif

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
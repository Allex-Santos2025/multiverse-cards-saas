<div x-data="{ open: false }" class="relative inline-block text-left z-50">
    
    {{-- GATILHO (AVATAR + NOME) --}}
    <button @click="open = !open" @click.away="open = false" 
        class="flex items-center gap-3 focus:outline-none transition-transform hover:scale-105">
        
        {{-- Avatar com borda baseada na cor da loja ou do versus --}}
        <div class="w-10 h-10 rounded-full border-2 overflow-hidden shadow-lg"
             style="border-color: {{ $isMarketplace ? '#f59e0b' : 'var(--cor-cta)' }};">
            
            @php
                $player = Auth::guard('player_user')->user();
                // Fallback absoluto do Multiverso
                $avatarUrl = asset('assets/images/avatars/multiverso-default.png'); 
                
                if ($player->photo) {
                    $avatarUrl = asset('storage/' . $player->photo);
                } elseif ($player->avatar) {
                    $avatarUrl = asset('assets/images/avatars/' . $player->avatar);
                }
            @endphp
            <img src="{{ $avatarUrl }}" class="w-full h-full object-cover bg-zinc-900">
        </div>

        {{-- Texto Olá, Player (Some no mobile para não quebrar layout) --}}
        <div class="text-left hidden md:block">
            <span style="display: block; font-size: 0.65rem; font-weight: 800; opacity: 0.5; text-transform: uppercase; letter-spacing: 0.05em;">
                Olá,
            </span>
            <span style="display: block; font-size: 0.9rem; font-weight: 900; color: {{ $isMarketplace ? '#ffffff' : 'var(--cor-texto-principal, #ffffff)' }};">
                {{ explode(' ', trim($player->name))[0] }}
            </span>
        </div>
    </button>

    {{-- MENU DROPDOWN --}}
    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-[-10px]"
        style="position: absolute; right: 0; margin-top: 0.75rem; width: 220px;
               background-color: {{ $isMarketplace ? 'rgba(9, 9, 11, 0.95)' : 'var(--cor-bg-header)' }}; 
               border: 1px solid {{ $isMarketplace ? 'rgba(255, 255, 255, 0.05)' : 'rgba(255,255,255,0.1)' }}; 
               border-radius: 1rem; padding: 0.5rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);"
    >
        <div style="padding: 0.75rem; border-bottom: 1px solid {{ $isMarketplace ? 'rgba(255,255,255,0.05)' : 'rgba(255,255,255,0.1)' }}; margin-bottom: 0.5rem;">
            <p style="font-size: 0.65rem; font-weight: 900; opacity: 0.5; text-transform: uppercase; letter-spacing: 0.1em; color: {{ $isMarketplace ? '#ffffff' : 'var(--cor-texto-secundaria, #ffffff)' }};">
                Seu Lobby
            </p>
        </div>

        <nav class="flex flex-col gap-1">
            <a href="{{ route('lobby.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition-colors" style="font-size: 0.85rem; font-weight: 600; color: {{ $isMarketplace ? '#ffffff' : 'var(--cor-texto-secundaria, #ffffff)' }};">
                Meu Perfil
            </a>
            <a href="{{ route('lobby.index', ['secao' => 'compras']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition-colors" style="font-size: 0.85rem; font-weight: 600; color: {{ $isMarketplace ? '#ffffff' : 'var(--cor-texto-secundaria, #ffffff)' }};">
                Minhas Compras
            </a>
            <a href="{{ route('lobby.index', ['secao' => 'colecoes']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition-colors" style="font-size: 0.85rem; font-weight: 600; color: {{ $isMarketplace ? '#ffffff' : 'var(--cor-texto-secundaria, #ffffff)' }};">
                Minhas Coleções
            </a>
            <a href="{{ route('lobby.index', ['secao' => 'decks']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition-colors" style="font-size: 0.85rem; font-weight: 600; color: {{ $isMarketplace ? '#ffffff' : 'var(--cor-texto-secundaria, #ffffff)' }};">
                Meus Decks
            </a>
        </nav>

        {{-- BOTÃO SAIR --}}
        <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid {{ $isMarketplace ? 'rgba(255,255,255,0.05)' : 'rgba(255,255,255,0.1)' }};">
            <button wire:click="logout" 
                style="width: 100%; text-align: left; padding: 0.75rem; border: none; background: none; color: #ef4444; font-weight: 800; font-size: 0.75rem; cursor: pointer; text-transform: uppercase; letter-spacing: 0.05em;"
                class="hover:bg-red-500/10 rounded-lg transition-colors">
                Sair
            </button>
        </div>
    </div>
</div>
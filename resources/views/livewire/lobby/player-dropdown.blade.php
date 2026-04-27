{{-- MENU DROPDOWN (CAIXA PURA) --}}
<div x-cloak class="relative z-[999]"
    style="width: 220px; 
           background-color: {{ $isMarketplace ? 'rgba(9, 9, 11, 0.95)' : '#ffffff' }}; 
           border: 1px solid {{ $isMarketplace ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.1)' }}; 
           border-radius: 1rem; padding: 0; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);"
>
    {{-- CABEÇALHO IGUAL AO DO CARRINHO --}}
    <div class="p-4 text-center rounded-t-xl" style="background-color: {{ $isMarketplace ? '#ff5500' : 'var(--cor-1)' }};">
        <h3 class="text-xs font-black uppercase tracking-widest" 
            style="color: {{ $isMarketplace ? '#ffffff' : 'var(--cor-1-txt, #ffffff)' }};">
            Olá, {{ Auth::guard('player')->user() ? explode(' ', trim(Auth::guard('player')->user()->name))[0] : 'Jogador' }}
        </h3>
    </div>

    <nav class="flex flex-col gap-1 p-2">
        <a href="{{ isset($loja) ? route('store.lobby.index', ['slug' => $loja->url_slug]) : (request()->route('game_slug') ? route('game.lobby.index', ['game_slug' => request()->route('game_slug')]) : route('lobby.index')) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $isMarketplace ? 'text-[#a1a1aa] hover:text-[#ffffff] hover:bg-zinc-800' : 'text-[#18181b] hover:bg-zinc-100' }} transition-colors" style="font-size: 0.85rem; font-weight: 700;">
            <i class="ph ph-user text-lg opacity-70"></i> Meu Perfil
        </a>
        
        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $isMarketplace ? 'text-[#a1a1aa] hover:text-[#ffffff] hover:bg-zinc-800' : 'text-[#18181b] hover:bg-zinc-100' }} transition-colors" style="font-size: 0.85rem; font-weight: 700;">
            <div class="flex items-center gap-3">
                <i class="ph ph-wallet text-lg opacity-70"></i> Meus Créditos
            </div>
            <span style="font-size: 0.7rem; font-weight: 900; opacity: 0.6;">R$ 0,00</span>
        </a>

        <a href="{{ route('lobby.index', ['secao' => 'compras']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $isMarketplace ? 'text-[#a1a1aa] hover:text-[#ffffff] hover:bg-zinc-800' : 'text-[#18181b] hover:bg-zinc-100' }} transition-colors" style="font-size: 0.85rem; font-weight: 700;">
            <i class="ph ph-shopping-bag text-lg opacity-70"></i> Meus Pedidos
        </a>

        <a href="{{ route('lobby.index', ['secao' => 'colecoes']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $isMarketplace ? 'text-[#a1a1aa] hover:text-[#ffffff] hover:bg-zinc-800' : 'text-[#18181b] hover:bg-zinc-100' }} transition-colors" style="font-size: 0.85rem; font-weight: 700;">
            <i class="ph ph-cards text-lg opacity-70"></i> Minha Coleção
        </a>

        <a href="{{ route('lobby.index', ['secao' => 'decks']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $isMarketplace ? 'text-[#a1a1aa] hover:text-[#ffffff] hover:bg-zinc-800' : 'text-[#18181b] hover:bg-zinc-100' }} transition-colors" style="font-size: 0.85rem; font-weight: 700;">
            <i class="ph ph-stack text-lg opacity-70"></i> Meus Decks
        </a>
    </nav>

    {{-- BOTÃO SAIR --}}
    <div style="padding: 0 0.5rem 0.5rem 0.5rem;">
        <div style="padding-top: 0.5rem; border-top: 1px solid rgba(0,0,0,0.05);">
            <button wire:click="logout" 
                style="width: 100%; text-align: left; padding: 0.75rem; border: none; background: none; color: #ef4444; font-weight: 800; font-size: 0.75rem; cursor: pointer; text-transform: uppercase; letter-spacing: 0.05em;"
                class="flex items-center gap-3 hover:bg-red-50 rounded-lg transition-colors">
                <i class="ph ph-sign-out text-lg"></i> Sair
            </button>
        </div>
    </div>
</div>
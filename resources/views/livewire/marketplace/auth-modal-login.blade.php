<div 
    x-data="{ show: false, state: 'player' }" 
    x-on:open-login-modal.window="show = true"
    x-on:close-login-modal.window="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show" x-cloak
    style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 9999; background-color: rgba(0, 0, 0, 0.85); backdrop-filter: blur(15px);"
>
    <div 
        @click.away="show = false"
        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 450px; 
        background-color: {{ $isMarketplace ? 'rgba(9, 9, 11, 0.95)' : 'var(--cor-bg-header)' }}; 
        border: 1px solid {{ $isMarketplace ? 'rgba(255, 255, 255, 0.05)' : 'rgba(255,255,255,0.1)' }}; 
        border-radius: 1.5rem; padding: 3rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); animation: fadeInVs 0.3s ease-out;
        color: {{ $isMarketplace ? '#ffffff' : 'var(--cor-texto-secundaria, #ffffff)' }};"
    >
        {{-- BOTÃO FECHAR --}}
        <button @click="show = false" style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; color: inherit; cursor: pointer; opacity: 0.5;">
            <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        {{-- LOGIN DO JOGADOR --}}
        <div x-show="state === 'player'" style="text-align: center;">
            <div style="margin-bottom: 2.5rem;">
                {{-- LOGO OU FALLBACK --}}
                <div class="flex justify-center mb-6">
                    @if(!$isMarketplace && isset($loja->visual) && $loja->visual->logo_main)
                        <img src="{{ asset('store_images/' . $loja->url_slug . '/' . $loja->visual->logo_main) }}" alt="{{ $loja->name }}" class="max-h-16 object-contain">
                    @else
                        <div style="background-color: {{ $isMarketplace ? '#f59e0b' : 'var(--cor-cta)' }}; color: #fff; font-weight: 900; padding: 0.8rem; border-radius: 8px; font-size: 1.2rem; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            {{ $isMarketplace ? 'VS' : strtoupper(substr($loja->name ?? 'L', 0, 1)) }}
                        </div>
                    @endif
                </div>

                <h2 style="font-size: 1.8rem; font-weight: 900; text-transform: uppercase; font-style: italic; letter-spacing: -0.025em;">Bem-vindo</h2>
                <p style="opacity: 0.7; font-size: 0.95rem; font-weight: 500;">Acesse sua conta de <span style="color: {{ $isMarketplace ? '#f59e0b' : 'var(--cor-cta)' }}; font-weight: 800;">Jogador</span></p>
            </div>

            <form wire:submit.prevent="loginPlayer" style="display: flex; flex-direction: column; gap: 1.25rem; text-align: left;">
                <div>
                    <label style="display: block; font-size: 0.7rem; font-weight: 800; opacity: 0.5; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">E-MAIL OU USUÁRIO</label>
                    <input type="email" wire:model="email" class="w-full text-sm border-none outline-none shadow-inner py-4 px-4 rounded-xl"
                        style="background-color: {{ $isMarketplace ? '#18181b' : 'var(--cor-terciaria)' }}; color: inherit;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.7rem; font-weight: 800; opacity: 0.5; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">SENHA</label>
                    <input type="password" wire:model="password" class="w-full text-sm border-none outline-none shadow-inner py-4 px-4 rounded-xl"
                        style="background-color: {{ $isMarketplace ? '#18181b' : 'var(--cor-terciaria)' }}; color: inherit;">
                </div>

                <button type="submit" 
                    style="width: 100%; background-color: {{ $isMarketplace ? '#ffffff' : 'var(--cor-cta)' }}; 
                    color: {{ $isMarketplace ? '#000000' : 'var(--cor-cta-txt)' }}; 
                    font-weight: 900; padding: 1.25rem; border-radius: 0.75rem; border: none; cursor: pointer; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.025em; transition: opacity 0.2s;">
                    ENTRAR
                </button>
            </form>
            @if(!$isMarketplace)
            <div style="margin-top: 1.5rem; text-align: center; border-top: 1px solid {{ $isMarketplace ? 'rgba(255,255,255,0.05)' : 'rgba(255,255,255,0.1)' }}; padding-top: 1.5rem;">
                <p style="font-size: 0.75rem; font-weight: 600; opacity: 0.6; margin-bottom: 0.5rem; color: {{ $isMarketplace ? '#ffffff' : 'var(--cor-texto-secundaria, #ffffff)' }};">
                    Ainda não tem uma conta no VersusTCG?
                </p>
                
                {{-- AQUI ENTRA O GATILHO PARA O SEU WIZARD --}}
                <a href="/registro/jogador?loja={{ $loja->url_slug }}" 
                    style="display: inline-block; background: none; border: none; font-weight: 900; cursor: pointer; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.05em; transition: opacity 0.2s; color: var(--cor-cta); text-decoration: none;"
                    class="hover:opacity-80">
                    Criar Minha Conta
                </a>
            </div>
            @endif

            @if($isMarketplace)
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
                <button @click="state = 'store'" style="background: none; border: none; color: #3b82f6; font-weight: 800; cursor: pointer; font-size: 0.85rem; width: 100%; text-transform: uppercase;">Acesse o Painel da Loja →</button>
            </div>
            @endif
        </div>

        {{-- (Seção de Lojista segue escondida para Marketplace=false) --}}
        @if($isMarketplace)
            {{-- Código do painel lojista... --}}
        @endif

        <style>
            @keyframes fadeInVs { from { opacity: 0; transform: translate(-50%, -48%) scale(0.95); } to { opacity: 1; transform: translate(-50%, -50%) scale(1); } }
        </style>
    </div>
</div>
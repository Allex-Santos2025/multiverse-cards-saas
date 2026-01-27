<div 
    x-data="{ show: false, state: 'player' }" 
    x-on:open-login-modal.window="show = true"
    x-on:close-login-modal.window="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 9999; background-color: rgba(0, 0, 0, 0.9); backdrop-filter: blur(20px);"
>
    <div 
        @click.away="show = false"
        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 450px; background: rgba(9, 9, 11, 0.95); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 1.5rem; padding: 3rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8); animation: fadeInVs 0.3s ease-out;"
    >
        {{-- BOTÃO FECHAR --}}
        <button @click="show = false" style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; color: #52525b; cursor: pointer;">
            <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        {{-- LOGIN DO JOGADOR --}}
        <div x-show="state === 'player'" style="text-align: center;">
            <div style="margin-bottom: 2rem;">
                <div style="display: inline-block; background-color: #f59e0b; color: #000; font-weight: 900; font-style: italic; padding: 0.3rem 0.8rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.8rem;">VS</div>
                <h2 style="font-size: 1.75rem; font-weight: 800; color: #ffffff; text-transform: uppercase; font-style: italic;">Bem-vindo</h2>
                <p style="color: #a1a1aa; font-size: 0.9rem;">Acesse sua conta de <span style="color: #f59e0b; font-weight: 700;">Jogador</span></p>
            </div>

            <form wire:submit.prevent="loginPlayer" style="display: flex; flex-direction: column; gap: 1rem; text-align: left;">
                <div>
                    <input type="email" wire:model="email" placeholder="Seu e-mail" style="width: 100%; background: #18181b; border: 1px solid #27272a; color: #fff; padding: 0.8rem 1rem; border-radius: 0.75rem; outline: none;">
                    {{-- CORREÇÃO AQUI: @enderror --}}
                    @error('email') <span style="color: #ef4444; font-size: 0.7rem; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>

                <div>
                    <input type="password" wire:model="password" placeholder="Sua senha" style="width: 100%; background: #18181b; border: 1px solid #27272a; color: #fff; padding: 0.8rem 1rem; border-radius: 0.75rem; outline: none;">
                    {{-- CORREÇÃO AQUI: @enderror --}}
                    @error('password') <span style="color: #ef4444; font-size: 0.7rem; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>

                <button type="submit" style="width: 100%; background: #fff; color: #000; font-weight: 900; padding: 1rem; border-radius: 0.75rem; border: none; cursor: pointer; text-transform: uppercase; font-size: 0.8rem;">
                    ENTRAR NA ARENA
                </button>
            </form>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05);">
                <button @click="state = 'store'" style="background: none; border: none; color: #3b82f6; font-weight: 700; cursor: pointer; font-size: 0.85rem; width: 100%;">Acesse o Painel da Loja →</button>
            </div>
        </div>

        {{-- LOCALIZADOR DE LOJA --}}
        <div x-show="state === 'store'" style="text-align: center;">
            <button @click="state = 'player'" style="background: none; border: none; color: #52525b; font-weight: 700; cursor: pointer; font-size: 0.75rem; margin-bottom: 1.5rem;">← VOLTAR</button>
            
            <div style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.75rem; font-weight: 800; color: #ffffff; text-transform: uppercase; font-style: italic;">Painel Loja</h2>
                <p style="color: #a1a1aa; font-size: 0.9rem;">Informe o slug da sua loja.</p>
            </div>

            <div style="display: flex; align-items: center; background: #121214; border-radius: 0.75rem; padding: 0.8rem 1rem; border: 1px solid #3b82f644; margin-bottom: 1.5rem;">
                <span style="color: #52525b; font-size: 0.85rem; font-weight: 700;">vs.com/</span>
                <input type="text" wire:model="storeSlug" placeholder="minha-loja" style="background: none; border: none; color: #fff; outline: none; margin-left: 0.25rem; width: 100%;">
            </div>

            <button type="button" wire:click="redirectToStore" style="width: 100%; background: #3b82f6; color: #fff; font-weight: 900; padding: 1rem; border-radius: 0.75rem; border: none; cursor: pointer; text-transform: uppercase; font-size: 0.8rem;">
                IR PARA MEU PAINEL
            </button>
        </div>

        <style>
            @keyframes fadeInVs {
                from { opacity: 0; transform: translate(-50%, -48%) scale(0.95); }
                to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            }
        </style>
    </div>
</div>
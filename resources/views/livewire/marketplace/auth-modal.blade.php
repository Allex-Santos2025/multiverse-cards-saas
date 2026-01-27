<div 
    x-data="{ show: false }" 
    x-on:open-auth-modal.window="show = true"
    x-on:close-auth-modal.window="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    {{-- FUNDO: Mantém o desfoque e a cor, mas serve apenas como moldura fixa --}}
    style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 9999; background-color: rgba(0, 0, 0, 0.9); backdrop-filter: blur(8px);"
>
    <div 
        @click.away="show = false"
        {{-- MODAL: Forçamos o centro usando 50% de topo/esquerda e uma tradução de -50% --}}
        {{-- Isso garante que o centro do MODAL fique no centro da TELA --}}
        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 750px; background-color: #09090b; border-radius: 1.5rem; border: 1px solid #27272a; padding: 2.5rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);"
    >
        {{-- BOTÃO FECHAR (Original) --}}
        <button @click="show = false" style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; color: #52525b; cursor: pointer; transition: color 0.2s;" onmouseenter="this.style.color='#fff'" onmouseleave="this.style.color='#52525b'">
            <svg style="width: 1.75rem; height: 1.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        {{-- CABEÇALHO (Cores Originais: #f59e0b) --}}
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: inline-block; background-color: #f59e0b; color: #000; font-weight: 900; font-style: italic; padding: 0.2rem 0.6rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.8rem;">VS</div>
            <h2 style="font-size: 2rem; font-weight: 800; color: #ffffff; margin-bottom: 0.5rem;">Crie sua conta <span style="color: #f59e0b; font-style: italic;">Versus</span></h2>
            <p style="color: #a1a1aa; font-size: 1rem;">Escolha como você quer participar do multiverso.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            
            {{-- OPÇÃO JOGADOR (Cores Originais: #f59e0b) --}}
            <div 
                @click="window.location.href = '/registro/jogador'"
                onmouseenter="this.style.borderColor='#f59e0b'; this.style.boxShadow='0 15px 50px -10px rgba(245, 158, 11, 0.4)'; this.querySelector('h3').style.color='#f59e0b'; this.querySelector('.icon-box').style.backgroundColor='#f59e0b'; this.querySelector('.icon-box').style.boxShadow='0 0 20px rgba(245, 158, 11, 0.6)'; this.querySelector('.icon-svg').style.color='#000';" 
                onmouseleave="this.style.borderColor='transparent'; this.style.boxShadow='none'; this.querySelector('h3').style.color='#fff'; this.querySelector('.icon-box').style.backgroundColor='#27272a'; this.querySelector('.icon-box').style.boxShadow='none'; this.querySelector('.icon-svg').style.color='#f59e0b';"
                style="cursor: pointer; border-radius: 1.25rem; border: 2px solid transparent; background-color: #18181b; padding: 2rem; transition: all 0.4s ease;"
            >
                <div class="icon-box" style="margin-bottom: 1.5rem; display: flex; height: 3.5rem; width: 3.5rem; align-items: center; justify-content: center; border-radius: 50%; background-color: #27272a; transition: all 0.3s ease;">
                    <svg class="icon-svg" style="width: 1.75rem; height: 1.75rem; color: #f59e0b; transition: all 0.3s ease;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 0.5rem; transition: all 0.3s ease;">Sou Jogador</h3>
                <p style="color: #a1a1aa; font-size: 0.9rem; line-height: 1.5;">Quero comprar cartas, montar decks, organizar minha coleção e participar de leilões.</p>
            </div>

            {{-- OPÇÃO LOJISTA (Cores Originais: #3b82f6) --}}
            <div 
                @click="window.location.href = '/planos'"
                onmouseenter="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 15px 50px -10px rgba(59, 130, 246, 0.4)'; this.querySelector('h3').style.color='#3b82f6'; this.querySelector('.icon-box').style.backgroundColor='#3b82f6'; this.querySelector('.icon-box').style.boxShadow='0 0 20px rgba(59, 130, 246, 0.6)'; this.querySelector('.icon-svg').style.color='#000';" 
                onmouseleave="this.style.borderColor='transparent'; this.style.boxShadow='none'; this.querySelector('h3').style.color='#fff'; this.querySelector('.icon-box').style.backgroundColor='#27272a'; this.querySelector('.icon-box').style.boxShadow='none'; this.querySelector('.icon-svg').style.color='#3b82f6';"
                style="cursor: pointer; border-radius: 1.25rem; border: 2px solid transparent; background-color: #18181b; padding: 2rem; transition: all 0.4s ease;"
            >
                <div class="icon-box" style="margin-bottom: 1.5rem; display: flex; height: 3.5rem; width: 3.5rem; align-items: center; justify-content: center; border-radius: 0.75rem; background-color: #27272a; transition: all 0.3s ease;">
                    <svg class="icon-svg" style="width: 1.75rem; height: 1.75rem; color: #3b82f6; transition: all 0.3s ease;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 0.5rem; transition: all 0.3s ease;">Sou Lojista</h3>
                <p style="color: #a1a1aa; font-size: 0.9rem; line-height: 1.5;">Quero criar minha loja virtual, gerenciar estoque em massa e vender para todo o Brasil.</p>
            </div>

        </div>

        <div style="margin-top: 2rem; text-align: center; color: #71717a; font-size: 0.9rem;">
            Já tem uma conta? <a href="/login" style="color: #fff; text-decoration: none; font-weight: 600;">Fazer Login</a>
        </div>
    </div>
</div>
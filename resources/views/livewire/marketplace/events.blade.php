<main class="pt-28 pb-12 px-4 min-h-[80vh] flex flex-col justify-center">
    <div class="max-w-6xl mx-auto w-full">
        
        {{-- HEADER COM O "FUNIL" --}}
        <div class="flex items-center gap-4 mb-12 animate-fadeIn">
            <div class="bg-[#ff5500] w-1.5 h-10 rounded-full shadow-[0_0_20px_rgba(255,85,0,0.6)]"></div>
            <h1 class="text-4xl font-black uppercase tracking-tighter italic text-white">Eventos</h1>
        </div>

        {{-- CONTEÚDO CENTRAL --}}
        <div class="flex-grow flex flex-col items-center justify-center py-10 text-center animate-fadeIn" style="animation-delay: 0.2s">
            
            {{-- DECK DE CARTAS ESTILIZADO (Tamanho Ajustado: w-48 h-48) --}}
            <div class="mb-12 relative w-48 h-48 inline-flex items-center justify-center">
                {{-- Brilho de fundo --}}
                <div class="absolute inset-0 bg-[#ff5500]/20 blur-[50px] rounded-full z-0"></div>
                
                <div class="relative z-10 w-full h-full text-[#ff5500] drop-shadow-[0_10px_20px_rgba(0,0,0,0.5)]">
                    {{-- SVG SÓLIDO --}}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" class="w-full h-full">
                        {{-- Carta de Trás --}}
                        <rect x="10" y="10" width="34" height="48" rx="3" transform="rotate(-12 27 34)" fill="#9a3412" /> 
                        {{-- Carta do Meio --}}
                        <rect x="15" y="7" width="34" height="48" rx="3" transform="rotate(-6 32 31)" fill="#c2410c" />
                        {{-- Carta da Frente (Com Gradiente) --}}
                        <defs>
                            <linearGradient id="cardGradientSmall" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#ff5500;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#ff7700;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        <rect x="20" y="4" width="34" height="48" rx="3" fill="url(#cardGradientSmall)" />
                        {{-- Detalhes internos --}}
                        <rect x="24" y="10" width="26" height="24" rx="1.5" fill="#7c2d12" opacity="0.4" />
                        <rect x="24" y="38" width="26" height="3" rx="1" fill="white" opacity="0.7" />
                        <rect x="24" y="44" width="16" height="3" rx="1" fill="white" opacity="0.7" />
                    </svg>
                </div>
            </div>

            <h2 class="text-5xl md:text-6xl font-black mb-6 uppercase tracking-tighter text-white drop-shadow-lg">
                A Arena está sendo preparada
            </h2>
            
            <p class="text-xl md:text-2xl text-gray-400 mb-12 max-w-3xl mx-auto leading-relaxed font-medium">
                Em breve, o <span class="text-white font-extrabold">Versus TCG</span> será o palco dos maiores torneios de Magic, Pokémon e TCGs do Brasil. <br>
                <span class="text-[#ff5500] font-bold uppercase tracking-[0.2em] mt-4 block text-base">Aguarde o lançamento oficial.</span>
            </p>

            {{-- TAG DE STATUS --}}
            <div class="py-4 px-10 bg-[#0a0a0a] border-2 border-orange-900/30 rounded-full inline-flex items-center gap-4 shadow-xl">
                <span class="relative flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-[#ff5500]"></span>
                </span>
                <span class="text-sm font-black uppercase tracking-[0.2em] text-gray-300">Módulo em Desenvolvimento</span>
            </div>
        </div>

    </div>
</main>
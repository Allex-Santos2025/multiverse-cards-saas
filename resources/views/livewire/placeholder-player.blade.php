<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arena em breve - {{ $user->nickname }} | Versus TCG</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0a0a0a] text-white flex items-center justify-center min-h-screen font-sans">
    <div class="max-w-xl p-8 text-center">
        {{-- ÍCONE DE USUÁRIO/PLAYER --}}
        <div class="mb-8 inline-flex items-center justify-center w-24 h-24 rounded-full bg-orange-900/20 text-[#ff5500]">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
        </div>

        <h1 class="text-4xl font-black mb-4 uppercase tracking-tighter">
            E-mail Verificado!
        </h1>
        
        <p class="text-2xl text-gray-400 mb-2">
            Bem-vindo à arena, <span class="text-white font-bold">{{ $user->nickname }}</span>.
        </p>
        
        <p class="text-lg text-gray-500 mb-8 leading-relaxed">
            Sua conta de jogador na <span class="text-[#ff5500]">Versus TCG</span> está ativa. <br>
            Estamos finalizando a montagem da sua <strong>Arena do Jogador</strong>. 
            Em breve, você poderá gerenciar sua coleção, decks e participar de torneios.
        </p>

        {{-- STATUS DO PAINEL --}}
        <div class="py-4 px-6 bg-gray-900/50 rounded-xl border border-gray-800 inline-block">
            <span class="text-sm text-gray-500 uppercase tracking-widest font-bold">Painel do Jogador</span>
            <div class="flex items-center justify-center gap-2 mt-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-[#ff5500]"></span>
                </span>
                <span class="text-white font-mono">EM CONSTRUÇÃO</span>
            </div>
        </div>

        <div class="mt-12 text-sm text-gray-600">
            &copy; {{ date('Y') }} Versus TCG - O Marketplace Definitivo.
        </div>
    </div>
</body>
</html>
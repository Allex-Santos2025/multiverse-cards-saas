<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Em breve - {{ $store->name }} | Versus TCG</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0a0a0a] text-white flex items-center justify-center min-h-screen font-sans">
    <div class="max-w-xl p-8 text-center">
        <div class="mb-8 inline-flex items-center justify-center w-24 h-24 rounded-full bg-orange-900/20 text-[#ff5500]">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>

        <h1 class="text-4xl font-black mb-4 uppercase tracking-tighter">
            E-mail Verificado!
        </h1>
        
        <p class="text-2xl text-gray-400 mb-2">
            Olá, <span class="text-white font-bold">{{ $store->name }}</span>.
        </p>
        
        <p class="text-lg text-gray-500 mb-8 leading-relaxed">
            Sua conta na <span class="text-[#ff5500]">Versus TCG</span> está ativa. <br>
            Nossa equipe está terminando de configurar as ferramentas da sua loja. 
            <strong>Em breve, você receberá um novo e-mail com seus dados de acesso ao painel.</strong>
        </p>

        <div class="py-4 px-6 bg-gray-900/50 rounded-xl border border-gray-800 inline-block">
            <span class="text-sm text-gray-500 uppercase tracking-widest font-bold">Status do Painel</span>
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
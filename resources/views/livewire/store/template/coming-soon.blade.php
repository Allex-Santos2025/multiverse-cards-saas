<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview da Loja | TCG Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { orange: { 500: '#f97316', 600: '#ea580c', 700: '#c2410c' } } } }
        }
    </script>
</head>
<body class="dark">

    <div class="min-h-screen bg-zinc-50 dark:bg-[#09090b] flex flex-col items-center justify-center p-6 antialiased font-sans">
        
        <div class="fixed inset-0 overflow-hidden pointer-events-none opacity-50">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-orange-600/10 rounded-full blur-[120px]"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-zinc-500/10 rounded-full blur-[120px]"></div>
        </div>

        <main class="relative w-full max-w-lg">
            <div class="relative flex justify-center mb-12">
                <div class="absolute w-28 h-40 bg-zinc-800 rounded-2xl border border-white/5 rotate-[-15deg] -translate-x-8 shadow-xl"></div>
                <div class="absolute w-28 h-40 bg-zinc-700 rounded-2xl border border-white/10 rotate-[10deg] translate-x-8 shadow-xl"></div>
                <div class="relative w-28 h-40 bg-gradient-to-br from-orange-500 to-orange-700 rounded-2xl border border-orange-400/50 shadow-[0_20px_50px_rgba(234,88,12,0.3)] flex items-center justify-center z-10">
                    <i class="ph-fill ph-storefront text-5xl text-white"></i>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900/50 border border-zinc-200 dark:border-white/5 shadow-2xl rounded-[2.5rem] p-8 md:p-12 backdrop-blur-2xl text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/10 border border-orange-500/20 mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-600"></span>
                    </span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-orange-500">Preview Modo Lojista</span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-zinc-900 dark:text-white leading-tight mb-4 tracking-tighter italic">
                    EM <span class="text-orange-600">BREVE</span> AQUI
                </h1>
                
                <p class="text-zinc-500 dark:text-zinc-400 text-sm leading-relaxed max-w-[320px] mx-auto font-medium">
                    Esta é a visão do seu cliente. Quando você terminar as configurações no Dashboard, sua vitrine aparecerá automaticamente nesta página.
                </p>

                <div class="mt-10 pt-8 border-t border-zinc-100 dark:border-white/5">
                    <p class="text-[9px] text-zinc-400 dark:text-zinc-600 uppercase tracking-[0.2em] font-bold">
                        Aguardando configurações de catálogo
                    </p>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
<main class="min-h-screen flex items-center justify-center relative px-4">
    <div class="glow-bg"></div>

    <div class="w-full max-w-md">
        <div class="bg-[#111] border border-[#222] rounded-2xl p-8 pt-10 shadow-2xl relative overflow-hidden">

            <div class="flex justify-center mt-2 mb-6"> 
                <div class="flex items-center gap-2.5 group cursor-default transform scale-90"> 
                    <div class="relative w-9 h-9 flex items-center justify-center shrink-0">
                        <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-yellow-500 transform -skew-x-12 rounded-sm shadow-lg shadow-orange-500/20"></div>
                        <span class="relative z-10 font-black text-black text-lg tracking-tighter italic pr-0.5">VS</span>
                    </div>

                    <div class="flex flex-col justify-center text-left">
                        <h1 class="font-black text-xl text-white tracking-wide italic leading-none">
                            VERSUS <span class="text-gray-600 text-base not-italic font-bold">TCG</span>
                        </h1>
                        <p class="text-[8px] text-zinc-500 font-medium tracking-[0.2em] uppercase mt-0.5 opacity-80">
                            Um login. Infinitos Universos.
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-white mb-2 tracking-tight">Painel do Lojista</h2>
                <p class="text-gray-400 text-sm">Gerencie sua loja, estoque e pedidos.</p>
            </div>
            @error('login_error')
                <div class="mb-6 p-4 bg-orange-500/10 border border-orange-500/30 rounded-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-2 duration-300">
                    <i class="ph ph-warning-circle text-orange-500 text-xl"></i>
                    <div class="flex flex-col">
                        <span class="text-orange-200 text-[10px] font-black uppercase tracking-wider">Falha na Autenticação</span>
                        <span class="text-orange-400/80 text-[9px] font-medium">{{ $message }}</span>
                    </div>
                </div>
            @enderror
            <form wire:submit.prevent="autenticar" class="space-y-5 relative">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">E-mail ou Usuário</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-envelope text-gray-500"></i>
                        </div>
                        <input type="email" wire:model="email" 
                            class="input-dark w-full pl-10 pr-4 py-3 rounded-lg text-sm text-white placeholder-gray-600" 
                            placeholder="ex: contato@sualoja.com" required>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Senha</label>
                        <a href="#" class="text-xs text-[#ff5500] hover:text-orange-400 transition-colors">Esqueceu?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-lock text-gray-500"></i>
                        </div>
                        <input type="password" id="password" wire:model="password" 
                            class="input-dark w-full pl-10 pr-10 py-3 rounded-lg text-sm text-white placeholder-gray-600" 
                            placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-white transition-colors focus:outline-none">
                            <i id="eye-icon" class="ph ph-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full py-4 rounded-lg text-white font-black text-sm tracking-wide shadow-lg shadow-orange-900/20 mt-2 uppercase">
                    ACESSAR PAINEL
                </button>
            </form>

            <div class="mt-10 pt-6 border-t border-white/[0.05] text-center">
                <div class="flex flex-col items-center gap-2 group cursor-default">
                    <div class="flex items-center gap-2 text-zinc-600 group-hover:text-orange-500/50 transition-colors duration-500">
                        <i class="ph ph-shield-check text-lg"></i>
                        <span class="text-[9px] font-black uppercase tracking-[0.3em]">Ambiente Criptografado</span>
                    </div>
                    <p class="text-[8px] text-zinc-700 font-bold uppercase tracking-[0.1em]">
                        Tecnologia <span class="text-zinc-500">Versus TCG</span> &copy; 2026
                    </p>
                </div>
            </div>
        </div> </div>
</main>
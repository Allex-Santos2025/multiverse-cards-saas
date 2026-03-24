{{-- 1. FAVICON --}}
@section('favicon')
    @if(isset($loja->visual) && $loja->visual->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('store_images/' . $loja->url_slug . '/' . $loja->visual->favicon) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/favicon.png') }}">
    @endif
@endsection

<div x-data="{ showPass: false, showNewPass: false, showConfirmPass: false }"> {{-- Root --}}

    @if(isset($loja))
        @include('partials.template.styles', ['loja' => $loja])
    @endif

    @php
        $v = $loja->visual;
        $corSecundaria = $v->color_secondary ?? '#1f2937'; 
        $corBgHeader   = $v->color_header_bg ?? '#111112';
        $corTerciaria  = $v->color_tertiary  ?? '#e5e7eb';
        
        // Lógica de Contraste para o White Label
        $textoNoCard  = getSafeTextColor($corSecundaria, $corBgHeader);
        $textoNoInput = getSafeTextColor($corSecundaria, $corTerciaria);
    @endphp

    {{-- BLOCO 1: LOJA COM IDENTIDADE (WHITE LABEL / CAMALEÃO) --}}
    @if($v && $v->logo_main)
        <main class="min-h-screen flex items-center justify-center relative px-4 transition-colors duration-500"
              style="background-color: var(--cor-secundaria); color: var(--cor-texto-secundaria);">
            
            <div class="w-full max-w-md relative z-10">
                <div class="rounded-3xl p-8 pt-10 shadow-2xl relative border"
                     style="background-color: var(--cor-bg-header); border-color: rgba(255,255,255,0.05); color: {{ $textoNoCard }};">

                    <div class="flex justify-center mb-8"> 
                        <img src="{{ asset('store_images/' . $loja->url_slug . '/' . $v->logo_main) }}" alt="{{ $loja->name }}" class="max-h-16 object-contain">
                    </div>

                    <div class="text-center mb-10">
                        <h2 class="text-2xl font-black mb-2 tracking-tight text-inherit uppercase italic">
                            @if($mode === 'login') PAINEL DO LOJISTA @elseif($mode === 'forgot') RECUPERAR SENHA @else DEFINIR NOVA SENHA @endif
                        </h2>
                        <p class="text-sm opacity-60 text-inherit font-medium uppercase tracking-widest">
                            @if($mode === 'login') Gerencie sua loja, estoque e pedidos. @elseif($mode === 'forgot') Enviaremos as instruções por e-mail. @else Escolha uma senha forte e segura. @endif
                        </p>
                    </div>

                    {{-- FORMULÁRIO 1: LOGIN --}}
                    @if($mode === 'login')
                    <form wire:submit.prevent="autenticar" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] opacity-50 mb-2 pl-1">E-MAIL OU USUÁRIO</label>
                            <div class="relative">
                                <i class="ph ph-envelope absolute left-3 top-1/2 -translate-y-1/2 opacity-50" style="color: {{ $textoNoInput }};"></i>
                                <input type="email" wire:model="email" class="w-full pl-10 pr-4 py-4 rounded-xl text-sm border-none outline-none shadow-inner" 
                                       style="background-color: var(--cor-terciaria); color: {{ $textoNoInput }};" placeholder="ex: contato@sualoja.com">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 pl-1">
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] opacity-50">SENHA</label>
                                <button type="button" wire:click="$set('mode', 'forgot')" class="text-[9px] font-black uppercase hover:opacity-75 transition-opacity" style="color: var(--cor-cta);">ESQUECEU?</button>
                            </div>
                            <div class="relative">
                                <i class="ph ph-lock absolute left-3 top-1/2 -translate-y-1/2 opacity-50" style="color: {{ $textoNoInput }};"></i>
                                <input :type="showPass ? 'text' : 'password'" wire:model="password" class="w-full pl-10 pr-12 py-4 rounded-xl text-sm border-none outline-none shadow-inner" 
                                       style="background-color: var(--cor-terciaria); color: {{ $textoNoInput }};" placeholder="••••••••">
                                <button type="button" @click="showPass = !showPass" class="absolute right-4 top-1/2 -translate-y-1/2 opacity-40 hover:opacity-100 transition-opacity">
                                    <i class="ph text-lg" :class="showPass ? 'ph-eye-slash' : 'ph-eye'" style="color: {{ $textoNoInput }};"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 rounded-xl font-black text-xs tracking-[0.3em] shadow-lg mt-2 uppercase transition-all hover:scale-[1.01]"
                                style="background-color: var(--cor-cta); color: var(--cor-cta-txt);">
                            ACESSAR PAINEL
                        </button>
                    </form>
                    @endif

                    {{-- FORMULÁRIO 2: RECUPERAÇÃO --}}
                    @if($mode === 'forgot')
                    <form wire:submit.prevent="enviarRecuperacao" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] opacity-50 mb-2 pl-1">SEU E-MAIL CADASTRADO</label>
                            <div class="relative">
                                <i class="ph ph-envelope absolute left-3 top-1/2 -translate-y-1/2 opacity-50" style="color: {{ $textoNoInput }};"></i>
                                <input type="email" wire:model.live="recoverEmail" wire:key="rec-white" class="w-full pl-10 pr-4 py-4 rounded-xl text-sm border-none outline-none shadow-inner" 
                                       style="background-color: var(--cor-terciaria); color: {{ $textoNoInput }};" placeholder="ex: contato@sualoja.com">
                            </div>
                            @error('recoverEmail') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            @if(session()->has('recoverSuccess')) <span class="text-green-500 text-xs mt-2 block font-bold">{{ session('recoverSuccess') }}</span> @endif
                        </div>

                        <button type="submit" class="w-full py-4 rounded-xl font-black text-xs tracking-[0.3em] shadow-lg mt-2 uppercase transition-all hover:scale-[1.01]"
                                style="background-color: var(--cor-cta); color: var(--cor-cta-txt);">
                            ENVIAR LINK DE ACESSO
                        </button>

                        <div class="text-center pt-2">
                            <button type="button" wire:click="$set('mode', 'login')" class="text-[9px] font-black uppercase tracking-[0.2em] opacity-50 hover:opacity-100 transition-opacity">
                                &larr; VOLTAR PARA O LOGIN
                            </button>
                        </div>
                    </form>
                    @endif

                    {{-- FORMULÁRIO 3: NOVA SENHA (RESET) --}}
                    @if($mode === 'reset')
                    <form wire:submit.prevent="redefinirSenha" class="space-y-6">
                        <input type="hidden" wire:model="token">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] opacity-50 mb-2 pl-1">NOVA SENHA</label>
                            <div class="relative">
                                <input :type="showNewPass ? 'text' : 'password'" wire:model="new_password" class="w-full px-4 py-4 rounded-xl text-sm border-none outline-none shadow-inner" 
                                       style="background-color: var(--cor-terciaria); color: {{ $textoNoInput }};" placeholder="Pelo menos 8 caracteres">
                                <button type="button" @click="showNewPass = !showNewPass" class="absolute right-4 top-1/2 -translate-y-1/2 opacity-40">
                                    <i class="ph text-lg" :class="showNewPass ? 'ph-eye-slash' : 'ph-eye'" style="color: {{ $textoNoInput }};"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] opacity-50 mb-2 pl-1">CONFIRMAR NOVA SENHA</label>
                            <div class="relative">
                                <input :type="showConfirmPass ? 'text' : 'password'" wire:model="new_password_confirmation" class="w-full px-4 py-4 rounded-xl text-sm border-none outline-none shadow-inner" 
                                       style="background-color: var(--cor-terciaria); color: {{ $textoNoInput }};" placeholder="Repita a nova senha">
                                <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-4 top-1/2 -translate-y-1/2 opacity-40">
                                    <i class="ph text-lg" :class="showConfirmPass ? 'ph-eye-slash' : 'ph-eye'" style="color: {{ $textoNoInput }};"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 rounded-xl font-black text-xs tracking-[0.3em] shadow-lg mt-2 uppercase transition-all hover:scale-[1.01]"
                                style="background-color: var(--cor-cta); color: var(--cor-cta-txt);">
                            ATUALIZAR SENHA
                        </button>
                    </form>
                    @endif

                    <div class="mt-10 pt-6 border-t border-white/[0.05] text-center opacity-30">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <i class="ph ph-shield-check text-lg"></i>
                            <span class="text-[9px] font-black uppercase tracking-[0.4em]">AMBIENTE CRIPTOGRAFADO</span>
                        </div>
                        <p class="text-[8px] font-bold uppercase mt-1">Tecnologia Versus TCG &copy; 2026</p>
                    </div>
                </div>
            </div>
        </main>

    {{-- BLOCO 2: LAYOUT ORIGINAL VERSUS TCG (DARK) --}}
    @else
        <main class="min-h-screen flex items-center justify-center relative px-4 bg-[#0a0a0b]">
            <div class="w-full max-w-md relative z-10">
                <div class="bg-[#111112] border border-white/[0.03] rounded-3xl p-8 pt-10 shadow-2xl relative overflow-hidden">
                    
                    <div class="flex justify-center mb-8"> 
                        <div class="flex items-center gap-2.5 transform scale-110"> 
                            <div class="relative w-10 h-10 flex items-center justify-center shrink-0">
                                <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-yellow-500 transform -skew-x-12 rounded-sm shadow-lg shadow-orange-500/20"></div>
                                <span class="relative z-10 font-black text-black text-xl italic pr-0.5">VS</span>
                            </div>
                            <div class="flex flex-col justify-center text-left">
                                <h1 class="font-black text-2xl text-white tracking-tighter italic leading-none uppercase">VERSUS <span class="text-zinc-600 not-italic">TCG</span></h1>
                                <p class="text-[8px] text-zinc-500 font-medium tracking-[0.3em] uppercase mt-1">Um login. Infinitos Universos.</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-10">
                        <h2 class="text-2xl font-black text-white mb-2 tracking-tight uppercase italic">
                            @if($mode === 'login') PAINEL DO LOJISTA @elseif($mode === 'forgot') RECUPERAR SENHA @else DEFINIR NOVA SENHA @endif
                        </h2>
                        <p class="text-sm text-zinc-500 font-medium uppercase tracking-widest">
                            @if($mode === 'login') Gerencie sua loja, estoque e pedidos. @elseif($mode === 'forgot') Enviaremos as instruções por e-mail. @else Escolha uma senha forte e segura. @endif
                        </p>
                    </div>

                    {{-- FORMULÁRIO 1: LOGIN --}}
                    @if($mode === 'login')
                    <form wire:submit.prevent="autenticar" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-zinc-600 mb-2 pl-1">E-MAIL OU USUÁRIO</label>
                            <input type="email" wire:model="email" class="input-versus w-full bg-[#18181b] text-white px-4 py-4 rounded-xl border border-white/[0.05] outline-none focus:border-orange-500/50 transition-all text-sm" placeholder="ex: contato@sualoja.com">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 pl-1">
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-zinc-600">SENHA</label>
                                <button type="button" wire:click="$set('mode', 'forgot')" class="text-[9px] font-black text-orange-500 uppercase tracking-tighter hover:text-orange-400">ESQUECEU?</button>
                            </div>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" wire:model="password" class="input-versus w-full bg-[#18181b] text-white px-4 py-4 pr-12 rounded-xl border border-white/[0.05] outline-none focus:border-orange-500/50 transition-all text-sm" placeholder="••••••••">
                                <button type="button" @click="showPass = !showPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-white transition-colors">
                                    <i class="ph text-lg" :class="showPass ? 'ph-eye-slash' : 'ph-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-orange-500 text-white py-4 rounded-xl font-black text-xs tracking-[0.3em] uppercase shadow-lg shadow-orange-600/10 hover:scale-[1.02] transition-all">
                            ACESSAR PAINEL
                        </button>
                    </form>
                    @endif

                    {{-- FORMULÁRIO 2: RECUPERAÇÃO --}}
                    @if($mode === 'forgot')
                    <form wire:submit.prevent="enviarRecuperacao" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-zinc-600 mb-2 pl-1">SEU E-MAIL CADASTRADO</label>
                            <input type="email" wire:model.live="recoverEmail" wire:key="rec-versus" class="input-versus w-full bg-[#18181b] text-white px-4 py-4 rounded-xl border border-white/[0.05] outline-none focus:border-orange-500/50 transition-all text-sm" placeholder="ex: contato@sualoja.com">
                            
                            @error('recoverEmail') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            @if(session()->has('recoverSuccess')) <span class="text-green-500 text-xs mt-2 block font-bold">{{ session('recoverSuccess') }}</span> @endif
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-orange-500 text-white py-4 rounded-xl font-black text-xs tracking-[0.3em] uppercase shadow-lg shadow-orange-600/10 hover:scale-[1.02] transition-all">
                            ENVIAR LINK DE ACESSO
                        </button>

                        <div class="text-center pt-2">
                            <button type="button" wire:click="$set('mode', 'login')" class="text-[9px] text-zinc-500 font-black uppercase tracking-[0.2em] hover:text-white transition-colors">
                                &larr; VOLTAR PARA O LOGIN
                            </button>
                        </div>
                    </form>
                    @endif

                    {{-- FORMULÁRIO 3: NOVA SENHA (RESET) --}}
                    @if($mode === 'reset')
                    <form wire:submit.prevent="redefinirSenha" class="space-y-6">
                        <input type="hidden" wire:model="token">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-zinc-600 mb-2 pl-1">NOVA SENHA</label>
                            <div class="relative">
                                <input :type="showNewPass ? 'text' : 'password'" wire:model="new_password" class="input-versus w-full bg-[#18181b] text-white px-4 py-4 pr-12 rounded-xl border border-white/[0.05] outline-none focus:border-orange-500/50 transition-all text-sm" placeholder="Pelo menos 8 caracteres">
                                <button type="button" @click="showNewPass = !showNewPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-white transition-colors">
                                    <i class="ph text-lg" :class="showNewPass ? 'ph-eye-slash' : 'ph-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-zinc-600 mb-2 pl-1">CONFIRMAR NOVA SENHA</label>
                            <div class="relative">
                                <input :type="showConfirmPass ? 'text' : 'password'" wire:model="new_password_confirmation" class="input-versus w-full bg-[#18181b] text-white px-4 py-4 pr-12 rounded-xl border border-white/[0.05] outline-none focus:border-orange-500/50 transition-all text-sm" placeholder="Repita a nova senha">
                                <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-white transition-colors">
                                    <i class="ph text-lg" :class="showConfirmPass ? 'ph-eye-slash' : 'ph-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-orange-500 text-white py-4 rounded-xl font-black text-xs tracking-[0.3em] uppercase shadow-lg shadow-orange-600/10 hover:scale-[1.02] transition-all">
                            SALVAR NOVA SENHA
                        </button>
                    </form>
                    @endif

                    <div class="mt-10 pt-6 border-t border-white/[0.03] text-center">
                        <div class="flex items-center justify-center gap-2 opacity-20">
                            <i class="ph ph-shield-check text-white text-lg"></i>
                            <span class="text-[9px] text-white font-black uppercase tracking-[0.4em]">AMBIENTE CRIPTOGRAFADO</span>
                        </div>
                        <p class="text-[8px] text-zinc-700 font-bold uppercase tracking-[0.1em] mt-2 text-center">Tecnologia Versus TCG &copy; 2026</p>
                    </div>
                </div>
            </div>
        </main>
    @endif

    <style>
        /* CONTRASTE NO LAYOUT WHITE LABEL (OLHO DE LEÃO) */
        input::placeholder {
            color: {{ $textoNoInput }} !important;
            opacity: 0.8 !important;
        }

        /* CONTRASTE NO LAYOUT OFICIAL VERSUS */
        .input-versus::placeholder {
            color: #ffffff !important;
            opacity: 0.5 !important;
        }
    </style>
</div>
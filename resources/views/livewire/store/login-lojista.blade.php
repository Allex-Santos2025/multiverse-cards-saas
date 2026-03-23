{{-- 1. FAVICON: Movido para fora de tudo para garantir que o Layout o encontre --}}
@section('favicon')
    @if(isset($loja->visual) && $loja->visual->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('store_images/' . $loja->url_slug . '/' . $loja->visual->favicon) }}">
    @else
        {{-- Fallback garantido para o sistema Versus --}}
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/favicon.png') }}">
    @endif
@endsection

<div> {{-- Root do Livewire --}}

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

    {{-- BLOCO 1: LOJA COM IDENTIDADE (WHITE LABEL) --}}
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
                        <h2 class="text-2xl font-black mb-2 tracking-tight text-inherit uppercase italic">PAINEL DO LOJISTA</h2>
                        <p class="text-sm opacity-60 text-inherit font-medium uppercase tracking-widest">Gerencie sua loja, estoque e pedidos.</p>
                    </div>

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
                                <a href="#" class="text-[9px] font-black uppercase" style="color: var(--cor-cta);">ESQUECEU?</a>
                            </div>
                            <div class="relative">
                                <i class="ph ph-lock absolute left-3 top-1/2 -translate-y-1/2 opacity-50" style="color: {{ $textoNoInput }};"></i>
                                <input type="password" id="password_white" wire:model="password" class="w-full pl-10 pr-12 py-4 rounded-xl text-sm border-none outline-none shadow-inner" 
                                       style="background-color: var(--cor-terciaria); color: {{ $textoNoInput }};" placeholder="••••••••">
                                <button type="button" onclick="togglePassword('password_white')" class="absolute right-4 top-1/2 -translate-y-1/2 opacity-40">
                                    <i class="ph ph-eye text-lg" style="color: {{ $textoNoInput }};"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 rounded-xl font-black text-xs tracking-[0.3em] shadow-lg mt-2 uppercase transition-all hover:scale-[1.01]"
                                style="background-color: var(--cor-cta); color: var(--cor-cta-txt);">
                            ACESSAR PAINEL
                        </button>
                    </form>

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

    {{-- BLOCO 2: LAYOUT ORIGINAL VERSUS TCG --}}
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
                        <h2 class="text-2xl font-black text-white mb-2 tracking-tight uppercase italic">PAINEL DO LOJISTA</h2>
                        <p class="text-sm text-zinc-500 font-medium uppercase tracking-widest">Gerencie sua loja, estoque e pedidos.</p>
                    </div>

                    <form wire:submit.prevent="autenticar" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-zinc-600 mb-2 pl-1">E-MAIL OU USUÁRIO</label>
                            <input type="email" wire:model="email" class="input-versus w-full bg-[#18181b] text-white px-4 py-4 rounded-xl border border-white/[0.05] outline-none focus:border-orange-500/50 transition-all text-sm" placeholder="ex: contato@sualoja.com">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 pl-1">
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-zinc-600">SENHA</label>
                                <a href="#" class="text-[9px] font-black text-orange-500 uppercase tracking-tighter">ESQUECEU?</a>
                            </div>
                            <div class="relative">
                                <input type="password" id="password_versus" wire:model="password" class="input-versus w-full bg-[#18181b] text-white px-4 py-4 rounded-xl border border-white/[0.05] outline-none focus:border-orange-500/50 transition-all text-sm" placeholder="••••••••">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-orange-500 text-white py-4 rounded-xl font-black text-xs tracking-[0.3em] uppercase shadow-lg shadow-orange-600/10 hover:scale-[1.02] transition-all">
                            ACESSAR PAINEL
                        </button>
                    </form>

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

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>

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
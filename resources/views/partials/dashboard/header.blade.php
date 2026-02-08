@php
    $user = auth('store_user')->user();
    
    $storeName = 'Minha Loja';
    $unreadCount = 0;
    $latestUpdates = collect();
    $slug = 'admin';

    
    if ($user) {
        $store = $user->current_store ?? $user->store; 
        $storeName = $store->name ?? 'Minha Loja';
        $slug = $store->url_slug ?? 'admin';

        // Novidades
        $latestUpdates = \App\Models\Changelog::where('is_published', true)->orderBy('published_at', 'desc')->take(5)->get();
        $unreadCount = \App\Models\Changelog::where('is_published', true)
            ->whereDoesntHave('reads', function($q) use ($user) { $q->where('store_user_id', $user->id); })
            ->count();
    }

    $initials = Str::upper(Str::substr($storeName, 0, 2));
@endphp

<div class="h-20 lg:h-20 px-4 lg:px-6 flex items-center justify-between gap-2 lg:gap-8">
    {{-- LOGO / INICIAIS --}}
    <div class="flex items-center gap-4 shrink-0">
        <div class="w-10 h-10 bg-zinc-900 dark:bg-white rounded flex items-center justify-center">
            <span class="text-white dark:text-zinc-900 font-bold text-xs">{{ $initials }}</span>
        </div>
        <h1 class="text-lg font-bold text-zinc-900 dark:text-white tracking-tight">{{ $storeName }}</h1>
    </div>

    {{-- BUSCA --}}
    <div class="hidden lg:flex flex-1 justify-center px-8">
        <div class="relative w-full max-w-2xl"> {{-- 'w-full' aqui força o preenchimento --}}
            <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400"></i>
            
            {{-- 2. O input agora é 'w-full' real, sem limite de largura --}}
            <input type="text" placeholder="Buscar pedido, carta, cliente..." 
                class="w-full bg-zinc-50 dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 rounded-lg py-2 pl-12 pr-4 text-sm text-zinc-900 dark:text-white focus:outline-none focus:border-orange-500 transition-all">
        </div>
    </div>

    {{-- AÇÕES (TEMA, SININHO, PERFIL) --}}
    <div class="flex items-center gap-1 lg:gap-5 shrink-0" x-data="{ openProfile: false, openNotifications: false }">
        
        <button onclick="toggleTheme()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-zinc-100 dark:hover:white/5 text-zinc-500 transition-all">
            <i id="theme-icon" class="ph ph-moon text-xl dark:ph-sun"></i>
        </button>

        {{-- SININHO --}}
        <div class="relative">
            <button @click="openNotifications = !openNotifications; openProfile = false" 
                class="relative flex items-center justify-center w-9 h-9 text-zinc-500 dark:text-zinc-400 hover:text-orange-500 transition-all group">
                <div class="relative inline-block leading-none">
                    <i class="ph ph-bell text-2xl group-hover:shake transition-transform"></i>
                    @if($unreadCount > 0)
                        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-orange-600 border-2 border-white dark:border-[#0f172a] shadow-sm">
                            <span class="text-[8px] font-black text-white leading-none">{{ $unreadCount }}</span>
                        </span>
                    @endif
                </div>
            </button>

            {{-- DROPDOWN SININHO --}}
            {{-- MENU DAS NOTIFICAÇÕES (DROPDOWN) --}}
<div x-show="openNotifications" 
     x-transition:enter="transition ease-out duration-100"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     @click.away="openNotifications = false"
     class="fixed inset-x-4 top-20 lg:absolute lg:inset-x-auto lg:right-0 lg:top-full lg:mt-3 
            w-auto lg:w-[450px] 
            bg-white dark:bg-[#1e293b] rounded-2xl shadow-2xl border border-zinc-200 dark:border-white/5 
            z-[200] overflow-hidden">
    
    {{-- Cabeçalho --}}
    <div class="p-4 border-b border-zinc-100 dark:border-white/5 flex items-center justify-between bg-zinc-50/50 dark:bg-white/5">
        <h3 class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-widest">Novidades</h3>
        <a href="{{ route('store.dashboard.novidades', ['slug' => $slug]) }}" class="text-[10px] font-bold text-orange-500 hover:text-orange-600 uppercase">Ver Tudo</a>
    </div>

    {{-- Lista de Itens --}}
    <div class="max-h-[60vh] overflow-y-auto lg:max-h-none lg:overflow-visible custom-scrollbar">
        @forelse($latestUpdates as $update)
            @php
                // Checa se já foi lido para aplicar o estilo
                $isRead = $update->reads()->where('store_user_id', auth('store_user')->id())->exists();
            @endphp
            
            <a href="{{ route('store.dashboard.novidades.show', ['slug' => $slug, 'changelog_slug' => $update->slug]) }}" 
               class="relative flex items-start gap-3 p-4 border-b border-zinc-50 dark:border-white/[0.02] hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors group">
                
                {{-- Barra Lateral: Só aparece se NÃO for lido --}}
                @if(!$isRead)
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-orange-600"></div>
                @endif

                {{-- Ícone Dinâmico --}}
                @php
                    // Usamos a variável $isRead que você já definiu no início do seu loop
                    $corSininho = match($update->category) {
                        'Recurso'  => 'bg-emerald-500/10 text-emerald-600',
                        'Melhoria' => 'bg-blue-500/10 text-blue-600',
                        'Correção' => 'bg-orange-500/10 text-orange-600',
                        default    => 'bg-zinc-100 text-zinc-400',
                    };

                    // Se já foi lido (usando a sua variável $isRead), forçamos o cinza
                    if ($isRead) {
                        $corSininho = 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400';
                    }
                @endphp

                <div class="w-8 h-8 shrink-0 rounded-lg flex items-center justify-center transition-colors {{ $corSininho }}">
                    <i class="ph {{ 
                        $update->category === 'Recurso' ? 'ph-star' : 
                        ($update->category === 'Melhoria' ? 'ph-rocket-launch' : 'ph-wrench') 
                    }} text-lg"></i>
                </div>

                {{-- Texto --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs truncate transition-colors
                        {{ $isRead ? 'font-medium text-zinc-500 dark:text-zinc-400' : 'font-black text-zinc-900 dark:text-zinc-100 group-hover:text-orange-500' }}">
                        {{ $update->title }}
                    </p>
                    <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-1 italic">
                        {{ $update->summary }}
                    </p>
                    <span class="text-[9px] text-zinc-400 dark:text-zinc-500 mt-2 block">
                        {{ $update->published_at->diffForHumans() }}
                    </span>
                </div>
            </a>
        @empty
            <div class="p-8 text-center text-zinc-400 text-xs italic">
                Nenhuma novidade recente.
            </div>
        @endforelse
    </div>

    {{-- Rodapé --}}
    <div class="p-2 bg-zinc-50 dark:bg-black/10 text-center border-t border-zinc-100 dark:border-white/5">
        <span class="text-[8px] font-bold text-zinc-400 uppercase tracking-widest">Versus TCG Engine</span>
    </div>
</div>
        </div>

        {{-- SEU PERFIL (RESTAURADO EXATAMENTE COMO ERA) --}}
        <div class="relative">
            <button @click="openProfile = !openProfile; openNotifications = false" @click.away="openProfile = false" 
                class="flex items-center gap-3 pl-5 border-l border-zinc-200 dark:border-white/10 group">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-zinc-900 dark:text-white group-hover:text-orange-500 transition-colors">{{ $user->name ?? 'Usuário' }}</p>
                    <p class="text-[10px] text-zinc-500 uppercase font-bold tracking-widest">Proprietário</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-zinc-200 dark:bg-zinc-800 border border-zinc-300 dark:border-white/5 flex items-center justify-center text-zinc-400 overflow-hidden group-hover:border-orange-500 transition-all">
                    @if($user && $user->avatar_url)
                        <img src="{{ $user->avatar_url }}" class="w-full h-full object-cover">
                    @else
                        <i class="ph ph-user text-xl"></i>
                    @endif
                </div>
            </button>

            <div x-show="openProfile" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute right-0 mt-3 w-64 bg-white dark:bg-[#1e293b] rounded-xl shadow-xl border border-zinc-200 dark:border-white/5 overflow-hidden z-[200]">
                
                {{-- Topo Laranja do Dropdown --}}
                <div class="bg-orange-600 p-4 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-white">
                        <i class="ph ph-user-circle text-xl"></i>
                        <span class="text-xs font-bold truncate w-32">{{ $user->name ?? 'Usuário' }}</span>
                    </div>
                    {{-- Botão de Saída Rápido --}}
                    <form action="{{ route('logout', ['slug' => request()->route('slug')]) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-white/80 hover:text-white transition-colors">
                            <i class="ph ph-sign-out text-xl"></i>
                        </button>
                    </form>
                </div>

                <div class="p-2">
                    <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-white/5 rounded-lg transition-colors">
                        <i class="ph ph-user-gear"></i> Meu Perfil
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-white/5 rounded-lg transition-colors">
                        <i class="ph ph-shield-check"></i> Segurança (2FA)
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-white/5 rounded-lg transition-colors">
                        <i class="ph ph-storefront"></i> Dados da Loja
                    </a>
                    
                    <div class="my-2 border-t border-zinc-100 dark:border-white/5"></div>

                    {{-- Botão de Saída Vermelho --}}
                    <form action="{{ route('logout', ['slug' => request()->route('slug')]) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors text-left">
                            <i class="ph ph-power"></i> Deslogar do Sistema
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
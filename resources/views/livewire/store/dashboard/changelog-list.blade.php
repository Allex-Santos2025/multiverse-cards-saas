<main class="min-h-screen bg-zinc-50 dark:bg-gray-900 text-zinc-900 dark:text-white p-4 md:p-8 transition-colors duration-300">
    <div class="container mx-auto">
        {{-- Breadcrumb --}}
        <nav class="text-xs md:text-sm text-zinc-500 dark:text-gray-400 mb-4 flex items-center gap-2">
            <a href="{{ route('store.dashboard', ['slug' => $slug]) }}" class="hover:text-orange-600 dark:hover:text-white transition-colors underline decoration-zinc-300 dark:decoration-gray-700 underline-offset-4">Dashboard</a>
            <span class="text-zinc-300 dark:text-gray-600">/</span>
            <span class="text-orange-500 font-bold">Novidades do Sistema</span>
        </nav>

        {{-- Título --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white uppercase tracking-tighter">O que há de novo?</h1>
            <p class="text-sm text-zinc-500 dark:text-gray-400 mt-1 font-medium italic">Acompanhe as atualizações e melhorias da plataforma.</p>
        </div>

        {{-- Container de Novidades --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm dark:shadow-lg border border-zinc-200 dark:border-gray-700">
            <div class="divide-y divide-zinc-100 dark:divide-gray-700/50">
                <div class="space-y-4">                
                    
                    @forelse($updates as $update)
                            @php
                                // Verifica se o lojista logado já leu esta novidade
                                $isRead = $update->reads()->where('store_user_id', auth('store_user')->id())->exists();
                            @endphp

                            <a href="{{ route('store.dashboard.novidades.show', ['slug' => $slug, 'changelog_slug' => $update->slug]) }}" 
                            class="relative block p-5 rounded-xl border transition-all duration-300 group
                            {{ $isRead 
                                ? 'bg-zinc-50/50 dark:bg-white/[0.02] border-zinc-200 dark:border-white/5 opacity-60 grayscale-[0.3]' 
                                : 'bg-white dark:bg-gray-800 border-orange-500/30 shadow-sm shadow-orange-500/10 ring-1 ring-orange-500/20' 
                            }}">
                                
                                {{-- Badge de "NOVO" apenas para os não lidos --}}
                                @if(!$isRead)
                                    <span class="absolute -top-2 -left-2 bg-orange-600 text-white text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest shadow-lg animate-pulse">
                                        Novo
                                    </span>
                                @endif

                                <div class="flex items-start gap-4">
                                    {{-- Ícone (fica cinza se lido) --}}
                                    <div class="w-12 h-12 shrink-0 rounded-xl flex items-center justify-center border transition-colors
                                    {{ $isRead 
                                        ? 'bg-zinc-100 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-400' 
                                        : ($update->category === 'Recurso' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600' : 
                                        ($update->category === 'Melhoria' ? 'bg-blue-500/10 border-blue-500/20 text-blue-600' : 
                                        'bg-orange-500/10 border-orange-500/20 text-orange-600')) 
                                    }}">
                                    
                                    <i class="ph {{ 
                                        $update->category === 'Recurso' ? 'ph-star' : 
                                        ($update->category === 'Melhoria' ? 'ph-rocket-launch' : 'ph-wrench') 
                                    }} text-2xl"></i>
                                </div>

                                    <div class="flex-1">
                                        <h3 class="text-sm font-black transition-colors {{ $isRead ? 'text-zinc-500' : 'text-zinc-900 dark:text-white group-hover:text-orange-500' }}">
                                            {{ $update->title }}
                                        </h3>
                                        <p class="text-xs text-zinc-400 mt-1 line-clamp-2">{{ $update->summary }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
    

                        {{-- O seu Estado Vazio --}}
                        <div class="p-12 text-center text-zinc-400 dark:text-gray-500 italic">
                            <i class="ph ph-megaphone text-4xl mb-3 block opacity-20"></i>
                            Ainda não temos novidades para mostrar. Fique atento!
                        </div>
                    @endforelse

                </div>
            </div>

            @if($updates->hasPages())
                <div class="mt-6 border-t border-zinc-100 dark:border-gray-700 pt-4">
                    {{ $updates->links() }}
                </div>
            @endif
        </div>
    </div>
</main>
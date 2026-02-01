<main class="min-h-screen bg-zinc-50 dark:bg-gray-900 text-zinc-900 dark:text-white p-4 md:p-8 transition-colors duration-300">
    <div class="container mx-auto">
        {{-- Nav/Breadcrumb --}}
        <nav class="text-xs md:text-sm text-zinc-500 dark:text-gray-400 mb-4 flex items-center gap-2">
            <a href="{{ route('store.dashboard', ['slug' => auth('store_user')->user()->store->url_slug]) }}" class="hover:text-orange-600 dark:hover:text-white transition-colors underline decoration-zinc-300 dark:decoration-gray-700 underline-offset-4">Dashboard</a>
            <span class="text-zinc-300 dark:text-gray-600">/</span>
            <span class="text-orange-500 font-bold">Log do Sistema</span>
        </nav>

        {{-- Cabeçalho --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white uppercase tracking-tighter">Log do Sistema</h1>
            <p class="text-sm text-zinc-500 dark:text-gray-400 mt-1 font-medium italic">Histórico de ações realizadas na loja.</p>
        </div>

        {{-- Lista de Logs --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm dark:shadow-2xl border border-zinc-200 dark:border-gray-700/50">
            <div class="divide-y divide-zinc-100 dark:divide-gray-700/50">
                <div class="space-y-4">                
                    
                    @forelse($logs as $log)
                        <a href="#" class="block bg-zinc-50 dark:bg-gray-900 p-4 rounded-lg hover:bg-zinc-100 dark:hover:bg-gray-700/50 transition-colors duration-200 group border border-zinc-100 dark:border-transparent">
                            <div class="flex items-start gap-4">
                                
                                {{-- Ícone Dinâmico com Inversão Total de Tema --}}
                                <div class="w-12 h-12 flex-shrink-0 rounded-xl flex items-center justify-center border transition-all 
                                    {{ $log->module === 'security' ? 'bg-zinc-200/50 border-zinc-300/50 dark:bg-zinc-700/50 dark:border-zinc-600/20 group-hover:border-zinc-400 dark:group-hover:border-zinc-500/50' : '' }}
                                    {{ $log->module === 'inventory' ? 'bg-orange-500/10 border-orange-200 dark:bg-orange-600/10 dark:border-orange-500/20 group-hover:border-orange-400 dark:group-hover:border-orange-500/50' : '' }}
                                    {{ $log->module === 'sales' ? 'bg-emerald-500/10 border-emerald-200 dark:bg-emerald-600/10 dark:border-emerald-500/20 group-hover:border-emerald-400 dark:group-hover:border-emerald-500/50' : '' }}
                                    {{ $log->module === 'finance' ? 'bg-blue-500/10 border-blue-200 dark:bg-blue-600/10 dark:border-blue-500/20 group-hover:border-blue-400 dark:group-hover:border-blue-500/50' : '' }}">
                                    
                                    @if($log->module === 'security')
                                        <i class="ph ph-shield-check text-zinc-500 dark:text-zinc-400 text-2xl group-hover:text-zinc-900 dark:group-hover:text-white"></i>
                                    @elseif($log->module === 'inventory')
                                        <i class="ph ph-package text-orange-600 dark:text-orange-500 text-2xl"></i>
                                    @elseif($log->module === 'sales')
                                        <i class="ph ph-shopping-cart text-emerald-600 dark:text-emerald-500 text-2xl"></i>
                                    @elseif($log->module === 'finance')
                                        <i class="ph ph-bank text-blue-600 dark:text-blue-500 text-2xl"></i>
                                    @else
                                        <i class="ph ph-cpu text-purple-600 dark:text-purple-500 text-2xl"></i>
                                    @endif
                                </div>

                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="text-sm md:text-base font-bold text-zinc-800 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-500 transition-colors tracking-tight">
                                            {{ $log->action }}
                                        </h3>
                                        <span class="text-[10px] font-black text-zinc-400 dark:text-gray-500 uppercase tracking-[0.2em] italic">
                                            {{ $log->module }}
                                        </span>
                                    </div>
                                    <p class="text-xs md:text-sm text-zinc-600 dark:text-gray-400 leading-relaxed font-medium">
                                        {{ $log->description }}
                                    </p>
                                    <div class="mt-3 flex items-center gap-2 text-[10px] text-zinc-400 dark:text-gray-500 font-bold uppercase tracking-widest">
                                        <i class="ph ph-calendar-blank"></i>
                                        {{ $log->created_at->translatedFormat('d \d\e F, Y • H:i') }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-12 text-center text-zinc-400 dark:text-gray-500 italic">
                            Nenhum registro de atividade encontrado.
                        </div>
                    @endforelse

                </div>
            </div>

            {{-- Paginação --}}
            @if($logs->hasPages())
                <div class="mt-6 border-t border-zinc-100 dark:border-gray-700 pt-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</main>
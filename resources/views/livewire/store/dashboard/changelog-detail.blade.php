@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-zinc-50 dark:bg-gray-900 p-4 md:p-8">
    <div class="max-w-3xl mx-auto">
        
        {{-- Topo: Voltar e Versão --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('store.dashboard.novidades', ['slug' => $slug]) }}" 
               class="text-zinc-500 hover:text-orange-500 flex items-center gap-2 transition-colors group">
                <i class="ph ph-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                <span class="text-xs font-bold uppercase tracking-widest">Voltar para a lista</span>
            </a>
            <span class="px-3 py-1 bg-orange-600 text-white rounded-full text-[10px] font-black uppercase shadow-sm shadow-orange-500/20">
                {{ $changelog->version }}
            </span>
        </div>

        {{-- Artigo Principal --}}
        <article class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-white/5 overflow-hidden">
            
            {{-- Header da Notícia --}}
            <header class="p-8 border-b border-zinc-100 dark:border-white/5 bg-zinc-50/50 dark:bg-white/5">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tighter
                        {{ $changelog->category === 'Recurso' ? 'bg-emerald-500/10 text-emerald-600' : '' }}
                        {{ $changelog->category === 'Melhoria' ? 'bg-blue-500/10 text-blue-600' : '' }}
                        {{ $changelog->category === 'Correção' ? 'bg-orange-500/10 text-orange-600' : '' }}">
                        {{ $changelog->category }}
                    </span>
                    <span class="text-zinc-300 dark:text-zinc-700">|</span>
                    <span class="text-zinc-400 text-[10px] font-medium uppercase italic">
                        Postado {{ $changelog->published_at->diffForHumans() }}
                    </span>
                </div>
                
                <h1 class="text-3xl font-black text-zinc-900 dark:text-white leading-tight">
                    {{ $changelog->title }}
                </h1>
            </header>

            {{-- Conteúdo Markdown --}}
            <div class="p-8">
                <div class="prose prose-zinc dark:prose-invert max-w-none 
                    text-zinc-600 dark:text-zinc-300 leading-relaxed 
                    prose-headings:font-black prose-a:text-orange-500 prose-strong:text-zinc-900 dark:prose-strong:text-white">
                    
                    {!! Str::markdown($changelog->content) !!}
                    
                </div>
            </div>

            {{-- Footer com Identidade Visual --}}
            <footer class="p-6 bg-zinc-50 dark:bg-white/5 border-t border-zinc-100 dark:border-white/5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-zinc-900 dark:bg-white flex items-center justify-center text-white dark:text-zinc-900">
                        <i class="ph ph-shield-check text-lg"></i>
                    </div>
                    <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Equipe Versus TCG</span>
                </div>
            </footer>
        </article>

    </div>
</div>
@endsection
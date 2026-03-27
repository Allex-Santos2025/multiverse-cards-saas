@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-1 text-xs font-bold uppercase tracking-wider">
        
        {{-- Botão Anterior --}}
        @if ($paginator->onFirstPage())
            <span class="px-2 py-1 opacity-30 cursor-not-allowed" style="color: var(--cor-texto-principal);">
                &laquo; Ant
            </span>
        @else
            <button wire:click="previousPage" wire:loading.attr="disabled" class="px-2 py-1 opacity-70 hover:opacity-100 transition-opacity" style="color: var(--cor-texto-principal);">
                &laquo; Ant
            </button>
        @endif

        {{-- Lógica da Janela Deslizante (Apenas 3 números por vez) --}}
        @php
            // Calcula o início e o fim da janela
            $start = $paginator->currentPage() - 1;
            $end = $paginator->currentPage() + 1;

            // Ajusta se estiver no começo
            if ($start < 1) {
                $start = 1;
                $end = min($paginator->lastPage(), 3);
            }

            // Ajusta se estiver no final
            if ($end > $paginator->lastPage()) {
                $end = $paginator->lastPage();
                $start = max(1, $end - 2);
            }
        @endphp

        {{-- Renderiza apenas os 3 números calculados --}}
        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $paginator->currentPage())
                {{-- PÁGINA ATIVA --}}
                <span class="px-3 py-1.5 rounded-md shadow-sm" style="background-color: var(--cor-secundaria); color: var(--cor-texto-secundaria);">
                    {{ $page }}
                </span>
            @else
                {{-- OUTRAS PÁGINAS --}}
                <button wire:click="gotoPage({{ $page }})" class="px-3 py-1.5 rounded-md opacity-70 hover:opacity-100 hover:bg-black/5 dark:hover:bg-white/5 transition-all" style="color: var(--cor-texto-principal);">
                    {{ $page }}
                </button>
            @endif
        @endfor

        {{-- Botão Próxima --}}
        @if ($paginator->hasMorePages())
            <button wire:click="nextPage" wire:loading.attr="disabled" class="px-2 py-1 opacity-70 hover:opacity-100 transition-opacity" style="color: var(--cor-texto-principal);">
                Próx &raquo;
            </button>
        @else
            <span class="px-2 py-1 opacity-30 cursor-not-allowed" style="color: var(--cor-texto-principal);">
                Próx &raquo;
            </span>
        @endif
    </nav>
@endif
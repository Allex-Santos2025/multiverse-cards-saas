<div
    class="relative flex w-full shadow-sm rounded-md"
    x-data="{
        open: false,
        top: 0,
        left: 0,
        width: 0,
        reposition() {
            const rect = this.$refs.input.getBoundingClientRect();
            this.top = rect.bottom + window.scrollY + 4;
            this.left = rect.left + window.scrollX;
            this.width = rect.width;
        }
    }"
    @click.away="open = false"
    @keydown.escape.window="open = false"
>
    <form wire:submit.prevent="search" class="flex w-full">
        <input
            x-ref="input"
            type="text"
            wire:model.live.debounce.300ms="query"
            @focus="reposition(); open = true"
            @input="reposition(); open = true"
            placeholder="Buscar cartas, coleções ou produtos..."
            class="w-full pl-4 pr-12 py-3 rounded-l-md border-2 border-r-0 border-main-1 text-gray-900 bg-white focus:outline-none focus:ring-0"
            autocomplete="off"
        >
        <button
            type="submit"
            class="bg-main-1 px-6 rounded-r-md transition-all flex items-center justify-center"
        >
            <i class="ph ph-magnifying-glass text-xl"></i>
        </button>
    </form>

    {{-- Dropdown usando position fixed para ficar acima de tudo --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-transition
            :style="`position: fixed; top: ${top}px; left: ${left}px; width: ${width}px; z-index: 9999;`"
            class="bg-white border border-gray-200 rounded-lg shadow-xl overflow-hidden"
            style="display: none;"
        >
            @forelse($results as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="flex flex-row items-center gap-3 px-4 py-2 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition-colors"
                >
                    {{-- Miniatura da Carta (Estilo Liga Magic) --}}
                    <div class="w-8 h-11 shrink-0 rounded overflow-hidden shadow-sm border border-gray-200/50 bg-gray-100">
                        <img src="{{ $item['imagem'] }}" alt="Miniatura" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://placehold.co/250x350/eeeeee/999999?text=X';">
                    </div>
                    
                    {{-- Textos --}}
                    <div class="flex flex-col overflow-hidden">
                        @if($item['name_pt'])
                            <span class="font-bold text-gray-900 text-[13px] truncate">{{ $item['name_pt'] }}</span>
                            <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide truncate">{{ $item['name'] }}</span>
                        @else
                            <span class="font-bold text-gray-900 text-[13px] truncate">{{ $item['name'] }}</span>
                        @endif
                    </div>
                </a>
            @empty
                @if(strlen(trim($query)) >= 2)
                    <div class="px-4 py-4 text-xs font-bold uppercase tracking-widest text-gray-400 text-center">
                        Nenhum resultado encontrado
                    </div>
                @endif
            @endforelse
        </div>
    </template>
</div>
@props(['options'])

{{-- 
    Recebe:
    - options: Array ['foil' => 'Foil', 'signed' => 'Assinada'] vindo do Enum
    - attributes: O wire:model vai estar aqui
--}}

<div 
    x-data="{ 
        open: false, 
        selected: @entangle($attributes->wire('model')), 
        
        get label() {
            if (!this.selected || this.selected.length === 0) return 'Normal';
            if (this.selected.length === 1) return '1 Extra';
            return this.selected.length + ' Extras';
        }
    }" 
    class="relative"
>
    <button 
        @click="open = !open" 
        @click.outside="open = false"
        type="button"
        class="w-full flex items-center justify-between gap-2 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 transition-all hover:bg-slate-50 dark:hover:bg-slate-800"
        :class="{'border-orange-500 ring-1 ring-orange-500': open}"
    >
        <span 
            class="truncate font-medium transition-colors"
            :class="selected && selected.length > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-slate-500 dark:text-slate-400'"
            x-text="label"
        >
            Normal
        </span>

        <svg 
            class="w-4 h-4 text-slate-400 transition-transform duration-200" 
            :class="{'rotate-180': open}" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div 
        x-show="open" 
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-1 w-56 p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-xl origin-top-left"
    >
        <div class="max-h-60 overflow-y-auto space-y-0.5 custom-scrollbar">
            @foreach($options as $value => $label)
                <label 
                    class="flex items-center gap-3 px-2 py-2 rounded-md cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors group"
                >
                    <input 
                        type="checkbox" 
                        value="{{ $value }}"
                        {{ $attributes }} {{-- Aplica o wire:model aqui --}}
                        class="w-4 h-4 text-orange-600 rounded border-slate-300 focus:ring-orange-500 dark:bg-slate-900 dark:border-slate-600 dark:checked:bg-orange-500 transition-all"
                    >
                    
                    <span class="text-sm text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white select-none">
                        {{ $label }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>
</div>
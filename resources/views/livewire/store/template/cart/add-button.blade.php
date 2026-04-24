<div>
    <button 
        type="button"
        x-on:click="
            let input = $el.closest('tr').querySelector('input[type=number]');
            $wire.addToCart(input ? input.value : 1);
        "
        class="px-4 py-2 w-full rounded-lg font-bold text-[10px] tracking-wider uppercase text-white shadow-sm hover:opacity-90 transition-opacity" 
        style="background-color: var(--cor-cta);"
    >
        Comprar
    </button>
</div>
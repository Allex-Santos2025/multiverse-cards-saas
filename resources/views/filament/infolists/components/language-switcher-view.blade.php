@php
    // Não precisamos mais do array $languageNames
@endphp

{{-- 
  Este container terá os botões. 
--}}
<div 
    class="flex flex-wrap gap-2 mt-4" 
    wire:loading.class="opacity-50" 
    wire:target="changeLanguage"
>
    
    {{-- Loopamos direto nas $availableLanguages (que agora são específicas do print) --}}
    @foreach ($availableLanguages as $lang)
        <x-filament::badge
            {{-- Define a cor com base no idioma selecionado --}}
            :color="$selectedLanguage === $lang ? 'primary' : 'gray'"
            
            {{-- CHAMA O MÉTODO changeLanguage() da sua classe PHP --}}
            wire:click="changeLanguage('{{ $lang }}')"
            
            {{-- Adiciona o cursor de clique --}}
            class="cursor-pointer transition hover:scale-110"
            {{-- Adiciona largura mínima para os botões ficarem uniformes --}}
            style="min-width: 2.5rem; text-align: center;" 
        >
            {{-- Exibe apenas as iniciais em maiúsculas --}}
            {{ strtoupper($lang) }}
        </x-filament::badge>
    @endforeach
</div>
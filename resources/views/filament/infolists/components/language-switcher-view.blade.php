{{--
    Blade: Seletor de Idiomas
    Objetivo: Exibir botões para trocar o print de uma carta para o idioma selecionado.
    
    Variáveis esperadas:
    - $availableLanguages: array de códigos de idioma (ex: ['en', 'pt', 'ja'])
    - $selectedLanguage: string (o código de idioma atualmente selecionado)
--}}

@php
    // Usamos um mapa simples para exibir o nome completo se quisermos.
    // O código atual exibe apenas o código em maiúsculas (ex: EN).
@endphp

<div 
    class="flex flex-wrap gap-2 mt-4 justify-center" 
    wire:loading.class="opacity-50" 
    {{-- Garante que o carregamento só afete o componente de troca --}}
    wire:target="changeLanguage, changePrint" 
>
    
    @foreach ($availableLanguages as $lang)
        <x-filament::badge
            {{-- Dispara a ação de Livewire no controlador (ViewCatalogConcept.php) --}}
            wire:click="changeLanguage('{{ $lang }}')"
            
            {{-- Define a cor com base no idioma selecionado --}}
            :color="$selectedLanguage === $lang ? 'primary' : 'gray'"
            
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
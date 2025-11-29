{{-- resources/views/partials/custom-styles.blade.php --}}

{{-- Links para os arquivos CSS locais --}}
<link href="{{ asset('css/keyrune.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/mana.css') }}" rel="stylesheet" type="text/css" />

{{-- Estilo customizado para a sombra do mana cost --}}
<style>
    /* Ajuste fino para sombra do mana cost */
    .ms.ms-cost.ms-shadow {
        margin: 1px 0.7px !important; 
        display: inline-block !important;
    }
</style>
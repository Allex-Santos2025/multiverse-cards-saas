import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

// 1. Registra os plugins que você quer usar
Alpine.plugin(persist);

// 2. Torna o Alpine global para que o Livewire o encontre e use a MESMA instância
window.Alpine = Alpine;

// ATENÇÃO: Remova ou comente a linha Alpine.start();
// No Livewire 3, o próprio Livewire chama o .start() internamente.
// Se você chamar aqui, o erro de "Cannot redefine" volta.
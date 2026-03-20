@php
    // Função de utilidade para garantir contraste (Texto Branco ou Escuro)
    if (!function_exists('getContrastColor')) {
        function getContrastColor($hex) {
            $hex = str_replace('#', '', $hex);
            if(strlen($hex) != 6) return '#1f2937'; // Fallback para texto escuro
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
            return ($yiq >= 128) ? '#1f2937' : '#ffffff';
        }
    }

    // DEFINIÇÃO DOS FALLBACKS (O SEU MARCO ZERO COM OS TESTES ATUAIS)
    $cor1 = $loja->color_primary ?? '#2563EB';           // Azul Versus
    $cor2 = $loja->color_secondary ?? '#1E3A8A';         // Azul Escuro (Menus)
    $cor3 = $loja->color_accent ?? '#F59E0B';            // Amarelo/Dourado (Destaques)
    $corBgHeader = $loja->color_bg_header ?? '#ffffff';  // Vinho (Centro do Header)
    
    // NOVA VARIÁVEL INDEPENDENTE: Barra de Contatos
    // Fallback: O mesmo azul da cor principal padrão
    $corBgTopBar = $loja->color_bg_top_bar ?? '#2563EB'; 

    // CÁLCULOS DE CONTRASTE AUTOMÁTICO
    $textoNoHeader = getContrastColor($corBgHeader);
    $textoNaCor1 = getContrastColor($cor1);
    
    // Novo contraste para a Barra de Contatos
    $textoNoTopBar = getContrastColor($corBgTopBar);
@endphp

<style>
    :root {
        /* PALETA DE CORES PRIMÁRIAS */
        --cor-1: {{ $cor1 }};
        --cor-2: {{ $cor2 }};
        --cor-3: {{ $cor3 }};
        
        /* FUNDOS E SUPERFÍCIES */
        --cor-bg-header: {{ $corBgHeader }};
        --cor-bg-loja: {{ $loja->color_bg_loja ?? '#f9fafb' }}; /* Gelo/Cinza claro */
        
        /* NOVA VARIÁVEL DE FUNDO: Barra de Contatos */
        --cor-bg-top-bar: {{ $corBgTopBar }};

        /* TEXTOS DINÂMICOS (Baseados no Contraste calculado pelo PHP) */
        --cor-texto-header: {{ $textoNoHeader }};
        --cor-texto-btn-1: {{ $textoNaCor1 }};
        
        /* Novo texto dinâmico para a Barra de Contatos */
        --cor-texto-top-bar: {{ $textoNoTopBar }};
    }

    /* CLASSES UTILITÁRIAS PARA USO RÁPIDO NAS BLADES */
    
    /* Cor Principal (Botões, Destaques) - Força contraste no texto */
    .bg-main-1 { background-color: var(--cor-1) !important; color: var(--cor-texto-btn-1) !important; }
    .text-main-1 { color: var(--cor-1) !important; }
    .border-main-1 { border-color: var(--cor-1) !important; }
    
    /* Centro do Header (Logo, Busca) - Força contraste no texto/ícones */
    .bg-header-custom { background-color: var(--cor-bg-header) !important; color: var(--cor-texto-header) !important; }
    
    /* NOVA CLASSE: Barra de Contatos (Top Bar) - Força contraste no texto */
    .bg-top-bar-custom { background-color: var(--cor-bg-top-bar) !important; color: var(--cor-texto-top-bar) !important; }

    /* Outros utilitários necessários para a Home */
    .text-accent-1 { color: var(--cor-3) !important; }
    .bg-accent-1 { background-color: var(--cor-3) !important; color: #1f2937 !important; }
    .bg-secondary-1 { background-color: var(--cor-2) !important; }
    
    /* Reset de inputs para não ficarem azuis padrão do Tailwind no focus */
    input:focus, select:focus {
        border-color: var(--cor-1) !important;
        ring-color: var(--cor-1) !important;
    }
</style>
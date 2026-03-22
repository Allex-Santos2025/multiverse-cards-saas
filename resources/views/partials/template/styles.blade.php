@php
    if (!function_exists('getContrastColor')) {
        function getContrastColor($hex) {
            $hex = str_replace('#', '', $hex);
            if(strlen($hex) != 6) return '#1f2937'; 
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
            return ($yiq >= 128) ? '#1f2937' : '#ffffff';
        }
    }

    $v = $loja->visual ?? (object)[];

    // 1. CORES BASE
    $cor1        = $v->color_primary   ?? '#2563EB'; 
    $corBgTopBar = $v->color_topbar_bg ?? '#2563EB'; 
    $corBgHeader = $v->color_header_bg ?? '#ffffff';
    $corBgFooter = $v->color_footer_bg ?? '#0f172a';
    $corBgLoja   = $v->global_bg_color ?? '#f9fafb';

    // 2. COR DE DESTAQUE (CTA) - Usado no var(--cor-3)
    $corCTA      = $v->color_cta ?? '#F59E0B';

    // 3. CORES DO MENU (TOTALMENTE INDEPENDENTES DA COR 1)
    $corMenuTxt  = $v->color_menu_text  ?? getContrastColor($corBgHeader);
    $corMenuHvr  = $v->color_menu_hover ?? '#2563EB'; // Cor escolhida no painel só para o hover

    // 4. CONTRASTES
    $textoNaCor1   = getContrastColor($cor1);
    $textoNoTopBar = getContrastColor($corBgTopBar);
    $textoNoFooter = getContrastColor($corBgFooter);
    $textoNoHeader = getContrastColor($corBgHeader);
    $textoNoCTA    = getContrastColor($corCTA);
    
    // A INTELIGÊNCIA DO HOVER DO MENU (Se o hover for escuro, letra branca)
    $textoNoHoverMenu = getContrastColor($corMenuHvr); 
@endphp

<style>
    :root {
        /* VARIÁVEIS PRINCIPAIS */
        --cor-1: {{ $cor1 }};
        --cor-2: {{ $corBgFooter }};
        --cor-3: {{ $corCTA }};
        
        --cor-cta: {{ $corCTA }};
        --cor-cta-txt: {{ $textoNoCTA }};

        /* FUNDOS */
        --cor-bg-header: {{ $corBgHeader }};
        --cor-bg-loja: {{ $corBgLoja }};
        --cor-bg-top-bar: {{ $corBgTopBar }};

        /* TEXTOS DINÂMICOS */
        --cor-texto-header: {{ $textoNoHeader }};
        --cor-texto-btn-1: {{ $textoNaCor1 }};
        --cor-texto-top-bar: {{ $textoNoTopBar }};
        --cor-texto-footer: {{ $textoNoFooter }};
        
        /* MENU INDEPENDENTE E INTELIGENTE */
        --menu-txt: {{ $corMenuTxt }};
        --menu-hvr: {{ $corMenuHvr }};
        --menu-hvr-txt: {{ $textoNoHoverMenu }};
    }

    /* --- CLASSES UTILITÁRIAS --- */
    .bg-main-1 { background-color: var(--cor-1) !important; color: var(--cor-texto-btn-1) !important; }
    .text-main-1 { color: var(--cor-1) !important; }
    .border-main-1 { border-color: var(--cor-1) !important; }
    .hover-text-main-1:hover { color: var(--cor-1) !important; }

    .group:hover .group-hover-bg-main-1 { 
        background-color: var(--cor-1) !important; 
        color: var(--cor-texto-btn-1) !important; 
    }
    
    /* Cabeçalhos e Topbar */
    .bg-header-custom { background-color: var(--cor-bg-header) !important; color: var(--cor-texto-header) !important; }
    .bg-top-bar-custom { background-color: var(--cor-bg-top-bar) !important; color: var(--cor-texto-top-bar) !important; }
    
    /* Newsletter / Rodapé */
    .bg-secondary-1 { background-color: var(--cor-2) !important; color: var(--cor-texto-footer) !important; }
    
    /* BOTÕES CTA E DESTAQUES */
    .bg-accent-1 { background-color: var(--cor-cta) !important; color: var(--cor-cta-txt) !important; }
    .text-accent-1 { color: var(--cor-cta) !important; }

    /* MENU PRINCIPAL E SUBMENU */
    .menu-link-custom {
        color: var(--menu-txt) !important;
        background-color: transparent !important;
        transition: all 0.2s ease-in-out;
    }
    .menu-link-custom:hover {
        background-color: var(--menu-hvr) !important;
        color: var(--menu-hvr-txt) !important;
    }

    .submenu-link-custom {
        color: var(--menu-txt) !important;
        transition: all 0.2s ease-in-out;
    }
    .submenu-link-custom:hover {
        background-color: var(--menu-hvr) !important;
        color: var(--menu-hvr-txt) !important;
    }
    /* BOTÃO DE ATUALIZAÇÕES DO SUBMENU */
    .btn-updates-custom {
        background-color: var(--menu-hvr) !important;
        color: var(--menu-hvr-txt) !important;
        transition: all 0.2s ease-in-out;
    }
    .btn-updates-custom:hover {
        /* O filtro de brightness(0.85) aplica essa "película" escura elegante */
        /* Coloquei um leve opacity junto para funcionar bem tanto em cores claras quanto escuras */
        filter: brightness(0.85);
        opacity: 0.95;
    }
</style>
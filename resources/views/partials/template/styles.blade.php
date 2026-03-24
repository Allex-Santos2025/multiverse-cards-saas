@php
    // FUNÇÃO ORIGINAL: Retorna APENAS Preto ou Branco dependendo do fundo
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

    // NOVA FUNÇÃO: Retorna a cor escolhida pelo lojista, MAS vira Preto/Branco se ficar ilegível
    if (!function_exists('getSafeTextColor')) {
        function getSafeTextColor($textHex, $bgHex) {
            $textHex = str_replace('#', '', $textHex);
            $bgHex = str_replace('#', '', $bgHex);
            
            // Corrige se a cor vier com 3 caracteres (ex: #FFF)
            if(strlen($textHex) == 3) $textHex = $textHex[0].$textHex[0].$textHex[1].$textHex[1].$textHex[2].$textHex[2];
            if(strlen($bgHex) == 3)   $bgHex = $bgHex[0].$bgHex[0].$bgHex[1].$bgHex[1].$bgHex[2].$bgHex[2];
            
            // Se vier zoado, assume preto pro texto e branco pro fundo
            if(strlen($textHex) != 6) $textHex = '1f2937';
            if(strlen($bgHex) != 6)   $bgHex = 'ffffff';

            $tyiq = ((hexdec(substr($textHex, 0, 2)) * 299) + (hexdec(substr($textHex, 2, 2)) * 587) + (hexdec(substr($textHex, 4, 2)) * 114)) / 1000;
            $byiq = ((hexdec(substr($bgHex, 0, 2)) * 299) + (hexdec(substr($bgHex, 2, 2)) * 587) + (hexdec(substr($bgHex, 4, 2)) * 114)) / 1000;

            // Diferença de luminosidade (se for menor que 90, não dá leitura)
            if (abs($tyiq - $byiq) < 90) {
                return ($byiq >= 128) ? '#1f2937' : '#ffffff'; // Aciona o salva-vidas
            }
            // Contrasta bem? Deixa a cor original do lojista!
            return '#' . $textHex;
        }
    }

    $v = $loja->visual ?? (object)[];

    // 1. CORES BASE DA LOJA
    $cor1        = $v->color_primary   ?? '#2563EB'; 
    $corBgTopBar = $v->color_topbar_bg ?? '#2563EB'; 
    $corBgHeader = $v->color_header_bg ?? '#ffffff';
    $corBgFooter = $v->color_footer_bg ?? '#0f172a';
    $corBgLoja   = $v->global_bg_color ?? '#f9fafb';
    $corCTA      = $v->color_cta       ?? '#F59E0B';

    // 2. NOVAS CORES (Cor 2 e Cor 3 para elementos da página)
    $corSecundaria = $v->color_secondary ?? '#475569'; // Chumbo
    $corTerciaria  = $v->color_tertiary  ?? '#94a3b8'; // Cinza Suave

    // 3. CORES DO MENU
    $corMenuTxtOriginal = $v->color_menu_text ?? '#1f2937'; // Cinza Chumbo como padrão
    $corMenuTxt = getSafeTextColor($corMenuTxtOriginal, '#ffffff');
    $corMenuHvr  = $v->color_menu_hover ?? '#2563EB';

    // 4. CONTRASTES PADRÃO (Preto/Branco automáticos)
    $textoNaCor1          = getContrastColor($cor1);
    $textoNoTopBar        = getContrastColor($corBgTopBar);
    $textoNoFooter        = getContrastColor($corBgFooter);
    $textoNoHeader        = getContrastColor($corBgHeader);
    $textoNoCTA           = getContrastColor($corCTA);
    $textoNaCorSecundaria = getContrastColor($corSecundaria); 
    $textoNoHoverMenu     = getContrastColor($corMenuHvr); 
    
    // 5. A INTELIGÊNCIA DO TEXTO PRINCIPAL:
    // Pega a Cor 2 (Secundária) que o cara escolheu, cruza com o Fundo do Site.
    $textoPrincipalLoja   = getSafeTextColor($corSecundaria, $corBgLoja);
@endphp

<style>
    :root {
        /* CORES LEGADO (MANTIDAS PARA NÃO QUEBRAR O RESTO DO SITE) */
        --cor-1: {{ $cor1 }};
        --cor-2: {{ $corBgFooter }};
        --cor-3: {{ $corCTA }};
        
        /* BOTÕES DE DESTAQUE */
        --cor-cta: {{ $corCTA }};
        --cor-cta-txt: {{ $textoNoCTA }};

        /* CORES DO DESIGN SYSTEM (Para os elementos novos, como a Timeline) */
        --cor-secundaria: {{ $corSecundaria }};
        --cor-terciaria: {{ $corTerciaria }};
        
        /* FUNDOS GERAIS */
        --cor-bg-header: {{ $corBgHeader }};
        --cor-bg-loja: {{ $corBgLoja }};
        --cor-bg-top-bar: {{ $corBgTopBar }};

        /* TEXTOS DE ESTRUTURA (Preto ou Branco dinâmico) */
        --cor-texto-header: {{ $textoNoHeader }};
        --cor-texto-btn-1: {{ $textoNaCor1 }};
        --cor-texto-top-bar: {{ $textoNoTopBar }};
        --cor-texto-footer: {{ $textoNoFooter }};
        --cor-texto-secundaria: {{ $textoNaCorSecundaria }};
        
        /* O TEXTO GERAL DA LOJA (Com o salva-vidas ativado) */
        --cor-texto-principal: {{ $textoPrincipalLoja }};
        
        /* MENU */
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
    
    /* Rodapé (Herdando o legado da --cor-2 para não quebrar site antigo) */
    .bg-secondary-1 { background-color: var(--cor-2) !important; color: var(--cor-texto-footer) !important; }
    
    /* BOTÕES CTA */
    .bg-accent-1 { background-color: var(--cor-cta) !important; color: var(--cor-cta-txt) !important; }
    .text-accent-1 { color: var(--cor-cta) !important; }

    /* MENU PRINCIPAL */
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
    
    .btn-updates-custom {
        background-color: var(--menu-hvr) !important;
        color: var(--menu-hvr-txt) !important;
        transition: all 0.2s ease-in-out;
    }
    .btn-updates-custom:hover {
        filter: brightness(0.85);
        opacity: 0.95;
    }
</style>
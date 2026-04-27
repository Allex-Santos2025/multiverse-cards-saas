{{-- resources/views/livewire/partials/menus/magic.blade.php --}}

<style>
    .magic-nav-link {
        font-family: 'Inter', sans-serif;
        color: #94a3b8;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        display: inline-block;
    }

    .magic-nav-link:hover, .magic-nav-link.active {
        color: #f59e0b;
        font-weight: 900;
        transform: scale(1.1);
        text-shadow: 0 0 15px rgba(245, 158, 11, 0.5);
    }

    .magic-nav-link::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 50%;
        width: 0;
        height: 2px;
        background: #f59e0b;
        transition: all 0.3s ease;
        transform: translateX(-50%);
        box-shadow: 0 0 10px #f59e0b;
    }

    .magic-nav-link:hover::after, .magic-nav-link.active::after {
        width: 100%;
    }

    .viewport-desktop { display: flex !important; }
    .viewport-mobile { display: none !important; }

    @media (max-width: 1024px) {
        .viewport-desktop { display: none !important; }
        .viewport-mobile { display: flex !important; }
    }

    .hamburguer-box { width: 24px; height: 18px; display: flex; flex-direction: column; justify-content: space-between; cursor: pointer; }
    .hamburguer-line { height: 2px; width: 100%; background: #f59e0b; transition: 0.3s; border-radius: 2px; }
    
    .menu-is-open .line-1 { transform: translateY(8px) rotate(45deg); }
    .menu-is-open .line-2 { opacity: 0; }
    .menu-is-open .line-3 { transform: translateY(-8px) rotate(-45deg); }

    .dropdown-deslizante {
        position: absolute; top: 56px; left: 0; width: 100%; 
        background: #0f172a; border-bottom: 2px solid #f59e0b; 
        padding: 20px; z-index: 99; box-shadow: 0 10px 15px rgba(0,0,0,0.4);
    }
</style>

{{-- MENU DESKTOP --}}
<nav class="viewport-desktop" style="background: #0f172a; border-bottom: 2px solid #f59e0b; height: 56px; position: sticky; top: 72px; z-index: 40; align-items: center;">
    <div style="width: 100%; max-width: 1400px; margin: 0 auto; padding: 0 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        
        <div style="display: flex; align-items: center; gap: 2.5rem;">
            <div style="border-right: 1px solid rgba(255,255,255,0.1); padding-right: 1.5rem;">
                <span style="color: #f59e0b; font-weight: 900; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.2em;">Universo Magic</span>
            </div>
            <div style="display: flex; gap: 2rem; align-items: center;">
                <a href="#" class="magic-nav-link active">Home</a>
                <a href="#" class="magic-nav-link">Marketplace</a>
                <a href="#" class="magic-nav-link">Torneios & Meta</a>
                <a href="#" class="magic-nav-link">Artigos</a>
                <a href="#" class="magic-nav-link">Spoilers</a>
            </div>
        </div>

        <div style="font-size: 0.6rem; color: #4ade80; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; display: flex; align-items: center; gap: 0.5rem;">
            <span style="width: 6px; height: 6px; background: #4ade80; border-radius: 50%;"></span>
            Marketplace Ativo
        </div>
    </div>
</nav>

{{-- MENU MOBILE --}}
<nav class="viewport-mobile" x-data="{ open: false }" style="background: #0f172a; border-bottom: 2px solid #f59e0b; height: 56px; position: sticky; top: 72px; z-index: 40; align-items: center; width: 100%;">
    <div style="width: 100%; padding: 0 1rem; display: flex; align-items: center; justify-content: space-between;">
        
        <span style="color: #f59e0b; font-weight: 900; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 0.1em;">Universo Magic</span>

        <div @click="open = !open" class="hamburguer-box" :class="open ? 'menu-is-open' : ''">
            <span class="hamburguer-line line-1"></span>
            <span class="hamburguer-line line-2"></span>
            <span class="hamburguer-line line-3"></span>
        </div>
    </div>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         class="dropdown-deslizante"
         x-cloak>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <a href="#" class="magic-nav-link active" style="font-size: 1.1rem;">Home</a>
            <a href="#" class="magic-nav-link" style="font-size: 1.1rem;">Marketplace</a>
            <a href="#" class="magic-nav-link" style="font-size: 1.1rem;">Torneios & Meta</a>
            <a href="#" class="magic-nav-link" style="font-size: 1.1rem;">Artigos</a>
            <a href="#" class="magic-nav-link" style="font-size: 1.1rem;">Spoilers</a>
        </div>
    </div>
</nav>
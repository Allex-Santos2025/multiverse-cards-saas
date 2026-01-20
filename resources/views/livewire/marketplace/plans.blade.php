<div x-data="{ billing: 'monthly' }">

    {{-- CSS Específico --}}
    <style>
        /* 1. CORREÇÃO DO CABEÇALHO */
        .main-content {
            padding-top: 100px; 
            padding-bottom: 3rem;
        }
        .container {
            padding-top: 50px;
        }

        /* Mobile First */
        .plans-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            width: 100%;
        }

        /* --- 1. LAYOUT E ESPAÇAMENTO --- */
        .main-content {
            padding-top: 180px;
            padding-bottom: 5rem;
        }

        /* --- 2. GRID --- */
        .plans-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            width: 100%;
        }

        @media (min-width: 1024px) {
            .plans-grid {
                grid-template-columns: repeat(3, 1fr) !important;
                align-items: center !important;
            }

            /* Pro começa levemente maior (Zoom Inicial) */
            .card-pro-transform {
                transform: translateY(-1.5rem) scale(1.05);
                z-index: 10;
            }
            /* Pro cresce mais ainda no hover */
            .card-pro-transform:hover {
                transform: translateY(-1.5rem) scale(1.10);
                z-index: 20;
            }
        }

        @media (max-width: 1023px) {
            .card-pro-transform { margin: 3rem 0; transform: scale(1.02); }
            .card-pro-transform:hover { transform: scale(1.05); }
        }

        /* --- 3. ESTILOS DOS CARDS E INTERAÇÃO (CSS PURO E SEGURO) --- */

        /* Base para todos os elementos */
        .plan-card {
            background-color: #0f0f0f;
            border: 1px solid #374151; /* Cinza escuro (gray-700) */
            transition: all 0.3s ease-in-out;
        }

        .plan-btn {
            background-color: transparent;
            border: 1px solid #4b5563; /* Cinza médio (gray-600) */
            color: #fff;
            height: 60px; /* Botão bem alto */
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 0.5rem;
            transition: all 0.3s ease-in-out;
        }

        .plan-title { color: #d1d5db; transition: color 0.3s; } /* gray-300 */
        .plan-detail { color: #6b7280; transition: color 0.3s; } /* gray-500 */
        .check-icon { color: #4b5563; transition: color 0.3s; } /* gray-600 (Check escuro inicial) */

        /* --- HOVER: BÁSICO (VERDE #10b981) --- */
        .hover-basic:hover {
            border-color: #10b981;
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.15);
            z-index: 20;
        }
        .hover-basic:hover .plan-btn {
            background-color: #10b981;
            border-color: #10b981;
            color: #000;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        }
        .hover-basic:hover .plan-title, 
        .hover-basic:hover .plan-detail,
        .hover-basic:hover .check-icon { color: #10b981; }

        /* --- HOVER: PRO (LARANJA #f97316) --- */
        .hover-pro:hover {
            border-color: #f97316;
            /* Scale já tratado no media query para respeitar a elevação */
            box-shadow: 0 0 40px rgba(249, 115, 22, 0.2);
        }
        .hover-pro:hover .plan-btn {
            background-color: #f97316;
            border-color: #f97316;
            color: #000;
            box-shadow: 0 4px 20px rgba(249, 115, 22, 0.4);
        }
        .hover-pro:hover .plan-title, 
        .hover-pro:hover .plan-detail,
        .hover-pro:hover .check-icon { color: #f97316; }

        /* --- HOVER: PREMIUM (ROXO #a855f7) --- */
        .hover-premium:hover {
            border-color: #a855f7;
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(168, 85, 247, 0.15);
            z-index: 20;
        }
        .hover-premium:hover .plan-btn {
            background-color: #a855f7;
            border-color: #a855f7;
            color: #fff;
            box-shadow: 0 4px 20px rgba(168, 85, 247, 0.4);
        }
        .hover-premium:hover .plan-title, 
        .hover-premium:hover .plan-detail,
        .hover-premium:hover .check-icon { color: #a855f7; }
        
        /* Desktop */
        @media (min-width: 768px) {
            .plans-grid {
                grid-template-columns: repeat(3, 1fr) !important;
                align-items: center !important; /* Centraliza verticalmente */
            }

            /* O card Pro começa elevado (destaque de tamanho), mas neutro de cor */
            .card-pro-transform {
                transform: scale(1.05); /* Levemente maior por padrão */
                z-index: 10;
            }

            /* No hover, ele cresce um pouco mais */
            .card-pro-transform:hover {
                transform: scale(1.10);
                z-index: 20;
            }
        }
        /* --- TOGGLE SWITCH (Estilo Fixo e Seguro) --- */
        .toggle-container {
            width: 64px;
            height: 32px;
            border-radius: 9999px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            border: 1px solid #4b5563; /* Borda padrão */
        }

        .toggle-knob {
            width: 24px;
            height: 24px;
            background-color: white;
            border: 3px solid #e5e7eb; /* A borda cinza claro que dá o efeito 3D */
            border-radius: 50%;
            position: absolute;
            top: 3px;
            left: 3px;
            transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); /* Deslize suave */
            box-shadow: 0 2px 4px rgba(0,0,0,0.3); /* Sombra para profundidade */
        }

        
    </style>

    <main class="main-content bg-black min-h-screen text-white">

        <!-- Hero Section -->
        <div class="container mx-auto px-6 text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-black mb-4 text-white">
                Sua loja no <span class="bg-clip-text- text-orange-500">VERSUS</span>
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto mb-10">
                Escolha o plano ideal para profissionalizar seu negócio de Card Games.<br>
                Gestão de estoque automatizada, layouts personalizáveis e proteção contra fraudes.
            </p>

                        <!-- Toggle Mensal/Anual (Final e Corrigido) -->
            <div class="flex items-center justify-center gap-8 mb-12 select-none"> 

                <!-- Texto Mensal -->
                <div class="cursor-pointer transition-colors duration-200"
                     :class="billing === 'monthly' ? 'text-white font-bold' : 'text-gray-500 font-medium'"
                     @click="billing = 'monthly'">
                    Mensal
                </div>

                <!-- O SWITCH -->
                <!-- A classe toggle-container garante o tamanho fixo -->
                <div class="toggle-container"
                     :class="billing === 'annual' ? 'bg-orange-600 border-orange-500' : 'bg-gray-800 border-gray-600'"
                     @click="billing = (billing === 'monthly' ? 'annual' : 'monthly')">

                    <!-- A Bolinha (Knob) -->
                    <!-- A classe toggle-knob garante o visual 3D e o tamanho -->
                    <div class="toggle-knob"
                         :style="billing === 'annual' ? 'transform: translateX(32px);' : 'transform: translateX(0);'">
                    </div>
                </div>

                <!-- Texto Anual -->
                <div class="cursor-pointer flex items-center gap-2"
                     @click="billing = 'annual'">
                    <span class="transition-colors duration-200"
                          :class="billing === 'annual' ? 'text-white font-bold' : 'text-gray-500 font-medium'">
                        Anual
                    </span>
                    <span class="text-[10px] font-black uppercase tracking-wider text-orange-500 bg-orange-500/10 px-2 py-0.5 rounded border border-orange-500/20">
                        (1 Mês Grátis)
                    </span>
                </div>
            </div>

        </div>

        <!-- GRID DE PLANOS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 plans-grid">

            <!-- 1. PLANO BÁSICO -->
            <div class="plan-card hover-basic rounded-2xl p-8 flex flex-col h-full relative border border-gray-800 bg-[#111]">
                <h3 class="text-2xl font-bold plan-title mb-2 text-white">Básico</h3>
                <p class="text-gray-500 text-sm mb-8 h-10">Para quem está começando a vender online.</p>
                <div class="mb-8">
                    <div class="flex items-baseline gap-1">
                        <span class="text-sm text-gray-400 font-bold">R$</span>
                        <span class="text-5xl font-black text-white tracking-tighter" x-text="billing === 'monthly' ? '79,90' : '73,24'">79,90</span>
                        <span class="text-gray-500 text-sm" x-text="billing === 'monthly' ? '/mês' : '/ano'">/mês</span>
                    </div>
                    <p class="text-[10px] plan-detail mt-2 font-bold uppercase tracking-wide text-gray-500">Cobrado mensalmente</p>
                </div>
                {{-- LINK ATUALIZADO PARA O WIZARD LIVEWIRE --}}
                <a href="{{ route('register.store', ['plan' => 'basico']) }}" class="plan-btn w-full mb-8 bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-8 rounded-lg transition-all transform hover:translate-y-[-2px] shadow-lg shadow-gray-900/20">
                    Começar Agora
                </a>
                <ul class="space-y-4 text-sm text-gray-400 font-medium flex-1">
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-orange-500">✓</span> 1 Usuário Administrativo</li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-orange-500">✓</span> Relatórios de vendas completos</li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-orange-500">✓</span> Produtos Ilimitados</li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-orange-500">✓</span> Personalização: <strong class="text-white">Básica (Logo, Cores Primárias)</strong></li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-orange-500">✓</span> Suporte: Padrão (e-mail)</li>
                </ul>
            </div>

            <!-- 2. PLANO PRO -->
            <div class="plan-card hover-pro card-pro-transform rounded-2xl p-8 flex flex-col h-full relative border border-orange-500 bg-[#111] shadow-lg shadow-orange-900/20">
                <!-- Badge Fixa (Sempre Laranja) -->
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-orange-600 text-white text-xs font-black px-6 py-1.5 rounded-full uppercase tracking-widest whitespace-nowrap shadow-lg z-30">
                    Mais Popular
                </div>
                <h3 class="text-2xl font-bold plan-title mb-2 text-white">Pro</h3>
                <p class="text-gray-500 text-sm mb-8 h-10">Para lojas em crescimento com volume médio.</p>
                <div class="mb-8">
                    <div class="flex items-baseline gap-1">
                        <span class="text-sm text-gray-400 font-bold">R$</span>
                        <span class="text-6xl font-black text-white tracking-tighter" x-text="billing === 'monthly' ? '99,90' : '91,57'">99,90</span>
                        <span class="text-gray-500 text-sm" x-text="billing === 'monthly' ? '/mês' : '/ano'">/mês</span>
                    </div>
                    <p class="text-[10px] plan-detail mt-2 font-bold uppercase tracking-wide text-gray-500">Cobrado mensalmente</p>
                </div>
                {{-- LINK ATUALIZADO PARA O WIZARD LIVEWIRE --}}
                <a href="{{ route('register.store', ['plan' => 'pro']) }}" class="plan-btn w-full mb-8 bg-[#ff5500] hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition-all transform hover:translate-y-[-2px] shadow-lg shadow-orange-900/20">
                    Escolher Plano Pro
                </a>
                <ul class="space-y-4 text-sm text-gray-400 font-medium flex-1">
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-orange-500">✓</span> 3 Usuários Administrativos</li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-orange-500">✓</span> Relatórios de vendas completos</li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-orange-500">✓</span> Produtos Ilimitados</li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-orange-500">✓</span> Personalização: <strong class="text-white">Avançada (Templates, CSS Básico)</strong></li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-orange-500">✓</span> Marketing: Ferramentas básicas (cupons, banners)</li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-orange-500">✓</span> Suporte: Prioritário (e-mail, chat)</li>
                </ul>
            </div>

            <!-- 3. PLANO PREMIUM -->
            <div class="plan-card hover-premium rounded-2xl p-8 flex flex-col h-full relative border border-purple-500 bg-[#111] shadow-lg shadow-purple-900/20">
                <h3 class="text-2xl font-bold plan-title mb-2 text-white">Premium</h3>
                <p class="text-gray-500 text-sm mb-8 h-10">Estrutura completa para grandes lojistas.</p>
                <div class="mb-8">
                    <div class="flex items-baseline gap-1">
                        <span class="text-sm text-gray-400 font-bold">R$</span>
                        <span class="text-5xl font-black text-white tracking-tighter" x-text="billing === 'monthly' ? '119,90' : '109,90'">119,90</span>
                        <span class="text-gray-500 text-sm" x-text="billing === 'monthly' ? '/mês' : '/ano'">/mês</span>
                    </div>
                    <p class="text-[10px] plan-detail mt-2 font-bold uppercase tracking-wide text-gray-500">Cobrado mensalmente</p>
                </div>
                {{-- LINK ATUALIZADO PARA O WIZARD LIVEWIRE --}}
                <a href="{{ route('register.store', ['plan' => 'premium']) }}" class="plan-btn w-full mb-8 bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-lg transition-all transform hover:translate-y-[-2px] shadow-lg shadow-purple-900/20">
                    Escolher Premium
                </a>
                <ul class="space-y-4 text-sm text-gray-400 font-medium flex-1">
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-purple-500">✓</span> Usuários Administrativos Ilimitados</li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-purple-500">✓</span> Relatórios personalizados</li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-purple-500">✓</span> Produtos Ilimitados</li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-purple-500">✓</span> Personalização: <strong class="text-white">Completa (todos templates, CSS/HTML, domínio próprio)</strong></li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-purple-500">✓</span> Marketing: E-mail marketing, redes sociais, SEO avançado</li>
                    <li class="flex items-start gap-3"><span class="check-icon font-bold text-purple-500">✓</span> Suporte: VIP 24/7 (telefone, chat, e-mail)</li>
                </ul>
            </div>

        </div>
    

        <!-- CTA Final -->
        <div class="container mx-auto px-6 mt-20 text-center">
            <div class="bg-gradient-to-r from-gray-900 to-black border border-gray-800 rounded-2xl p-10 md:p-16 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-orange-600 rounded-full mix-blend-overlay filter blur-3xl opacity-20 -mr-16 -mt-16"></div>
                <h2 class="text-3xl font-bold mb-6 text-white">Pronto para expandir seu universo?</h2>
                <p class="text-gray-400 mb-8 max-w-2xl mx-auto">
                    Junte-se a centenas de lojistas que já estão vendendo Magic, Pokémon e Yu-Gi-Oh! para todo o Brasil com segurança.
                </p>
                <a href="#" class="inline-block px-10 py-4 rounded-full text-lg font-bold text-white shadow-lg bg-orange-600 hover:bg-orange-500 transition-colors">
                    Quero ser um lojista Parceiro
                </a>
                <p class="mt-4 text-xs text-gray-500">Sem necessidade de cartão de crédito para cadastro inicial.</p>
            </div>
        </div>
    </main>
</div>

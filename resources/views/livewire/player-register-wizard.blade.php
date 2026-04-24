<div> {{-- ROOT DO LIVEWIRE (OBRIGATÓRIO PARA NÃO DAR ERRO CHILDNODES) --}}

    @if($isMarketplace)
        {{-- ========================================== --}}
        {{-- VERSÃO 1: SISTEMA VERSUS (MARKETPLACE)     --}}
        {{-- ========================================== --}}
        @push('styles')
            <style>
                :root {
                    --vs-orange: #FF8C00;
                    --vs-dark-bg: #1A1A1A;
                    --vs-card-bg: #000000;
                    --vs-gray-border: #333333;
                    --vs-light-gray-text: #B0B0B0;
                    --vs-white-text: #FFFFFF;
                    --vs-input-bg: #222222;
                    --vs-social-button-bg: #222222;
                    --vs-social-button-border: #333333;
                    --vs-social-button-hover-bg: #3A3A3A;
                    --vs-social-button-hover-border: #555555;
                }
                body {
                    font-family: 'Inter', sans-serif;
                    background-color: #0a0a0a;
                    color: white;
                }
                .animate-fadeIn {
                    animation: fadeIn 0.5s ease-out forwards;
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .custom-scrollbar::-webkit-scrollbar {
                    width: 8px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: #1a1a1a;
                    border-radius: 4px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: #333;
                    border-radius: 4px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: #ff5500;
                }    
                .social-button {
                    background-color: var(--vs-social-button-bg);
                    border: 1px solid var(--vs-social-button-border);
                    color: var(--vs-white-text);
                    font-weight: 600;
                    font-size: 0.95rem;
                    border-radius: 0.5rem;
                    transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
                }
                .social-button:hover {
                    background-color: var(--vs-social-button-hover-bg);
                    border-color: var(--vs-social-button-hover-border);
                }
                .social-button img {
                    width: 1.25rem;
                    height: 1.25rem;
                    margin-right: 0.75rem;
                }
                .divider-text {
                    color: var(--vs-light-gray-text);
                    font-size: 0.875rem;
                    font-weight: 500;
                }   
            </style>
        @endpush

        <main class="flex-grow flex items-center justify-center pt-28 pb-12 px-4">
            <div class="w-full max-w-3xl">

                {{-- PROGRESS BAR BLINDADA --}}
                <div class="flex justify-between mb-12 relative" wire:key="progress-bar-container">
                    
                    {{-- LINHA DE FUNDO (Trilho) --}}
                    <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-800 z-0 transform -translate-y-1/2 flex" wire:key="progress-track">
                        <div wire:key="track-line-1" class="flex-1 h-full transition-all duration-500 {{ $currentStep >= 2 ? 'bg-[#ff5500]' : 'bg-gray-800' }}"></div>
                        <div wire:key="track-line-2" class="flex-1 h-full transition-all duration-500 {{ $currentStep >= 3 ? 'bg-[#ff5500]' : 'bg-gray-800' }}"></div>
                    </div>

                    {{-- INDICADORES (Bolinhas) --}}
                    @foreach([1 => 'Termos', 2 => 'Pessoal', 3 => 'Conclusão'] as $num => $label)
                        <div wire:key="step-wrapper-{{ $num }}" class="relative z-10 flex flex-col items-center gap-2 transition-all duration-300 {{ $currentStep >= $num ? 'opacity-100' : 'opacity-50' }}">
                            
                            <div wire:key="step-circle-{{ $num }}" class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 border-[#0a0a0a] transition-colors duration-500 {{ $currentStep >= $num ? 'bg-[#ff5500] text-white' : 'bg-gray-800 text-gray-400' }}">
                                {{ $num }}
                            </div>
                            
                            <span wire:key="step-label-{{ $num }}" class="text-xs font-bold uppercase tracking-wider transition-colors duration-500 {{ $currentStep >= $num ? 'text-white' : 'text-gray-500' }}">
                                {{ $label }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div id="form-container" class="bg-[#111] border border-gray-800 rounded-2xl p-8 shadow-2xl relative overflow-hidden transition-all duration-500">
                    
                    {{-- PASSO 1: ACEITE OS TERMOS --}}
                    @if ($currentStep === 1)
                        <div id="step-1" wire:key="content-step-1" class="step-content animate-fadeIn">
                            <h2 class="text-2xl font-bold text-white mb-2">Termos de Uso</h2>
                            <p class="text-gray-400 mb-6 text-sm">Por favor, leia atentamente as regras do marketplace Versus TCG.</p>  

                            <div class="bg-black border border-gray-800 rounded-lg p-6 h-64 overflow-y-auto custom-scrollbar mb-6 text-sm text-gray-300 leading-relaxed">
                                <h3 class="text-xl font-bold text-white mb-4">1. Aceitação dos Termos</h3>
                                <p class="mb-4">Ao se registrar e utilizar os serviços da VERSUS TCG, você concorda em cumprir e estar vinculado aos seguintes termos e condições de uso. Se você não concordar com qualquer parte destes termos, por favor, não utilize nossos serviços.</p>

                                <h3 class="text-xl font-bold text-white mb-4">2. Elegibilidade</h3>
                                <p class="mb-4">Você deve ter pelo menos 18 anos de idade para se registrar como lojista. Ao se registrar, você declara e garante que tem a idade legal para formar um contrato vinculativo.</p>

                                <h3 class="text-xl font-bold text-white mb-4">3. Registro de Conta</h3>
                                <p class="mb-4">Para acessar certas funcionalidades do site, você precisará criar uma conta. Você concorda em fornecer informações precisas, completas e atualizadas durante o processo de registro e em manter a segurança de sua senha.</p>

                                <h3 class="text-xl font-bold text-white mb-4">4. Planos de Assinatura</h3>
                                <p class="mb-4">Os lojistas podem escolher entre diferentes planos de assinatura (Básico, Pro, Premium), cada um com suas próprias funcionalidades e custos. A ativação da loja no marketplace está condicionada à contratação e pagamento de um plano.</p>

                                <h3 class="text-xl font-bold text-white mb-4">5. Conteúdo do Usuário</h3>
                                <p class="mb-4">Você é responsável por todo o conteúdo que publica em sua loja, incluindo descrições de produtos, imagens e preços. A VERSUS TCG reserva-se o direito de remover qualquer conteúdo que viole estes termos ou seja considerado inadequado.</p>

                                <h3 class="text-xl font-bold text-white mb-4">6. Limitação de Responsabilidade</h3>
                                <p class="mb-4">A VERSUS TCG não será responsável por quaisquer danos diretos, indiretos, incidentais, especiais, consequenciais ou exemplares resultantes do uso ou da incapacidade de usar o serviço.</p>

                                <h3 class="text-xl font-bold text-white mb-4">7. Modificações dos Termos</h3>
                                <p class="mb-4">A VERSUS TCG reserva-se o direito de modificar estes termos a qualquer momento. As alterações entrarão em vigor imediatamente após a publicação no site. Seu uso continuado do serviço após tais modificações constitui sua aceitação dos novos termos.</p>

                                <h3 class="text-xl font-bold text-white mb-4">8. Rescisão</h3>
                                <p class="mb-4">Podemos rescindir ou suspender seu acesso ao nosso serviço imediatamente, sem aviso prévio ou responsabilidade, por qualquer motivo, incluindo, sem limitação, se você violar os Termos.</p>
                            </div>              
                        </div>    
                            
                        <div class="flex items-center mb-8">
                            <input id="terms-check" type="checkbox" wire:model.live="acceptTerms" class="w-5 h-5 rounded border-gray-700 bg-gray-900 text-[#ff5500] focus:ring-[#ff5500]">
                            <label for="terms-check" class="ml-3 text-sm text-gray-300">Li e concordo com os Termos de Uso e Política de Privacidade.</label>
                        </div>       
                            
                        @error('acceptTerms') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                        <div class="mt-8 flex justify-end">
                            <button
                                wire:click="nextStep"
                                wire:loading.attr="disabled"
                                @if (!$acceptTerms) disabled @endif
                                class="btn-submit disabled:opacity-50 disabled:cursor-not-allowed bg-[#ff5500] hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition-all transform hover:translate-y-[-2px] shadow-lg shadow-orange-900/20"
                                >
                                    Aceitar e Continuar
                            </button>
                        </div>               
                    @endif

                    {{-- PASSO 2: SEUS DADOS PESSOAIS --}}
                    @if ($currentStep === 2)
                        <div id="step-2" wire:key="content-step-2" class="step-content animate-fadeIn">
                            <h2 class="text-2xl font-bold text-white mb-2">Criar conta de Jogador</h2>
                            <p class="text-gray-400 mb-8 text-sm">Entre no jogo em menos de 1 minuto.</p>

                            <div class="grid grid-cols-2 gap-4 mb-8">
                                <button type="button" class="group flex items-center justify-center gap-3 py-3 px-4 bg-black border border-gray-800 rounded-lg text-white text-sm font-medium transition-all duration-200 hover:bg-white/10 hover:border-gray-600">
                                    <img src="{{ asset('assets/google_2702602.png') }}" alt="Google" class="w-5 h-5 object-contain transform transition-transform duration-200 group-hover:scale-110">
                                    Entrar com Google
                                </button>

                                <button type="button" class="group flex items-center justify-center gap-3 py-3 px-4 bg-black border border-gray-800 rounded-lg text-white text-sm font-medium transition-all duration-200 hover:bg-white/10 hover:border-gray-600">
                                    <img src="{{ asset('assets/microsoft_11379067.png') }}" alt="Microsoft" class="w-5 h-5 object-contain transform transition-transform duration-200 group-hover:scale-110">
                                    Entrar com Microsoft
                                </button>

                                <button type="button" class="group flex items-center justify-center gap-3 py-3 px-4 bg-black border border-gray-800 rounded-lg text-white text-sm font-medium transition-all duration-200 hover:bg-white/10 hover:border-gray-600">
                                    <div class="w-5 h-5 bg-white transition-transform duration-200 group-hover:scale-110"
                                        style="-webkit-mask-image: url('{{ asset('assets/apple-big-logo_80676.svg') }}'); 
                                        mask-image: url('{{ asset('assets/apple-big-logo_80676.svg') }}');
                                        -webkit-mask-repeat: no-repeat;
                                        mask-repeat: no-repeat;
                                        -webkit-mask-size: contain;
                                        mask-size: contain;">
                                    </div>
                                        Entrar com Apple
                                </button>

                                <button type="button" class="group flex items-center justify-center gap-3 py-3 px-4 bg-black border border-gray-800 rounded-lg text-white text-sm font-medium transition-all duration-200 hover:bg-white/10 hover:border-gray-600">
                                    <img src="{{ asset('assets/discord_5968756.png') }}" alt="Discord" class="w-5 h-5 object-contain transform transition-transform duration-200 group-hover:scale-110">
                                        Entrar com Discord
                                </button>
                            </div>
                            <div class="flex items-center my-10 px-1">
                                <div class="flex-1 border-t border-gray-800"></div>
                                <span class="px-4 text-gray-500 text-xs font-black uppercase tracking-widest">Ou</span>
                                <div class="flex-1 border-t border-gray-800"></div>
                            </div>
                        
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-500 uppercase">Nome</label>
                                    <input type="text" wire:model="name" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="Seu nome">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-500 uppercase">Sobrenome</label>
                                    <input type="text" wire:model="surname" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="Seu sobrenome">
                                </div>            
                                <div class="col-span-1 md:col-span-2 space-y-2">
                                    <label class="text-xs font-bold text-gray-500 uppercase">Login (Usuário)</label>
                                    <input type="text" wire:model="nickname" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="ex: MasterOfMagic99">
                                </div>               
                                <div class="col-span-1 md:col-span-2 space-y-2">
                                    <label class="text-xs font-bold text-gray-500 uppercase">E-mail Corporativo</label>
                                    <input type="email" wire:model="email" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="seu@email.com">
                                </div>
                                
                                <div class="space-y-2" x-data="{ showPass: false }">
                                    <label class="text-xs font-bold text-gray-500 uppercase">Senha</label>
                                    <div class="relative">
                                        <input :type="showPass ? 'text' : 'password'" wire:model="password" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 pr-12 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="••••••••">
                                        <button type="button" @click="showPass = !showPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-white transition-colors">
                                            <i class="ph text-lg" :class="showPass ? 'ph-eye-slash' : 'ph-eye'"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="space-y-2" x-data="{ showConfirmPass: false }">
                                    <label class="text-xs font-bold text-gray-500 uppercase">Confirmar Senha</label>
                                    <div class="relative">
                                        <input :type="showConfirmPass ? 'text' : 'password'" wire:model="password_confirmation" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 pr-12 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="••••••••">
                                        <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-white transition-colors">
                                            <i class="ph text-lg" :class="showConfirmPass ? 'ph-eye-slash' : 'ph-eye'"></i>
                                        </button>
                                    </div>
                                </div>                
                            </div> 

                            <div class="mt-10 flex justify-between items-center">
                                <button wire:click="previousStep" class="text-gray-500 hover:text-white font-bold text-sm transition-colors uppercase tracking-tight">
                                    Voltar
                                </button>
                                <button wire:click="validateCurrentStep" class="bg-[#ff5500] hover:bg-orange-600 text-white font-black py-4 px-10 rounded-lg transition-all shadow-lg shadow-orange-900/20 active:scale-95 flex items-center gap-2">
                                    Próximo <span class="text-lg">&rarr;</span>
                                </button>
                            </div>
                        </div>
                    @endif
                    {{-- PASSO 3: CONCLUSÃO --}}
                    @if ($currentStep === 3)
                        <div id="step-3" wire:key="content-step-3" class="step-content animate-fadeIn text-center py-8">
                            <div class="w-20 h-20 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>

                            <h2 class="text-3xl font-bold text-white mb-4">Bem-vindo ao Versus!</h2>
                            <p class="text-gray-400 mb-8 max-w-md mx-auto">Falta apenas mais um passo para você ingressar em nosso Marketplace. Enviamos um e-mail de confirmação para <span class="text-white font-bold">{{ $userCreated ? $userCreated->masked_email : '' }}</span>.</p>

                            <div class="bg-gray-900/50 rounded-lg p-6 max-w-sm mx-auto text-left mb-8 border border-gray-800">
                                <h4 class="text-sm font-bold text-gray-300 uppercase mb-4">Próximos Passos:</h4>
                                <ul class="space-y-3 text-sm text-gray-400">
                                    <li class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold">1</span>
                                        Verifique seu e-mail para ativar a conta.
                                    </li>
                                    <li class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold">2</span>
                                        Acesse o Painel do jogador.
                                    </li>
                                    <li class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold">3</span>
                                        Configure seus métodos de pagamento e endereço.
                                    </li>
                                </ul>
                            </div>

                            <a href="{{ $userCreated ? $userCreated->email_provider_url : '#' }}" class="inline-block bg-[#ff5500] hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition-all transform hover:translate-y-[-2px] shadow-lg shadow-orange-900/20">
                                Ir para Verificação de E-mail
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </main>

    @else
        {{-- ========================================== --}}
        {{-- VERSÃO 2: LOJAS CLIENTES (WHITE LABEL)     --}}
        {{-- ========================================== --}}
        
        {{-- PUXANDO O MOTOR DE CORES E VARIÁVEIS OFICIAL DO SISTEMA --}}
        @if(isset($loja))
            @include('partials.template.styles', ['loja' => $loja])
        @endif

        @push('styles')
            <style>
                .animate-fadeIn {
                    animation: fadeIn 0.5s ease-out forwards;
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .custom-scrollbar::-webkit-scrollbar {
                    width: 8px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: transparent;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: var(--cor-secundaria);
                    opacity: 0.5;
                    border-radius: 4px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: var(--cor-cta);
                }
                .mask-apple {
                    -webkit-mask-image: url('{{ asset('assets/apple-big-logo_80676.svg') }}'); 
                    mask-image: url('{{ asset('assets/apple-big-logo_80676.svg') }}');
                    -webkit-mask-repeat: no-repeat;
                    mask-repeat: no-repeat;
                    -webkit-mask-size: contain;
                    mask-size: contain;
                }
                
                /* PLACEHOLDER DINAMICO COM A COR DE TEXTO DA COR TERCIARIA */
                .loja-input::placeholder {
                    color: var(--cor-texto-terciaria) !important;
                    opacity: 0.6 !important;
                }
            </style>
        @endpush

        <main class="flex-grow flex items-center justify-center pt-28 pb-12 px-4 transition-colors duration-500" style="background-color: var(--cor-bg-loja); color: var(--cor-texto-principal);">
            <div class="w-full max-w-3xl">

                {{-- PROGRESS BAR BLINDADA --}}
                <div class="flex justify-between mb-12 relative" wire:key="progress-bar-container-loja">
                    
                    {{-- LINHA DE FUNDO (Trilho) --}}
                    <div class="absolute top-1/2 left-0 w-full h-0.5 z-0 transform -translate-y-1/2 flex" style="background-color: var(--cor-terciaria);" wire:key="progress-track-loja">
                        <div wire:key="track-line-1-loja" class="flex-1 h-full transition-all duration-500" style="background-color: {{ $currentStep >= 2 ? 'var(--cor-cta)' : 'transparent' }};"></div>
                        <div wire:key="track-line-2-loja" class="flex-1 h-full transition-all duration-500" style="background-color: {{ $currentStep >= 3 ? 'var(--cor-cta)' : 'transparent' }};"></div>
                    </div>

                    {{-- INDICADORES (Bolinhas) --}}
                    @foreach([1 => 'Termos', 2 => 'Pessoal', 3 => 'Conclusão'] as $num => $label)
                        <div wire:key="step-wrapper-loja-{{ $num }}" class="relative z-10 flex flex-col items-center gap-2 transition-all duration-300 {{ $currentStep >= $num ? 'opacity-100' : 'opacity-70' }}">
                            
                            <div wire:key="step-circle-loja-{{ $num }}" class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 transition-colors duration-500"
                                style="background-color: {{ $currentStep >= $num ? 'var(--cor-cta)' : 'var(--cor-terciaria)' }};
                                        border-color: {{ $currentStep >= $num ? 'var(--cor-cta)' : 'var(--cor-bg-header)' }};
                                        color: {{ $currentStep >= $num ? 'var(--cor-cta-txt)' : 'var(--cor-texto-terciaria)' }};">
                                {{ $num }}
                            </div>
                            
                            <span wire:key="step-label-loja-{{ $num }}" class="text-xs font-bold uppercase tracking-wider transition-colors duration-500" 
                                style="color: {{ $currentStep >= $num ? 'var(--cor-texto-principal)' : 'var(--cor-texto-principal)' }}; opacity: {{ $currentStep >= $num ? '1' : '0.6' }};">
                                {{ $label }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div id="form-container" class="rounded-2xl p-8 shadow-2xl relative overflow-hidden transition-all duration-500 border border-white/10" style="background-color: var(--cor-bg-header); color: var(--cor-texto-header);">
                    
                    {{-- PASSO 1: ACEITE OS TERMOS --}}
                    @if ($currentStep === 1)
                        <div id="step-1" wire:key="content-step-1-loja" class="step-content animate-fadeIn">
                            <h2 class="text-2xl font-bold mb-2" style="color: inherit;">Termos de Uso</h2>
                            <p class="mb-6 text-sm" style="color: inherit; opacity: 0.7;">Por favor, leia atentamente as regras do marketplace Versus TCG.</p>  

                            <div class="rounded-lg p-6 h-64 overflow-y-auto custom-scrollbar mb-6 text-sm leading-relaxed border border-white/5" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);">
                                <h3 class="text-xl font-bold mb-4" style="color: inherit;">1. Aceitação dos Termos</h3>
                                <p class="mb-4">Ao se registrar e utilizar os serviços da VERSUS TCG, você concorda em cumprir e estar vinculado aos seguintes termos e condições de uso. Se você não concordar com qualquer parte destes termos, por favor, não utilize nossos serviços.</p>

                                <h3 class="text-xl font-bold mb-4" style="color: inherit;">2. Elegibilidade</h3>
                                <p class="mb-4">Você deve ter pelo menos 18 anos de idade para se registrar como lojista. Ao se registrar, você declara e garante que tem a idade legal para formar um contrato vinculativo.</p>

                                <h3 class="text-xl font-bold mb-4" style="color: inherit;">3. Registro de Conta</h3>
                                <p class="mb-4">Para acessar certas funcionalidades do site, você precisará criar uma conta. Você concorda em fornecer informações precisas, completas e atualizadas durante o processo de registro e em manter a segurança de sua senha.</p>

                                <h3 class="text-xl font-bold mb-4" style="color: inherit;">4. Planos de Assinatura</h3>
                                <p class="mb-4">Os lojistas podem escolher entre diferentes planos de assinatura (Básico, Pro, Premium), cada um com suas próprias funcionalidades e custos. A ativação da loja no marketplace está condicionada à contratação e pagamento de um plano.</p>

                                <h3 class="text-xl font-bold mb-4" style="color: inherit;">5. Conteúdo do Usuário</h3>
                                <p class="mb-4">Você é responsável por todo o conteúdo que publica em sua loja, incluindo descrições de produtos, imagens e preços. A VERSUS TCG reserva-se o direito de remover qualquer conteúdo que viole estes termos ou seja considerado inadequado.</p>

                                <h3 class="text-xl font-bold mb-4" style="color: inherit;">6. Limitação de Responsabilidade</h3>
                                <p class="mb-4">A VERSUS TCG não será responsável por quaisquer danos diretos, indiretos, incidentais, especiais, consequenciais ou exemplares resultantes do uso ou da incapacidade de usar o serviço.</p>

                                <h3 class="text-xl font-bold mb-4" style="color: inherit;">7. Modificações dos Termos</h3>
                                <p class="mb-4">A VERSUS TCG reserva-se o direito de modificar estes termos a qualquer momento. As alterações entrarão em vigor imediatamente após a publicação no site. Seu uso continuado do serviço após tais modificações constitui sua aceitação dos novos termos.</p>

                                <h3 class="text-xl font-bold mb-4" style="color: inherit;">8. Rescisão</h3>
                                <p class="mb-4">Podemos rescindir ou suspender seu acesso ao nosso serviço imediatamente, sem aviso prévio ou responsabilidade, por qualquer motivo, incluindo, sem limitação, se você violar os Termos.</p>
                            </div>              
                        </div>    
                            
                        <div class="flex items-center mb-8">
                            <input id="terms-check-loja" type="checkbox" wire:model.live="acceptTerms" class="w-5 h-5 rounded outline-none transition-colors border" style="background-color: var(--cor-terciaria); border-color: rgba(255,255,255,0.2); color: var(--cor-cta);">
                            <label for="terms-check-loja" class="ml-3 text-sm font-medium" style="color: inherit; opacity: 0.8;">Li e concordo com os Termos de Uso e Política de Privacidade.</label>
                        </div>       
                            
                        @error('acceptTerms') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror

                        <div class="mt-8 flex justify-end">
                            <button
                                wire:click="nextStep"
                                wire:loading.attr="disabled"
                                @if (!$acceptTerms) disabled @endif
                                class="font-bold py-3 px-8 rounded-lg transition-all transform hover:translate-y-[-2px] shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                style="background-color: var(--cor-cta); color: var(--cor-cta-txt);"
                                >
                                    Aceitar e Continuar
                            </button>
                        </div>               
                    @endif

                    {{-- PASSO 2: SEUS DADOS PESSOAIS --}}
                    @if ($currentStep === 2)
                        <div id="step-2" wire:key="content-step-2-loja" class="step-content animate-fadeIn">
                            <h2 class="text-2xl font-bold mb-2" style="color: inherit;">Criar conta de Jogador</h2>
                            <p class="mb-8 text-sm" style="color: inherit; opacity: 0.7;">Entre no jogo em menos de 1 minuto.</p>

                            <div class="grid grid-cols-2 gap-4 mb-8">
                                <button type="button" class="group flex items-center justify-center gap-3 py-3 px-4 rounded-lg text-sm font-medium transition-all hover:opacity-80 border border-white/20" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);">
                                    <img src="{{ asset('assets/google_2702602.png') }}" alt="Google" class="w-5 h-5 object-contain transform transition-transform duration-200 group-hover:scale-110">
                                    Entrar com Google
                                </button>

                                <button type="button" class="group flex items-center justify-center gap-3 py-3 px-4 rounded-lg text-sm font-medium transition-all hover:opacity-80 border border-white/20" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);">
                                    <img src="{{ asset('assets/microsoft_11379067.png') }}" alt="Microsoft" class="w-5 h-5 object-contain transform transition-transform duration-200 group-hover:scale-110">
                                    Entrar com Microsoft
                                </button>

                                <button type="button" class="group flex items-center justify-center gap-3 py-3 px-4 rounded-lg text-sm font-medium transition-all hover:opacity-80 border border-white/20" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);">
                                    <div class="w-5 h-5 transition-transform duration-200 group-hover:scale-110"
                                        style="background-color: currentColor;
                                                -webkit-mask-image: url('{{ asset('assets/apple-big-logo_80676.svg') }}'); 
                                                mask-image: url('{{ asset('assets/apple-big-logo_80676.svg') }}');
                                                -webkit-mask-repeat: no-repeat;
                                                mask-repeat: no-repeat;
                                                -webkit-mask-size: contain;
                                                mask-size: contain;">
                                    </div>
                                    Entrar com Apple
                                </button>

                                <button type="button" class="group flex items-center justify-center gap-3 py-3 px-4 rounded-lg text-sm font-medium transition-all hover:opacity-80 border border-white/20" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);">
                                    <img src="{{ asset('assets/discord_5968756.png') }}" alt="Discord" class="w-5 h-5 object-contain transform transition-transform duration-200 group-hover:scale-110">
                                    Entrar com Discord
                                </button>
                            </div>
                            <div class="flex items-center my-10 px-1 opacity-50">
                                <div class="flex-1 border-t border-white/20"></div>
                                <span class="px-4 text-xs font-black uppercase tracking-widest" style="color: inherit;">Ou</span>
                                <div class="flex-1 border-t border-white/20"></div>
                            </div>
                        
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase" style="color: inherit; opacity: 0.7;">Nome</label>
                                    <input type="text" wire:model="name" class="loja-input w-full rounded-lg px-4 py-3 outline-none transition-colors border-none shadow-inner placeholder-white placeholder-opacity-50" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);" placeholder="Seu nome">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase" style="color: inherit; opacity: 0.7;">Sobrenome</label>
                                    <input type="text" wire:model="surname" class="loja-input w-full rounded-lg px-4 py-3 outline-none transition-colors border-none shadow-inner placeholder-white placeholder-opacity-50" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);" placeholder="Seu sobrenome">
                                </div>            
                                <div class="col-span-1 md:col-span-2 space-y-2">
                                    <label class="text-xs font-bold uppercase" style="color: inherit; opacity: 0.7;">Login (Usuário)</label>
                                    <input type="text" wire:model="nickname" class="loja-input w-full rounded-lg px-4 py-3 outline-none transition-colors border-none shadow-inner placeholder-white placeholder-opacity-50" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);" placeholder="ex: MasterOfMagic99">
                                </div>               
                                <div class="col-span-1 md:col-span-2 space-y-2">
                                    <label class="text-xs font-bold uppercase" style="color: inherit; opacity: 0.7;">E-mail Corporativo</label>
                                    <input type="email" wire:model="email" class="loja-input w-full rounded-lg px-4 py-3 outline-none transition-colors border-none shadow-inner placeholder-white placeholder-opacity-50" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);" placeholder="seu@email.com">
                                </div>
                                
                                <div class="space-y-2" x-data="{ showPass: false }">
                                    <label class="text-xs font-bold uppercase" style="color: inherit; opacity: 0.7;">Senha</label>
                                    <div class="relative">
                                        <input :type="showPass ? 'text' : 'password'" wire:model="password" class="loja-input w-full rounded-lg px-4 py-3 pr-12 outline-none transition-colors border-none shadow-inner placeholder-white placeholder-opacity-50" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);" placeholder="••••••••">
                                        <button type="button" @click="showPass = !showPass" class="absolute right-4 top-1/2 -translate-y-1/2 transition-colors" style="color: var(--cor-texto-terciaria); opacity: 0.6;">
                                            <i class="ph text-lg" :class="showPass ? 'ph-eye-slash' : 'ph-eye'"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="space-y-2" x-data="{ showConfirmPass: false }">
                                    <label class="text-xs font-bold uppercase" style="color: inherit; opacity: 0.7;">Confirmar Senha</label>
                                    <div class="relative">
                                        <input :type="showConfirmPass ? 'text' : 'password'" wire:model="password_confirmation" class="loja-input w-full rounded-lg px-4 py-3 pr-12 outline-none transition-colors border-none shadow-inner placeholder-white placeholder-opacity-50" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);" placeholder="••••••••">
                                        <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-4 top-1/2 -translate-y-1/2 transition-colors" style="color: var(--cor-texto-terciaria); opacity: 0.6;">
                                            <i class="ph text-lg" :class="showConfirmPass ? 'ph-eye-slash' : 'ph-eye'"></i>
                                        </button>
                                    </div>
                                </div>                
                            </div> 

                            <div class="mt-10 flex justify-between items-center">
                                <button wire:click="previousStep" class="font-bold text-sm uppercase tracking-tight transition-all hover:opacity-70" style="color: inherit;">
                                    Voltar
                                </button>
                                <button wire:click="validateCurrentStep" class="font-black py-4 px-10 rounded-lg transition-all shadow-lg active:scale-95 flex items-center gap-2 hover:opacity-90" style="background-color: var(--cor-cta); color: var(--cor-cta-txt);">
                                    Próximo <span class="text-lg">&rarr;</span>
                                </button>
                            </div>
                        </div>
                    @endif
                    {{-- PASSO 3: CONCLUSÃO --}}
                    @if ($currentStep === 3)
                        <div id="step-3" wire:key="content-step-3-loja" class="step-content animate-fadeIn text-center py-8">
                            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 relative">
                                <div class="absolute inset-0 rounded-full opacity-20" style="background-color: var(--cor-cta);"></div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--cor-cta);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>

                            <h2 class="text-3xl font-bold mb-4" style="color: inherit;">Bem-vindo!</h2>
                            <p class="mb-8 max-w-md mx-auto text-sm" style="color: inherit; opacity: 0.8;">Falta apenas mais um passo para você ingressar. Enviamos um e-mail de confirmação para <span class="font-bold">{{ $userCreated ? $userCreated->masked_email : '' }}</span>.</p>

                            <div class="rounded-lg p-6 max-w-sm mx-auto text-left mb-8 border border-white/5" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);">
                                <h4 class="text-sm font-bold uppercase mb-4" style="color: inherit; opacity: 0.9;">Próximos Passos:</h4>
                                <ul class="space-y-3 text-sm" style="color: inherit; opacity: 0.8;">
                                    <li class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" style="background-color: var(--cor-bg-header); color: var(--cor-texto-header);">1</span>
                                        Verifique seu e-mail para ativar a conta.
                                    </li>
                                    <li class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" style="background-color: var(--cor-bg-header); color: var(--cor-texto-header);">2</span>
                                        Acesse o Painel do jogador.
                                    </li>
                                    <li class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" style="background-color: var(--cor-bg-header); color: var(--cor-texto-header);">3</span>
                                        Configure seus métodos de pagamento e endereço.
                                    </li>
                                </ul>
                            </div>

                            <a href="{{ $userCreated ? $userCreated->email_provider_url : '#' }}" class="inline-block font-bold py-3 px-8 rounded-lg transition-all transform hover:translate-y-[-2px] shadow-lg hover:opacity-90" style="background-color: var(--cor-cta); color: var(--cor-cta-txt);">
                                Ir para Verificação de E-mail
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    @endif
</div>
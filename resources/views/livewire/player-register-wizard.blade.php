@push('styles')
    <style>
        :root {
            --vs-orange: #FF8C00; /* Laranja principal */
            --vs-dark-bg: #1A1A1A; /* Fundo geral do body */
            --vs-card-bg: #000000; /* Fundo do card do wizard */
            --vs-gray-border: #333333; /* Borda de inputs e separadores */
            --vs-light-gray-text: #B0B0B0; /* Texto secundário */
            --vs-white-text: #FFFFFF; /* Texto principal */
            --vs-input-bg: #222222; /* Fundo dos campos de input */
            --vs-social-button-bg: #222222; /* Fundo dos botões sociais */
            --vs-social-button-border: #333333; /* Borda dos botões sociais */
            --vs-social-button-hover-bg: #3A3A3A; /* Fundo hover dos botões sociais */
            --vs-social-button-hover-border: #555555; /* Borda hover dos botões sociais */
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0a0a0a; /* Sobrescreve o background do app.blade.php se necessário */
            color: white;
        }
        /* Animações e Utilitários */
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* Scrollbar customizada para os termos */
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
         /* Estilo para os botões sociais */
        .social-button {
            background-color: var(--vs-social-button-bg);
            border: 1px solid var(--vs-social-button-border);
            color: var(--vs-white-text);
            font-weight: 600; /* Semibold */
            font-size: 0.95rem; /* Tamanho de texto ligeiramente menor */
            border-radius: 0.5rem; /* Cantos arredondados */
            transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
        }

        .social-button:hover {
            background-color: var(--vs-social-button-hover-bg);
            border-color: var(--vs-social-button-hover-border);
        }

        .social-button img {
            width: 1.25rem; /* w-5 */
            height: 1.25rem; /* h-5 */
            margin-right: 0.75rem; /* mr-3 */
        }

        /* Estilo para o divisor "Ou" */
        .divider-text {
            color: var(--vs-light-gray-text);
            font-size: 0.875rem; /* text-sm */
            font-weight: 500; /* medium */
        }   
    </style>
@endpush
<main class="flex-grow flex items-center justify-center pt-28 pb-12 px-4">
    <div class="w-full max-w-3xl">

        {{-- PROGRESS BAR --}}
        <div class="flex justify-between mb-12 relative" x-data="{ currentStepAlpine: @entangle('currentStep') }">
            
        {{-- LINHA DE FUNDO (Trilho) --}}
        <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-800 -z-10 transform -translate-y-1/2 flex">
            {{-- Segmentos da Linha que mudam de cor sozinhos --}}
            <div class="flex-1 h-full transition-all duration-500" :class="currentStepAlpine >= 2 ? 'bg-[#ff5500]' : 'bg-gray-800'"></div>
            <div class="flex-1 h-full transition-all duration-500" :class="currentStepAlpine >= 3 ? 'bg-[#ff5500]' : 'bg-gray-800'"></div>
            
        </div>

        {{-- INDICADORES (Bolinhas) --}}
        @foreach([1 => 'Termos', 2 => 'Pessoal', 3 => 'Conclusão'] as $num => $label)
            <div class="flex flex-col items-center gap-2 transition-all duration-300" 
                :class="currentStepAlpine >= {{ $num }} ? 'opacity-100' : 'opacity-50'">
                
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 border-[#0a0a0a] transition-colors duration-500"
                    :class="currentStepAlpine >= {{ $num }} ? 'bg-[#ff5500] text-white' : 'bg-gray-800 text-gray-400'">
                    {{ $num }}
                </div>
                
                <span class="text-xs font-bold uppercase tracking-wider transition-colors duration-500"
                    :class="currentStepAlpine >= {{ $num }} ? 'text-white' : 'text-gray-500'">
                    {{ $label }}
                </span>
            </div>
        @endforeach
    </div>
    <!-- FORM CONTAINER -->
    <div id="form-container" class="bg-[#111] border border-gray-800 rounded-2xl p-8 shadow-2xl relative overflow-hidden transition-all duration-500">
        {{-- PASSO 1: ACEITE OS TERMOS --}}
        @if ($currentStep === 1)

            <!-- STEP 1: TERMOS -->
            <div id="step-1" class="step-content animate-fadeIn">
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
                    @if (!$acceptTerms) disabled @endif {{-- Isso desabilita o botão se acceptTerms for falso --}}
                    class="btn-submit disabled:opacity-50 disabled:cursor-not-allowed bg-[#ff5500] hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition-all transform hover:translate-y-[-2px] shadow-lg shadow-orange-900/20"
                    >
                        Aceitar e Continuar
                </button>
            </div>               
        @endif

        {{-- PASSO 2: SEUS DADOS PESSOAIS --}}
        @if ($currentStep === 2)

            <!-- STEP 2: DADOS PESSOAIS -->
            <div id="step-2" class="step-content animate-fadeIn">
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
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">Senha</label>
                        <input type="password" wire:model="password" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="••••••••">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">Confirmar Senha</label>
                        <input type="password" wire:model="password_confirmation" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="••••••••">
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
            
            <!-- STEP 3: CONCLUSÃO -->
            <div id="step-3" class="step-content animate-fadeIn text-center py-8">
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
</main>
{{-- resources/views/livewire/store/register.blade.php --}}

{{--
    Este componente Livewire agora assume que está sendo renderizado DENTRO
    de um layout principal (como app.blade.php) que já fornece as tags
    <html>, <head>, <body>, e o header/footer.

    O conteúdo do seu HTML original (que estava dentro do <body>)
    será colocado aqui, e os estilos globais do <head> serão movidos para @push('styles').
--}}

{{-- Inclusão dos estilos globais do seu HTML original --}}
@push('styles')
    <style>
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
    </style>
@endpush

{{-- O conteúdo principal da página de registro --}}
<!-- MAIN CONTENT -->
<main class="flex-grow flex items-center justify-center pt-28 pb-12 px-4">
    <div class="w-full max-w-3xl">

{{-- PROGRESS BAR --}}
<div class="flex justify-between mb-12 relative" x-data="{ currentStepAlpine: @entangle('currentStep') }">
    
    {{-- LINHA DE FUNDO (Trilho) --}}
    <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-800 -z-10 transform -translate-y-1/2 flex">
        {{-- Segmentos da Linha que mudam de cor sozinhos --}}
        <div class="flex-1 h-full transition-all duration-500" :class="currentStepAlpine >= 2 ? 'bg-[#ff5500]' : 'bg-gray-800'"></div>
        <div class="flex-1 h-full transition-all duration-500" :class="currentStepAlpine >= 3 ? 'bg-[#ff5500]' : 'bg-gray-800'"></div>
        <div class="flex-1 h-full transition-all duration-500" :class="currentStepAlpine >= 4 ? 'bg-[#ff5500]' : 'bg-gray-800'"></div>
    </div>

    {{-- INDICADORES (Bolinhas) --}}
    @foreach([1 => 'Termos', 2 => 'Pessoal', 3 => 'Loja', 4 => 'Conclusão'] as $num => $label)
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
                <h2 class="text-2xl font-bold text-white mb-2">Quem é você?</h2>
                <p class="text-gray-400 mb-8 text-sm">Preencha seus dados pessoais para criar o usuário administrador.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">Nome</label>
                        <input type="text" id="name" wire:model="name" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="Seu nome">@error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">Sobrenome</label>
                        <input type="text" id="surname" wire:model="surname" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="Seu sobrenome">@error('surname') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">Login (Usuário)</label>
                        <input type="text" id="login" wire:model="login" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="ex: mestre_cards">@error('login') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">CPF</label>
                        <input type="text" id="document_number" wire:model="document_number" x-data x-mask="999.999.999-99" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="000.000.000-00">@error('document_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-1 md:col-span-2 space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">E-mail Corporativo</label>
                        <input type="email" id="email" wire:model="email" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="contato@sualoja.com">@error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">Senha</label>
                        <input type="password" id="password" wire:model="password" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="••••••••">@error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">Confirmar Senha</label>
                        <input type="password" id="password_confirmation" wire:model="password_confirmation" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="••••••••">@error('password_confirmation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>                
                </div> 

                <div class="mt-8 flex justify-between">
                    <button wire:click="previousStep" class="text-gray-500 hover:text-white font-bold py-3 px-6 transition-colors">
                        Voltar
                    </button>
                    <button wire:click="nextStep" class="bg-[#ff5500] hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition-all transform hover:translate-y-[-2px] shadow-lg shadow-orange-900/20">
                        Próximo &rarr;
                    </button>
                </div>
                
            </div>
        @endif

        {{-- PASSO 3: INFORMAÇÕES DA LOJA --}}
        @if ($currentStep === 3)

            <!-- STEP 3: DADOS DA LOJA (Com Seletor de Plano) -->
            <div id="step-3" class="step-content animate-fadeIn">

                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-white mb-2">Sua Loja</h2>
                        <p class="text-gray-400 text-sm">Configure a identidade da sua loja no marketplace.</p>
                    </div>
                    <!-- Badge Dinâmica do Plano -->
                    <div class="mb-6 text-center">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold transition-all
                        @if($plan_slug === 'premium') bg-purple-900/30 border border-purple-500/50 text-purple-300 premium-glow @endif
                        @if($plan_slug === 'pro') bg-orange-900/30 border border-orange-500/50 text-[#ff5500] @endif
                        @if($plan_slug === 'basic') bg-gray-800 border border-gray-700 text-gray-300 @endif">
                            <span class="w-2 h-2 rounded-full
                                @if($plan_slug === 'premium') bg-purple-500 animate-pulse @endif
                                @if($plan_slug === 'pro') bg-[#ff5500] @endif
                                @if($plan_slug === 'basic') bg-gray-500 @endif">
                            </span>
                            PLANO {{ strtoupper($plan_name) }}
                        </span>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">Nome da Loja</label>
                        <input type="text" id="store_name" wire:model="store_name" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="Ex: Cards do Poder">@error('store_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="url_slug" class="text-xs font-bold text-gray-500 uppercase">URL da Loja (Slug)</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-4 rounded-l-lg border border-r-0 border-gray-800 bg-gray-900 text-gray-500 text-sm">
                                versustcg.com/loja/
                            </span>
                            <input type="text" id="url_slug" wire:model.live="url_slug" class="flex-1 bg-black border border-gray-800 rounded-r-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="cards-do-poder">
                        </div>
                        @error('url_slug') {{-- Adicionado @error para exibir mensagens de erro --}}
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> {{-- Estilo de erro adaptado para o seu contexto --}}
                        @enderror
                        <p class="text-xs text-gray-600">Este será o endereço direto da sua loja.</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-500 uppercase">Slogan (Opcional)</label>
                        <input type="text" id="store_slogan" wire:model="store_slogan" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="O melhor preço do Brasil">
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase">CEP (Origem)</label>
                            <input type="text" id="store_zip_code" wire:model="store_zip_code" x-data x-mask="99999-999" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors" placeholder="00000-000">@error('store_zip_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase">Estado (UF)</label>
                            <input type="text" id="store_state_code" wire:model="store_state_code" maxlength="2" class="w-full bg-black border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] outline-none transition-colors appearance-none">@error('store_state_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror                            
                        </div>
                    </div> 
                    
                    <div class="flex justify-between mt-8">    
                        <button type="button"
                                wire:click="previousStep"
                                wire:loading.attr="disabled"
                                class="text-gray-500 hover:text-white font-bold py-3 px-6 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            Voltar
                        </button>
    
                        <button type="button" 
                                wire:click="finishRegistration" {{-- Assumindo que finishRegistration é o método para o último passo --}}
                                wire:loading.attr="disabled"
                                class="bg-[#ff5500] hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition-all transform hover:translate-y-[-2px] shadow-lg shadow-orange-900/20 disabled:opacity-50 disabled:cursor-not-allowed">
                            Finalizar Cadastro
                        </button>
                    </div> 
                </div>               
        @endif

        {{-- PASSO 4: CONCLUSÃO --}}
        @if ($currentStep === 4)

            <!-- STEP 4: CONCLUSÃO -->
            <div id="step-4" class="step-content animate-fadeIn text-center py-8">
                <div class="w-20 h-20 bg-green-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h2 class="text-3xl font-bold text-white mb-4">Bem-vindo ao Versus!</h2>
                <p class="text-gray-400 mb-8 max-w-md mx-auto">Sua loja foi criada com sucesso. Enviamos um e-mail de confirmação para <span class="text-white font-bold">{{ $userCreated ? $userCreated->masked_email : '' }}</span>.</p>

                <div class="bg-gray-900/50 rounded-lg p-6 max-w-sm mx-auto text-left mb-8 border border-gray-800">
                    <h4 class="text-sm font-bold text-gray-300 uppercase mb-4">Próximos Passos:</h4>
                    <ul class="space-y-3 text-sm text-gray-400">
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold">1</span>
                            Verifique seu e-mail para ativar a conta.
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold">2</span>
                            Acesse o Painel do Lojista.
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold">3</span>
                            Configure seus métodos de pagamento e envio.
                        </li>
                    </ul>
                </div>

                <a href="{{ $userCreated ? $userCreated->email_provider_url : '#' }}" class="inline-block bg-[#ff5500] hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition-all transform hover:translate-y-[-2px] shadow-lg shadow-orange-900/20">
                    Ir para Verificação de E-mail
                </a>
            </div>                       
        @endif
 


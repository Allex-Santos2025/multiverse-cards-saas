<div>
    <div class="max-w-7xl mx-auto p-6 md:p-10" x-data="{ tab: @entangle('tab'), showAdvanced: false }">
    
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Identidade Visual</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Personalize as cores, logotipos e o estilo visual da sua loja.</p>
            </div>
            <div class="flex items-center gap-3">
                <button 
                    type="button"
                    wire:click="resetToDefault" 
                    wire:confirm="Tem certeza que deseja voltar para as cores padrão de fábrica?"
                    class="text-slate-500 hover:text-red-600 font-semibold text-sm transition-colors"
                >
                    <i class="ph ph-arrow-counter-clockwise"></i> Resetar Cores
                </button>
                <button wire:click="save" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-5 rounded-lg shadow-sm transition-colors duration-200 flex items-center gap-2">
                    <i class="ph ph-floppy-disk text-xl"></i> 
                    <span wire:loading.remove wire:target="save">Salvar Alterações</span>
                    <span wire:loading wire:target="save">Salvando...</span>
                </button>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-8">
            
            {{-- MENU LATERAL --}}
            <div class="w-full md:w-64 flex flex-col gap-2 shrink-0">
                <button @click="tab = 'basico'" :class="tab === 'basico' ? 'bg-white dark:bg-[#1e293b] text-blue-600 dark:text-blue-400 border-blue-600 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 border-transparent'" class="text-left px-4 py-3 rounded-lg border-l-4 font-semibold transition-all flex items-center gap-2">
                    <i class="ph ph-paint-brush text-lg"></i> Básico (Identidade)
                </button>
                <button @click="tab = 'pro'" :class="tab === 'pro' ? 'bg-white dark:bg-[#1e293b] text-blue-600 dark:text-blue-400 border-blue-600 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 border-transparent'" class="text-left px-4 py-3 rounded-lg border-l-4 font-semibold flex justify-between items-center transition-all">
                    <span class="flex items-center gap-2"><i class="ph ph-text-aa text-lg"></i> Plano PRO</span>
                </button>
                <button @click="tab = 'premium'" :class="tab === 'premium' ? 'bg-white dark:bg-[#1e293b] text-blue-600 dark:text-blue-400 border-blue-600 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 border-transparent'" class="text-left px-4 py-3 rounded-lg border-l-4 font-semibold flex justify-between items-center transition-all">
                    <span class="flex items-center gap-2"><i class="ph ph-code text-lg"></i> Plano PREMIUM</span>
                </button>
            </div>

            {{-- ÁREA DE CONTEÚDO --}}
            <div class="flex-1 bg-white dark:bg-[#1e293b] rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden relative min-h-[500px]">
                
                <div x-show="tab === 'basico'" x-transition.opacity class="p-6 md:p-8 space-y-12">
                    
                    @php $lojaSlug = auth('store_user')->user()->store->url_slug; @endphp

                    {{-- ========================================== --}}
                    {{-- BLOC 1: LOGOTIPOS DA LOJA                --}}
                    {{-- ========================================== --}}
                    <div>
                        <h4 class="text-md font-bold text-slate-800 dark:text-slate-100 border-b border-slate-200 dark:border-slate-700 pb-2 mb-2">Logotipos da Loja</h4>
                        <p class="text-sm text-slate-500 mb-6">As marcas principais que aparecem dentro do seu site.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            {{-- Logo Principal (Header) --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col justify-between gap-3">
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Logo do Cabeçalho</span>
                                    <span class="text-[11px] text-slate-500 block">L: 100px à 210px / A: 85px (85kb). PNG Transparente.</span>
                                </div>
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="w-20 h-16 rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-zinc-900 flex items-center justify-center overflow-hidden shrink-0">
                                        @if ($upload_logo_main)
                                            <img src="{{ $upload_logo_main->temporaryUrl() }}" class="max-w-full max-h-full object-contain p-1">
                                        @elseif ($current_logo_main)
                                            <img src="{{ asset('store_images/' . $lojaSlug . '/' . $current_logo_main) }}" class="max-w-full max-h-full object-contain p-1">
                                        @else
                                            <i class="ph ph-image text-2xl text-slate-300"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <input type="file" wire:model="upload_logo_main" id="upload_logo_main" class="hidden" accept="image/png, image/jpeg">
                                        <label for="upload_logo_main" class="cursor-pointer bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 text-xs font-semibold py-1.5 px-3 rounded shadow-sm inline-block transition-colors">Trocar</label>
                                        <div wire:loading wire:target="upload_logo_main" class="text-[10px] text-blue-500 mt-1 block">Carregando...</div>
                                    </div>
                                </div>
                                <div class="mt-2 pt-3 border-t border-slate-200 dark:border-slate-600 flex items-center gap-2">
                                    <input type="checkbox" wire:model="use_logo_dashboard" id="use_logo_dashboard" class="rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                                    <label for="use_logo_dashboard" class="text-xs font-medium text-slate-600 dark:text-slate-300 cursor-pointer">Usar também no cabeçalho do painel</label>
                                </div>
                            </div>

                            {{-- Logo Footer --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col justify-between gap-3">
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Logo do Rodapé</span>
                                    <span class="text-[11px] text-slate-500 block">Versão monocromática (L: 100-210px / A: 85px).</span>
                                </div>
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="w-20 h-16 rounded border border-slate-200 dark:border-slate-600 bg-zinc-800 flex items-center justify-center overflow-hidden shrink-0">
                                        @if ($upload_logo_footer)
                                            <img src="{{ $upload_logo_footer->temporaryUrl() }}" class="max-w-full max-h-full object-contain p-1">
                                        @elseif ($current_logo_footer)
                                            <img src="{{ asset('store_images/' . $lojaSlug . '/' . $current_logo_footer) }}" class="max-w-full max-h-full object-contain p-1">
                                        @else
                                            <i class="ph ph-image text-2xl text-slate-500"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <input type="file" wire:model="upload_logo_footer" id="upload_logo_footer" class="hidden" accept="image/png, image/jpeg">
                                        <label for="upload_logo_footer" class="cursor-pointer bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 text-xs font-semibold py-1.5 px-3 rounded shadow-sm inline-block transition-colors">Trocar</label>
                                        <div wire:loading wire:target="upload_logo_footer" class="text-[10px] text-blue-500 mt-1 block">Carregando...</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Favicon --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col justify-between gap-3">
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Favicon (Ícone da Aba)</span>
                                    <span class="text-[11px] text-slate-500 block">Recomendado: Arquivo PNG ou ICO quadrado (ex: 512x512).</span>
                                </div>
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="w-16 h-16 rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-zinc-900 flex items-center justify-center overflow-hidden shrink-0">
                                        @if ($upload_favicon)
                                            <img src="{{ $upload_favicon->temporaryUrl() }}" class="max-w-full max-h-full object-contain p-2">
                                        @elseif ($current_favicon)
                                            <img src="{{ asset('store_images/' . $lojaSlug . '/' . $current_favicon) }}" class="max-w-full max-h-full object-contain p-2">
                                        @else
                                            <i class="ph ph-app-window text-2xl text-slate-300"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <input type="file" wire:model="upload_favicon" id="upload_favicon" class="hidden" accept="image/png, image/x-icon">
                                        <label for="upload_favicon" class="cursor-pointer bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 text-xs font-semibold py-1.5 px-3 rounded shadow-sm inline-block transition-colors">Trocar</label>
                                        <div wire:loading wire:target="upload_favicon" class="text-[10px] text-blue-500 mt-1 block">Carregando...</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- BLOC 2: IDENTIDADE MARKETPLACE           --}}
                    {{-- ========================================== --}}
                    <div>
                        <h4 class="text-md font-bold text-slate-800 dark:text-slate-100 border-b border-slate-200 dark:border-slate-700 pb-2 mb-2">Identidade do Marketplace</h4>
                        <p class="text-sm text-slate-500 mb-6">Como sua loja será vista na vitrine geral e em listagens competitivas.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            {{-- Avatar MKP --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col justify-between gap-3">
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Avatar / Perfil (Quadrado)</span>
                                    <span class="text-[11px] text-slate-500 block">Padrão: JPG (55x55px). Usado no popover de detalhes da loja.</span>
                                </div>
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="w-14 h-14 rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-zinc-900 flex items-center justify-center overflow-hidden shrink-0 shadow-sm">
                                        @if ($upload_avatar_marketplace)
                                            <img src="{{ $upload_avatar_marketplace->temporaryUrl() }}" class="w-full h-full object-cover">
                                        @elseif ($current_avatar_marketplace)
                                            <img src="{{ asset('store_images/' . $lojaSlug . '/' . $current_avatar_marketplace) }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="ph ph-user-square text-3xl text-slate-300"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <input type="file" wire:model="upload_avatar_marketplace" id="upload_avatar_marketplace" class="hidden" accept="image/png, image/jpeg">
                                        <label for="upload_avatar_marketplace" class="cursor-pointer bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 text-xs font-semibold py-1.5 px-3 rounded shadow-sm inline-block transition-colors">Trocar</label>
                                        <div wire:loading wire:target="upload_avatar_marketplace" class="text-[10px] text-blue-500 mt-1 block">Carregando...</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Logo MKP --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col justify-between gap-3">
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Logo Retangular (Listagem)</span>
                                    <span class="text-[11px] text-slate-500 block">Padrão: JPG (101x30px). Usada na listagem de preços.</span>
                                </div>
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="w-24 h-10 rounded border border-slate-200 dark:border-slate-600 bg-white dark:bg-zinc-900 flex items-center justify-center overflow-hidden shrink-0">
                                        @if ($upload_logo_marketplace)
                                            <img src="{{ $upload_logo_marketplace->temporaryUrl() }}" class="max-w-full max-h-full object-contain p-1">
                                        @elseif ($current_logo_marketplace)
                                            <img src="{{ asset('store_images/' . $lojaSlug . '/' . $current_logo_marketplace) }}" class="max-w-full max-h-full object-contain p-1">
                                        @else
                                            <i class="ph ph-image text-xl text-slate-300"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <input type="file" wire:model="upload_logo_marketplace" id="upload_logo_marketplace" class="hidden" accept="image/png, image/jpeg">
                                        <label for="upload_logo_marketplace" class="cursor-pointer bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 text-xs font-semibold py-1.5 px-3 rounded shadow-sm inline-block transition-colors">Trocar</label>
                                        <div wire:loading wire:target="upload_logo_marketplace" class="text-[10px] text-blue-500 mt-1 block">Carregando...</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- BLOC 3: PALETA DE CORES                    --}}
                    {{-- ========================================== --}}
                    <div>
                        <h4 class="text-md font-bold text-slate-800 dark:text-slate-100 border-b border-slate-200 dark:border-slate-700 pb-2 mb-2">Paleta de Cores</h4>
                        <p class="text-sm text-slate-500 mb-6">O sistema ajusta a cor dos textos automaticamente para garantir a melhor leitura sobre esses fundos.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            
                            {{-- Cor Primária --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center gap-3">
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Cor Primária</span>
                                    <span class="text-xs text-slate-500">Botões e destaques básicos</span>
                                </div>
                                <input type="color" wire:model="color_primary" class="h-10 w-14 rounded cursor-pointer bg-transparent border-0 p-0 shadow-sm flex-shrink-0">
                            </div>

                            {{-- NOVA: Cor Secundária --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center gap-3">
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Cor Secundária</span>
                                    <span class="text-xs text-slate-500">Faixas (Breadcrumb) e destaques secundários</span>
                                </div>
                                <input type="color" wire:model="color_secondary" class="h-10 w-14 rounded cursor-pointer bg-transparent border-0 p-0 shadow-sm flex-shrink-0">
                            </div>

                            {{-- NOVA: Cor Terciária --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center gap-3">
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Cor Terciária</span>
                                    <span class="text-xs text-slate-500">Linha do tempo, anos e elementos sutis</span>
                                </div>
                                <input type="color" wire:model="color_tertiary" class="h-10 w-14 rounded cursor-pointer bg-transparent border-0 p-0 shadow-sm flex-shrink-0">
                            </div>

                            {{-- Barra Contatos --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center gap-3">
                                <div><span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Barra Contatos</span><span class="text-xs text-slate-500">Faixa superior</span></div>
                                <input type="color" wire:model="color_topbar_bg" class="h-10 w-14 rounded cursor-pointer bg-transparent border-0 p-0 shadow-sm flex-shrink-0">
                            </div>

                            {{-- Fundo Cabeçalho --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center gap-3">
                                <div><span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Fundo Cabeçalho</span><span class="text-xs text-slate-500">Logo e menu principal</span></div>
                                <input type="color" wire:model="color_header_bg" class="h-10 w-14 rounded cursor-pointer bg-transparent border-0 p-0 shadow-sm flex-shrink-0">
                            </div>

                            {{-- Fundo do Site --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center gap-3">
                                <div><span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Fundo do Site</span><span class="text-xs text-slate-500">Área dos produtos</span></div>
                                <input type="color" wire:model="global_bg_color" class="h-10 w-14 rounded cursor-pointer bg-transparent border-0 p-0 shadow-sm flex-shrink-0">
                            </div>

                            {{-- Fundo Rodapé --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center gap-3">
                                <div><span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Fundo Rodapé</span><span class="text-xs text-slate-500">Final da página</span></div>
                                <input type="color" wire:model="color_footer_bg" class="h-10 w-14 rounded cursor-pointer bg-transparent border-0 p-0 shadow-sm flex-shrink-0">
                            </div>

                            {{-- CTA --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center gap-3">
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Cor dos Botões (CTA)</span>
                                    <span class="text-xs text-slate-500">Newsletter e Ofertas</span>
                                </div>
                                <input type="color" wire:model="color_cta" class="h-10 w-14 rounded cursor-pointer bg-transparent border-0 p-0 shadow-sm flex-shrink-0">
                            </div>

                            {{-- Hover Menu --}}
                            <div class="p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center gap-3">
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Hover do Menu</span>
                                    <span class="text-xs text-slate-500">Cor ao passar o mouse no menu principal</span>
                                </div>
                                <input type="color" wire:model="color_menu_hover" class="h-10 w-14 rounded cursor-pointer bg-transparent border-0 p-0 shadow-sm flex-shrink-0">
                            </div>
                        </div>

                        {{-- Avançadas --}}
                        <div class="mt-6">
                            <button type="button" @click="showAdvanced = !showAdvanced" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                <i class="ph" :class="showAdvanced ? 'ph-caret-up' : 'ph-caret-down'"></i>
                                Configurações Avançadas de Cor (Manual)
                            </button>
                            
                            <div x-show="showAdvanced" x-collapse class="mt-4 p-5 border-l-4 border-blue-500 bg-blue-50 dark:bg-blue-900/20 rounded-r-lg">
                                <p class="text-xs text-blue-800 dark:text-blue-300 mb-5">
                                    <i class="ph ph-warning-circle"></i> Ao definir uma cor aqui, você desativa o ajuste automático de contraste para aquele elemento.
                                </p>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-2 relative">
                                        <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Cor da Fonte do Menu Principal</label>
                                        <div class="flex items-center gap-3">
                                            <input type="color" wire:model="color_menu_text" class="h-10 w-14 rounded cursor-pointer bg-transparent border-0 p-0 shadow-sm flex-shrink-0">
                                            <button type="button" wire:click="$set('color_menu_text', null)" class="text-xs font-semibold text-slate-500 hover:text-red-500 transition-colors flex items-center gap-1" title="Voltar ao Automático">
                                                <i class="ph ph-trash"></i> Limpar (Automático)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div x-show="tab === 'pro'" x-transition.opacity style="display: none;" class="p-6 md:p-8">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-6 border-b border-slate-100 dark:border-slate-700 pb-2">Tipografia e Design</h3>
                    <p class="text-slate-500">Configurações de fontes, bordas e ícones entrarão aqui.</p>
                </div>

                <div x-show="tab === 'premium'" x-transition.opacity style="display: none;" class="p-6 md:p-8">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-6 border-b border-slate-100 dark:border-slate-700 pb-2">Custom CSS e Imersão</h3>
                    <p class="text-slate-500">Editor de código CSS e imagens de fundo imersivas entrarão aqui.</p>
                </div>

            </div>
        </div>
    </div>
</div>
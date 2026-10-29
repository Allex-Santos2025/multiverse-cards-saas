<div class="space-y-8">
    @if($sandboxDeckId)
        <livewire:lobby.deck-sandbox :deckId="$sandboxDeckId" wire:key="deck-sandbox-{{ $sandboxDeckId }}" />
    @elseif($activeDeckId)
        <livewire:lobby.deck-builder :deckId="$activeDeckId" wire:key="deck-builder-{{ $activeDeckId }}" />
    @else
        <!-- HEADER LIMPO E CLARO -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Meus Decks</h1>
                    <p class="text-xs font-medium text-slate-500">Caixas de baralho apoiadas na mesa. Passe o mouse ou selecione para abrir a tampa e ver a carta destaque.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="openCreateDeckModal" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-2xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    NOVO DECK
                </button>
            </div>
        </div>

        <!-- GRADE DE DECKS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-6">
            @foreach($decks as $deck)
                @php
                    $targetCards = strtolower($deck->format) === 'commander' ? 100 : 60;
                    
                    $totalDeckCards = (int) ($deck->cards_sum_quantity ?? ($deck->cards ? $deck->cards->sum('quantity') : 0));
                    $physicalCards = (int) ($deck->cards ? $deck->cards->where('is_virtual', false)->sum('quantity') : 0);
                    $wishlistCards = (int) ($deck->cards ? $deck->cards->where('is_virtual', true)->sum('quantity') : 0);

                    $pctPhysical = $targetCards > 0 ? min(100, round(($physicalCards / $targetCards) * 100)) : 0;
                    $pctWishlist = $targetCards > 0 ? min(100 - $pctPhysical, round(($wishlistCards / $targetCards) * 100)) : 0;

                    $coverImg = $deck->cover_image_path ? asset($deck->cover_image_path) : '/images/placeholder-card.png';
                @endphp

                <div class="relative bg-white rounded-[18px] border border-slate-200 overflow-hidden flex flex-col transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] shadow-[0_4px_12px_-2px_rgba(15,23,42,0.05),0_2px_6px_-1px_rgba(15,23,42,0.03)] hover:-translate-y-1 hover:border-slate-300 hover:shadow-[0_18px_28px_-6px_rgba(15,23,42,0.1),0_6px_10px_-3px_rgba(15,23,42,0.04)] group">
                    
                    <!-- PALCO SUPERIOR (MESA COM PLANO DE CONTATO FÍSICO) -->
                    <div class="relative h-[220px] bg-gradient-to-b from-white via-slate-50 to-[#edf2f7] flex items-end justify-center pb-[22px] [perspective:850px] [perspective-origin:50%_28%] border-b border-slate-200 overflow-hidden">
                        
                        <!-- Linha sutil de horizonte da mesa -->
                        <div class="absolute bottom-0 inset-x-0 h-[70px] bg-gradient-to-b from-slate-200/30 to-slate-300/45 pointer-events-none"></div>

                        <!-- Sombras de contato firme com a mesa -->
                        <div class="absolute w-[96px] h-[28px] bottom-[14px] left-1/2 -translate-x-[42%] -rotate-[5deg] bg-[radial-gradient(ellipse_at_center,rgba(15,23,42,0.65)_0%,rgba(15,23,42,0.25)_45%,transparent_72%)] blur-[4px] pointer-events-none transition-all duration-500"></div>
                        <div class="absolute w-[78px] h-[6px] bottom-[21px] left-1/2 -translate-x-[38%] -rotate-[3deg] bg-slate-900/75 blur-[2px] rounded-full pointer-events-none"></div>

                        <!-- DECK CASE 3D: PERSPECTIVA DE MESA REAL -->
                        <div wire:click="openDeckBuilder({{ $deck->id }})" class="w-[86px] h-[126px] relative [transform-style:preserve-3d] [transform:rotateX(-11deg)_rotateY(-24deg)] transition-transform duration-500 ease-[cubic-bezier(0.34,1.3,0.64,1)] cursor-pointer group-hover:[transform:rotateX(-4deg)_rotateY(-18deg)_translateY(-2px)]">
                            
                            <!-- 1. FACE TOPO COM ETIQUETA -->
                            <div class="absolute [backface-visibility:hidden] w-[86px] h-[64px] -top-[32px] [transform:rotateX(90deg)] rounded-t-[5px] brightness-[1.18] shadow-[inset_0_2px_3px_rgba(255,255,255,0.7),inset_0_-2px_3px_rgba(0,0,0,0.25)] flex items-center justify-center p-1 z-[6] bg-gradient-to-br from-red-600 via-red-700 to-red-800">
                                <div class="w-[70px] h-[20px] bg-[#fdfdfd] border border-black/15 rounded-[2px] shadow-[inset_0_1px_2px_rgba(0,0,0,0.2),0_1px_1px_rgba(255,255,255,0.6)] flex items-center justify-center text-[7.5px] font-extrabold text-slate-700 uppercase tracking-wider truncate px-1">
                                    {{ Str::limit(strtoupper($deck->name), 12) }}
                                </div>
                            </div>

                            <!-- 2. LATERAL DIREITA -->
                            <div class="absolute [backface-visibility:hidden] w-[64px] h-[126px] left-[54px] [transform:rotateY(90deg)] rounded-r-[4px] brightness-[0.68] contrast-[1.1] shadow-[inset_4px_0_12px_rgba(0,0,0,0.5)] flex flex-col justify-between p-2 overflow-hidden bg-gradient-to-br from-red-600 via-red-700 to-red-800">
                                <div class="absolute top-[40px] inset-x-0 h-[2px] bg-black/60 rotate-[14deg] [transform-origin:left_center] shadow-[0_1px_0_rgba(255,255,255,0.2)]"></div>
                                <div></div>
                                <span class="text-[6.5px] font-black tracking-widest uppercase text-white/40 drop-shadow-[0_1px_1px_rgba(0,0,0,0.7)] text-right">VERSUS</span>
                            </div>

                            <!-- 3. LATERAL ESQUERDA, FUNDO E TRASEIRA -->
                            <div class="absolute [backface-visibility:hidden] w-[64px] h-[126px] -left-[32px] [transform:rotateY(-90deg)] brightness-[0.48] bg-gradient-to-br from-red-600 via-red-700 to-red-800"></div>
                            <div class="absolute [backface-visibility:hidden] w-[86px] h-[64px] top-[94px] [transform:rotateX(-90deg)] brightness-[0.28] bg-gradient-to-br from-red-600 via-red-700 to-red-800"></div>
                            <div class="absolute [backface-visibility:hidden] w-[86px] h-[126px] [transform:translateZ(-32px)_rotateY(180deg)] brightness-[0.4] bg-gradient-to-br from-red-600 via-red-700 to-red-800"></div>

                            <!-- 4. FACE FRONTAL (CORPO FIXO + CARTA + ABA DA TAMPA) -->
                            <div class="absolute [backface-visibility:hidden] w-[86px] h-[126px] [transform:translateZ(32px)] rounded-[5px] flex flex-col shadow-[inset_0_1px_2px_rgba(255,255,255,0.4),inset_0_-2px_4px_rgba(0,0,0,0.5)] bg-gradient-to-br from-red-600 via-red-700 to-red-800">
                                
                                <!-- Carta Destaque Oculta no Interior -->
                                <div class="absolute top-[14px] left-[7px] right-[7px] h-[104px] rounded-[4px] bg-cover bg-[center_20%] shadow-[0_4px_10px_rgba(0,0,0,0.7)] transform translate-y-0 opacity-0 transition-all duration-[450ms] ease-[cubic-bezier(0.34,1.3,0.64,1)] z-[2] border border-white/20 group-hover:opacity-100 group-hover:-translate-y-4" style="background-image: url('{{ $coverImg }}');">
                                    <div class="absolute inset-0 rounded-[4px] bg-gradient-to-br from-white/30 to-transparent pointer-events-none"></div>
                                </div>

                                <!-- Tampa Articulada -->
                                <div class="absolute top-0 left-0 w-[86px] h-[56px] [transform-origin:top_center] [transform-style:preserve-3d] transition-transform duration-[550ms] ease-[cubic-bezier(0.34,1.25,0.64,1)] z-[5] group-hover:[transform:rotateX(125deg)]">
                                    <div class="w-[86px] h-[56px] [clip-path:polygon(0%_0%,100%_0%,100%_70%,50%_100%,0%_70%)] p-2 flex flex-col border-b-2 border-black/35 drop-shadow-[0_4px_3px_rgba(0,0,0,0.4)] bg-gradient-to-br from-red-600 via-red-700 to-red-800">
                                        <span class="text-[8px] font-black tracking-wider uppercase text-white/85 drop-shadow-[0_1px_2px_rgba(0,0,0,0.7)]">DECK CASE</span>
                                        <span class="inline-block text-[6.5px] font-extrabold border border-white/45 px-[2.5px] rounded-[2px] text-white/80 mt-0.5 self-start bg-black/20">{{ $targetCards }}+</span>
                                    </div>
                                </div>

                                <!-- Corpo Inferior da Caixa -->
                                <div class="absolute inset-0 flex flex-col justify-end p-2 pb-[10px]">
                                    <span class="text-[6.5px] font-black tracking-widest uppercase text-white/40 drop-shadow-[0_1px_1px_rgba(0,0,0,0.7)] text-right leading-tight">ULTIMATE<br>GUARD</span>
                                </div>
                            </div>

                        </div>

                        <!-- Badges Flutuantes -->
                        <span class="absolute top-3 left-3 px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-white/95 backdrop-blur-xs text-orange-700 shadow-2xs border border-orange-100">
                            {{ $deck->game->name ?? 'TCG' }}
                        </span>
                        <span class="absolute top-3 right-3 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-900/85 backdrop-blur-xs text-amber-300">
                            {{ strtoupper($deck->format ?? 'CONSTRUCTED') }}
                        </span>
                    </div>

                    <!-- Painel de Dados -->
                    <div class="p-4 flex-1 flex flex-col justify-between bg-white">
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <h3 wire:click="openDeckBuilder({{ $deck->id }})" class="font-extrabold text-sm text-slate-900 truncate hover:text-indigo-600 cursor-pointer" title="{{ $deck->name }}">
                                    {{ $deck->name }}
                                </h3>
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0 inline-block shadow-2xs"></span>
                            </div>

                            <!-- Progresso Físico vs Wishlist -->
                            <div class="mt-2.5 mb-3 bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <div class="flex items-center justify-between text-[10px] mb-1 font-medium">
                                    <span class="text-slate-500">Acervo Real</span>
                                    <span class="text-emerald-600 font-bold font-mono">{{ $pctPhysical }}% ({{ $physicalCards }}/{{ $targetCards }})</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden flex">
                                    <div class="bg-emerald-500" style="width: {{ $pctPhysical }}%"></div>
                                    @if($pctWishlist > 0)
                                        <div class="bg-indigo-400 opacity-60" style="width: {{ $pctWishlist }}%"></div>
                                    @endif
                                </div>
                            </div>

                            <!-- Mini Winrate & Contagem Real da Lista -->
                            <div class="grid grid-cols-2 gap-2 text-center text-xs mb-3">
                                <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                                    <span class="text-[9px] font-bold text-slate-400 block uppercase tracking-wider">Win Rate</span>
                                    <span class="font-bold text-slate-400 font-mono text-xs">-- (0v)</span>
                                </div>
                                <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                                    <span class="text-[9px] font-bold text-slate-400 block uppercase tracking-wider">Lista</span>
                                    <span class="font-black text-slate-700 font-mono text-xs">{{ $totalDeckCards }} / {{ $targetCards }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ações do Card -->
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                            <button type="button" wire:click="deleteDeck({{ $deck->id }})" wire:confirm="Deseja excluir este deck?" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Excluir">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="openSandbox({{ $deck->id }})" class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[11px] font-bold rounded-lg transition">
                                    Sandbox
                                </button>
                                <button type="button" wire:click="openDeckBuilder({{ $deck->id }})" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold rounded-lg transition">
                                    Abrir Construtor
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- SLOT NOVO DECK -->
            <div wire:click="openCreateDeckModal" class="border-2 border-dashed border-slate-300 bg-slate-50 hover:border-indigo-600 hover:bg-indigo-50/60 rounded-[18px] flex flex-col items-center justify-center min-h-[400px] cursor-pointer hover:-translate-y-1 transition-all duration-200 group">
                <div class="w-14 h-14 rounded-2xl bg-white group-hover:bg-indigo-600 text-slate-400 group-hover:text-white flex items-center justify-center transition shadow-xs border border-slate-200 group-hover:border-indigo-600 mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-sm font-extrabold text-slate-700 group-hover:text-indigo-600 transition">NOVO DECK</span>
                <span class="text-[11px] font-medium text-slate-400 mt-0.5">Montar novo baralho</span>
            </div>
        </div>

        <!-- MODAL NOVO DECK -->
        @if($modalCreateDeck)
            <div class="fixed inset-0 z-50 bg-black/75 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative border border-slate-200">
                    <button type="button" wire:click="$set('modalCreateDeck', false)" class="absolute top-5 right-5 text-slate-400 hover:text-slate-700">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>

                    <h3 class="text-lg font-black text-slate-900">Novo Deck</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Defina os parâmetros do seu novo deck.</p>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nome do Deck</label>
                            <input type="text" wire:model="newDeckName" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:outline-none" placeholder="Ex: Krenko, Mob Boss">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jogo TCG</label>
                            <select wire:model="newDeckGameId" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900">
                                @foreach($games as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Formato</label>
                            <select wire:model="newDeckFormat" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900">
                                <option value="commander">Commander / EDH</option>
                                <option value="constructed">Constructed</option>
                                <option value="standard">Standard</option>
                                <option value="modern">Modern</option>
                                <option value="pauper">Pauper</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="$set('modalCreateDeck', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500">Cancelar</button>
                        <button type="button" wire:click="saveNewDeck" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-orange-600 text-white text-xs font-black transition">
                            Criar Deck
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

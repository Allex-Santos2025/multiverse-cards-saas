<div class="w-full flex flex-col h-[calc(100vh-100px)] min-h-[750px] bg-slate-950 rounded-2xl border border-slate-800 shadow-2xl overflow-hidden select-none font-sans text-slate-200 relative"
     x-data="{
         hoveredCard: null,
         modalToken: false,
         tokenName: 'Soldado',
         tokenPt: '1/1',
         tokenLine: 'front',
         handleDrop(e, toZone) {
             e.preventDefault();
             try {
                 const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                 if (data && data.instanceId && data.fromZone) {
                     $wire.dropCard(parseInt(data.instanceId), data.fromZone, toZone);
                 }
             } catch (err) {}
         },
         handleAttach(e, targetId, targetZone) {
             e.preventDefault();
             e.stopPropagation();
             try {
                 const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                 if (data && data.instanceId && data.fromZone) {
                     $wire.attachCard(parseInt(data.instanceId), data.fromZone, targetId, targetZone);
                 }
             } catch (err) {}
         }
     }">
    
    <!-- 1. TOOLBAR SUPERIOR -->
    <div class="h-12 bg-slate-900/90 border-b border-slate-800 px-4 flex items-center justify-between text-xs font-semibold shrink-0">
        <div class="flex items-center gap-3">
            <button wire:click="closeSandbox" class="p-1.5 hover:bg-slate-800 text-slate-400 hover:text-white rounded-lg transition" title="Sair do Sandbox">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </button>
            <div class="h-4 w-px bg-slate-800"></div>
            <div class="flex items-center gap-2">
                <span class="text-white font-extrabold text-sm truncate max-w-[200px]">{{ $deck->name }}</span>
                <span class="text-[9px] uppercase px-2 py-0.5 bg-orange-950/60 text-orange-400 border border-orange-800/60 rounded font-black">{{ $deck->game->name ?? 'TCG' }}</span>
                <span class="text-[9px] uppercase px-1.5 py-0.5 bg-slate-800 text-slate-400 rounded border border-slate-700">{{ $deck->format }}</span>
            </div>
        </div>

        <!-- CONTADORES E AÇÕES RÁPIDAS -->
        <div class="flex items-center gap-3">
            <!-- Marcador de Vida -->
            <div class="flex items-center gap-1.5 bg-slate-950 px-2.5 py-1 rounded-lg border border-slate-800">
                <span class="text-[9px] text-slate-500 uppercase font-black tracking-wider">Vida</span>
                <button wire:click="adjustLife(-1)" class="w-5 h-5 rounded bg-slate-800 hover:bg-rose-600 text-slate-300 font-bold flex items-center justify-center transition">-</button>
                <span class="font-mono text-sm font-black px-1.5 {{ $life > 0 ? 'text-emerald-400' : 'text-rose-500' }}">{{ $life }}</span>
                <button wire:click="adjustLife(1)" class="w-5 h-5 rounded bg-slate-800 hover:bg-emerald-600 text-slate-300 font-bold flex items-center justify-center transition">+</button>
            </div>

            <!-- Contador de Turno -->
            <div class="flex items-center gap-1.5 bg-slate-950 px-2.5 py-1 rounded-lg border border-slate-800">
                <span class="text-[9px] text-slate-500 uppercase font-black tracking-wider">Turno</span>
                <span class="font-mono text-xs font-black text-amber-400">{{ $turn }}</span>
                <button wire:click="nextTurn" class="px-2 py-0.5 rounded bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-bold transition ml-1" title="Passa o turno, desvira permanentes e compra 1 carta">
                    Passar
                </button>
            </div>

            <div class="h-4 w-px bg-slate-800"></div>

            <!-- Ações Globais -->
            <div class="flex items-center gap-1.5">
                <button @click="modalToken = true" class="px-2.5 py-1 rounded-lg bg-amber-600/30 border border-amber-500/50 hover:bg-amber-600 text-amber-300 hover:text-white text-[11px] font-bold transition flex items-center gap-1">
                    <span>+</span> Ficha
                </button>
                <button wire:click="untapAll" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-bold transition">
                    Desvirar Tudo
                </button>
                <button wire:click="drawCards(1)" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-bold transition">
                    +1 Carta
                </button>
                <button wire:click="mulligan" wire:confirm="Embaralhar a mão de volta no grimório e comprar 7 novas cartas?" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-amber-600 text-slate-400 hover:text-white text-[11px] font-semibold transition">
                    Mulligan (7)
                </button>
                <button wire:click="restartGame" wire:confirm="Reiniciar o teste do zero?" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white text-[11px] font-semibold transition">
                    Reiniciar
                </button>
            </div>
        </div>
    </div>

    <!-- 2. ÁREA PRINCIPAL DO PLAYMAT + BARRA LATERAL -->
    <div class="flex-1 flex overflow-hidden relative">
        
        <!-- MESA DE JOGO -->
        <div class="flex-1 flex flex-col justify-between p-3 bg-[radial-gradient(ellipse_at_center,rgba(15,23,42,0.6)_0%,rgba(2,6,23,0.98)_100%)] overflow-hidden">
            
            <!-- CAMPO DE BATALHA DIVIDIDO EM 2 LINHAS -->
            <div class="flex-1 flex flex-col justify-between gap-3 border border-slate-800/80 rounded-xl p-3 bg-slate-900/20 overflow-y-auto">
                
                <!-- LINHA DA FRENTE (CRIATURAS / ATAQUE) -->
                <div class="flex-1 min-h-[180px] border-b border-dashed border-slate-800/60 pb-3 flex flex-col rounded-lg transition-colors"
                     x-data="{ isOver: false }"
                     :class="isOver ? 'bg-indigo-950/30 border-indigo-500/50' : ''"
                     x-on:dragover.prevent="isOver = true"
                     x-on:dragleave.prevent="isOver = false"
                     x-on:drop="isOver = false; handleDrop($event, 'front')">
                    
                    <div class="flex items-center justify-between mb-1.5 px-1">
                        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">
                            Linha de Frente (Criaturas / Ataque) [{{ count($battlefieldFront) }}]
                        </span>
                        <span class="text-[9px] text-slate-500">Arraste sobre uma carta para ATRELAR • Passe o mouse para contadores</span>
                    </div>

                    <div class="flex-1 flex flex-wrap content-start gap-5 p-2 rounded-lg min-h-[140px]">
                        @forelse($battlefieldFront as $card)
                            <div class="relative group select-none transition-all duration-200 cursor-grab active:cursor-grabbing {{ $card['is_tapped'] ? 'rotate-90 mx-6 my-4 shadow-indigo-500/30' : '' }}"
                                 draggable="true"
                                 @mouseenter="hoveredCard = '{{ $card['image'] }}'"
                                 @mouseleave="hoveredCard = null"
                                 x-on:dragstart="
                                    $event.dataTransfer.setData('text/plain', JSON.stringify({ instanceId: {{ $card['instance_id'] }}, fromZone: 'front' }));
                                    $event.dataTransfer.effectAllowed = 'move';
                                 "
                                 x-on:dragover.prevent
                                 x-on:drop.stop="handleAttach($event, {{ $card['instance_id'] }}, 'front')">
                                
                                <!-- ATRELADAS ATRÁS -->
                                @if(!empty($card['attached']))
                                    <div class="absolute -top-3 inset-x-0 flex flex-col items-center pointer-events-auto">
                                        @foreach($card['attached'] as $attIdx => $attCard)
                                            <div class="relative group/att -mb-[88px] hover:-translate-y-2 transition-transform cursor-pointer"
                                                 @mouseenter.stop="hoveredCard = '{{ $attCard['image'] }}'"
                                                 @mouseleave.stop="hoveredCard = null"
                                                 title="Atrelada: {{ $attCard['name'] }}">
                                                @if(!empty($attCard['image']))
                                                    <img src="{{ $attCard['image'] }}" class="h-[105px] w-auto max-w-[80px] object-cover rounded-md border border-indigo-400/80 shadow-md brightness-90">
                                                @else
                                                    <div class="h-[105px] w-[75px] rounded-md border border-amber-500/80 bg-slate-900 p-1 flex flex-col justify-between text-[8px] text-amber-200">
                                                        <span class="font-bold truncate">{{ $attCard['name'] }}</span>
                                                    </div>
                                                @endif
                                                <button type="button" 
                                                        wire:click.stop="detachCard({{ $card['instance_id'] }}, 'front', {{ $attIdx }})" 
                                                        class="absolute -top-1 -right-1 w-4 h-4 bg-slate-900 border border-slate-700 hover:bg-rose-600 text-white rounded-full flex items-center justify-center text-[8px] opacity-0 group-hover/att:opacity-100 transition shadow" 
                                                        title="Desanexar">
                                                    ×
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- CARTA OU TOKEN PRINCIPAL -->
                                <div class="relative z-10">
                                    @if(!empty($card['image']))
                                        <img src="{{ $card['image'] }}" 
                                             wire:click="toggleTap({{ $card['instance_id'] }}, 'front')"
                                             class="h-[130px] w-auto max-w-[95px] object-cover rounded-lg border {{ $card['is_tapped'] ? 'border-amber-400/90 shadow-md ring-1 ring-amber-400/40' : 'border-slate-700 shadow-md group-hover:border-indigo-400' }} select-none pointer-events-auto transition bg-slate-950" 
                                             alt="{{ $card['name'] }}" 
                                             title="{{ $card['name'] }} • Clique para Virar">
                                    @else
                                        <!-- Card Estilizado de Ficha Customizada -->
                                        <div wire:click="toggleTap({{ $card['instance_id'] }}, 'front')"
                                             class="h-[130px] w-[95px] rounded-lg border-2 {{ $card['is_tapped'] ? 'border-amber-400 ring-2 ring-amber-400/50' : 'border-amber-500/60' }} bg-gradient-to-b from-slate-900 via-slate-950 to-amber-950/40 p-2 flex flex-col justify-between shadow-lg cursor-pointer">
                                            <div class="flex items-center justify-between border-b border-amber-500/30 pb-1">
                                                <span class="text-[9px] font-black text-amber-400 truncate">{{ $card['name'] }}</span>
                                                <span class="text-[7px] uppercase px-1 rounded bg-amber-500/20 text-amber-300 font-bold">Ficha</span>
                                            </div>
                                            <div class="text-center my-auto">
                                                <span class="text-xl font-black text-slate-300 tracking-wider">{{ $card['custom_pt'] ?? '1/1' }}</span>
                                            </div>
                                            <div class="text-[7px] text-slate-500 text-center italic">Token em Jogo</div>
                                        </div>
                                    @endif

                                    <!-- Marcadores Numéricos -->
                                    <div class="absolute bottom-1 right-1 flex items-center bg-slate-950/90 border border-slate-700 rounded-md px-1 py-0.5 text-[9px] font-mono font-black z-20 shadow">
                                        <button type="button" 
                                                wire:click.stop="adjustCounters({{ $card['instance_id'] }}, 'front', -1)" 
                                                class="w-3.5 h-3.5 bg-slate-800 hover:bg-rose-600 text-white rounded flex items-center justify-center text-[8px] mr-1 opacity-0 group-hover:opacity-100 transition">-</button>
                                        <span class="{{ ($card['counters'] ?? 0) > 0 ? 'text-amber-400' : 'text-slate-400' }}">
                                            {{ $card['counters'] ?? 0 }}
                                        </span>
                                        <button type="button" 
                                                wire:click.stop="adjustCounters({{ $card['instance_id'] }}, 'front', 1)" 
                                                class="w-3.5 h-3.5 bg-slate-800 hover:bg-emerald-600 text-white rounded flex items-center justify-center text-[8px] ml-1 opacity-0 group-hover:opacity-100 transition">+</button>
                                    </div>

                                    @if(!empty($card['is_token']))
                                        <span class="absolute top-1 left-1 px-1 py-0.2 rounded bg-amber-600/90 text-[7px] font-black text-white z-20 shadow">
                                            TOKEN
                                        </span>
                                    @elseif(!empty($card['attached']))
                                        <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-indigo-900/90 border border-indigo-500/80 text-[8px] font-black text-indigo-200 z-20 shadow">
                                            +{{ count($card['attached']) }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Ações Rápidas -->
                                <div class="absolute -top-2 -right-2 flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition z-30 pointer-events-auto">
                                    <button type="button" 
                                            wire:click.stop="moveBetweenLines({{ $card['instance_id'] }}, 'front')" 
                                            class="w-4 h-4 bg-slate-900 border border-slate-700 hover:bg-indigo-600 text-white rounded-full flex items-center justify-center text-[8px] shadow" 
                                            title="Mover de Linha">
                                        ↕
                                    </button>
                                    <button type="button" 
                                            wire:click.stop="returnToHand({{ $card['instance_id'] }}, 'front')" 
                                            class="w-4 h-4 bg-slate-900 border border-slate-700 hover:bg-emerald-600 text-white rounded-full flex items-center justify-center text-[8px] shadow" 
                                            title="Mão">
                                        ↩
                                    </button>
                                    <button type="button" 
                                            wire:click.stop="sendToGraveyard({{ $card['instance_id'] }}, 'front')" 
                                            class="w-4 h-4 bg-slate-900 border border-slate-700 hover:bg-rose-600 text-white rounded-full flex items-center justify-center text-[8px] shadow" 
                                            title="Destruir / Cemitério">
                                        ×
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="w-full h-full flex items-center justify-center text-[11px] text-slate-600 italic">
                                Linha de Frente vazia. Baixe cartas ou gere uma Ficha no botão superior.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- LINHA DE APOIO (TERRENOS / ARTEFATOS / SUPORTE) -->
                <div class="h-[175px] pt-1 flex flex-col rounded-lg transition-colors"
                     x-data="{ isOver: false }"
                     :class="isOver ? 'bg-emerald-950/20 border-emerald-500/40' : ''"
                     x-on:dragover.prevent="isOver = true"
                     x-on:dragleave.prevent="isOver = false"
                     x-on:drop="isOver = false; handleDrop($event, 'back')">
                    
                    <div class="flex items-center justify-between mb-1.5 px-1">
                        <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">
                            Linha de Apoio (Terrenos & Recursos) [{{ count($battlefieldBack) }}]
                        </span>
                        <span class="text-[9px] text-slate-500">Passe o mouse para ler • Clique para Virar • Arraste para mover</span>
                    </div>

                    <div class="flex-1 border border-dashed border-slate-800/40 rounded-lg flex items-center px-3 gap-5 overflow-x-auto bg-slate-950/30">
                        @forelse($battlefieldBack as $card)
                            <div class="relative group select-none transition-all duration-200 cursor-grab active:cursor-grabbing {{ $card['is_tapped'] ? 'rotate-90 mx-6 my-4 shadow-emerald-500/30' : '' }}"
                                 draggable="true"
                                 @mouseenter="hoveredCard = '{{ $card['image'] }}'"
                                 @mouseleave="hoveredCard = null"
                                 x-on:dragstart="
                                    $event.dataTransfer.setData('text/plain', JSON.stringify({ instanceId: {{ $card['instance_id'] }}, fromZone: 'back' }));
                                    $event.dataTransfer.effectAllowed = 'move';
                                 "
                                 x-on:dragover.prevent
                                 x-on:drop.stop="handleAttach($event, {{ $card['instance_id'] }}, 'back')">
                                
                                @if(!empty($card['attached']))
                                    <div class="absolute -top-3 inset-x-0 flex flex-col items-center pointer-events-auto">
                                        @foreach($card['attached'] as $attIdx => $attCard)
                                            <div class="relative group/att -mb-[88px] hover:-translate-y-2 transition-transform cursor-pointer"
                                                 @mouseenter.stop="hoveredCard = '{{ $attCard['image'] }}'"
                                                 @mouseleave.stop="hoveredCard = null">
                                                <img src="{{ $attCard['image'] }}" class="h-[105px] w-auto max-w-[80px] object-cover rounded-md border border-emerald-400/80 shadow-md brightness-90">
                                                <button type="button" 
                                                        wire:click.stop="detachCard({{ $card['instance_id'] }}, 'back', {{ $attIdx }})" 
                                                        class="absolute -top-1 -right-1 w-4 h-4 bg-slate-900 border border-slate-700 hover:bg-rose-600 text-white rounded-full flex items-center justify-center text-[8px] opacity-0 group-hover/att:opacity-100 transition shadow">
                                                    ×
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="relative z-10">
                                    <img src="{{ $card['image'] }}" 
                                         wire:click="toggleTap({{ $card['instance_id'] }}, 'back')"
                                         class="h-[130px] w-auto max-w-[95px] object-cover rounded-lg border {{ $card['is_tapped'] ? 'border-amber-400/90 shadow-md ring-1 ring-amber-400/40' : 'border-slate-700 shadow-md group-hover:border-indigo-400' }} select-none pointer-events-auto transition bg-slate-950" 
                                         alt="{{ $card['name'] }}" 
                                         title="{{ $card['name'] }} • Clique para Virar">

                                    <div class="absolute bottom-1 right-1 flex items-center bg-slate-950/90 border border-slate-700 rounded-md px-1 py-0.5 text-[9px] font-mono font-black z-20 shadow">
                                        <button type="button" 
                                                wire:click.stop="adjustCounters({{ $card['instance_id'] }}, 'back', -1)" 
                                                class="w-3.5 h-3.5 bg-slate-800 hover:bg-rose-600 text-white rounded flex items-center justify-center text-[8px] mr-1 opacity-0 group-hover:opacity-100 transition">-</button>
                                        <span class="{{ ($card['counters'] ?? 0) > 0 ? 'text-amber-400' : 'text-slate-400' }}">
                                            {{ $card['counters'] ?? 0 }}
                                        </span>
                                        <button type="button" 
                                                wire:click.stop="adjustCounters({{ $card['instance_id'] }}, 'back', 1)" 
                                                class="w-3.5 h-3.5 bg-slate-800 hover:bg-emerald-600 text-white rounded flex items-center justify-center text-[8px] ml-1 opacity-0 group-hover:opacity-100 transition">+</button>
                                    </div>

                                    @if(!empty($card['attached']))
                                        <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-emerald-900/90 border border-emerald-500/80 text-[8px] font-black text-emerald-200 z-20 shadow">
                                            +{{ count($card['attached']) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute -top-2 -right-2 flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition z-30 pointer-events-auto">
                                    <button type="button" 
                                            wire:click.stop="moveBetweenLines({{ $card['instance_id'] }}, 'back')" 
                                            class="w-4 h-4 bg-slate-900 border border-slate-700 hover:bg-indigo-600 text-white rounded-full flex items-center justify-center text-[8px] shadow" 
                                            title="Mover para Linha de Frente">
                                        ↕
                                    </button>
                                    <button type="button" 
                                            wire:click.stop="returnToHand({{ $card['instance_id'] }}, 'back')" 
                                            class="w-4 h-4 bg-slate-900 border border-slate-700 hover:bg-emerald-600 text-white rounded-full flex items-center justify-center text-[8px] shadow" 
                                            title="Retornar para a Mão">
                                        ↩
                                    </button>
                                    <button type="button" 
                                            wire:click.stop="sendToGraveyard({{ $card['instance_id'] }}, 'back')" 
                                            class="w-4 h-4 bg-slate-900 border border-slate-700 hover:bg-rose-600 text-white rounded-full flex items-center justify-center text-[8px] shadow" 
                                            title="Cemitério">
                                        ×
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="w-full text-center text-[10px] text-slate-600 italic">
                                Arraste terrenos para cá para gerir os recursos.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- 3. MÃO DO JOGADOR -->
            <div class="mt-2.5 pt-2.5 border-t border-slate-800/80 relative rounded-lg transition-colors"
                 x-data="{ isOverHand: false }"
                 :class="isOverHand ? 'bg-slate-900/60 ring-1 ring-emerald-500/40' : ''"
                 x-on:dragover.prevent="isOverHand = true"
                 x-on:dragleave.prevent="isOverHand = false"
                 x-on:drop="isOverHand = false; handleDrop($event, 'hand')">
                
                <div class="flex items-center justify-between mb-1.5 px-1">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Sua Mão ({{ count($hand) }} cartas)
                    </span>
                    <span class="text-[9px] text-slate-500">Passe o mouse para ler • Arraste para o campo ou solte sobre uma carta para atrelar</span>
                </div>

                <div class="flex items-center gap-2.5 overflow-x-auto pb-1.5 max-h-[135px]">
                    @forelse($hand as $card)
                        <div class="shrink-0 relative group cursor-grab active:cursor-grabbing transition-transform hover:-translate-y-1 select-none"
                             draggable="true"
                             x-on:dragstart="
                                $event.dataTransfer.setData('text/plain', JSON.stringify({ instanceId: {{ $card['instance_id'] }}, fromZone: 'hand' }));
                                $event.dataTransfer.effectAllowed = 'move';
                             "
                             @mouseenter="hoveredCard = '{{ $card['image'] }}'"
                             @mouseleave="hoveredCard = null">
                            
                            <img src="{{ $card['image'] }}" 
                                 wire:click="playCard({{ $card['instance_id'] }})"
                                 class="h-[110px] w-auto max-w-[80px] object-cover rounded-md border border-slate-700 shadow-md group-hover:border-indigo-400 select-none pointer-events-auto transition" 
                                 alt="{{ $card['name'] }}">

                            <div class="absolute -top-2 left-1/2 -translate-x-1/2 flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition z-20 pointer-events-auto">
                                <button type="button" 
                                        wire:click.stop="playCard({{ $card['instance_id'] }}, 'front')" 
                                        class="px-1.5 py-0.5 bg-slate-900 border border-slate-700 hover:bg-indigo-600 text-white rounded text-[8px] font-bold shadow" 
                                        title="Baixar na Linha de Frente">
                                    Frente
                                </button>
                                <button type="button" 
                                        wire:click.stop="playCard({{ $card['instance_id'] }}, 'back')" 
                                        class="px-1.5 py-0.5 bg-slate-900 border border-slate-700 hover:bg-emerald-600 text-white rounded text-[8px] font-bold shadow" 
                                        title="Baixar na Linha de Apoio">
                                    Apoio
                                </button>
                                <button type="button" 
                                        wire:click.stop="sendToGraveyard({{ $card['instance_id'] }}, 'hand')" 
                                        class="w-4 h-4 bg-slate-900 border border-slate-700 hover:bg-rose-600 text-white rounded-full flex items-center justify-center text-[8px] shadow" 
                                        title="Descartar">
                                    ×
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-[11px] text-slate-600 italic py-4">Mão vazia. Compre cartas através da Biblioteca.</div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- 4. PAINEL LATERAL DIREITO ORIGINAL (ORDEM SAGRADA VERTICAL: BIBLIOTECA -> CEMITÉRIO -> EXÍLIO) -->
        <div class="w-56 bg-slate-900/90 border-l border-slate-800 p-3 flex flex-col justify-between shrink-0 overflow-y-auto">
            
            <!-- BIBLIOTECA (MONTE CLÁSSICO DE CARTA VIRADA PARA BAIXO) -->
            <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800 text-center flex flex-col items-center">
                <span class="text-[9px] font-black uppercase text-slate-400 block tracking-wider mb-2">
                    Biblioteca ({{ count($library) }})
                </span>
                
                <!-- Monte representando a carta em proporção real em pé -->
                <div wire:click="drawCards(1)" 
                     class="w-20 h-28 rounded-lg bg-gradient-to-br from-indigo-950 via-slate-900 to-indigo-900 border-2 border-indigo-700/60 shadow-lg flex flex-col items-center justify-center cursor-pointer hover:border-indigo-400 hover:scale-102 transition group relative" 
                     title="Clique para comprar 1 carta do topo">
                    <svg class="w-5 h-5 text-indigo-400 group-hover:text-white transition mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span class="text-[9px] font-bold text-indigo-300 group-hover:text-white">COMPRAR</span>
                </div>

                <div class="mt-2.5 flex gap-1 w-full">
                    <button wire:click="shuffleLibrary" class="flex-1 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[9px] font-bold transition">
                        Embaralhar
                    </button>
                    <button wire:click="drawCards(1)" class="px-2 py-1 rounded bg-indigo-600/80 hover:bg-indigo-600 text-white text-[9px] font-bold transition">
                        +1
                    </button>
                </div>
            </div>

            <!-- TOKENS OFICIAIS DO DECK (SE EXISTIREM, LISTA COMPACTA RETRÁTIL SEM ROUBAR O CEMITÉRIO) -->
            @if(!empty($availableTokens))
                <div class="bg-slate-950 p-2 rounded-xl border border-amber-900/40 text-center">
                    <span class="text-[8px] font-black uppercase text-amber-400 block tracking-wider mb-1">Fichas do Deck</span>
                    <div class="flex gap-1.5 overflow-x-auto pb-0.5 justify-center">
                        @foreach($availableTokens as $tokIdx => $tok)
                            <div wire:click="spawnOfficialToken({{ $tokIdx }}, 'front')" 
                                 class="shrink-0 relative cursor-pointer hover:scale-105 transition"
                                 title="Colocar {{ $tok['name'] }} na mesa">
                                <img src="{{ $tok['image'] }}" class="h-12 w-auto max-w-[36px] object-cover rounded border border-amber-500/50">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- CEMITÉRIO (GRAVEYARD ORIGINAL) -->
            <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800 text-center transition-colors"
                 x-data="{ isOverGrave: false }"
                 :class="isOverGrave ? 'ring-2 ring-rose-500/80 bg-rose-950/40' : ''"
                 x-on:dragover.prevent="isOverGrave = true"
                 x-on:dragleave.prevent="isOverGrave = false"
                 x-on:drop="isOverGrave = false; handleDrop($event, 'graveyard')">
                
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Cemitério</span>
                    <span class="text-[11px] font-mono font-bold text-slate-300">{{ count($graveyard) }}</span>
                </div>

                @if(!empty($graveyard))
                    @php $topGraveyard = end($graveyard); @endphp
                    <div class="w-18 h-24 mx-auto rounded-md overflow-hidden border border-slate-700 cursor-pointer"
                         @mouseenter="hoveredCard = '{{ $topGraveyard['image'] }}'"
                         @mouseleave="hoveredCard = null"
                         title="Última carta no cemitério: {{ $topGraveyard['name'] }}">
                        <img src="{{ $topGraveyard['image'] }}" class="w-full h-full object-cover grayscale opacity-75" alt="Cemitério">
                    </div>
                @else
                    <div class="w-18 h-24 mx-auto rounded-md border border-dashed border-slate-800 flex items-center justify-center text-[9px] text-slate-600">
                        Solte aqui
                    </div>
                @endif
            </div>

            <!-- EXÍLIO (EXILE ORIGINAL) -->
            <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800 text-center transition-colors"
                 x-data="{ isOverExile: false }"
                 :class="isOverExile ? 'ring-2 ring-purple-500/80 bg-purple-950/40' : ''"
                 x-on:dragover.prevent="isOverExile = true"
                 x-on:dragleave.prevent="isOverExile = false"
                 x-on:drop="isOverExile = false; handleDrop($event, 'exile')">
                
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Exílio</span>
                    <span class="text-[11px] font-mono font-bold text-slate-300">{{ count($exile) }}</span>
                </div>

                @if(!empty($exile))
                    @php $topExile = end($exile); @endphp
                    <div class="w-18 h-24 mx-auto rounded-md overflow-hidden border border-slate-700 cursor-pointer"
                         @mouseenter="hoveredCard = '{{ $topExile['image'] }}'"
                         @mouseleave="hoveredCard = null"
                         title="Última carta no exílio: {{ $topExile['name'] }}">
                        <img src="{{ $topExile['image'] }}" class="w-full h-full object-cover opacity-60" alt="Exílio">
                    </div>
                @else
                    <div class="w-18 h-24 mx-auto rounded-md border border-dashed border-slate-800 flex items-center justify-center text-[9px] text-slate-600">
                        Solte aqui
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- 5. PREVIEW DE LEITURA FIXO GLOBAL -->
    <div x-show="hoveredCard" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute bottom-4 left-4 z-50 pointer-events-none drop-shadow-[0_25px_30px_rgba(0,0,0,0.95)]"
         style="display: none;">
        <img :src="hoveredCard" class="w-[260px] h-auto rounded-xl border-2 border-indigo-500/90 bg-slate-950 shadow-2xl">
    </div>

    <!-- 6. MODAL LEVE: CRIADOR RÁPIDO DE FICHAS / TOKENS -->
    <div x-show="modalToken" 
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-xs p-4"
         style="display: none;">
        <div @click.away="modalToken = false" 
             class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-sm p-5 shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-black text-amber-400 uppercase tracking-wider flex items-center gap-2">
                    <span>🪙</span> Criar Ficha / Token
                </h3>
                <button @click="modalToken = false" class="text-slate-400 hover:text-white text-lg">×</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-400 font-bold mb-1">Nome / Tipo</label>
                    <input type="text" x-model="tokenName" placeholder="Ex: Soldado, Zumbi, Tesouro" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white font-semibold focus:outline-none focus:border-amber-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-bold mb-1">Poder / Resistência</label>
                        <input type="text" x-model="tokenPt" placeholder="1/1" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white font-mono font-bold text-center focus:outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold mb-1">Linha de Entrada</label>
                        <select x-model="tokenLine" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-2 py-2 text-white font-semibold focus:outline-none focus:border-amber-500">
                            <option value="front">Linha de Frente</option>
                            <option value="back">Linha de Apoio</option>
                        </select>
                    </div>
                </div>

                <div>
                    <span class="block text-[10px] text-slate-500 font-black uppercase mb-1">Atalhos rápidos:</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" @click="tokenName='Soldado'; tokenPt='1/1'" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 rounded text-[10px] font-bold text-slate-300">Soldado 1/1</button>
                        <button type="button" @click="tokenName='Zumbi'; tokenPt='2/2'" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 rounded text-[10px] font-bold text-slate-300">Zumbi 2/2</button>
                        <button type="button" @click="tokenName='Tesouro'; tokenPt='0/0'; tokenLine='back'" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 rounded text-[10px] font-bold text-slate-300">Tesouro</button>
                        <button type="button" @click="tokenName='Inseto'; tokenPt='1/1'" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 rounded text-[10px] font-bold text-slate-300">Inseto 1/1</button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                <button type="button" @click="modalToken = false" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold text-xs">
                    Cancelar
                </button>
                <button type="button" 
                        @click="$wire.spawnCustomToken(tokenName, tokenPt, tokenLine); modalToken = false" 
                        class="px-4 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-lg shadow-amber-600/30">
                    Gerar Ficha
                </button>
            </div>

        </div>
    </div>
</div>

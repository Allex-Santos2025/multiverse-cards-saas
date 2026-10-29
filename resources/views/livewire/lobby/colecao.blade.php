<div class="space-y-8">
    @if($activeSetId)
        <livewire:lobby.player-set-page :setId="$activeSetId" :language="$activeSetLang" :isFoil="$activeSetIsFoil" wire:key="set-page-{{ $activeSetId }}-{{ $activeSetLang }}-{{ (int)$activeSetIsFoil }}" />
    @elseif($activeBinderSlug)
        <livewire:lobby.fichario-viewer :slug="$activeBinderSlug" wire:key="viewer-{{ $activeBinderSlug }}" />
    @else

        {{-- SESSÃO 1: MEUS FICHÁRIOS --}}
        <section>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Meus Fichários</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Organize suas cartas fisicamente em pastas com capacidade e formato definidos na fabricação.</p>
                </div>

                <div class="flex items-center gap-3">
                    @if($totalBindersCount > 3)
                        <span class="text-xs font-bold text-slate-500">Total: {{ $totalBindersCount }} pastas</span>
                    @endif
                    <button wire:click="openCreateBinderModal" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-orange-500 text-white text-xs font-black flex items-center gap-2 shadow-sm transition">
                        <i class="ph-bold ph-plus"></i> NOVO FICHÁRIO
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @for($slotIndex = 0; $slotIndex < 3; $slotIndex++)
                    @php $binder = $binders->get($slotIndex); @endphp

                    @if($binder)
                        @php
                            $isBulk = $binder->pocket_layout === 'bulk';
                            $capacity = $binder->current_capacity;
                            $stored = (int) ($binder->total_cards_stored ?? 0);
                            $percent = (!$isBulk && $capacity > 0) ? min(100, round(($stored / $capacity) * 100)) : 0;
                            
                            $coverBg = match($binder->cover_material) {
                                'cover-blue' => 'bg-gradient-to-br from-blue-700 via-blue-900 to-slate-950 border border-blue-500',
                                'cover-box'  => 'bg-gradient-to-br from-amber-800 via-amber-950 to-stone-950 border border-amber-600',
                                default      => 'bg-gradient-to-br from-slate-800 via-slate-900 to-black border border-slate-700',
                            };

                            $strapBg = match($binder->strap_color) {
                                'cyan' => 'bg-sky-500 shadow-[1px_0_3px_rgba(0,0,0,0.5),-1px_0_3px_rgba(0,0,0,0.5)]',
                                'gold' => 'bg-amber-500 shadow-[1px_0_3px_rgba(0,0,0,0.5),-1px_0_3px_rgba(0,0,0,0.5)]',
                                default => 'bg-orange-500 shadow-[1px_0_3px_rgba(0,0,0,0.5),-1px_0_3px_rgba(0,0,0,0.5)]',
                            };
                        @endphp

                        <div wire:click="openBinder('{{ $binder->slug }}')" class="bg-white rounded-2xl p-4 md:p-5 border border-slate-200 shadow-sm flex flex-col justify-between cursor-pointer relative overflow-hidden group hover:border-slate-300 hover:-translate-y-0.5 hover:shadow-md transition duration-200">
                            <div class="flex gap-4 items-start pl-1">
                                
                                <div class="w-[86px] h-[116px] rounded-[10px] relative shadow-[-4px_6px_14px_rgba(0,0,0,0.35)] shrink-0 overflow-hidden transition-transform duration-300 group-hover:-translate-y-1 group-hover:scale-[1.03] {{ $coverBg }}">
                                    @if($binder->cover_image_path)
                                        <img src="{{ $binder->cover_image_path }}" 
                                             onerror="this.style.display='none'" 
                                             class="absolute inset-0 w-full h-full object-cover opacity-60">
                                    @endif
                                    
                                    <div class="absolute inset-y-0 left-0 w-3 bg-gradient-to-r from-black/80 via-white/[0.08] to-black/60 border-r border-black/50"></div>
                                    <div class="absolute inset-1 border border-dashed border-white/20 rounded-[7px] pointer-events-none"></div>
                                    <div class="absolute top-1 right-1 w-2 h-2 border-t border-r border-amber-500 pointer-events-none"></div>
                                    <div class="absolute bottom-1 right-1 w-2 h-2 border-b border-r border-amber-500 pointer-events-none"></div>
                                    <div class="absolute inset-y-0 right-3 w-[7px] {{ $strapBg }}"></div>
                                    
                                    <div class="absolute inset-0 flex flex-col items-center justify-center p-1 text-center pointer-events-none">
                                        <span class="text-[8px] font-black tracking-widest uppercase text-white bg-black/70 px-1 py-0.5 rounded border border-white/20">
                                            {{ $binder->pocket_layout }}
                                        </span>
                                        <span class="text-[7px] text-slate-200 font-bold mt-1 line-clamp-2 drop-shadow">
                                            {{ Str::limit($binder->name, 14) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ strtoupper($binder->pocket_layout) }}
                                        </span>

                                        @if($binder->is_locked_sheets)
                                            <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                                                <i class="ph-bold ph-lock-key"></i> Fixo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                                <i class="ph-bold ph-lock-key-open"></i> Expansível
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="text-sm font-extrabold text-slate-900 truncate group-hover:text-orange-600 transition">
                                        {{ $binder->name }}
                                    </h3>
                                    <p class="text-[11px] text-slate-400 truncate">{{ $binder->description ?: 'Pasta do Acervo' }}</p>

                                    <div class="mt-2.5 text-[10px] text-slate-500 font-medium">
                                        <span class="text-slate-400 block">Capacidade de Fábrica:</span>
                                        <strong class="text-slate-700 font-bold">
                                            {{ $isBulk ? 'Ilimitada' : "{$binder->factory_sheets_count} folhas ({$binder->factory_capacity} bolsos)" }}
                                        </strong>
                                    </div>

                                    <div class="mt-2">
                                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                                            <span class="text-slate-400 uppercase text-[9px]">Lotação</span>
                                            <span class="text-slate-700 font-mono font-extrabold">
                                                {{ $stored }} / {{ $isBulk ? '∞' : $capacity }}
                                            </span>
                                        </div>
                                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full {{ $percent >= 100 ? 'bg-rose-500' : 'bg-emerald-500' }} rounded-full" style="width: {{ $isBulk ? 50 : $percent }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @php
                                $playerUser = auth('player')->user();
                                $userHandle = $playerUser?->nickname ?: \Illuminate\Support\Str::slug($playerUser?->name ?? 'player');
                                $baseUrl = rtrim(config('app.url', 'https://versustcg.com.br'), '/');
                                $shareUrl = "{$baseUrl}/fichario/{$userHandle}/{$binder->slug}";
                                $waText = urlencode("Confira meu fichário virtual no Versus TCG: " . $shareUrl);
                            @endphp
                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-100 text-xs" x-data="{ copied: false }">
                                <div class="flex items-center gap-1">
                                    <button type="button" wire:click.stop="openEditBinder({{ $binder->id }})" class="text-slate-400 hover:text-slate-800 transition p-1" title="Gerenciar Fichário">
                                        <i class="ph-bold ph-gear text-base"></i>
                                    </button>
                                    <a href="https://api.whatsapp.com/send?text={{ $waText }}" target="_blank" @click.stop class="text-emerald-500 hover:text-emerald-600 transition p-1" title="Compartilhar no WhatsApp">
                                        <i class="ph-bold ph-whatsapp-logo text-base"></i>
                                    </a>
                                    <button type="button" @click.stop="navigator.clipboard.writeText('{{ $shareUrl }}'); copied = true; setTimeout(() => copied = false, 2000)" class="text-slate-400 hover:text-orange-500 transition p-1 relative" title="Copiar Link">
                                        <i class="ph-bold" :class="copied ? 'ph-check text-emerald-500' : 'ph-link text-base'"></i>
                                    </button>
                                </div>
                                <span class="font-extrabold text-slate-800 group-hover:text-orange-600 flex items-center gap-1 transition">
                                    Abrir Pasta <i class="ph-bold ph-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    @else
                        <div wire:click="openCreateBinderModal" class="bg-white/70 hover:bg-white rounded-2xl p-4 md:p-5 border-2 border-dashed border-slate-200 hover:border-orange-300 shadow-sm flex flex-col justify-between cursor-pointer group transition">
                            <div class="flex gap-4 items-start pl-1">
                                <div class="w-[86px] h-[116px] rounded-xl border border-dashed border-slate-300 group-hover:border-orange-400 bg-slate-50/80 group-hover:bg-orange-50/50 flex flex-col items-center justify-center transition shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-white group-hover:bg-orange-500 text-slate-400 group-hover:text-white shadow-sm flex items-center justify-center transition">
                                        <i class="ph-bold ph-plus text-sm"></i>
                                    </div>
                                    <span class="text-[8px] font-black uppercase text-slate-400 group-hover:text-orange-600 mt-2">Criar</span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-100 text-slate-400">
                                            Disponível
                                        </span>
                                    </div>

                                    <h3 class="text-sm font-extrabold text-slate-400 group-hover:text-slate-700 transition">
                                        Novo Fichário
                                    </h3>
                                    <p class="text-[11px] text-slate-400 truncate">Clique para configurar</p>

                                    <div class="mt-2.5 text-[10px] text-slate-400 font-medium">
                                        <span class="block">Capacidade:</span>
                                        <strong class="font-normal text-slate-400">Personalizável</strong>
                                    </div>

                                    <div class="mt-2">
                                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                                            <span class="text-slate-300 uppercase text-[9px]">Lotação</span>
                                            <span class="text-slate-400 font-mono font-semibold">0 / 0</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-slate-200 rounded-full" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-100 text-xs">
                                <span class="text-slate-300">
                                    <i class="ph-bold ph-folder-plus text-base"></i>
                                </span>
                                <span class="font-extrabold text-slate-400 group-hover:text-orange-600 flex items-center gap-1 transition">
                                    Adicionar Pasta <i class="ph-bold ph-plus"></i>
                                </span>
                            </div>
                        </div>
                    @endif
                @endfor
            </div>
        </section>

        {{-- SESSÃO 2: COMPLETAR EDIÇÕES --}}
        <section class="bg-white rounded-2xl p-6 md:p-8 border border-slate-200 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Completar Edições</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Acompanhe seu progresso e valorização para fechar os sets completos (Regular, Foil e Variantes).</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button wire:click="openAddCardModal" class="px-3.5 py-1.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-black flex items-center gap-1.5 shadow-sm transition">
                        <i class="ph-bold ph-plus"></i> CADASTRAR CARTA
                    </button>

                    @if($availableGames->count() > 1)
                        <div class="relative">
                            <select wire:model.live="selectedGameId" class="appearance-none bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold py-2.5 pl-3.5 pr-8 rounded-xl focus:outline-none cursor-pointer">
                                <option value="">Todos os Meus Jogos ({{ $availableGames->count() }})</option>
                                @foreach($availableGames as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                                <i class="ph-bold ph-caret-down text-xs"></i>
                            </div>
                        </div>
                    @elseif($availableGames->count() === 1)
                        <div class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ $availableGames->first()->name }}
                        </div>
                    @endif

                    <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200">
                        <button wire:click="setMeta('1x')" class="px-3.5 py-1.5 rounded-lg text-xs font-extrabold transition {{ $metaColecao === '1x' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            Meta 1x (Singles)
                        </button>
                        <button wire:click="setMeta('4x')" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $metaColecao === '4x' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            Meta 4x (Playsets)
                        </button>
                    </div>
                </div>
            </div>

            @if($completedSets->isEmpty())
                <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-8 text-center my-6">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center mb-3">
                        <i class="ph-bold ph-cards text-2xl"></i>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-800">Sua coleção está vazia</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Adicione cartas ao seu acervo para acompanhar a porcentagem de conclusão por edição, idioma e acabamento.
                    </p>
                    <button wire:click="openAddCardModal" class="mt-4 px-4 py-2 rounded-xl bg-slate-900 hover:bg-orange-600 text-white text-xs font-black transition inline-flex items-center gap-1.5 shadow-sm">
                        <i class="ph-bold ph-plus"></i> Adicionar Minha Primeira Carta
                    </button>
                </div>
            @else
                <div class="mt-6 space-y-4">
                    @foreach($completedSets as $item)

                        {{-- REGULAR --}}
                        @if($item['regular']['has_cards'])
                            @php
                                $r = $item['regular'];
                                $is1x = ($metaColecao === '1x');
                                $count = $is1x ? $r['unique_count'] : $r['total_copies'];
                                $target = $is1x ? $r['total_target_1x'] : $r['total_target_4x'];
                                $pct = $is1x ? $r['pct_1x'] : $r['pct_4x'];
                                $patrimonio = $is1x ? $r['patrimonio_1x'] : $r['patrimonio_4x'];
                                $missing = $is1x ? $r['missing_1x'] : $r['missing_4x'];
                                $cost = $is1x ? $r['cost_to_finish_1x'] : $r['cost_to_finish_4x'];
                            @endphp
                            <div class="p-4 md:p-5 rounded-2xl bg-white border border-slate-200 hover:border-slate-300 transition shadow-sm space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center font-extrabold text-[10px] text-slate-700">
                                            {{ $item['set_code'] }}
                                        </span>
                                        <h4 class="text-sm font-extrabold text-slate-900">
                                            {{ $item['set_name'] }}
                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-slate-800 text-white">
                                                {{ $item['language'] }}
                                            </span>
                                            <span class="text-blue-600 font-bold ml-1.5">&bull; Regular</span>
                                        </h4>
                                    </div>

                                    <div class="flex items-center gap-4 text-xs">
                                        <span class="text-slate-500 font-semibold">
                                            Cards regulares: <strong class="text-slate-900 font-mono">{{ $count }} de {{ $target }}</strong>
                                        </span>
                                        <button wire:click="openSetPage({{ $item['set_id'] }}, '{{ $item['language'] }}', false)" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-orange-600 text-white text-xs font-black transition shadow-sm flex items-center gap-1.5 shrink-0">
                                            <span>Ver Coleção</span>
                                            <i class="ph-bold ph-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-extrabold text-blue-600 font-mono w-8">{{ $pct }}%</span>
                                    <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 pt-2 text-[11px] border-t border-slate-100">
                                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                        <span class="text-slate-400 block text-[9px] uppercase font-bold">Patrimônio Obtido (Méd)</span>
                                        <span class="text-emerald-600 font-extrabold font-mono text-xs">R$ {{ number_format($patrimonio, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                        <span class="text-slate-400 block text-[9px] uppercase font-bold">Faltam para fechar ({{ $metaColecao }})</span>
                                        <span class="text-slate-700 font-extrabold font-mono text-xs">{{ $missing }} cartas</span>
                                    </div>
                                    <div class="bg-rose-50/70 p-2.5 rounded-xl border border-rose-100">
                                        <span class="text-rose-500 block text-[9px] uppercase font-bold">Custo Est. p/ fechar ({{ $metaColecao }})</span>
                                        <span class="text-rose-700 font-extrabold font-mono text-xs">R$ {{ number_format($cost, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                        <span class="text-slate-400 block text-[9px] uppercase font-bold">Total do Set Oficial</span>
                                        <span class="text-slate-700 font-extrabold font-mono text-xs">{{ $target }} cartas</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- FOIL --}}
                        @if($item['foil']['has_cards'])
                            @php
                                $f = $item['foil'];
                                $is1x = ($metaColecao === '1x');
                                $countFoil = $is1x ? $f['unique_count'] : $f['total_copies'];
                                $targetFoil = $is1x ? $f['total_target_1x'] : $f['total_target_4x'];
                                $pctFoil = $is1x ? $f['pct_1x'] : $f['pct_4x'];
                                $patrimonioFoil = $is1x ? $f['patrimonio_1x'] : $f['patrimonio_4x'];
                                $missingFoil = $is1x ? $f['missing_1x'] : $f['missing_4x'];
                                $costFoil = $is1x ? $f['cost_to_finish_1x'] : $f['cost_to_finish_4x'];
                            @endphp
                            <div class="p-4 md:p-5 rounded-2xl bg-white border border-slate-200 hover:border-slate-300 transition shadow-sm space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center font-extrabold text-[10px] text-slate-700">
                                            {{ $item['set_code'] }}
                                        </span>
                                        <h4 class="text-sm font-extrabold text-slate-900">
                                            {{ $item['set_name'] }}
                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-slate-800 text-white">
                                                {{ $item['language'] }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-[9px] ml-1 border border-amber-300 font-extrabold text-slate-900 bg-gradient-to-r from-amber-200 via-pink-300 to-sky-300">Foil</span>
                                        </h4>
                                    </div>

                                    <div class="flex items-center gap-4 text-xs">
                                        <span class="text-slate-500 font-semibold">
                                            Cards foils: <strong class="text-slate-900 font-mono">{{ $countFoil }} de {{ $targetFoil }}</strong>
                                        </span>
                                        <button wire:click="openSetPage({{ $item['set_id'] }}, '{{ $item['language'] }}', true)" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-orange-600 text-white text-xs font-black transition shadow-sm flex items-center gap-1.5 shrink-0">
                                            <span>Ver Coleção</span>
                                            <i class="ph-bold ph-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-extrabold text-amber-500 font-mono w-8">{{ $pctFoil }}%</span>
                                    <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-amber-400 to-rose-400 rounded-full" style="width: {{ $pctFoil }}%"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 pt-2 text-[11px] border-t border-slate-100">
                                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                        <span class="text-slate-400 block text-[9px] uppercase font-bold">Patrimônio Foil (Méd)</span>
                                        <span class="text-emerald-600 font-extrabold font-mono text-xs">R$ {{ number_format($patrimonioFoil, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                        <span class="text-slate-400 block text-[9px] uppercase font-bold">Faltam para fechar ({{ $metaColecao }})</span>
                                        <span class="text-slate-700 font-extrabold font-mono text-xs">{{ $missingFoil }} cartas</span>
                                    </div>
                                    <div class="bg-rose-50/70 p-2.5 rounded-xl border border-rose-100">
                                        <span class="text-rose-500 block text-[9px] uppercase font-bold">Custo Est. p/ fechar ({{ $metaColecao }})</span>
                                        <span class="text-rose-700 font-extrabold font-mono text-xs">R$ {{ number_format($costFoil, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                        <span class="text-slate-400 block text-[9px] uppercase font-bold">Total do Set Oficial</span>
                                        <span class="text-slate-700 font-extrabold font-mono text-xs">{{ $targetFoil }} cartas</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </section>

    @endif

    {{-- MODAL 1: CRIAR NOVO FICHÁRIO --}}
    @if($modalCreateBinder)
        <div class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-xl w-full p-6 md:p-8 shadow-2xl relative border border-slate-200 max-h-[92vh] overflow-y-auto">
                <button type="button" wire:click="$set('modalCreateBinder', false)" class="absolute top-5 right-5 text-slate-400 hover:text-slate-700">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>

                <h3 class="text-lg font-black text-slate-900">Criar Novo Fichário</h3>
                <p class="text-xs text-slate-500 mt-1">Configure seu álbum. O formato e o bloqueio de folhas são permanentes.</p>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nome do Fichário</label>
                        <input type="text" wire:model="newBinderName" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:outline-none" placeholder="Ex: Raras & Míticas">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Descrição</label>
                        <input type="text" wire:model="newBinderDesc" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none" placeholder="Ex: Commander e Modern">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Formato dos Bolsos (Imutável)</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" wire:click="$set('newBinderLayout', '2x2')" class="p-3 rounded-xl border text-center transition {{ $newBinderLayout === '2x2' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-slate-50 text-slate-700' }}">
                                <div class="font-black text-xs">2x2</div>
                                <div class="text-[9px] opacity-75">4 Bolsos</div>
                            </button>
                            <button type="button" wire:click="$set('newBinderLayout', '3x3')" class="p-3 rounded-xl border text-center transition {{ $newBinderLayout === '3x3' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-slate-50 text-slate-700' }}">
                                <div class="font-black text-xs">3x3</div>
                                <div class="text-[9px] opacity-75">9 Bolsos</div>
                            </button>
                            <button type="button" wire:click="$set('newBinderLayout', '4x4')" class="p-3 rounded-xl border text-center transition {{ $newBinderLayout === '4x4' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-slate-50 text-slate-700' }}">
                                <div class="font-black text-xs">4x4</div>
                                <div class="text-[9px] opacity-75">Playset</div>
                            </button>
                            <button type="button" wire:click="$set('newBinderLayout', 'bulk')" class="p-3 rounded-xl border text-center transition {{ $newBinderLayout === 'bulk' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-slate-50 text-slate-700' }}">
                                <div class="font-black text-xs">BOX</div>
                                <div class="text-[9px] opacity-75">Infinita</div>
                            </button>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl border transition {{ $newBinderLockedSheets ? 'bg-amber-50/70 border-amber-200' : 'bg-slate-50 border-slate-200' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center {{ $newBinderLockedSheets ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-600' }}">
                                    <i class="ph-bold {{ $newBinderLockedSheets ? 'ph-lock-key' : 'ph-lock-key-open' }} text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-slate-900">
                                        {{ $newBinderLockedSheets ? 'Fichário Soldado / Costurado (Travado)' : 'Fichário de Argolas (Expansível)' }}
                                    </h4>
                                    <p class="text-[10px] text-slate-500">
                                        {{ $newBinderLockedSheets ? 'Capacidade cravada em 20 folhas de fábrica.' : 'Permite refis e folhas infinitas.' }}
                                    </p>
                                </div>
                            </div>
                            <button type="button" wire:click="$toggle('newBinderLockedSheets')" class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition {{ $newBinderLockedSheets ? 'bg-amber-600 text-white' : 'bg-slate-900 text-white' }}">
                                {{ $newBinderLockedSheets ? 'Trancado' : 'Destrancado' }}
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Textura do Couro</label>
                            <select wire:model="newBinderCover" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900">
                                <option value="cover-black">Couro Preto Ónix</option>
                                <option value="cover-blue">Safira Real</option>
                                <option value="cover-box">Kraft Rústico (Box)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Cor do Elástico</label>
                            <select wire:model="newBinderStrap" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900">
                                <option value="orange">Laranja Versus</option>
                                <option value="cyan">Ciano Elétrico</option>
                                <option value="gold">Dourado / Âmbar</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Estampa da Capa (Selecione uma arte)</label>
                        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl mb-2.5 overflow-x-auto">
                            <button type="button" wire:click="$set('selectedGameTab', 'magic')" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase transition whitespace-nowrap {{ $selectedGameTab === 'magic' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">Magic</button>
                            <button type="button" wire:click="$set('selectedGameTab', 'pokemon')" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase transition whitespace-nowrap {{ $selectedGameTab === 'pokemon' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">Pokémon</button>
                            <button type="button" wire:click="$set('selectedGameTab', 'yugioh')" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase transition whitespace-nowrap {{ $selectedGameTab === 'yugioh' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">Yu-Gi-Oh!</button>
                            <button type="button" wire:click="$set('selectedGameTab', 'onepiece')" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase transition whitespace-nowrap {{ $selectedGameTab === 'onepiece' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">One Piece</button>
                            <button type="button" wire:click="$set('selectedGameTab', 'battlescenes')" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase transition whitespace-nowrap {{ $selectedGameTab === 'battlescenes' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">Battle Scenes</button>
                        </div>

                        <div class="grid grid-cols-5 gap-2">
                            @foreach($this->iconicArts[$selectedGameTab] as $art)
                                <div wire:click="selectCoverArt('{{ $art['url'] }}')" 
                                     class="relative rounded-xl overflow-hidden border-2 cursor-pointer aspect-[2.5/3.5] bg-black group transition {{ $newBinderSelectedArt === $art['url'] ? 'border-orange-500' : 'border-slate-200' }}">
                                    <img src="{{ $art['url'] }}" alt="{{ $art['name'] }}" class="w-full h-full object-cover">
                                    @if($newBinderSelectedArt === $art['url'])
                                        <div class="absolute top-1 right-1 w-4 h-4 rounded-full bg-orange-500 text-white flex items-center justify-center text-[9px]">
                                            <i class="ph-bold ph-check"></i>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ou envie sua própria imagem do computador</label>
                        <input type="file" wire:model="newBinderUploadCover" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white cursor-pointer">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('modalCreateBinder', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500">Cancelar</button>
                    <button type="button" wire:click="saveNewBinder" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-orange-600 text-white text-xs font-black transition">
                        Criar Fichário
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 2: EDITAR FICHÁRIO --}}
    @if($modalEditBinder)
        <div class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-xl w-full p-6 md:p-8 shadow-2xl relative border border-slate-200 max-h-[92vh] overflow-y-auto">
                <button type="button" wire:click="$set('modalEditBinder', false)" class="absolute top-5 right-5 text-slate-400 hover:text-slate-700">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>

                <h3 class="text-lg font-black text-slate-900">Configurações da Pasta</h3>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                        {{ strtoupper($editBinderLayout) }} (Imutável)
                    </span>
                    @if($editBinderIsLocked)
                        <span class="text-[10px] font-black uppercase text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                            <i class="ph-bold ph-lock-key"></i> Folhas Fixas
                        </span>
                    @else
                        <span class="text-[10px] font-black uppercase text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                            <i class="ph-bold ph-lock-key-open"></i> Folhas Expansíveis
                        </span>
                    @endif
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nome do Fichário</label>
                        <input type="text" wire:model="editBinderName" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Descrição</label>
                        <input type="text" wire:model="editBinderDesc" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Textura do Couro</label>
                            <select wire:model="editBinderCover" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900">
                                <option value="cover-black">Couro Preto Ónix</option>
                                <option value="cover-blue">Safira Real</option>
                                <option value="cover-box">Kraft Rústico</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Cor do Elástico</label>
                            <select wire:model="editBinderStrap" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900">
                                <option value="orange">Laranja Versus</option>
                                <option value="cyan">Ciano Elétrico</option>
                                <option value="gold">Dourado / Âmbar</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alterar Estampa da Capa</label>
                        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl mb-2.5 overflow-x-auto">
                            <button type="button" wire:click="$set('editGameTab', 'magic')" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase transition whitespace-nowrap {{ $editGameTab === 'magic' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">Magic</button>
                            <button type="button" wire:click="$set('editGameTab', 'pokemon')" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase transition whitespace-nowrap {{ $editGameTab === 'pokemon' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">Pokémon</button>
                            <button type="button" wire:click="$set('editGameTab', 'yugioh')" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase transition whitespace-nowrap {{ $editGameTab === 'yugioh' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">Yu-Gi-Oh!</button>
                            <button type="button" wire:click="$set('editGameTab', 'onepiece')" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase transition whitespace-nowrap {{ $editGameTab === 'onepiece' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">One Piece</button>
                            <button type="button" wire:click="$set('editGameTab', 'battlescenes')" class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase transition whitespace-nowrap {{ $editGameTab === 'battlescenes' ? 'bg-slate-900 text-white' : 'text-slate-500' }}">Battle Scenes</button>
                        </div>

                        <div class="grid grid-cols-5 gap-2">
                            @foreach($this->iconicArts[$editGameTab] as $art)
                                <div wire:click="selectEditCoverArt('{{ $art['url'] }}')" 
                                     class="relative rounded-xl overflow-hidden border-2 cursor-pointer aspect-[2.5/3.5] bg-black group transition {{ $editBinderSelectedArt === $art['url'] ? 'border-orange-500' : 'border-slate-200' }}">
                                    <img src="{{ $art['url'] }}" alt="{{ $art['name'] }}" class="w-full h-full object-cover">
                                    @if($editBinderSelectedArt === $art['url'])
                                        <div class="absolute top-1 right-1 w-4 h-4 rounded-full bg-orange-500 text-white flex items-center justify-center text-[9px]">
                                            <i class="ph-bold ph-check"></i>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ou envie nova imagem do computador</label>
                        <input type="file" wire:model="editBinderUploadCover" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white cursor-pointer">
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" wire:click="deleteBinder" wire:confirm="Tem certeza de que deseja apagar este fichário?" class="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition">
                        <i class="ph-bold ph-trash mr-1"></i> Apagar Fichário
                    </button>
                    <button type="button" wire:click="updateBinder" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-orange-600 text-white text-xs font-black transition">
                        Salvar Alterações
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 3: CADASTRAR / GERENCIAR CARTA (EXIBIÇÃO PURA DE CADA PRINT FÍSICO) --}}
    @if($modalAddCard)
        <div class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 md:p-8 shadow-2xl relative border border-slate-200 max-h-[92vh] overflow-y-auto">
                <button type="button" wire:click="$set('modalAddCard', false)" class="absolute top-5 right-5 text-slate-400 hover:text-slate-700">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>

                <div class="flex items-center justify-between pr-8">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Cadastrar Carta no Acervo</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Cadastre suas cartas continuamente sem fechar o modal.</p>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    {{-- CAMPO DE BUSCA DE ESTAMPA --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Buscar Estampa no Catálogo</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="searchQuery" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-3.5 pr-8 py-2.5 text-xs font-semibold text-slate-900 focus:outline-none" placeholder="Digite o nome ou 'Nome Edição' (ex: Holy 3ed)...">
                            @if($searchQuery)
                                <button type="button" wire:click="$set('searchQuery', '')" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-700">
                                    <i class="ph-bold ph-x"></i>
                                </button>
                            @endif
                        </div>

                        {{-- RESULTADOS: CADA PRINT FÍSICO COM SEU IDIOMA E NOME EXATO --}}
                        @if($searchResults->isNotEmpty())
                            <div class="mt-2 bg-white border border-slate-200 rounded-2xl shadow-2xl max-h-72 overflow-y-auto divide-y divide-slate-100 z-50 relative">
                                @foreach($searchResults as $res)
                                    <div wire:click="selectPrint({{ $res->id }})" class="p-2.5 flex items-center justify-between hover:bg-slate-50 cursor-pointer transition group">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <img src="{{ $res->image_path ?: '/images/placeholder-card.png' }}" class="w-8 h-11 object-cover rounded shadow-sm bg-black shrink-0">
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-slate-900 truncate group-hover:text-orange-600">
                                                    {{ $res->printed_name ?: ($res->concept->name ?? 'Carta') }}
                                                </div>
                                                <div class="text-[10px] text-slate-400 truncate">
                                                    {{ $res->set->name ?? 'Edição n/a' }} &bull; #{{ $res->collector_number }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                            <span class="text-[9px] font-black uppercase tracking-wider bg-slate-800 text-white px-1.5 py-0.5 rounded">
                                                {{ strtoupper($res->language_code ?: 'EN') }}
                                            </span>
                                            <span class="text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200">
                                                {{ $res->set->code }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($selectedPrintData)
                        {{-- CABEÇALHO DO PRINT SELECIONADO COM BOTÃO DE TROCAR CARTA --}}
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex gap-3 items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $selectedPrintData['image'] }}" class="w-12 h-16 object-cover rounded-lg shadow-sm bg-black shrink-0">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <span class="text-[9px] font-black uppercase bg-slate-800 text-white px-1.5 py-0.5 rounded">
                                            {{ strtoupper($selectedPrintData['language_code'] ?: 'EN') }}
                                        </span>
                                        <span class="text-[9px] font-black uppercase text-slate-400">
                                            {{ $selectedPrintData['code'] }} #{{ $selectedPrintData['number'] }}
                                        </span>
                                    </div>
                                    <h4 class="text-xs font-extrabold text-slate-900 leading-tight truncate">{{ $selectedPrintData['name'] }}</h4>
                                    <p class="text-[10px] text-slate-500 truncate">{{ $selectedPrintData['set_name'] }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1.5 shrink-0">
                                @if($isEditingItem)
                                    <span class="text-[9px] font-black uppercase text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                        Editando Lote
                                    </span>
                                @endif
                                <button type="button" wire:click="resetCardPicker" class="text-[10px] font-bold text-orange-600 hover:text-orange-700 underline flex items-center gap-1">
                                    <i class="ph-bold ph-arrows-clockwise"></i> Trocar Carta
                                </button>
                            </div>
                        </div>

                        {{-- FORMULÁRIO DO LOTE / CÓPIA --}}
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 space-y-3 shadow-sm">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Idioma Físico</label>
                                    <input type="text" wire:model="cardLanguage" class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 text-xs font-black uppercase text-slate-700 cursor-not-allowed" readonly title="Idioma determinado pela estampa selecionada">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Conservação</label>
                                    <select wire:model="cardCondition" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900">
                                        <option value="NM">Near Mint (NM)</option>
                                        <option value="SP">Slightly Played (SP)</option>
                                        <option value="MP">Moderately Played (MP)</option>
                                        <option value="HP">Heavily Played (HP)</option>
                                        <option value="D">Damaged (D)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- DROPDOWN DE EXTRAS FLUTUANTE --}}
                            @if(!empty($availableExtras))
                                <div class="relative" 
                                     x-data="{ 
                                        open: false,
                                        localExtras: @entangle('selectedExtras'),
                                        toggleExtra(val) {
                                            const v = val.toLowerCase();
                                            let current = [...this.localExtras];
                                            const lowers = current.map(i => i.toLowerCase());
                                            if (lowers.includes(v)) {
                                                current = current.filter(i => i.toLowerCase() !== v);
                                            } else {
                                                if (v === 'foil') current = current.filter(i => i.toLowerCase() !== 'etched' && i.toLowerCase() !== 'foil_etched');
                                                if (v === 'etched' || v === 'foil_etched') current = current.filter(i => i.toLowerCase() !== 'foil');
                                                current.push(val);
                                            }
                                            this.localExtras = current;
                                        },
                                        isChecked(val) {
                                            return this.localExtras.map(i => i.toLowerCase()).includes(val.toLowerCase());
                                        }
                                     }">

                                    <label class="block text-xs font-bold text-slate-700 mb-1">Acabamentos & Extras</label>

                                    <button @click="open = !open" type="button" 
                                            class="w-full flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs transition">
                                        <span class="font-bold text-orange-600 uppercase truncate" 
                                              x-text="localExtras.length > 0 ? localExtras.length + ' selecionado(s)' : 'Nenhum extra'"></span>
                                        <i class="ph-bold ph-caret-down text-slate-400"></i>
                                    </button>

                                    <div x-show="open" @click.outside="open = false" 
                                         class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden"
                                         style="display: none;">
                                        <div class="max-h-48 overflow-y-auto p-1.5 divide-y divide-slate-50">
                                            @foreach($availableExtras as $value => $label)
                                                <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 cursor-pointer transition"
                                                       @click.prevent="toggleExtra('{{ $value }}')">
                                                    <input type="checkbox" :checked="isChecked('{{ $value }}')" class="rounded border-slate-300 text-orange-600 focus:ring-0 pointer-events-none">
                                                    <span class="text-xs font-bold text-slate-800 select-none">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Quantidade</label>
                                    <input type="number" min="1" wire:model="cardQuantity" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Preço Pago (R$)</label>
                                    <input type="text" wire:model="cardPrice" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900" placeholder="0,00">
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                @if($isEditingItem)
                                    <button type="button" wire:click="cancelItemEdit" class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800">
                                        Cancelar Edição
                                    </button>
                                    <button type="button" wire:click="saveCardToCollection" class="px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-black transition shadow-sm flex items-center gap-1.5">
                                        <i class="ph-bold ph-check"></i> Atualizar Cópia
                                    </button>
                                @else
                                    <button type="button" wire:click="resetCardPicker" class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                                        <i class="ph-bold ph-plus-circle"></i> Outra Carta
                                    </button>
                                    <button type="button" wire:click="saveCardToCollection" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-orange-600 text-white text-xs font-black transition shadow-sm flex items-center gap-1.5">
                                        <i class="ph-bold ph-plus"></i> Cadastrar no Acervo
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- LISTA DE CÓPIAS DESTA ESTAMPA NO ACERVO --}}
                        <div class="pt-2">
                            <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider mb-2 flex items-center justify-between">
                                <span>Cópias no seu Acervo ({{ $existingPrintItems->sum('quantity') }} un.)</span>
                                <span class="text-[10px] text-slate-400 font-bold lowercase">desta estampa</span>
                            </h5>

                            @if($existingPrintItems->isEmpty())
                                <div class="p-3 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                    Nenhuma cópia cadastrada ainda. Use o formulário acima para adicionar.
                                </div>
                            @else
                                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                    @foreach($existingPrintItems as $item)
                                        <div class="p-2.5 rounded-xl border transition flex items-center justify-between {{ $editingItemId === $item->id ? 'bg-orange-50/70 border-orange-300' : 'bg-slate-50 border-slate-200' }}">
                                            <div class="flex items-center gap-2 text-xs">
                                                <span class="font-black bg-slate-900 text-white px-2 py-0.5 rounded text-[10px]">{{ $item->quantity }}x</span>
                                                <span class="font-bold text-slate-800 uppercase">{{ $item->language }} &bull; {{ $item->condition }}</span>
                                                @if(!empty($item->extras))
                                                    <span class="text-amber-600 font-bold text-[10px]">
                                                        ({{ implode(', ', (array)$item->extras) }})
                                                    </span>
                                                @endif
                                                @if($item->acquisition_price > 0)
                                                    <span class="text-emerald-600 font-mono font-bold text-[10px]">
                                                        R$ {{ number_format($item->acquisition_price, 2, ',', '.') }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-1">
                                                <button type="button" wire:click="editCollectionItem({{ $item->id }})" class="p-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-900 hover:text-white text-slate-600 transition" title="Editar este lote">
                                                    <i class="ph-bold ph-pencil-simple text-xs"></i>
                                                </button>
                                                <button type="button" wire:click="deleteCollectionItem({{ $item->id }})" wire:confirm="Deseja remover esta cópia do seu acervo?" class="p-1.5 rounded-lg bg-rose-50 border border-rose-200 hover:bg-rose-600 hover:text-white text-rose-600 transition" title="Excluir do acervo">
                                                    <i class="ph-bold ph-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- FECHAMENTO MANUAL DO MODAL --}}
                <div class="mt-6 flex items-center justify-between pt-4 border-t border-slate-100">
                    <span class="text-[11px] text-slate-400 font-medium">Cadastre quantas cartas desejar</span>
                    <button type="button" wire:click="$set('modalAddCard', false)" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-black text-slate-700 transition">
                        Concluir e Fechar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

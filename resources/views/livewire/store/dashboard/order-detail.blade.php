<main class="flex-1 p-6 md:p-8 max-w-[1600px] mx-auto w-full space-y-6 pb-24"
    x-data="{
        previewImage: null,
        previewTitle: '',
        coletados: {},
        totalItens: {{ $items->count() }},
        get marcadosCount() {
            return Object.values(this.coletados).filter(Boolean).length;
        }
    }">

    <!-- Notificação Flash Livewire -->
    @if($successMessage)
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ $successMessage }}
            </span>
            <button @click="show = false" class="text-emerald-400 hover:text-white">&times;</button>
        </div>
    @endif

    <!-- 1. TOPO: BREADCRUMB & BARRA DE AÇÕES -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
            <nav class="text-xs text-slate-400 mb-1.5 flex items-center gap-1.5 font-medium">
                <a href="{{ route('store.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('store.dashboard.orders.index') }}" class="hover:text-white transition-colors">Pedidos</a>
                <span>/</span>
                <span class="text-sky-400 font-semibold font-mono">#{{ $order->id ?? $orderId }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <span class="w-2 h-6 bg-sky-500 rounded-full"></span>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-3 font-mono">
                    #{{ $order->id ?? $orderId }}
                    @if(($order->channel ?? 'marketplace') === 'marketplace')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-sans font-bold bg-orange-500/10 text-orange-400 border border-orange-500/30">
                            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                            Versus Marketplace
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-sans font-bold bg-blue-500/10 text-blue-400 border border-blue-500/30">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Loja Própria
                        </span>
                    @endif
                </h1>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-lg border border-slate-700 bg-slate-800/80 hover:bg-slate-700 text-xs font-bold uppercase tracking-wider text-slate-300 hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir Pedido
            </button>
        </div>
    </div>

    <!-- 2. GRID PRINCIPAL -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- === COLUNA ESQUERDA: ITENS, MENSAGENS E HISTÓRICO (8 colunas) === -->
        <div class="lg:col-span-8 space-y-6">

            <!-- TABELA DE ITENS -->
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
                    <div class="flex items-center gap-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="w-1.5 h-4 bg-sky-500 rounded-full"></span>
                            Itens para Coleta ({{ $items->count() }})
                        </h2>
                        <span class="text-xs text-slate-400">{{ $shipping->created_at ? $shipping->created_at->format('d/m/Y H:i') : '' }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold font-mono transition-colors"
                            :class="marcadosCount === totalItens && totalItens > 0 ? 'bg-emerald-500 text-slate-950 border border-emerald-400' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'"
                            x-text="`${marcadosCount}/${totalItens} Coletados`">
                        </span>
                    </div>
                </div>

                <!-- Lista de Itens -->
                <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse ($items as $item)
                        @php
                            $stock = $item->stockItem;
                            $print = optional($stock)->catalogPrint;
                            $concept = optional($print)->concept;
                            $set = optional($print)->set;

                            $cardImg = '';
                            if ($print && !empty($print->image_path)) {
                                $cleanPath = ltrim($print->image_path, '/');
                                $cardImg = str_starts_with($cleanPath, 'http')
                                    ? $cleanPath
                                    : 'https://versustcg.com.br/' . $cleanPath;
                            }
                        @endphp
                        <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors flex items-start gap-4 group">
                            <div class="pt-1">
                                <input type="checkbox" x-model="coletados[{{ $item->id }}]"
                                    class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sky-500 focus:ring-sky-500 cursor-pointer">
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($concept && $concept->slug)
                                                <a href="{{ route('store.dashboard.stock.manage-card', ['game_slug' => 'magic', 'conceptSlug' => $concept->slug]) }}"
                                                    target="_blank"
                                                    class="text-sm font-bold text-slate-800 dark:text-white hover:text-sky-400 transition-colors flex items-center gap-1.5"
                                                    @mouseenter="previewImage = '{{ $cardImg }}'; previewTitle = '{{ addslashes($concept->name ?? $item->item_name) }}'"
                                                    @mouseleave="previewImage = null">
                                                    {{ $item->quantity }}x {{ $concept->name ?? $item->item_name }}
                                                    <svg class="w-3 h-3 text-slate-500 group-hover:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                </a>
                                            @else
                                                <span class="text-sm font-bold text-slate-800 dark:text-white cursor-pointer"
                                                    @mouseenter="previewImage = '{{ $cardImg }}'; previewTitle = '{{ addslashes($item->item_name) }}'"
                                                    @mouseleave="previewImage = null">
                                                    {{ $item->quantity }}x {{ $item->item_name }}
                                                </span>
                                            @endif

                                            @if($print && $print->collector_number)
                                                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700">
                                                    #{{ $print->collector_number }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="text-xs text-slate-400 mt-1 flex items-center gap-2 flex-wrap">
                                            <span>{{ $set->name ?? 'Edição Desconhecida' }}</span>
                                            <span>•</span>
                                            <span class="text-slate-300 font-medium uppercase">{{ $stock->language ?? 'EN' }}</span>
                                            <span>•</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-emerald-400 border border-emerald-500/30">
                                                {{ $stock->condition ?? 'NM' }}
                                            </span>
                                            @if($print && $print->rarity)
                                                <span>•</span>
                                                <span class="text-slate-400 capitalize">Raridade: <strong>{{ $print->rarity }}</strong></span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <div class="text-sm font-bold text-slate-900 dark:text-white">
                                            R$ {{ number_format($item->unit_price * $item->quantity, 2, ',', '.') }}
                                        </div>
                                        <div class="text-[11px] text-slate-400">
                                            R${{ number_format($item->unit_price, 2, ',', '.') }} unid.
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2.5 flex items-center justify-between text-[11px] pt-2 border-t border-slate-100 dark:border-slate-800/60">
                                    <span class="font-mono text-slate-400">Item ID: {{ $item->id }}</span>
                                    @if($stock)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-300 border border-slate-200 dark:border-slate-700">
                                            <span class="text-slate-400">Estoque ID:</span>
                                            <strong class="font-mono text-sky-400">#{{ $stock->id }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-slate-500">
                            Nenhum item registrado para esta loja neste pedido.
                        </div>
                    @endforelse
                </div>

                <!-- Resumo Financeiro da Loja -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-700/60">
                    <div class="max-w-xs ml-auto space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal dos Itens ({{ $items->count() }}):</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">R${{ number_format($subtotalItens, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>Frete ({{ $shipping->shipping_method_name ?? 'Padrão' }}):</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">R${{ number_format($shipping->shipping_cost, 2, ',', '.') }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-700/80 flex justify-between items-baseline">
                            <span class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Valor Total:</span>
                            <span class="text-lg font-black text-slate-900 dark:text-white font-mono">R${{ number_format($totalGeral, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. COMENTÁRIOS & HISTÓRICO COM O CLIENTE -->
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-sm p-5 space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-1.5 h-3.5 bg-sky-500 rounded-full"></span>
                    Comentários & Histórico com o Cliente
                </h3>

                <div class="space-y-3">
                    @forelse ($messages as $msg)
                        <div class="p-3.5 rounded-lg border text-xs {{ $msg->sender_type === 'store_user' ? 'bg-sky-500/10 border-sky-500/20 text-slate-200' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-200' }}">
                            <div class="flex items-center justify-between text-[11px] mb-1.5">
                                <span class="font-bold {{ $msg->sender_type === 'store_user' ? 'text-sky-400' : 'text-slate-300' }}">
                                    {{ $msg->sender_name ?? ($msg->sender_type === 'store_user' ? 'Loja' : 'Cliente') }}
                                </span>
                                <span class="text-slate-400 font-mono">{{ \Carbon\Carbon::parse($msg->created_at)->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-slate-300 whitespace-pre-wrap leading-relaxed">{{ $msg->message }}</p>
                        </div>
                    @empty
                        <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 text-slate-400 text-xs">
                            Nenhuma mensagem direta trocada com o cliente para este pedido.
                        </div>
                    @endforelse
                </div>

                <div class="space-y-3 pt-2">
                    <textarea wire:model="newMessageText" rows="3"
                        placeholder="Digite uma observação interna ou comentário para enviar ao cliente..."
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg p-3 text-xs text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-sky-500 resize-none"></textarea>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-400 hover:text-slate-200">
                            <input type="checkbox" wire:model="sendEmailCopy" class="w-4 h-4 rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-500">
                            <span>Enviar cópia para o email do cliente ({{ $playerUser->email ?? 'não informado' }})</span>
                        </label>

                        <button type="button" wire:click="addMessage"
                            class="px-5 py-2.5 rounded-lg bg-sky-500 hover:bg-sky-400 font-bold text-xs uppercase tracking-wider text-slate-950 transition-colors shadow-sm self-end sm:self-auto">
                            Adicionar Comentário
                        </button>
                    </div>
                </div>
            </div>

            <!-- 4. HISTÓRICO DE AÇÕES (LOGS DE AUDITORIA) -->
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-sm p-5 space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-1.5 h-3.5 bg-sky-500 rounded-full"></span>
                    Histórico de Ações (Logs de Auditoria)
                </h3>

                <div class="divide-y divide-slate-100 dark:divide-slate-800/80 text-xs">
                    @forelse ($histories as $log)
                        <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <span class="font-mono text-slate-400 text-[11px] shrink-0">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}</span>
                            <div class="flex-1 sm:px-4">
                                @if($log->old_status_name &&$log->new_status_name)
                                    <span class="text-slate-300">Status alterado para: <strong class="text-amber-400">{{ $log->new_status_name }}</strong></span>
                                @endif
                                @if($log->comment)
                                    <span class="text-slate-400 block text-[11px] mt-0.5">{{ $log->comment }}</span>
                                @endif
                            </div>
                            <span class="text-slate-400 text-[11px] shrink-0">Por: <strong class="text-slate-200">{{ $log->actor_name ?? 'Sistema' }}</strong></span>
                        </div>
                    @empty
                        <div class="py-2.5 text-slate-400 text-[11px]">
                            Nenhuma ação manual registrada após a criação.
                        </div>
                    @endforelse

                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span class="font-mono text-slate-500 text-[11px] shrink-0">{{ $order->created_at ? $order->created_at->format('d/m/Y H:i:s') : '-' }}</span>
                        <div class="flex-1 sm:px-4 text-slate-300">Pedido criado pelo cliente na loja</div>
                        <span class="text-slate-400 text-[11px] shrink-0">Por: <strong class="text-slate-200">{{ $playerUser->name ?? 'Cliente' }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- === COLUNA DIREITA: LATERAL INTEGRAL (4 colunas) === -->
        <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-[154px] self-start z-10">

            <!-- 1. PREVIEW DA CARTA (SO APARECE NO HOVER) -->
            <div x-show="previewImage" x-transition.opacity
                style="display: none;"
                class="bg-slate-900 border border-slate-700 p-3 rounded-2xl shadow-2xl flex flex-col items-center w-full">
                <span class="text-xs font-bold text-sky-400 mb-2 truncate max-w-full tracking-wide" x-text="previewTitle"></span>
                <img :src="previewImage" class="w-full max-w-[360px] h-auto rounded-xl shadow-2xl object-contain border border-slate-700/60" alt="Card Preview" onerror="this.onerror=null; this.src='/placeholder-card.jpg';">
            </div>

            <!-- CONTAINER DOS DADOS OPERACIONAIS (SOME NO HOVER) -->
            <div x-show="!previewImage" class="space-y-5">


            

            <!-- 2. DADOS DO CLIENTE & ENDEREÇO DE ENTREGA -->
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-sm p-5 space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Endereço de Entrega
                </h3>

                <div class="space-y-1 text-xs">
                    <div class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                        {{ optional($address)->receiver_name ?? ($playerUser->name . ' ' . ($playerUser->surname ?? '')) }}
                        @if($playerUser &&$playerUser->nickname)
                            <span class="text-slate-400 font-normal text-xs">({{ $playerUser->nickname }})</span>
                        @endif
                    </div>

                    @if($address)
                        <div class="text-slate-300">{{ $address->street }}, {{$address->number }} {{ $address->complement ? '- ' . $address->complement : '' }}</div>
                        <div class="text-slate-300">{{ $address->neighborhood }} — {{ $address->city }} / {{$address->state }}</div>
                        <div class="text-slate-400 font-mono">CEP: {{ $address->zip_code }}</div>
                    @else
                        <div class="text-slate-400 italic">Endereço não cadastrado ou retirada física.</div>
                    @endif

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex justify-between text-slate-400">
                        <span>Tel: <strong class="text-slate-200">{{ $playerUser->phone_number ?? 'Não informado' }}</strong></span>
                        <span>CPF: <strong class="text-slate-200">{{ $playerUser->document_number ?? 'Não informado' }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- 3. INFORMAÇÕES DO PAGAMENTO -->
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-sm p-5 space-y-3 text-xs">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Informações do Pagamento
                </h3>
                <div class="space-y-1.5 text-slate-300">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Método:</span>
                        <span class="font-bold text-slate-800 dark:text-white uppercase">{{ $order->payment_method ?? 'PIX' }}</span>
                    </div>
                    @if($order->gateway_transaction_id)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Transação ID:</span>
                            <span class="font-mono text-sky-400">#{{ $order->gateway_transaction_id }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Status:</span>
                        @if(in_array(strtolower($order->payment_status ?? ''), ['pago', 'confirmado', 'approved']))
                            <span class="font-semibold text-emerald-400 uppercase">Confirmado</span>
                        @else
                            <span class="font-semibold text-amber-400 uppercase">{{ $order->payment_status ?? 'Aguardando Pagamento' }}</span>
                        @endif
                    </div>
                </div>

                <!-- SEÇÃO COMPROVANTE PIX (AUDITORIA DO LOJISTA) -->
                @if(strtolower($order->payment_method ?? 'pix') === 'pix')
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 space-y-2">
                        <span class="block text-[11px] font-bold text-slate-400 uppercase">Comprovante de Pagamento</span>

                        @if(!empty($order->payment_proof_path))
                            @php
                                $proofUrl = str_starts_with($order->payment_proof_path, 'http')
                                    ? $order->payment_proof_path
                                    : 'https://versustcg.com.br/' . ltrim($order->payment_proof_path, '/');
                            @endphp
                            <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-2">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-emerald-400 font-medium flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Comprovante Anexado
                                    </span>
                                    <span class="text-slate-400 font-mono text-[10px]">
                                        {{ $order->payment_proof_uploaded_at ? \Carbon\Carbon::parse($order->payment_proof_uploaded_at)->format('d/m H:i') : '' }}
                                    </span>
                                </div>
                                <a href="{{ $proofUrl }}" target="_blank"
                                    class="w-full py-1.5 px-3 rounded bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold text-center flex items-center justify-center gap-1.5 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Visualizar Comprovante
                                </a>
                            </div>

                            @if(!in_array(strtolower($order->payment_status ?? ''), ['pago', 'confirmado', 'approved']))
                                <button type="button" wire:click="confirmPixPayment"
                                    class="w-full py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 font-bold text-xs uppercase tracking-wider text-white transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Confirmar Recebimento PIX
                                </button>
                            @endif
                        @else
                            <div class="p-2.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[11px] flex items-start gap-2">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span>Aguardando envio do comprovante pelo comprador.</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- 4. SITUAÇÃO DO PEDIDO -->
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Situação Atual</span>
                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-orange-500/10 text-orange-400 border border-orange-500/30">
                        {{ optional($shipping->status)->name ?? 'Em Separação' }}
                    </span>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Alterar Situação</label>
                        <select wire:model="newStatusId" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg py-2 px-3 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-1 focus:ring-sky-500">
                            @foreach ($statuses as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-1">Observação do Histórico (Opcional)</label>
                        <input type="text" wire:model="newComment" placeholder="Ex: Itens separados, aguardando embalagem..."
                            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg py-1.5 px-3 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-1 focus:ring-sky-500">
                    </div>

                    <button type="button" wire:click="updateStatus" class="w-full py-2 bg-sky-600 hover:bg-sky-500 text-xs font-bold text-white rounded-lg transition-colors">
                        Atualizar Situação & Registrar Log
                    </button>
                </div>
            </div>

            <!-- 5. EXPEDIÇÃO & ENVIO -->
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-sm p-5 space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    Expedição & Envio
                </h3>

                <div class="space-y-1 text-xs pb-2 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Modalidade:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $shipping->shipping_method_name ?? 'Envio Padrão' }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase">Código de Rastreamento</label>
                    <div class="flex gap-2">
                        <input type="text" wire:model="trackingCode" placeholder="Ex: QA123456789BR"
                            class="flex-1 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg py-2 px-3 text-xs font-mono text-slate-800 dark:text-white focus:outline-none focus:ring-1 focus:ring-sky-500 uppercase">
                        <button type="button" wire:click="updateTracking" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-xs font-bold text-sky-400 rounded-lg transition-colors">
                            Salvar
                        </button>
                    </div>

                    <button type="button" wire:click="markAsShipped"
                        class="w-full mt-2 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 font-bold text-xs uppercase tracking-wider text-white transition-colors shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Marcar Pedido como Enviado
                    </button>
                </div>
            </div>

            <!-- 6. AVALIAÇÃO DA COMPRA -->
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-slate-200 dark:border-slate-700/60 shadow-sm p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="text-amber-400">★</span> Avaliação da Compra
                    </h3>
                    <span class="text-[10px] text-slate-400">Pelo cliente</span>
                </div>

                @if($review)
                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <div class="flex text-amber-400 text-sm">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= ($review->rating ?? 5) ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            <span class="text-slate-400 text-[11px] font-mono">
                                {{ \Carbon\Carbon::parse($review->created_at)->format('d/m/Y') }}
                            </span>
                        </div>

                        <p class="text-slate-300 italic text-[11px] bg-slate-50 dark:bg-slate-900/60 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800">
                            "{{ $review->comment ?: 'Sem comentário adicional.' }}"
                        </p>

                        <!-- Toggle de Visibilidade na Loja Própria -->
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <button type="button" wire:click="toggleReviewStoreVisibility({{ $review->id }})"
                                class="inline-flex items-center gap-1.5 text-[11px] font-bold transition-colors {{ $review->is_visible_on_store ? 'text-emerald-400 hover:text-emerald-300' : 'text-slate-500 hover:text-slate-300' }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>{{ $review->is_visible_on_store ? 'Visível na página principal da loja' : 'Oculto na página principal da loja' }}</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="text-[11px] text-slate-400 py-2">
                        Pedido ainda não avaliado pelo comprador.
                    </div>
                @endif
            </div>

        
            </div>
        </div>

</main>

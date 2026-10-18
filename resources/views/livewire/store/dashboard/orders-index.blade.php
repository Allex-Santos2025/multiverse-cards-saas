<main class="flex-1 p-6 md:p-8 max-w-[1600px] mx-auto w-full space-y-8">
    <!-- Cabeçalho & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <nav class="text-xs text-slate-400 mb-1 flex items-center gap-1.5 font-medium">
                <a href="{{ route('store.dashboard') }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-sky-500">Pedidos</span>
            </nav>
            <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <span class="w-1.5 h-5 bg-sky-500 rounded-full"></span>
                Gerenciamento de Pedidos
            </h1>
        </div>

        <!-- Abas Rápidas -->
        <div class="flex flex-wrap items-center gap-2">
            <button wire:click="setTab('all')" 
                class="px-3 py-1.5 rounded-md border text-xs font-bold uppercase transition-all duration-150 {{ $tab === 'all' ? 'border-sky-500 bg-sky-500/10 text-sky-500' : 'border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Todos
            </button>
            <button wire:click="setTab('shipping')" 
                class="px-3 py-1.5 rounded-md border text-xs font-bold uppercase transition-all duration-150 {{ $tab === 'shipping' ? 'border-orange-500 bg-orange-500/10 text-orange-500' : 'border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Aguard. Envio
            </button>
            <button wire:click="setTab('pickup')" 
                class="px-3 py-1.5 rounded-md border text-xs font-bold uppercase transition-all duration-150 {{ $tab === 'pickup' ? 'border-blue-500 bg-blue-500/10 text-blue-500' : 'border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Aguard. Retirada
            </button>
            <button wire:click="setTab('preorder')" 
                class="px-3 py-1.5 rounded-md border text-xs font-bold uppercase transition-all duration-150 {{ $tab === 'preorder' ? 'border-emerald-500 bg-emerald-500/10 text-emerald-500' : 'border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                Pré-Vendas
            </button>
        </div>
    </div>

    <!-- Container de Busca e Filtros -->
    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 space-y-4">
        <!-- Linha da Busca Simples -->
        <div class="flex flex-col md:flex-row gap-3 items-center">
            <div class="relative flex-1 w-full">
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar por #pedido, cliente ou status..."
                    class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 pl-10 pr-4 text-xs text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-sky-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                <button type="button" wire:click="toggleAdvanced" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 {{ $advancedOpen ? 'rotate-180 text-sky-500' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    <span>{{ $advancedOpen ? 'Recolher Busca Avançada' : 'Busca Avançada' }}</span>
                </button>
                @if($search || $filterOrderId || $filterClient || $filterStatusId || $filterDateFrom || $filterDateTo || $filterPaymentMethod || $filterShippingMethod)
                    <button type="button" wire:click="clearFilters" class="px-3 py-2 rounded-lg text-xs font-semibold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                        Limpar Filtros
                    </button>
                @endif
            </div>
        </div>

        <!-- Painel Deslizante da Busca Avançada -->
        <div x-data="{ open: @entangle('advancedOpen') }" x-show="open" x-collapse style="display: none;">
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Pedido #</label>
                    <input type="text" wire:model.live.debounce.300ms="filterOrderId" placeholder="Ex: 1004"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Cliente</label>
                    <input type="text" wire:model.live.debounce.300ms="filterClient" placeholder="Nome ou email"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Status</label>
                    <select wire:model.live="filterStatusId" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-slate-800 dark:text-white">
                        <option value="">Todos os status</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Forma de Envio</label>
                    <input type="text" wire:model.live.debounce.300ms="filterShippingMethod" placeholder="Ex: Balcão, Sedex..."
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Data De</label>
                    <input type="date" wire:model.live="filterDateFrom"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Data Até</label>
                    <input type="date" wire:model.live="filterDateTo"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Forma de Pagamento</label>
                    <input type="text" wire:model.live.debounce.300ms="filterPaymentMethod" placeholder="Ex: Pix, Cartão..."
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-3 text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Ordenar Por</label>
                    <div class="grid grid-cols-2 gap-2">
                        <select wire:model.live="orderBy" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-2 text-slate-800 dark:text-white">
                            <option value="id">Pedido ID</option>
                            <option value="created_at">Data</option>
                        </select>
                        <select wire:model.live="orderDirection" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-2 px-2 text-slate-800 dark:text-white">
                            <option value="desc">Desc</option>
                            <option value="asc">Asc</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Pedidos -->
    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-400">
                <thead class="text-[11px] font-bold text-slate-400 uppercase border-b border-slate-100 dark:border-slate-700 bg-transparent">
                    <tr>
                        <th class="px-6 py-3.5">Pedido / Cliente</th>
                        <th class="px-4 py-3.5">Data</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Forma de Envio / Pag.</th>
                        <th class="px-6 py-3.5 text-right">Valor Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($shippings as $shipping)
                        <tr class="bg-transparent hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer group" @click="Livewire.navigate('{{ route('store.dashboard.orders.show', ['slug' => request()->route('slug'), 'orderId' => $shipping->order_id]) }}')\">
                            <!-- Pedido e Cliente -->
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <a href="{{ route('store.dashboard.orders.show', ['slug' => request()->route('slug'), 'orderId' => $shipping->order_id]) }}" wire:navigate class="flex items-center gap-2 font-mono font-bold text-sky-500 hover:text-sky-400">
                                    <span class="w-2 h-2 rounded-full shrink-0 {{ $shipping->formatted_channel === 'marketplace' ? 'bg-orange-500' : 'bg-blue-500' }}"></span>
                                    #{{ $shipping->formatted_id }}
                                </a>
                                <span class="block text-[11px] font-medium text-slate-700 dark:text-slate-300 mt-0.5">
                                    {{ $shipping->client_name }}
                                </span>
                            </td>

                            <!-- Data -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-600 dark:text-slate-300 font-medium">
                                {{ $shipping->created_at ? $shipping->created_at->format('d/m/Y') : '-' }}
                                <span class="block text-[10px] text-slate-400">{{ $shipping->created_at ? $shipping->created_at->format('H:i') : '' }}</span>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="text-[10px] px-2.5 py-1 rounded font-semibold inline-block"
                                    style="color: {{ $shipping->formatted_status_color }}; background-color: {{ $shipping->formatted_status_color }}18;">
                                    {{ $shipping->formatted_status_name }}
                                </span>
                            </td>

                            <!-- Forma de Envio e Pagamento -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-medium text-slate-700 dark:text-slate-300 block">
                                    {{ $shipping->shipping_method_name ?? 'Envio Padrão' }}
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    {{ $shipping->payment_name }}
                                </span>
                            </td>

                            <!-- Valor -->
                            <td class="px-6 py-3.5 text-right whitespace-nowrap font-bold text-slate-900 dark:text-white text-sm">
                                R$ {{ number_format($shipping->formatted_total, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-xs text-slate-500">
                                Nenhum pedido encontrado para os filtros selecionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($shippings->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-transparent">
                {{ $shippings->links() }}
            </div>
        @endif
    </div>
</main>

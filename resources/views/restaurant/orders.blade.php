@php
    $orders = $restaurant->orders()->with(['restaurantTable', 'items.options', 'payments', 'statusHistories'])->latest('id')->get();
@endphp

<div class="space-y-6">
    <!-- Barre Supérieure : Titre & Compteurs -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-brand-dark-border">
        <div>
            <h3 class="text-sm font-bold text-brand-text dark:text-white">Gestion des Commandes</h3>
            <p class="text-xs text-gray-500">Supervisez l'ensemble des commandes passées, en cours et terminées</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-brand-light dark:bg-brand-orange/15 text-brand-orange">
                {{ $orders->count() }} commande(s) au total
            </span>
        </div>
    </div>

    <!-- Barre de Filtres et Recherche -->
    <div class="bg-white dark:bg-brand-dark-card p-4 rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-sm space-y-3">
        <!-- Ligne 1 : Recherche et Filtre Mode -->
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
            <div class="relative w-full sm:w-80">
                <input type="text" id="order-search-input" placeholder="Rechercher par n°, client, table..." class="w-full text-xs pl-8 pr-3 py-2 bg-gray-50 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border rounded-brand text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange">
                <span class="absolute left-2.5 top-2.5 text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
            </div>

            <!-- Filtre Mode -->
            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                <span class="text-xs text-gray-500 font-medium">Mode :</span>
                <button type="button" class="filter-mode-btn px-3 py-1 rounded-brand text-xs font-bold bg-brand-orange text-white" data-mode="">
                    Tous
                </button>
                <button type="button" class="filter-mode-btn px-3 py-1 rounded-brand text-xs font-semibold bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-400 hover:bg-gray-200 transition-colors" data-mode="dine_in">
                    Sur place
                </button>
                <button type="button" class="filter-mode-btn px-3 py-1 rounded-brand text-xs font-semibold bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-400 hover:bg-gray-200 transition-colors" data-mode="takeaway">
                    À emporter
                </button>
            </div>
        </div>

        <!-- Ligne 2 : Filtres par Statut de Préparation (Pills défilants) -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
            <span class="text-gray-500 font-medium whitespace-nowrap mr-1">Statut :</span>
            <button type="button" class="filter-status-btn px-3 py-1 rounded-full font-bold bg-brand-orange text-white shrink-0" data-status="">
                Tous
            </button>
            <button type="button" class="filter-status-btn px-3 py-1 rounded-full font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shrink-0" data-status="pending">
                En attente
            </button>
            <button type="button" class="filter-status-btn px-3 py-1 rounded-full font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shrink-0" data-status="accepted">
                Acceptées
            </button>
            <button type="button" class="filter-status-btn px-3 py-1 rounded-full font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800 shrink-0" data-status="preparing">
                En cuisine
            </button>
            <button type="button" class="filter-status-btn px-3 py-1 rounded-full font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shrink-0" data-status="ready">
                Prêtes
            </button>
            <button type="button" class="filter-status-btn px-3 py-1 rounded-full font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shrink-0" data-status="served">
                Servies
            </button>
            <button type="button" class="filter-status-btn px-3 py-1 rounded-full font-semibold bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border border-red-200 dark:border-red-800 shrink-0" data-status="cancelled">
                Annulées
            </button>
        </div>
    </div>

    <!-- Tableau des Commandes -->
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs" id="orders-table">
                <thead class="bg-gray-50 dark:bg-brand-dark text-gray-500 uppercase font-semibold border-b border-gray-200 dark:border-brand-dark-border">
                    <tr>
                        <th class="p-3.5">N° Commande</th>
                        <th class="p-3.5">Heure</th>
                        <th class="p-3.5">Mode &amp; Table</th>
                        <th class="p-3.5">Client</th>
                        <th class="p-3.5">Articles</th>
                        <th class="p-3.5 text-right">Montant</th>
                        <th class="p-3.5 text-center">Préparation</th>
                        <th class="p-3.5 text-center">Paiement</th>
                        <th class="p-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-brand-dark-border" id="orders-table-body">
                    @forelse($orders as $order)
                        @php
                            $minutesAgo = $order->created_at ? $order->created_at->diffInMinutes(now()) : 0;
                            $isLate = in_array($order->preparation_status, ['pending', 'accepted', 'preparing']) && $minutesAgo >= 45;
                        @endphp
                        <tr class="order-row hover:bg-gray-50 dark:hover:bg-brand-dark/40 transition-colors {{ $isLate ? 'bg-red-50/30 dark:bg-red-950/20' : '' }}"
                            data-id="{{ $order->id }}"
                            data-number="{{ strtolower($order->number) }}"
                            data-customer="{{ strtolower($order->customer_name ?: '') }}"
                            data-mode="{{ $order->mode }}"
                            data-status="{{ $order->preparation_status }}"
                            data-payment-status="{{ $order->payment_status }}">
                            <!-- N° Commande avec alerte éventuelle -->
                            <td class="p-3.5 font-bold font-mono text-brand-text dark:text-white flex items-center gap-1.5">
                                @if($isLate)
                                    <span class="w-2 h-2 rounded-full bg-red-500 animate-ping" title="Retard critique (+45 min)"></span>
                                @endif
                                <span>#{{ $order->number }}</span>
                            </td>

                            <!-- Heure -->
                            <td class="p-3.5 text-gray-500 whitespace-nowrap">
                                <div>{{ $order->created_at ? $order->created_at->format('H:i') : '' }}</div>
                                <div class="text-[10px] text-gray-400">{{ $order->created_at ? $order->created_at->format('d/m') : '' }}</div>
                            </td>

                            <!-- Mode & Table -->
                            <td class="p-3.5 whitespace-nowrap">
                                @if($order->mode === 'dine_in')
                                    <span class="inline-flex items-center gap-1 font-semibold text-gray-700 dark:text-gray-200">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <span>{{ $order->restaurantTable?->label ?? 'Table inconnue' }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 font-semibold text-brand-orange">
                                        <svg class="w-3.5 h-3.5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        <span>À emporter</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Client -->
                            <td class="p-3.5">
                                <div class="font-semibold text-brand-text dark:text-white">{{ $order->customer_name ?: 'Client comptoir' }}</div>
                                @if($order->customer_phone)
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $order->customer_phone }}</div>
                                @endif
                            </td>

                            <!-- Articles -->
                            <td class="p-3.5 text-gray-600 dark:text-gray-300">
                                <span class="font-semibold">{{ $order->items->sum('quantity') }}</span> article(s)
                            </td>

                            <!-- Montant Total -->
                            <td class="p-3.5 text-right font-black text-brand-text dark:text-white whitespace-nowrap">
                                {{ number_format($order->total_minor / 100, 0, ',', ' ') }} <span class="text-[10px] text-gray-400 font-normal">{{ $restaurant->currency }}</span>
                            </td>

                            <!-- Statut Préparation -->
                            <td class="p-3.5 text-center">
                                @php
                                    $statusBadges = [
                                        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-300',
                                        'accepted' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-300',
                                        'preparing' => 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border-purple-300',
                                        'ready' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-300',
                                        'served' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-300',
                                        'completed' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-300',
                                        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300 border-red-300',
                                    ];
                                    $statusTexts = [
                                        'pending' => 'En attente',
                                        'accepted' => 'Acceptée',
                                        'preparing' => 'En cuisine',
                                        'ready' => 'Prête',
                                        'served' => 'Servie',
                                        'completed' => 'Terminée',
                                        'cancelled' => 'Annulée',
                                    ];
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadges[$order->preparation_status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $statusTexts[$order->preparation_status] ?? $order->preparation_status }}
                                </span>
                            </td>

                            <!-- Statut Paiement -->
                            <td class="p-3.5 text-center">
                                @if($order->payment_status === 'paid')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        Payée
                                    </span>
                                @elseif($order->payment_status === 'partially_paid')
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        Partiel
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        Non payée
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="p-3.5 text-right whitespace-nowrap">
                                <button type="button" class="btn-view-order px-2.5 py-1 text-xs font-semibold text-brand-orange hover:bg-brand-orange/10 rounded transition-colors" data-id="{{ $order->id }}">
                                    Détails →
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-gray-400 text-sm">
                                Aucune commande trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modale de Détail de Commande -->
<div id="modal-order-detail" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-2xl p-6 space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-brand-dark-border">
            <div>
                <h3 class="text-base font-bold text-brand-text dark:text-white" id="modal-order-number">Détails de la commande</h3>
                <p class="text-xs text-gray-400" id="modal-order-date"></p>
            </div>
            <button type="button" id="btn-close-order-modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Contenu dynamique injecté par JS -->
        <div id="modal-order-content" class="space-y-4 text-xs">
            <!-- Injecté par restaurant.js -->
        </div>

        <div class="pt-4 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end">
            <button type="button" id="btn-close-order-modal-footer" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-brand transition-colors">
                Fermer
            </button>
        </div>
    </div>
</div>

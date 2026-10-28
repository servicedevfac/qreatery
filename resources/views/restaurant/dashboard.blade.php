@php
    $demoService = $demoService ?? app(\App\Services\RestaurantDemoDataService::class);
    $isDemoSession = $isDemoSession ?? $demoService->isDemoSession(auth()->user(), $restaurant);
    $dashData = $demoService->getDashboardData($restaurant, $isDemoSession);

    $chargesTodayMinor = $dashData['chargesTodayMinor'];
    $totalOrdersTodayCount = $dashData['totalOrdersTodayCount'];
    $activeOrdersCount = $dashData['activeOrdersCount'];
    $totalTables = $dashData['totalTables'];
    $occupiedTables = $dashData['occupiedTables'];
    $avgBasketMinor = $dashData['averageBasketMinor'];
    $pendingAssistance = $dashData['pendingAssistance'];
    $lateOrders = $dashData['lateOrders'];
    $recentOrders = $dashData['recentOrders'];
    $tables = $dashData['tables'];
    $occupiedTableIds = $dashData['occupiedTableIds'];
@endphp

<div class="space-y-6">
    <!-- Bandeau Alertes Opérationnelles Urgentes (Retards > 45min ou Demandes d'assistance) -->
    @if($lateOrders->isNotEmpty() || $pendingAssistance->isNotEmpty())
        <div class="space-y-3">
            @foreach($lateOrders as $lateOrder)
                <div class="bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-400 p-4 rounded-brand-lg flex items-center justify-between animate-pulse">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <p class="text-sm font-bold">Alerte Retard Critique (+45 min) — Commande #{{ $lateOrder->number }}</p>
                            <p class="text-xs text-red-600/80 dark:text-red-400/80">
                                Lancée il y a {{ $lateOrder->created_at->diffInMinutes(now()) }} minutes • {{ $lateOrder->mode === 'dine_in' ? 'Table ' . ($lateOrder->restaurantTable?->label ?? 'N/A') : 'À emporter' }}
                            </p>
                        </div>
                    </div>
                    <a href="?view=kds&restaurant={{ $restaurant->slug }}" target="_blank" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-brand transition-colors shadow-sm">
                        Voir en Cuisine
                    </a>
                </div>
            @endforeach

            @foreach($pendingAssistance as $assistance)
                <div class="bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-300 p-4 rounded-brand-lg flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <div>
                            <p class="text-sm font-bold">Appel Serveur — {{ $assistance->restaurantTable?->label ?? 'Table' }}</p>
                            <p class="text-xs text-amber-700/80 dark:text-amber-300/80">
                                Motif : {{ $assistance->type === 'waiter' ? 'Service / Demande en salle' : ($assistance->type === 'bill' ? 'Demande de l\'addition' : 'Autre') }}
                                @if($assistance->message) • « {{ $assistance->message }} » @endif
                            </p>
                        </div>
                    </div>
                    <button type="button" class="btn-acknowledge-assistance px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-brand transition-colors shadow-sm" data-id="{{ $assistance->id }}">
                        Acquitter l'appel
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Grille des KPIs du Jour -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Chiffre d'Affaires du Jour -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-5 shadow-sm hover:border-brand-orange/40 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Recettes Aujourd'hui</span>
                <span class="p-2 rounded-brand bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-black text-brand-text dark:text-white">
                    {{ number_format($chargesTodayMinor / 100, 0, ',', ' ') }} <span class="text-xs font-semibold text-gray-500">{{ $restaurant->currency }}</span>
                </p>
                <p class="text-[11px] text-gray-500 mt-1">Paiements encaissés avec succès</p>
            </div>
        </div>

        <!-- Commandes en Cours -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-5 shadow-sm hover:border-brand-orange/40 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Commandes Actives</span>
                <span class="p-2 rounded-brand bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-black text-brand-text dark:text-white">
                    {{ $activeOrdersCount }} <span class="text-xs font-semibold text-gray-500">/ {{ $totalOrdersTodayCount }} totales</span>
                </p>
                <p class="text-[11px] text-gray-500 mt-1">En attente, préparation ou prêtes</p>
            </div>
        </div>

        <!-- Occupation des Tables -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-5 shadow-sm hover:border-brand-orange/40 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Occupation Salle</span>
                <span class="p-2 rounded-brand bg-brand-orange/10 text-brand-orange">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-black text-brand-text dark:text-white">
                    {{ $occupiedTables }} <span class="text-xs font-semibold text-gray-500">/ {{ $totalTables }} tables</span>
                </p>
                <p class="text-[11px] text-gray-500 mt-1">
                    {{ $totalTables > 0 ? round(($occupiedTables / $totalTables) * 100) : 0 }} % d'occupation actuelle
                </p>
            </div>
        </div>

        <!-- Panier Moyen -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-5 shadow-sm hover:border-brand-orange/40 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Panier Moyen</span>
                <span class="p-2 rounded-brand bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-black text-brand-text dark:text-white">
                    {{ number_format($avgBasketMinor / 100, 0, ',', ' ') }} <span class="text-xs font-semibold text-gray-500">{{ $restaurant->currency }}</span>
                </p>
                <p class="text-[11px] text-gray-500 mt-1">Moyenne par commande aujourd'hui</p>
            </div>
        </div>
    </div>

    <!-- Raccourcis Opérationnels Directs -->
    <div class="bg-brand-orange/10 border border-brand-orange/20 rounded-brand-lg p-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-brand bg-brand-orange text-white flex items-center justify-center font-bold text-lg shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </span>
            <div>
                <h3 class="text-sm font-bold text-brand-text dark:text-white">Actions Rapides du Restaurant</h3>
                <p class="text-xs text-gray-500">Accédez directement aux postes de service ou aux outils de gestion</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="?view=pos&restaurant={{ $restaurant->slug }}" target="_blank" class="px-3.5 py-2 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Caisse POS Tactile</span>
            </a>
            <a href="?view=kds&restaurant={{ $restaurant->slug }}" target="_blank" class="px-3.5 py-2 bg-gray-900 hover:bg-black text-white dark:bg-brand-dark dark:hover:bg-gray-800 text-xs font-bold rounded-brand transition-colors shadow-sm flex items-center gap-2 border border-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                <span>Écran Cuisine KDS</span>
            </a>
            <a href="?view=restaurant&section=menu&restaurant={{ $restaurant->slug }}" class="px-3 py-2 bg-white dark:bg-brand-dark-card hover:bg-gray-50 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-brand border border-gray-200 dark:border-brand-dark-border transition-colors">
                + Nouveau Plat
            </a>
            <a href="?view=restaurant&section=tables&restaurant={{ $restaurant->slug }}" class="px-3 py-2 bg-white dark:bg-brand-dark-card hover:bg-gray-50 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-brand border border-gray-200 dark:border-brand-dark-border transition-colors">
                QR Codes des Tables
            </a>
        </div>
    </div>

    <!-- Grille Deux Colonnes : Flux des Dernières Commandes & État des Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 2/3 : Dernières Commandes -->
        <div class="lg:col-span-2 bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-brand-text dark:text-white">Commandes Récentes</h3>
                    <p class="text-xs text-gray-500">Flux d'activité du service en direct</p>
                </div>
                <a href="?view=restaurant&section=orders&restaurant={{ $restaurant->slug }}" class="text-xs font-bold text-brand-orange hover:text-brand-orange-dark">
                    Toutes les commandes →
                </a>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-brand-dark-border overflow-x-auto">
                @forelse($recentOrders as $order)
                    <div class="p-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:hover:bg-brand-dark/50 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-brand bg-gray-100 dark:bg-brand-dark text-gray-700 dark:text-gray-300 flex items-center justify-center font-mono text-xs font-bold shrink-0">
                                #{{ substr($order->number, -4) }}
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-brand-text dark:text-white truncate">
                                        {{ $order->customer_name ?: 'Client sans nom' }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-mono">
                                        {{ $order->created_at ? $order->created_at->format('H:i') : '' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $order->mode === 'dine_in' ? 'Table ' . ($order->restaurantTable?->label ?? 'N/A') : 'À emporter' }} • {{ isset($order->items) ? $order->items->count() : 1 }} article(s)
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <!-- Statut de Préparation -->
                            @php
                                $statusStyles = [
                                    'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-300',
                                    'accepted' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-300',
                                    'preparing' => 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border-purple-300',
                                    'ready' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-300',
                                    'served' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-300',
                                    'completed' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-300',
                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300 border-red-300',
                                ];
                                $statusLabels = [
                                    'pending' => 'En attente',
                                    'accepted' => 'Acceptée',
                                    'preparing' => 'En cuisine',
                                    'ready' => 'Prête',
                                    'served' => 'Servie',
                                    'completed' => 'Terminée',
                                    'cancelled' => 'Annulée',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $statusStyles[$order->preparation_status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$order->preparation_status] ?? $order->preparation_status }}
                            </span>

                            <!-- Montant -->
                            <span class="text-sm font-black text-brand-text dark:text-white w-20 text-right">
                                {{ number_format($order->total_minor / 100, 0, ',', ' ') }} <span class="text-[10px] text-gray-500 font-normal">{{ $restaurant->currency }}</span>
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 text-sm">
                        Aucune commande enregistrée aujourd'hui.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 1/3 : Plan Rapide des Tables -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg shadow-sm p-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:divide-brand-dark-border">
                    <h3 class="text-sm font-bold text-brand-text dark:text-white">Plan de Salle</h3>
                    <a href="?view=restaurant&section=tables&restaurant={{ $restaurant->slug }}" class="text-xs font-semibold text-brand-orange hover:underline">
                        Gérer les tables →
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4">
                    @forelse($tables->take(6) as $tbl)
                        @php
                            $isOccupied = in_array($tbl->id, $occupiedTableIds);
                        @endphp
                        <div class="p-3 rounded-brand border {{ $isOccupied ? 'border-amber-400 bg-amber-50/50 dark:bg-amber-950/20' : 'border-gray-200 dark:border-brand-dark-border bg-gray-50/50 dark:bg-brand-dark/40' }} flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-brand-text dark:text-white truncate">{{ $tbl->label }}</span>
                                <span class="w-2 h-2 rounded-full {{ $isOccupied ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                            </div>
                            <div class="mt-2 text-[11px] text-gray-500 flex items-center justify-between">
                                <span>{{ $tbl->capacity }} places</span>
                                <span class="font-semibold {{ $isOccupied ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $isOccupied ? 'Occupée' : 'Libre' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-6 text-xs text-gray-400">
                            Aucune table configurée.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Aperçu QR Code Général Takeaway -->
            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-brand-dark-border">
                <div class="p-3 bg-brand-light dark:bg-brand-orange/10 rounded-brand flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-brand-orange">QR Code À Emporter</p>
                        <p class="text-[11px] text-gray-500 truncate">Menu digital pour commandes directes</p>
                    </div>
                    <a href="/?restaurant={{ $restaurant->slug }}" target="_blank" class="px-2.5 py-1 bg-brand-orange text-white text-[11px] font-bold rounded hover:bg-brand-orange-dark transition-colors shrink-0">
                        Ouvrir
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

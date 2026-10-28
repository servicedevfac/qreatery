<x-layouts.super-admin
    title="Abonnements & Règlements SaaS"
    headerTitle="Abonnements & Règlements"
    active="subscriptions"
    :adminUser="$adminUser ?? auth()->user()"
>
    @php
        $availablePlans = $plans ?? \App\Models\Plan::query()->where('is_active', true)->orderBy('price_minor')->get();
        $restaurantsList = \App\Models\Restaurant::query()->orderBy('name')->get(['id', 'name', 'slug', 'currency']);
    @endphp

    <x-slot name="headerActions">
        <div class="flex items-center gap-3">
            <span id="subscriptions-total-badge" class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 dark:bg-orange-950/80 text-brand-orange dark:text-orange-400 border border-orange-200 dark:border-orange-900/50">
                0 contrat
            </span>

            <button
                type="button"
                id="btn-refresh-subscriptions"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-brand text-xs font-semibold bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-brand-dark transition-colors focus-ring touch-target"
                aria-label="Actualiser la liste des abonnements"
            >
                <svg id="subscriptions-refresh-icon" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Actualiser</span>
            </button>

            <button
                type="button"
                id="btn-create-subscription"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-brand text-xs font-semibold bg-brand-orange hover:bg-brand-orange-dark text-white shadow-sm transition-colors focus-ring touch-target"
                aria-label="Souscrire un nouvel abonnement"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nouvel abonnement</span>
            </button>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- En-tête de section avec explications et indicateurs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-brand-dark-border pb-4">
            <div>
                <h2 class="text-xl font-black text-brand-text dark:text-white tracking-tight">
                    Supervision des Abonnements & Facturations SaaS
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Gestion des contrats, périodes de grâce (DEC-07 / DEC-08), encaissements et historiques de paiement.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-brand-dark-border">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Règle DEC-07 Active (7j grâce)
                </span>
            </div>
        </div>

        <!-- 4 Cartes Métriques / KPIs en direct -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Abonnements Actifs</span>
                    <span class="w-8 h-8 rounded-brand bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span id="kpi-active-count" class="text-2xl font-black text-brand-text dark:text-white">—</span>
                    <span class="text-[11px] font-medium text-emerald-600">en règle</span>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Période de Grâce (DEC-07)</span>
                    <span class="w-8 h-8 rounded-brand bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span id="kpi-grace-count" class="text-2xl font-black text-amber-600 dark:text-amber-400">—</span>
                    <span class="text-[11px] font-medium text-amber-600">relances en cours</span>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Suspendus / Expirés</span>
                    <span class="w-8 h-8 rounded-brand bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span id="kpi-suspended-count" class="text-2xl font-black text-rose-600 dark:text-rose-400">—</span>
                    <span class="text-[11px] font-medium text-rose-600">accès bloqués (DEC-08)</span>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Paiements Récoltés</span>
                    <span class="w-8 h-8 rounded-brand bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span id="kpi-payments-total" class="text-2xl font-black text-blue-600 dark:text-blue-400">—</span>
                    <span class="text-[11px] font-medium text-gray-400">règlements</span>
                </div>
            </div>
        </div>

        <!-- Barre de recherche et filtres -->
        <div class="p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs space-y-3">
            <div class="flex flex-col lg:flex-row gap-3">
                <!-- Recherche par nom de restaurant ou de plan -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="search"
                        id="subscriptions-search-input"
                        placeholder="Rechercher par restaurant, formule ou note..."
                        class="w-full pl-10 pr-9 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                        aria-label="Rechercher un abonnement"
                    >
                    <button
                        type="button"
                        id="subscriptions-search-clear"
                        class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 touch-target"
                        aria-label="Effacer la recherche"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Filtres par Statut et Formule -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Filtre Statut -->
                    <select
                        id="subscriptions-status-filter"
                        class="px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all"
                    >
                        <option value="">Tous les statuts</option>
                        <option value="active">Actif</option>
                        <option value="grace_period">Période de grâce</option>
                        <option value="trialing">Période d'essai</option>
                        <option value="suspended">Suspendu</option>
                        <option value="expired">Expiré</option>
                        <option value="cancelled">Résilié</option>
                    </select>

                    <!-- Filtre Plan -->
                    <select
                        id="subscriptions-plan-filter"
                        class="px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all"
                    >
                        <option value="">Toutes les formules</option>
                        @foreach($availablePlans as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>

                    <!-- Bouton réinitialiser -->
                    <button
                        type="button"
                        id="btn-reset-subscription-filters"
                        class="px-3 py-2 rounded-brand text-xs font-semibold bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors focus-ring touch-target"
                        title="Réinitialiser les filtres"
                    >
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        <!-- ÉTAT 1 : Chargement (Skeleton Loader) -->
        <div id="subscriptions-loading-state" class="space-y-4 animate-pulse" aria-busy="true">
            <div class="h-64 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border p-4 space-y-3">
                <div class="h-6 bg-gray-200 dark:bg-gray-800 rounded w-1/4"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-1/2"></div>
                <div class="h-10 bg-gray-200 dark:bg-gray-800 rounded"></div>
                <div class="h-10 bg-gray-200 dark:bg-gray-800 rounded"></div>
            </div>
        </div>

        <!-- ÉTAT 2 : Erreur -->
        <div
            id="subscriptions-error-state"
            class="hidden p-6 bg-rose-50 dark:bg-rose-950/40 rounded-brand-lg border border-rose-200 dark:border-rose-900/50 text-center space-y-4"
            role="alert"
        >
            <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-rose-900 dark:text-rose-200">
                    Erreur de chargement des abonnements
                </h3>
                <p id="subscriptions-error-message" class="text-xs text-rose-700 dark:text-rose-300 max-w-md mx-auto">
                    Une erreur est survenue lors de la communication avec l'API Super-Admin.
                </p>
            </div>
            <button
                type="button"
                id="btn-retry-subscriptions"
                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-brand text-xs font-semibold shadow-sm focus-ring touch-target"
            >
                Réessayer
            </button>
        </div>

        <!-- ÉTAT 3 : Liste vide -->
        <div
            id="subscriptions-empty-state"
            class="hidden p-12 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border text-center space-y-4"
        >
            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-brand-dark text-gray-400 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-brand-text dark:text-white">
                    Aucun abonnement trouvé
                </h3>
                <p id="subscriptions-empty-message" class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    Aucun contrat ne correspond aux critères de filtre sélectionnés.
                </p>
            </div>
            <div class="flex items-center justify-center gap-2 pt-1">
                <button
                    type="button"
                    id="btn-empty-reset-sub"
                    class="px-3.5 py-1.5 bg-brand-light dark:bg-brand-orange/10 text-brand-orange text-xs font-semibold rounded-brand hover:bg-brand-orange hover:text-white transition-colors"
                >
                    Effacer les filtres
                </button>
                <button
                    type="button"
                    id="btn-empty-create-sub"
                    class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-semibold rounded-brand transition-colors"
                >
                    + Créer un abonnement
                </button>
            </div>
        </div>

        <!-- ÉTAT 4 : Tableau & Cartes Succès -->
        <div id="subscriptions-content" class="hidden space-y-4">
            <!-- Vue Desktop : Tableau complet -->
            <div class="hidden md:block bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300" aria-label="Liste des abonnements SaaS">
                        <thead class="bg-gray-50 dark:bg-brand-dark border-b border-gray-200 dark:border-brand-dark-border text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="py-3 px-4">Établissement</th>
                                <th scope="col" class="py-3 px-4">Formule SaaS</th>
                                <th scope="col" class="py-3 px-4">Période &amp; Échéance</th>
                                <th scope="col" class="py-3 px-4 text-center">Statut</th>
                                <th scope="col" class="py-3 px-4">Règlements</th>
                                <th scope="col" class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="subscriptions-table-body" class="divide-y divide-gray-100 dark:divide-brand-dark-border font-medium">
                            <!-- Injecté dynamiquement par admin.js -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vue Mobile : Cartes individuelles -->
            <div id="subscriptions-mobile-cards" class="md:hidden space-y-3">
                <!-- Injecté dynamiquement par admin.js -->
            </div>

            <!-- Pagination -->
            <div id="subscriptions-pagination" class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs text-xs">
                <div id="sub-pagination-info" class="text-gray-500 dark:text-gray-400">
                    Affichage de 0 à 0 sur 0 contrat(s)
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        id="btn-sub-pagination-prev"
                        class="px-3 py-1.5 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-white dark:bg-brand-dark text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus-ring touch-target"
                        disabled
                        aria-label="Page précédente"
                    >
                        &larr; Précédent
                    </button>
                    <span id="sub-pagination-current-page" class="px-2 font-bold text-brand-orange">
                        1
                    </span>
                    <button
                        type="button"
                        id="btn-sub-pagination-next"
                        class="px-3 py-1.5 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-white dark:bg-brand-dark text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus-ring touch-target"
                        disabled
                        aria-label="Page suivante"
                    >
                        Suivant &rarr;
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- 1. Modale Création d'un Abonnement SaaS -->
    <!-- ===================================================================== -->
    <div
        id="create-subscription-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="create-sub-modal-title"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center transition-all duration-200 hidden"
    >
        <div id="create-sub-modal-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" aria-hidden="true"></div>

        <div class="relative w-full max-w-xl bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10 max-h-[90vh] flex flex-col">
            <div class="p-5 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between shrink-0 bg-gray-50/50 dark:bg-brand-dark/40">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-brand bg-brand-orange/10 text-brand-orange flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 id="create-sub-modal-title" class="text-base font-bold text-brand-text dark:text-white">
                            Activer un nouvel abonnement
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Rattachez un restaurant à une formule SaaS avec calcul automatique de l'échéance.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    id="btn-close-create-sub-modal"
                    class="touch-target p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-brand focus-ring"
                    aria-label="Fermer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="create-subscription-form" class="p-6 space-y-4 overflow-y-auto flex-1">
                <div id="create-sub-general-error" class="hidden p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-brand text-xs text-rose-700 dark:text-rose-300"></div>

                <!-- Sélection Restaurant -->
                <div class="space-y-1">
                    <label for="create-sub-restaurant-id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Établissement <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="create-sub-restaurant-id"
                        name="restaurant_id"
                        required
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none font-medium"
                    >
                        <option value="">-- Choisir un restaurant --</option>
                        @foreach($restaurantsList as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} (/{{ $r->slug }})</option>
                        @endforeach
                    </select>
                    <p id="create-sub-error-restaurant_id" class="hidden text-[11px] text-rose-500 font-medium"></p>
                </div>

                <!-- Sélection Plan SaaS -->
                <div class="space-y-1">
                    <label for="create-sub-plan-id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Formule SaaS <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="create-sub-plan-id"
                        name="plan_id"
                        required
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none font-semibold text-brand-orange"
                    >
                        <option value="">-- Choisir un plan tarifaire --</option>
                        @foreach($availablePlans as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->price_minor }}" data-period="{{ $p->billing_period }}">
                                {{ $p->name }} — {{ number_format($p->price_minor / 100, 0, ',', ' ') }} {{ strtoupper($p->currency) }} / {{ $p->billing_period }}
                            </option>
                        @endforeach
                    </select>
                    <p id="create-sub-error-plan_id" class="hidden text-[11px] text-rose-500 font-medium"></p>
                </div>

                <!-- Date de Début & Période de Grâce -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="create-sub-starts-at" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Date de début
                        </label>
                        <input
                            type="datetime-local"
                            id="create-sub-starts-at"
                            name="starts_at"
                            value="{{ now()->format('Y-m-d\TH:i') }}"
                            class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none"
                        >
                        <p id="create-sub-error-starts_at" class="hidden text-[11px] text-rose-500 font-medium"></p>
                    </div>

                    <div class="space-y-1">
                        <label for="create-sub-grace-days" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Jours de grâce post-expiration (DEC-07)
                        </label>
                        <input
                            type="number"
                            id="create-sub-grace-days"
                            name="grace_days"
                            value="7"
                            min="0"
                            max="90"
                            class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none font-semibold"
                        >
                        <span class="text-[10px] text-gray-400">7 jours par défaut selon l'arbitrage officiel DEC-07</span>
                        <p id="create-sub-error-grace_days" class="hidden text-[11px] text-rose-500 font-medium"></p>
                    </div>
                </div>

                <!-- Statut initial -->
                <div class="space-y-1">
                    <label for="create-sub-status" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Statut initial
                    </label>
                    <select
                        id="create-sub-status"
                        name="status"
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none"
                    >
                        <option value="active" selected>Actif (Opérationnel)</option>
                        <option value="trialing">Période d'essai (Trial)</option>
                    </select>
                </div>

                <!-- Notes / Commentaire -->
                <div class="space-y-1">
                    <label for="create-sub-notes" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Notes administratives
                    </label>
                    <textarea
                        id="create-sub-notes"
                        name="notes"
                        rows="2"
                        placeholder="Contrat annuel signé, accord commercial ou remise promotionnelle..."
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                    ></textarea>
                </div>

                <!-- Boutons d'action -->
                <div class="pt-4 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        id="btn-cancel-create-sub-modal"
                        class="px-4 py-2 rounded-brand text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-brand-dark hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors focus-ring touch-target"
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        id="btn-submit-create-sub-modal"
                        class="px-5 py-2 rounded-brand text-xs font-bold text-white bg-brand-orange hover:bg-brand-orange-dark shadow-sm focus-ring touch-target flex items-center gap-2"
                    >
                        <span id="create-sub-submit-spinner" class="hidden animate-spin w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span id="create-sub-submit-text">Activer l'abonnement</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- 2. Modale Changement de Statut d'Abonnement -->
    <!-- ===================================================================== -->
    <div
        id="subscription-status-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="sub-status-modal-title"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center transition-all duration-200 hidden"
    >
        <div id="sub-status-modal-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" aria-hidden="true"></div>

        <div class="relative w-full max-w-md bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10">
            <div class="p-5 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div id="sub-status-modal-icon-container" class="w-8 h-8 rounded-brand flex items-center justify-center bg-brand-orange/10 text-brand-orange">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 id="sub-status-modal-title" class="text-base font-bold text-brand-text dark:text-white">
                        Modifier le statut de l'abonnement
                    </h3>
                </div>
                <button
                    type="button"
                    id="btn-close-sub-status-modal"
                    class="touch-target p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-brand focus-ring"
                    aria-label="Fermer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="subscription-status-form" class="p-5 space-y-4">
                <input type="hidden" id="sub-status-modal-id">

                <!-- Contexte contrat -->
                <div class="p-3 rounded-brand bg-gray-50 dark:bg-brand-dark/50 border border-gray-100 dark:border-brand-dark-border">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Contrat concerné :</p>
                    <p id="sub-status-modal-restaurant-name" class="text-sm font-bold text-brand-text dark:text-white mt-0.5"></p>
                    <p id="sub-status-modal-plan-info" class="text-[11px] text-brand-orange font-semibold"></p>
                </div>

                <!-- Nouveau statut -->
                <div class="space-y-1.5">
                    <label for="sub-status-modal-target-status" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Nouveau statut <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="sub-status-modal-target-status"
                        required
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none"
                    >
                        <option value="active">Actif (Rétablir les accès complets)</option>
                        <option value="grace_period">Période de grâce (Alerte persistante - DEC-07)</option>
                        <option value="suspended">Suspendu (Blocage des commandes - DEC-08)</option>
                        <option value="cancelled">Résilié (Clôture définitive du contrat)</option>
                    </select>
                </div>

                <!-- Motif obligatoire en suspension/résiliation -->
                <div class="space-y-1.5">
                    <label for="sub-status-modal-reason" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Motif de la transition <span id="sub-status-reason-required" class="text-rose-500">*</span>
                    </label>
                    <textarea
                        id="sub-status-modal-reason"
                        rows="2"
                        placeholder="Précisez la raison (ex: régularisation, impayé, résiliation demandée)..."
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                    ></textarea>
                    <p id="sub-status-modal-error" class="hidden text-[11px] text-rose-500 font-medium"></p>
                </div>

                <!-- Boutons -->
                <div class="pt-3 border-t border-gray-100 dark:border-brand-dark-border flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        id="btn-cancel-sub-status-modal"
                        class="px-3.5 py-2 rounded-brand text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-brand-dark hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors focus-ring touch-target"
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        id="btn-submit-sub-status-modal"
                        class="px-4 py-2 rounded-brand text-xs font-semibold text-white bg-brand-orange hover:bg-brand-orange-dark shadow-sm focus-ring touch-target flex items-center gap-1.5"
                    >
                        <span id="sub-status-submit-spinner" class="hidden animate-spin w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span id="sub-status-submit-text">Confirmer le statut</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- 3. Modale Enregistrement d'un Règlement (Paiement) -->
    <!-- ===================================================================== -->
    <div
        id="subscription-payment-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="sub-payment-modal-title"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center transition-all duration-200 hidden"
    >
        <div id="sub-payment-modal-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" aria-hidden="true"></div>

        <div class="relative w-full max-w-md bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10">
            <div class="p-5 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-brand bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 id="sub-payment-modal-title" class="text-base font-bold text-brand-text dark:text-white">
                        Enregistrer un règlement
                    </h3>
                </div>
                <button
                    type="button"
                    id="btn-close-sub-payment-modal"
                    class="touch-target p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-brand focus-ring"
                    aria-label="Fermer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="subscription-payment-form" class="p-5 space-y-4">
                <input type="hidden" id="sub-payment-modal-id">

                <div class="p-3 rounded-brand bg-gray-50 dark:bg-brand-dark/50 border border-gray-100 dark:border-brand-dark-border">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Règlement pour :</p>
                    <p id="sub-payment-modal-restaurant-name" class="text-sm font-bold text-brand-text dark:text-white mt-0.5"></p>
                    <p id="sub-payment-modal-plan-info" class="text-[11px] text-gray-400 font-mono"></p>
                </div>

                <!-- Montant et Devise -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2 space-y-1">
                        <label for="sub-payment-modal-amount" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Montant réglé <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="sub-payment-modal-amount"
                            required
                            min="1"
                            step="any"
                            placeholder="35000"
                            class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none font-bold"
                        >
                    </div>

                    <div class="space-y-1">
                        <label for="sub-payment-modal-currency" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Devise
                        </label>
                        <select
                            id="sub-payment-modal-currency"
                            class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none font-semibold"
                        >
                            <option value="XOF" selected>XOF</option>
                            <option value="EUR">EUR</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                </div>

                <!-- Méthode de paiement -->
                <div class="space-y-1">
                    <label for="sub-payment-modal-method" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Mode de paiement <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="sub-payment-modal-method"
                        required
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none"
                    >
                        <option value="bank_transfer" selected>Virement bancaire</option>
                        <option value="mobile_money">Mobile Money (Wave, Orange, MTN)</option>
                        <option value="card">Carte bancaire (Visa / Mastercard)</option>
                        <option value="cash">Espèces</option>
                        <option value="other">Autre moyen</option>
                    </select>
                </div>

                <!-- Référence de transaction -->
                <div class="space-y-1">
                    <label for="sub-payment-modal-reference" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Référence de transaction / reçu
                    </label>
                    <input
                        type="text"
                        id="sub-payment-modal-reference"
                        placeholder="ex: VIR-2026-1029 ou Wave-TX99"
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none font-mono text-xs"
                    >
                </div>

                <!-- Statut du paiement -->
                <div class="space-y-1">
                    <label for="sub-payment-modal-status" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Statut de l'encaissement
                    </label>
                    <select
                        id="sub-payment-modal-status"
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none font-semibold text-emerald-600"
                    >
                        <option value="succeeded" selected>Validé / Réussi</option>
                        <option value="pending">En attente de validation</option>
                        <option value="failed">Échoué</option>
                    </select>
                </div>

                <p id="sub-payment-modal-error" class="hidden text-[11px] text-rose-500 font-medium"></p>

                <!-- Boutons -->
                <div class="pt-3 border-t border-gray-100 dark:border-brand-dark-border flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        id="btn-cancel-sub-payment-modal"
                        class="px-3.5 py-2 rounded-brand text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-brand-dark hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors focus-ring touch-target"
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        id="btn-submit-sub-payment-modal"
                        class="px-4 py-2 rounded-brand text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm focus-ring touch-target flex items-center gap-1.5"
                    >
                        <span id="sub-payment-submit-spinner" class="hidden animate-spin w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span id="sub-payment-submit-text">Valider l'encaissement</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- 4. Modale Détails & Historique d'un Contrat -->
    <!-- ===================================================================== -->
    <div
        id="subscription-details-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="sub-details-modal-title"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center transition-all duration-200 hidden"
    >
        <div id="sub-details-modal-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" aria-hidden="true"></div>

        <div class="relative w-full max-w-2xl bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10 max-h-[90vh] flex flex-col">
            <div class="p-5 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between shrink-0 bg-gray-50/50 dark:bg-brand-dark/40">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-brand bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 id="sub-details-modal-title" class="text-base font-bold text-brand-text dark:text-white">
                            Détails du Contrat &amp; Règlements
                        </h3>
                        <p id="sub-details-modal-subtitle" class="text-xs text-gray-500 dark:text-gray-400"></p>
                    </div>
                </div>
                <button
                    type="button"
                    id="btn-close-sub-details-modal"
                    class="touch-target p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-brand focus-ring"
                    aria-label="Fermer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-5 overflow-y-auto flex-1">
                <!-- Récapitulatif du contrat -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3.5 rounded-brand bg-gray-50 dark:bg-brand-dark/50 border border-gray-100 dark:border-brand-dark-border text-xs">
                    <div>
                        <span class="block text-[10px] uppercase text-gray-400">Statut</span>
                        <span id="sub-details-status" class="font-bold"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase text-gray-400">Formule</span>
                        <span id="sub-details-plan" class="font-bold text-brand-orange"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase text-gray-400">Début</span>
                        <span id="sub-details-starts" class="font-medium text-gray-700 dark:text-gray-300"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase text-gray-400">Échéance</span>
                        <span id="sub-details-ends" class="font-medium text-gray-700 dark:text-gray-300"></span>
                    </div>
                </div>

                <!-- Historique des Paiements -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200 flex items-center justify-between">
                        <span>Paiements enregistrés</span>
                        <span id="sub-details-payments-count" class="text-[11px] font-normal text-gray-400"></span>
                    </h4>
                    <div id="sub-details-payments-list" class="space-y-1.5 max-h-48 overflow-y-auto">
                        <!-- Rempli en JS -->
                    </div>
                </div>

                <!-- Historique des Transitions de Statut -->
                <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-brand-dark-border">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                        Journal des transitions de statut
                    </h4>
                    <div id="sub-details-history-list" class="space-y-1.5 max-h-48 overflow-y-auto">
                        <!-- Rempli en JS -->
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end">
                <button
                    type="button"
                    id="btn-dismiss-sub-details-modal"
                    class="px-4 py-2 rounded-brand text-xs font-semibold bg-gray-100 dark:bg-brand-dark text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors"
                >
                    Fermer
                </button>
            </div>
        </div>
    </div>
</x-layouts.super-admin>

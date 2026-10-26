<x-layouts.super-admin
    title="Tableau de bord SaaS"
    headerTitle="Tableau de bord SaaS"
    active="dashboard"
    :adminUser="$adminUser ?? auth()->user()"
>
    <x-slot name="headerActions">
        <div class="flex items-center gap-3">
            <span id="dashboard-last-updated" class="text-xs text-gray-500 dark:text-gray-400 hidden sm:inline">
                Initialisation...
            </span>
            <button
                type="button"
                id="btn-refresh-dashboard"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-brand text-xs font-semibold bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-brand-dark transition-colors focus-ring touch-target"
                aria-label="Actualiser les indicateurs du tableau de bord"
            >
                <svg id="dashboard-refresh-icon" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Actualiser</span>
            </button>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- En-tête de section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-brand-dark-border pb-4">
            <div>
                <h2 class="text-xl font-black text-brand-text dark:text-white tracking-tight">
                    Supervision Plateforme SaaS
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Indicateurs opérationnels, suivi des établissements abonnés et flux financiers directs.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 dark:bg-brand-orange/10 text-brand-orange border border-orange-200 dark:border-brand-orange/20">
                    <span class="w-2 h-2 rounded-full bg-brand-orange animate-pulse"></span>
                    Temps réel API
                </span>
            </div>
        </div>

        <!-- ÉTAT 1 : Chargement (Skeleton loaders accessibles) -->
        <div id="dashboard-loading-state" class="space-y-6 animate-pulse" aria-busy="true" aria-label="Chargement des données du tableau de bord">
            <!-- Skeleton Alerte -->
            <div class="h-16 bg-gray-200 dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border"></div>

            <!-- Skeleton Cartes KPI -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="h-32 bg-gray-200 dark:bg-brand-dark-card rounded-brand-lg"></div>
                <div class="h-32 bg-gray-200 dark:bg-brand-dark-card rounded-brand-lg"></div>
                <div class="h-32 bg-gray-200 dark:bg-brand-dark-card rounded-brand-lg"></div>
            </div>

            <!-- Skeleton Souscriptions & Finances -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="h-44 bg-gray-200 dark:bg-brand-dark-card rounded-brand-lg"></div>
                <div class="h-44 bg-gray-200 dark:bg-brand-dark-card rounded-brand-lg"></div>
            </div>
        </div>

        <!-- ÉTAT 2 : Erreur avec bouton de réessai -->
        <div
            id="dashboard-error-state"
            class="hidden p-6 bg-rose-50 dark:bg-rose-950/40 rounded-brand-lg border border-rose-200 dark:border-rose-900/50 text-center space-y-4"
            role="alert"
        >
            <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-rose-900 dark:text-rose-200">
                    Impossible de charger les indicateurs
                </h3>
                <p id="dashboard-error-message" class="text-xs text-rose-700 dark:text-rose-300 max-w-md mx-auto">
                    Une erreur est survenue lors de la récupération des données de l'API Super-Admin.
                </p>
            </div>
            <div>
                <button
                    type="button"
                    id="btn-retry-dashboard"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-brand text-xs font-semibold shadow-sm focus-ring touch-target"
                >
                    Réessayer
                </button>
            </div>
        </div>

        <!-- ÉTAT 3 : Données indisponibles ou nulles -->
        <div
            id="dashboard-empty-state"
            class="hidden p-10 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border text-center space-y-3"
        >
            <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <h3 class="text-base font-bold text-brand-text dark:text-white">
                Aucun indicateur disponible
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                L'API Super-Admin a retourné un jeu de données vide. Vérifiez l'état de la plateforme.
            </p>
        </div>

        <!-- ÉTAT 4 : Succès & Affichage des données réelles -->
        <div id="dashboard-content" class="hidden space-y-6">
            <!-- Bannière d'échéances critiques sous 7 jours -->
            <div
                id="banner-expiring-alert"
                class="p-4 rounded-brand-lg border transition-all duration-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs"
            >
                <div class="flex items-center gap-3">
                    <div id="banner-expiring-icon" class="w-9 h-9 rounded-brand flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 id="banner-expiring-title" class="text-sm font-bold">
                            Échéances sous 7 jours
                        </h4>
                        <p id="banner-expiring-desc" class="text-xs mt-0.5">
                            Surveillance des abonnements approchant de leur terme.
                        </p>
                    </div>
                </div>
                <a
                    href="{{ Route::has('admin.subscriptions') ? route('admin.subscriptions') : url('/admin?section=subscriptions') }}"
                    class="text-xs font-semibold px-3 py-1.5 rounded-brand border transition-colors touch-target self-start sm:self-center"
                    id="banner-expiring-link"
                >
                    Voir les abonnements &rarr;
                </a>
            </div>

            <!-- SECTION A : INDICATEURS DES ÉTABLISSEMENTS (RESTAURANTS) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Établissements (Tenants)
                    </h3>
                    <a
                        href="{{ Route::has('admin.restaurants') ? route('admin.restaurants') : url('/admin?section=restaurants') }}"
                        class="text-xs font-medium text-brand-orange hover:text-brand-orange-dark transition-colors"
                    >
                        Gérer les restaurants &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Total Restaurants -->
                    <div class="p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Établissements</span>
                        <div class="mt-2 flex items-baseline justify-between">
                            <span id="kpi-restaurants-total" class="text-2xl sm:text-3xl font-black text-brand-text dark:text-white">0</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                Inscrits
                            </span>
                        </div>
                    </div>

                    <!-- Restaurants Actifs -->
                    <div class="p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Établissements Actifs</span>
                        <div class="mt-2 flex items-baseline justify-between">
                            <span id="kpi-restaurants-active" class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">0</span>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Opérationnels
                            </span>
                        </div>
                    </div>

                    <!-- Restaurants Suspendus -->
                    <div class="p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Établissements Suspendus</span>
                        <div class="mt-2 flex items-baseline justify-between">
                            <span id="kpi-restaurants-suspended" class="text-2xl sm:text-3xl font-black text-rose-600 dark:text-rose-400">0</span>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Bloqués
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION B & C : ABONNEMENTS ET FINANCES SAAS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Souscriptions SaaS -->
                <div class="p-5 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-brand-dark-border pb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Santé des Abonnements
                        </h3>
                        <a
                            href="{{ Route::has('admin.subscriptions') ? route('admin.subscriptions') : url('/admin?section=subscriptions') }}"
                            class="text-xs text-brand-orange hover:underline font-medium"
                        >
                            Détails &rarr;
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <!-- Actifs -->
                        <div class="p-3 bg-gray-50 dark:bg-brand-dark/50 rounded-brand border border-gray-100 dark:border-brand-dark-border">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block">Actifs</span>
                            <span id="kpi-subscriptions-active" class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 block">0</span>
                        </div>

                        <!-- Période de grâce -->
                        <div class="p-3 bg-gray-50 dark:bg-brand-dark/50 rounded-brand border border-gray-100 dark:border-brand-dark-border">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block">En période de grâce</span>
                            <span id="kpi-subscriptions-grace" class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1 block">0</span>
                            <span class="text-[10px] text-gray-400 block mt-0.5">Délai 7 jours actif</span>
                        </div>

                        <!-- Expirés -->
                        <div class="p-3 bg-gray-50 dark:bg-brand-dark/50 rounded-brand border border-gray-100 dark:border-brand-dark-border">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block">Expirés</span>
                            <span id="kpi-subscriptions-expired" class="text-xl font-bold text-rose-600 dark:text-rose-400 mt-1 block">0</span>
                        </div>

                        <!-- Échéance sous 7 jours -->
                        <div class="p-3 bg-gray-50 dark:bg-brand-dark/50 rounded-brand border border-gray-100 dark:border-brand-dark-border">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block">Échéance &le; 7 jours</span>
                            <span id="kpi-subscriptions-expiring" class="text-xl font-bold text-brand-orange mt-1 block">0</span>
                            <span class="text-[10px] text-gray-400 block mt-0.5">À renouveler</span>
                        </div>
                    </div>
                </div>

                <!-- Finances SaaS : Revenus des Abonnements -->
                <div class="p-5 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-brand-dark-border pb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Revenus des Abonnements SaaS
                        </h3>
                        <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">
                            Forfaits SaaS
                        </span>
                    </div>

                    <div class="space-y-4">
                        <!-- Revenus du mois courant (MRR) -->
                        <div class="p-4 bg-orange-50/50 dark:bg-brand-orange/5 rounded-brand border border-orange-100 dark:border-brand-orange/10 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-brand-orange uppercase tracking-wider block">Mois courant (MRR)</span>
                                <span id="kpi-revenue-current-month" class="text-2xl font-black text-brand-text dark:text-white mt-1 block">
                                    0 FCFA
                                </span>
                            </div>
                            <span class="w-8 h-8 rounded-full bg-brand-orange/10 text-brand-orange flex items-center justify-center font-bold text-sm">
                                <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </span>
                        </div>

                        <!-- Revenus Cumulés Totaux -->
                        <div class="p-3 bg-gray-50 dark:bg-brand-dark/50 rounded-brand border border-gray-100 dark:border-brand-dark-border flex items-center justify-between">
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 block">Total cumulé encaissé</span>
                                <span id="kpi-revenue-total" class="text-lg font-bold text-brand-text dark:text-white mt-0.5 block">
                                    0 FCFA
                                </span>
                            </div>
                            <span class="text-xs text-gray-400">Paiements réussis</span>
                        </div>

                        <p class="text-[11px] text-gray-400 dark:text-gray-500">
                            * Montants issus des règlements enregistrés des abonnements restaurants.
                        </p>
                    </div>
                </div>
            </div>

            <!-- SECTION D : EXCLUSION EXPLICITE DU GMV & CLARIFICATION PÉRIMÈTRE -->
            <div class="p-4 bg-gray-50 dark:bg-brand-dark/40 rounded-brand-lg border border-dashed border-gray-300 dark:border-brand-dark-border flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Volume d'affaires global (GMV)</span>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">
                            Indicateur indisponible — endpoint backend requis
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Le chiffre d'affaires cumulé des commandes des restaurants n'est pas consolidé dans l'API SaaS actuelle. Seuls les revenus directs des forfaits d'abonnement sont affichés ci-dessus.
                    </p>
                </div>
                <div class="text-[11px] text-gray-400 shrink-0">
                    Conforme REC-P6
                </div>
            </div>
        </div>
    </div>
</x-layouts.super-admin>

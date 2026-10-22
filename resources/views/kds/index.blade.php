<x-layouts.kds :restaurant-name="$restaurantName ?? 'Cuisine'">
    <!-- En-tête KDS spécifique avec métriques en direct -->
    <x-slot name="headerMetrics">
        <div class="hidden sm:flex items-center gap-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">File active :</span>
            <span id="kds-metric-total" class="px-2.5 py-0.5 rounded-full bg-brand-orange text-white text-xs font-black shadow-xs">
                0
            </span>
        </div>
        <div class="flex items-center gap-1.5 text-xs">
            <div class="flex items-center gap-1 px-2 py-1 rounded bg-brand-dark-card border border-brand-dark-border text-gray-300">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span class="text-[11px] font-semibold text-gray-400">Attente:</span>
                <span id="kds-metric-pending" class="font-bold text-white">0</span>
            </div>
            <div class="flex items-center gap-1 px-2 py-1 rounded bg-brand-dark-card border border-brand-dark-border text-gray-300">
                <span class="w-2 h-2 rounded-full bg-brand-orange"></span>
                <span class="text-[11px] font-semibold text-gray-400">Prépa:</span>
                <span id="kds-metric-preparing" class="font-bold text-white">0</span>
            </div>
            <div class="flex items-center gap-1 px-2 py-1 rounded bg-brand-dark-card border border-brand-dark-border text-gray-300">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-[11px] font-semibold text-gray-400">Prêtes:</span>
                <span id="kds-metric-ready" class="font-bold text-white">0</span>
            </div>
        </div>
    </x-slot>

    <!-- Conteneur KDS Kanban -->
    <div class="h-full flex flex-col space-y-3">
        <!-- Barre de filtres et d'outils KDS -->
        <div class="flex items-center justify-between gap-3 bg-brand-dark-card px-4 py-2.5 rounded-brand border border-brand-dark-border shrink-0">
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Filtre de mode -->
                <div class="inline-flex rounded-brand p-0.5 bg-brand-dark border border-brand-dark-border text-xs font-semibold">
                    <button type="button" class="kds-filter-mode active px-3 py-1 rounded-sm bg-brand-orange text-white transition-all" data-mode="all">
                        Toutes
                    </button>
                    <button type="button" class="kds-filter-mode px-3 py-1 rounded-sm text-gray-400 hover:text-white transition-all" data-mode="dine_in">
                        🍽️ Sur place
                    </button>
                    <button type="button" class="kds-filter-mode px-3 py-1 rounded-sm text-gray-400 hover:text-white transition-all" data-mode="takeaway">
                        🛍️ À emporter
                    </button>
                </div>

                <!-- Sélecteur de restaurant si plusieurs -->
                <div id="kds-restaurant-selector-container" class="hidden items-center gap-2">
                    <label for="kds-restaurant-select" class="text-xs text-gray-400 font-semibold">Établissement :</label>
                    <select id="kds-restaurant-select" class="bg-brand-dark border border-brand-dark-border text-white text-xs rounded-brand px-2.5 py-1 focus-ring">
                        <!-- Rempli en JS -->
                    </select>
                </div>
            </div>

            <!-- Actions droite : Rafraîchir manuellement et indicateur de mise à jour -->
            <div class="flex items-center gap-3">
                <span id="kds-last-sync-time" class="text-[11px] text-gray-400 hidden sm:inline">
                    Dernière synchro : --:--
                </span>
                <button
                    type="button"
                    id="btn-kds-refresh"
                    class="touch-target px-3 py-1.5 rounded-brand bg-brand-dark hover:bg-gray-800 border border-brand-dark-border text-xs font-semibold text-gray-200 flex items-center gap-1.5 transition-colors focus-ring"
                    title="Actualiser immédiatement"
                >
                    <svg id="kds-refresh-spinner" class="w-3.5 h-3.5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Actualiser</span>
                </button>
            </div>
        </div>

        <!-- État de chargement initial -->
        <div id="kds-loading-overlay" class="hidden flex-1 flex items-center justify-center">
            <div class="text-center space-y-3">
                <div class="inline-block animate-spin w-10 h-10 border-4 border-brand-orange border-t-transparent rounded-full"></div>
                <p class="text-sm font-semibold text-gray-300">Chargement des commandes de cuisine...</p>
            </div>
        </div>

        <!-- État d'erreur / Déconnexion -->
        <div id="kds-error-banner" class="hidden bg-rose-950/80 border border-rose-800 text-rose-200 px-4 py-3 rounded-brand flex items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2 text-xs">
                <span class="text-base">⚠️</span>
                <span id="kds-error-message">Erreur de communication avec le serveur.</span>
            </div>
            <button type="button" id="btn-kds-error-retry" class="px-2.5 py-1 bg-rose-800 hover:bg-rose-700 text-white rounded text-xs font-bold">
                Réessayer
            </button>
        </div>

        <!-- Grille Kanban des colonnes KDS -->
        <div id="kds-kanban-board" class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4 min-h-0 overflow-hidden">
            <!-- Colonne 1 : NOUVELLES COMMANDES (En attente) -->
            <div class="flex flex-col rounded-brand-lg bg-brand-dark-card border border-brand-dark-border min-h-0 overflow-hidden shadow-md">
                <!-- En-tête de colonne -->
                <div class="px-4 py-3 bg-amber-950/40 border-b border-amber-900/50 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
                        <h2 class="text-sm font-black text-amber-300 uppercase tracking-wider">
                            En Attente
                        </h2>
                    </div>
                    <span id="col-count-pending" class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-xs font-black border border-amber-500/30">
                        0
                    </span>
                </div>

                <!-- Liste défilante des tickets -->
                <div id="kds-col-pending" class="flex-1 overflow-y-auto p-3 space-y-3" tabindex="0">
                    <!-- Cartes injectées en JS -->
                </div>
            </div>

            <!-- Colonne 2 : EN PRÉPARATION (Au fourneaux) -->
            <div class="flex flex-col rounded-brand-lg bg-brand-dark-card border border-brand-dark-border min-h-0 overflow-hidden shadow-md">
                <!-- En-tête de colonne -->
                <div class="px-4 py-3 bg-brand-orange/15 border-b border-brand-orange/30 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-brand-orange"></span>
                        <h2 class="text-sm font-black text-brand-orange uppercase tracking-wider">
                            En Préparation
                        </h2>
                    </div>
                    <span id="col-count-preparing" class="px-2 py-0.5 rounded-full bg-brand-orange/20 text-brand-orange text-xs font-black border border-brand-orange/30">
                        0
                    </span>
                </div>

                <!-- Liste défilante des tickets -->
                <div id="kds-col-preparing" class="flex-1 overflow-y-auto p-3 space-y-3" tabindex="0">
                    <!-- Cartes injectées en JS -->
                </div>
            </div>

            <!-- Colonne 3 : PRÊTES AU COMPTOIR (Passe-plat) -->
            <div class="flex flex-col rounded-brand-lg bg-brand-dark-card border border-brand-dark-border min-h-0 overflow-hidden shadow-md">
                <!-- En-tête de colonne -->
                <div class="px-4 py-3 bg-emerald-950/40 border-b border-emerald-900/50 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <h2 class="text-sm font-black text-emerald-300 uppercase tracking-wider">
                            Prêtes à Servir
                        </h2>
                    </div>
                    <span id="col-count-ready" class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-black border border-emerald-500/30">
                        0
                    </span>
                </div>

                <!-- Liste défilante des tickets -->
                <div id="kds-col-ready" class="flex-1 overflow-y-auto p-3 space-y-3" tabindex="0">
                    <!-- Cartes injectées en JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modale de connexion rapide cuisine si non authentifié -->
    @include('kds.partials.login-modal')

    <!-- Modale de motif de refus/annulation de commande -->
    @include('kds.partials.reject-modal')

    <!-- Modale de détail complet de commande -->
    @include('kds.partials.detail-modal')

    <!-- Scripts KDS -->
    @push('scripts')
    @vite(['resources/js/kds.js'])
    @endpush
</x-layouts.kds>

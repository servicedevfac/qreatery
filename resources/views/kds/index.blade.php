<x-layouts.kds :restaurant-name="$restaurantName ?? 'Cuisine'">
    <!-- En-tête KDS spécifique avec métriques en direct -->
    <x-slot name="headerMetrics">
        <div class="hidden sm:flex items-center gap-2">
            <span class="text-xs font-black text-gray-400 uppercase tracking-wider">File active :</span>
            <span id="kds-metric-total" class="px-2.5 py-0.5 rounded-full bg-brand-orange text-white text-xs font-black shadow-sm font-mono">
                0
            </span>
        </div>
        <div class="flex items-center gap-1.5 text-xs">
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-brand bg-[#111827] border border-gray-800 text-gray-300 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-amber-500 shadow-[0_0_6px_rgba(245,158,11,0.6)]"></span>
                <span class="text-[11px] font-semibold text-gray-400">Attente:</span>
                <span id="kds-metric-pending" class="font-bold text-white font-mono">0</span>
            </div>
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-brand bg-[#111827] border border-gray-800 text-gray-300 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-brand-orange shadow-[0_0_6px_rgba(249,115,22,0.6)]"></span>
                <span class="text-[11px] font-semibold text-gray-400">Prépa:</span>
                <span id="kds-metric-preparing" class="font-bold text-white font-mono">0</span>
            </div>
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-brand bg-[#111827] border border-gray-800 text-gray-300 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.6)]"></span>
                <span class="text-[11px] font-semibold text-gray-400">Prêtes:</span>
                <span id="kds-metric-ready" class="font-bold text-white font-mono">0</span>
            </div>
        </div>
    </x-slot>

    <!-- Conteneur KDS Kanban -->
    <div class="h-full flex flex-col space-y-3">
        <!-- Barre de filtres et d'outils KDS -->
        <div class="flex items-center justify-between gap-3 bg-[#0B0F19] px-4 py-2.5 rounded-brand-lg border border-gray-800/80 shadow-md shrink-0">
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Filtre de mode -->
                <div class="inline-flex rounded-brand p-1 bg-[#111827] border border-gray-800 text-xs font-semibold shadow-inner">
                    <button type="button" class="kds-filter-mode active px-3.5 py-1.5 rounded-sm bg-brand-orange text-white shadow-xs transition-all font-bold" data-mode="all">
                        Toutes
                    </button>
                    <button type="button" class="kds-filter-mode px-3.5 py-1.5 rounded-sm text-gray-400 hover:text-white transition-all font-medium" data-mode="dine_in">
                        Sur place
                    </button>
                    <button type="button" class="kds-filter-mode px-3.5 py-1.5 rounded-sm text-gray-400 hover:text-white transition-all font-medium" data-mode="takeaway">
                        À emporter
                    </button>
                </div>

                <!-- Sélecteur de restaurant si plusieurs -->
                <div id="kds-restaurant-selector-container" class="hidden items-center gap-2">
                    <label for="kds-restaurant-select" class="text-xs text-gray-400 font-semibold">Établissement :</label>
                    <select id="kds-restaurant-select" class="bg-[#111827] border border-gray-700 text-white text-xs rounded-brand px-2.5 py-1.5 focus-ring">
                        <!-- Rempli en JS -->
                    </select>
                </div>
            </div>

            <!-- Actions droite : Rafraîchir manuellement et indicateur de mise à jour -->
            <div class="flex items-center gap-3">
                <span id="kds-last-sync-time" class="text-[11px] text-gray-400 font-mono hidden sm:inline">
                    Dernière synchro : --:--
                </span>
                <button
                    type="button"
                    id="btn-kds-refresh"
                    class="touch-target px-3.5 py-1.5 rounded-brand bg-[#111827] hover:bg-gray-800 border border-gray-700/80 text-xs font-semibold text-gray-200 flex items-center gap-2 transition-all active:scale-95 focus-ring shadow-xs"
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
                <div class="inline-block animate-spin w-10 h-10 border-4 border-brand-orange border-t-transparent rounded-full shadow-lg"></div>
                <p class="text-sm font-semibold text-gray-300">Chargement des commandes de cuisine...</p>
            </div>
        </div>

        <!-- État d'erreur / Déconnexion -->
        <div id="kds-error-banner" class="hidden bg-rose-950/90 border border-rose-800 text-rose-200 px-4 py-3 rounded-brand flex items-center justify-between gap-3 shrink-0 shadow-md">
            <div class="flex items-center gap-2 text-xs font-medium">
                <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span id="kds-error-message">Erreur de communication avec le serveur.</span>
            </div>
            <button type="button" id="btn-kds-error-retry" class="px-3 py-1 bg-rose-800 hover:bg-rose-700 text-white rounded text-xs font-bold transition-all active:scale-95">
                Réessayer
            </button>
        </div>

        <!-- Grille Kanban des colonnes KDS -->
        <div id="kds-kanban-board" class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4 min-h-0 overflow-hidden">
            <!-- Colonne 1 : NOUVELLES COMMANDES (En attente) -->
            <div class="flex flex-col rounded-brand-xl bg-[#0B0F19] border border-gray-800/80 border-t-4 border-t-amber-500 min-h-0 overflow-hidden shadow-xl">
                <!-- En-tête de colonne -->
                <div class="px-4 py-3 bg-gradient-to-r from-amber-950/40 to-transparent border-b border-gray-800/80 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.8)] animate-pulse"></span>
                        <h2 class="text-sm font-black text-amber-300 uppercase tracking-wider">
                            En Attente
                        </h2>
                    </div>
                    <span id="col-count-pending" class="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-xs font-black border border-amber-500/40 font-mono shadow-xs">
                        0
                    </span>
                </div>

                <!-- Liste défilante des tickets -->
                <div id="kds-col-pending" class="flex-1 overflow-y-auto p-3 space-y-3.5" tabindex="0">
                    <!-- Cartes injectées en JS -->
                </div>
            </div>

            <!-- Colonne 2 : EN PRÉPARATION (Au fourneaux) -->
            <div class="flex flex-col rounded-brand-xl bg-[#0B0F19] border border-gray-800/80 border-t-4 border-t-brand-orange min-h-0 overflow-hidden shadow-xl">
                <!-- En-tête de colonne -->
                <div class="px-4 py-3 bg-gradient-to-r from-brand-orange/20 to-transparent border-b border-gray-800/80 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-brand-orange shadow-[0_0_8px_rgba(249,115,22,0.8)]"></span>
                        <h2 class="text-sm font-black text-brand-orange uppercase tracking-wider">
                            En Préparation
                        </h2>
                    </div>
                    <span id="col-count-preparing" class="px-2.5 py-0.5 rounded-full bg-brand-orange/20 text-brand-orange text-xs font-black border border-brand-orange/40 font-mono shadow-xs">
                        0
                    </span>
                </div>

                <!-- Liste défilante des tickets -->
                <div id="kds-col-preparing" class="flex-1 overflow-y-auto p-3 space-y-3.5" tabindex="0">
                    <!-- Cartes injectées en JS -->
                </div>
            </div>

            <!-- Colonne 3 : PRÊTES AU COMPTOIR (Passe-plat) -->
            <div class="flex flex-col rounded-brand-xl bg-[#0B0F19] border border-gray-800/80 border-t-4 border-t-emerald-500 min-h-0 overflow-hidden shadow-xl">
                <!-- En-tête de colonne -->
                <div class="px-4 py-3 bg-gradient-to-r from-emerald-950/40 to-transparent border-b border-gray-800/80 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                        <h2 class="text-sm font-black text-emerald-300 uppercase tracking-wider">
                            Prêtes à Servir
                        </h2>
                    </div>
                    <span id="col-count-ready" class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-black border border-emerald-500/40 font-mono shadow-xs">
                        0
                    </span>
                </div>

                <!-- Liste défilante des tickets -->
                <div id="kds-col-ready" class="flex-1 overflow-y-auto p-3 space-y-3.5" tabindex="0">
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

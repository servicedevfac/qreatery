<x-layouts.pos :restaurant-name="$restaurantName ?? 'Caisse'" :operator-name="$operatorName ?? 'Caissier'">
    <!-- En-tête central : Bascule Caisse / Historique & Sélecteur Restaurant -->
    <x-slot name="headerCenter">
        <div class="inline-flex rounded-brand p-1 bg-[#111827] border border-gray-700/80 text-xs font-semibold shadow-inner">
            <button
                type="button"
                id="btn-pos-tab-sale"
                class="pos-view-tab active px-4 py-1.5 rounded-sm bg-brand-orange text-white shadow-xs transition-all flex items-center gap-1.5 focus-ring font-bold"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Nouvelle vente</span>
            </button>
            <button
                type="button"
                id="btn-pos-tab-orders"
                class="pos-view-tab px-4 py-1.5 rounded-sm text-gray-400 hover:text-white transition-all flex items-center gap-1.5 focus-ring font-medium"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Commandes du jour</span>
                <span id="pos-badge-unpaid-count" class="hidden px-2 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-black font-mono shadow-xs">
                    0
                </span>
            </button>
        </div>

        <!-- Sélecteur d'établissement multi-tenant -->
        <div id="pos-restaurant-selector-container" class="hidden items-center gap-1.5 ml-2">
            <label for="pos-restaurant-select" class="text-xs text-gray-400 font-medium">Resto :</label>
            <select id="pos-restaurant-select" class="bg-[#111827] border border-gray-700 text-white text-xs rounded-brand px-2.5 py-1.5 focus-ring">
                <!-- Rempli en JS -->
            </select>
        </div>
    </x-slot>

    <!-- En-tête droite : Actions rapides -->
    <x-slot name="headerActions">
        <button
            type="button"
            id="btn-pos-refresh"
            class="touch-target px-3 py-1.5 rounded-brand bg-[#111827] hover:bg-gray-800 border border-gray-700/80 text-gray-300 hover:text-white transition-all active:scale-95 focus-ring shadow-xs flex items-center gap-1.5"
            title="Rafraîchir les données"
        >
            <svg id="pos-refresh-icon" class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="text-xs font-semibold hidden sm:inline">Actualiser</span>
        </button>
        <button
            type="button"
            id="btn-pos-fullscreen"
            class="touch-target p-2 rounded-brand bg-[#111827] hover:bg-gray-800 border border-gray-700/80 text-gray-300 hover:text-white transition-all active:scale-95 focus-ring shadow-xs hidden sm:inline-flex"
            title="Plein écran"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
        </button>
    </x-slot>

    <!-- Zone principale : VUE NOUVELLE VENTE (Catalogue produits) -->
    <div id="pos-view-sale" class="h-full flex flex-col space-y-3">
        <!-- Barre de recherche et catégories -->
        <div class="space-y-2 shrink-0">
            <!-- Recherche rapide de produit -->
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input
                    type="search"
                    id="pos-search-input"
                    placeholder="Rechercher un plat, boisson, dessert..."
                    autocomplete="off"
                    class="w-full pl-10 pr-10 py-3 rounded-brand-lg bg-white dark:bg-[#111827] border border-gray-200 dark:border-gray-800 text-brand-text dark:text-white text-sm font-medium focus-ring placeholder-gray-400 dark:placeholder-gray-500 shadow-sm transition-all"
                >
                <button
                    type="button"
                    id="pos-search-clear"
                    class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-white"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Défilement horizontal des catégories -->
            <div id="pos-category-bar" class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar select-none">
                <button
                    type="button"
                    class="pos-category-pill active shrink-0 px-4 py-2 rounded-full text-xs font-bold bg-brand-orange text-white shadow-xs transition-all focus-ring"
                    data-category-id="all"
                >
                    Tout le menu
                </button>
                <!-- Injecté dynamiquement en JS -->
            </div>
        </div>

        <!-- État de chargement initial du catalogue -->
        <div id="pos-loading-state" class="flex-1 flex items-center justify-center">
            <div class="text-center space-y-3">
                <div class="inline-block animate-spin w-10 h-10 border-4 border-brand-orange border-t-transparent rounded-full shadow-lg"></div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Chargement de la carte des produits...</p>
            </div>
        </div>

        <!-- Grille des cartes produits tactiles -->
        <div id="pos-products-grid" class="hidden flex-1 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3.5 pr-1">
            <!-- Cartes injectées en JS -->
        </div>

        <!-- Aucun produit trouvé -->
        <div id="pos-empty-products" class="hidden flex-1 flex flex-col items-center justify-center p-8 text-center bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border">
            <svg class="w-10 h-10 mb-2 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <h3 class="text-sm font-bold text-brand-text dark:text-white">Aucun produit trouvé</h3>
            <p class="text-xs text-gray-500 mt-1">Essayez un autre mot-clé ou sélectionnez une autre catégorie.</p>
        </div>
    </div>

    <!-- Zone principale alternative : HISTORIQUE DES COMMANDES / VENTES DU JOUR -->
    <div id="pos-view-orders" class="hidden h-full flex flex-col space-y-3">
        @include('pos.partials.orders-view')
    </div>

    <!-- Barre latérale droite : TICKET DE CAISSE & ENCAISSEMENT -->
    <x-slot name="sidebar">
        @include('pos.partials.cart-sidebar')
    </x-slot>

    <!-- Modale d'options & variantes pour produit -->
    @include('pos.partials.option-modal')

    <!-- Modale d'encaissement et calcul de monnaie -->
    @include('pos.partials.checkout-modal')

    <!-- Modale de reçu de vente thermique imprimable -->
    @include('pos.partials.receipt-modal')

    <!-- Modale de connexion caissier si non authentifié -->
    @include('pos.partials.login-modal')

    <!-- Scripts POS -->
    @push('scripts')
    @vite(['resources/js/pos.js'])
    @endpush
</x-layouts.pos>

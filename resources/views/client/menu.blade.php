<x-layouts.client>
    <!-- Slot d'en-tête mobile -->
    <x-slot:header>
        @include('client.partials.header')
    </x-slot:header>

    <div class="space-y-4">
        <!-- Barre de recherche instantanée -->
        @include('client.partials.search-bar')

        <!-- Barre de navigation des catégories (Pills défilants) -->
        @include('client.partials.category-bar')

        <!-- État de chargement initial (Skeleton Loader) -->
        <div id="client-loading-state" class="space-y-4 py-4 animate-pulse">
            <div class="h-5 w-36 bg-gray-200 dark:bg-gray-800 rounded-brand"></div>
            <div class="space-y-3">
                <div class="h-24 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border p-3 flex gap-3">
                    <div class="w-20 h-20 bg-gray-200 dark:bg-gray-800 rounded-brand shrink-0"></div>
                    <div class="flex-1 space-y-2 py-1">
                        <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-1/2"></div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-1/4 mt-2"></div>
                    </div>
                </div>
                <div class="h-24 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border p-3 flex gap-3">
                    <div class="w-20 h-20 bg-gray-200 dark:bg-gray-800 rounded-brand shrink-0"></div>
                    <div class="flex-1 space-y-2 py-1">
                        <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-1/2"></div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-1/4 mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- État d'erreur / Restaurant introuvable -->
        <div id="client-error-state" class="hidden text-center py-12 px-4 space-y-3 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-sm">
            <span class="text-4xl block">⚠️</span>
            <h3 id="client-error-title" class="text-base font-bold text-brand-text dark:text-white">
                Établissement introuvable
            </h3>
            <p id="client-error-message" class="text-xs text-gray-500 max-w-xs mx-auto">
                Impossible de charger le menu. Veuillez vérifier le lien ou rescanner le QR Code de votre table.
            </p>
            <x-button type="button" id="btn-client-retry" variant="secondary" size="sm">
                Réessayer
            </x-button>
        </div>

        <!-- Conteneur principal du menu (Catégories et Cartes produits) -->
        <div id="client-menu-container" class="space-y-6">
            <!-- Injecté dynamiquement par client.js -->
        </div>

        <!-- Vue de suivi de commande en direct (s'affiche si une commande est active) -->
        @include('client.partials.tracking-view')
    </div>

    <!-- Modale de personnalisation des variantes et options -->
    @include('client.partials.option-modal')

    <!-- Tiroir et barre flottante du panier dynamique -->
    @include('client.partials.cart-drawer')

    <!-- Modale d'assistance / Appel serveur -->
    @include('client.partials.assistance-modal')

    <!-- Scripts client -->
    @push('scripts')
    @vite(['resources/js/client.js'])
    @endpush
</x-layouts.client>

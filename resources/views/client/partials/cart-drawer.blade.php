<!-- Barre flottante récapitulative du panier -->
<div id="client-floating-cart-bar" class="hidden fixed bottom-4 inset-x-4 z-30 max-w-md mx-auto pointer-events-auto">
    <button
        type="button"
        id="btn-open-cart-drawer"
        class="w-full bg-brand-orange hover:bg-brand-orange-dark active:bg-brand-orange-dark text-white rounded-brand-lg p-3.5 shadow-xl flex items-center justify-between transition-all transform hover:-translate-y-0.5 focus-ring"
        aria-label="Voir le panier"
    >
        <div class="flex items-center gap-3">
            <span id="floating-cart-count" class="w-7 h-7 rounded-full bg-white text-brand-orange font-black text-xs flex items-center justify-center shadow-xs">
                0
            </span>
            <span class="text-sm font-bold tracking-tight">Voir ma commande</span>
        </div>
        <div class="flex items-center gap-2">
            <span id="floating-cart-total" class="text-base font-black">0 FCFA</span>
            <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </button>
</div>

<!-- Tiroir / Modal plein écran du panier -->
<div
    id="cart-drawer-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cart-drawer-title"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center hidden"
    data-modal
>
    <!-- Arrière-plan semi-transparent -->
    <div
        class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
        data-modal-backdrop="cart-drawer-modal"
        aria-hidden="true"
    ></div>

    <!-- Conteneur panier -->
    <div class="relative w-full max-w-md bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10 max-h-[92vh] flex flex-col">
        <!-- En-tête -->
        <div class="px-5 py-4 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <h3 id="cart-drawer-title" class="text-base font-bold text-brand-text dark:text-white">
                    Votre Panier
                </h3>
                <span id="cart-drawer-items-count" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-brand-light text-brand-orange border border-brand-orange/30">
                    0 articles
                </span>
            </div>
            <button
                type="button"
                class="touch-target p-1.5 -mr-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-brand focus-ring"
                data-modal-close="cart-drawer-modal"
                aria-label="Fermer le panier"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Corps du panier défilable -->
        <div class="flex-1 overflow-y-auto p-5 space-y-5">
            <!-- Sélecteur de mode de commande : Sur place vs À emporter -->
            <div>
                <label class="text-xs font-bold text-brand-text dark:text-white uppercase tracking-wider block mb-2">
                    Mode de commande
                </label>
                <div class="grid grid-cols-2 gap-2 bg-gray-100 dark:bg-brand-dark p-1 rounded-brand border border-gray-200 dark:border-brand-dark-border text-xs font-bold">
                    <button
                        type="button"
                        id="btn-mode-dine-in"
                        class="py-2.5 px-3 rounded-brand text-center transition-all bg-brand-orange text-white shadow-xs"
                    >
                        🍽️ Sur place
                    </button>
                    <button
                        type="button"
                        id="btn-mode-takeaway"
                        class="py-2.5 px-3 rounded-brand text-center transition-all text-gray-600 dark:text-gray-400 hover:text-brand-text"
                    >
                        🛍️ À emporter
                    </button>
                </div>
                <p id="cart-mode-note" class="text-[11px] text-gray-500 mt-1.5">
                    <!-- Rempli en JS: ex. Table 3 assignée -->
                </p>
            </div>

            <!-- Liste des articles -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-brand-text dark:text-white uppercase tracking-wider">
                        Articles sélectionnés
                    </span>
                    <button
                        type="button"
                        id="btn-clear-cart"
                        class="text-[11px] text-rose-600 dark:text-rose-400 hover:underline font-semibold"
                    >
                        Vider le panier
                    </button>
                </div>

                <!-- Conteneur articles -->
                <div id="cart-items-list" class="space-y-3">
                    <!-- Injecté en JS -->
                </div>

                <!-- État panier vide -->
                <div id="cart-empty-state" class="hidden text-center py-8">
                    <span class="text-4xl block mb-2">🛒</span>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Votre panier est vide</p>
                    <p class="text-xs text-gray-500 mt-1">Parcourez le menu pour ajouter des plats savoureux.</p>
                </div>
            </div>

            <!-- Formulaire coordonnées client -->
            <div class="pt-3 border-t border-gray-100 dark:border-brand-dark-border space-y-3">
                <span class="text-xs font-bold text-brand-text dark:text-white uppercase tracking-wider block">
                    Coordonnées du client
                </span>

                <div>
                    <label for="cart-customer-name" class="text-xs font-medium text-gray-700 dark:text-gray-300 block mb-1">
                        Votre nom / Prénom <span class="text-gray-400 font-normal">(facultatif sur place)</span>
                    </label>
                    <input
                        type="text"
                        id="cart-customer-name"
                        placeholder="Ex: Kouamé Jean"
                        maxlength="100"
                        class="w-full text-xs p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-orange"
                    />
                </div>

                <div>
                    <label for="cart-customer-phone" class="text-xs font-medium text-gray-700 dark:text-gray-300 block mb-1">
                        Numéro de téléphone <span class="text-gray-400 font-normal">(recommandé pour le suivi)</span>
                    </label>
                    <input
                        type="tel"
                        id="cart-customer-phone"
                        placeholder="Ex: +225 07 00 00 00 00"
                        maxlength="30"
                        class="w-full text-xs p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-orange"
                    />
                </div>

                <div>
                    <label for="cart-order-notes" class="text-xs font-medium text-gray-700 dark:text-gray-300 block mb-1">
                        Note pour la commande / Remarque
                    </label>
                    <textarea
                        id="cart-order-notes"
                        rows="2"
                        maxlength="1000"
                        placeholder="Instructions générales pour la commande..."
                        class="w-full text-xs p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-orange"
                    ></textarea>
                </div>
            </div>

            <!-- Alerte erreur serveur (ex: 422 indisponible ou validation) -->
            <div id="cart-submit-error" class="hidden p-3 rounded-brand bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-medium">
            </div>
        </div>

        <!-- Pied de page avec récapitulatif financier et bouton de confirmation -->
        <div class="px-5 py-4 bg-gray-50 dark:bg-brand-dark/70 border-t border-gray-200 dark:border-brand-dark-border space-y-3 shrink-0">
            <div class="space-y-1.5 text-xs">
                <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                    <span>Sous-total</span>
                    <span id="cart-summary-subtotal" class="font-medium">0 FCFA</span>
                </div>
                <div class="flex items-center justify-between text-sm font-black text-brand-text dark:text-white pt-1 border-t border-gray-200 dark:border-brand-dark-border">
                    <span>Total à régler</span>
                    <span id="cart-summary-total" class="text-brand-orange text-base">0 FCFA</span>
                </div>
            </div>

            <x-button
                type="button"
                id="btn-confirm-order"
                variant="primary"
                class="w-full font-bold text-sm"
            >
                Valider ma commande
            </x-button>
        </div>
    </div>
</div>

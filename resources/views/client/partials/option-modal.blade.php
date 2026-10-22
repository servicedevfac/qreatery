<div
    id="options-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="options-modal-title"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center hidden"
    data-modal
>
    <!-- Arrière-plan semi-transparent -->
    <div
        class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
        data-modal-backdrop="options-modal"
        aria-hidden="true"
    ></div>

    <!-- Conteneur modal -->
    <div class="relative w-full max-w-md bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10 max-h-[90vh] flex flex-col">
        <!-- En-tête modal -->
        <div class="px-5 py-4 border-b border-gray-200 dark:border-brand-dark-border flex items-start justify-between gap-3 shrink-0">
            <div class="min-w-0">
                <h3 id="options-modal-title" class="text-base font-bold text-brand-text dark:text-white leading-snug">
                    Personnaliser
                </h3>
                <p id="options-modal-base-price" class="text-xs font-bold text-brand-orange mt-0.5">
                    <!-- Rempli en JS: ex: 2 500 FCFA -->
                </p>
            </div>
            <button
                type="button"
                class="touch-target p-1.5 -mr-1.5 -mt-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-brand focus-ring"
                data-modal-close="options-modal"
                aria-label="Fermer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Corps du modal (Formulaire variantes & options) -->
        <form id="options-modal-form" class="flex-1 overflow-y-auto p-5 space-y-5">
            <input type="hidden" id="opt-product-id" name="product_id" value="" />

            <!-- Description du plat -->
            <p id="options-modal-description" class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
            </p>

            <!-- Section 1 : Variantes (Taille / Format) -->
            <div id="options-variants-container" class="hidden space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-brand-text dark:text-white uppercase tracking-wider">
                        Format / Taille
                    </label>
                    <span class="text-[11px] font-semibold text-brand-orange">1 choix obligatoire</span>
                </div>
                <div id="options-variants-list" class="space-y-2">
                    <!-- Injecté en JS: Radio buttons -->
                </div>
            </div>

            <!-- Section 2 : Groupes d'options (Suppléments, Sauces, Cuisson) -->
            <div id="options-groups-container" class="space-y-4">
                <!-- Injecté en JS: Groupes d'options dynamiques -->
            </div>

            <!-- Section 3 : Instructions spéciales -->
            <div class="space-y-1.5 pt-1 border-t border-gray-100 dark:border-brand-dark-border">
                <label for="opt-instructions" class="text-xs font-bold text-brand-text dark:text-white">
                    Instructions particulières (facultatif)
                </label>
                <textarea
                    id="opt-instructions"
                    name="instructions"
                    rows="2"
                    maxlength="500"
                    placeholder="Ex: Sans oignon, sauce bien relevée..."
                    class="w-full text-xs p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-orange"
                ></textarea>
            </div>

            <!-- Section 4 : Quantité -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-brand-dark-border">
                <span class="text-xs font-bold text-brand-text dark:text-white">Quantité</span>
                <x-quantity-picker id="opt-quantity-picker" name="quantity" :value="1" :min="1" :max="99" />
            </div>

            <!-- Alerte validation options requises -->
            <div id="options-validation-error" class="hidden p-3 rounded-brand bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-medium">
            </div>
        </form>

        <!-- Pied de page avec bouton d'ajout et total dynamique -->
        <div class="px-5 py-3.5 bg-gray-50 dark:bg-brand-dark/70 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-between gap-3 shrink-0">
            <div>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 block leading-none">Total estimé</span>
                <span id="options-modal-total-display" class="text-base font-black text-brand-text dark:text-white">
                    0 FCFA
                </span>
            </div>
            <x-button type="button" id="btn-add-customized-to-cart" variant="primary">
                Ajouter au panier
            </x-button>
        </div>
    </div>
</div>

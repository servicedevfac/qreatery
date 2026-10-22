<x-modal id="pos-options-modal" title="Personnaliser l'Article" maxWidth="md">
    <div class="space-y-4">
        <!-- Informations d'en-tête du produit -->
        <div class="flex items-start justify-between gap-3 p-3 rounded-brand bg-gray-50 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border">
            <div>
                <h3 id="pos-opt-product-title" class="text-sm font-bold text-brand-text dark:text-white">
                    Nom du plat
                </h3>
                <p id="pos-opt-product-desc" class="text-xs text-gray-500 mt-0.5 line-clamp-2"></p>
            </div>
            <span id="pos-opt-base-price" class="text-sm font-black text-brand-orange shrink-0 font-mono">
                0 FCFA
            </span>
        </div>

        <div id="pos-options-validation-error" class="hidden p-2.5 rounded bg-rose-950/70 border border-rose-800 text-rose-300 text-xs">
            <!-- Erreur min/max -->
        </div>

        <!-- Section Variantes (Formats / Tailles) -->
        <div id="pos-opt-variants-container" class="space-y-1.5 hidden">
            <label class="text-xs font-bold text-brand-text dark:text-gray-300 uppercase tracking-wider block">
                Format / Taille
            </label>
            <div id="pos-opt-variants-list" class="space-y-1.5 text-xs">
                <!-- Variantes injectées en JS -->
            </div>
        </div>

        <!-- Section Groupes d'options / Suppléments -->
        <div id="pos-opt-groups-container" class="space-y-3">
            <!-- Groupes d'options injectés en JS -->
        </div>

        <!-- Instructions pour la cuisine -->
        <div class="space-y-1">
            <label for="pos-opt-instructions" class="text-xs font-bold text-brand-text dark:text-gray-300 uppercase tracking-wider block">
                Remarque pour la cuisine (facultatif)
            </label>
            <input
                type="text"
                id="pos-opt-instructions"
                maxlength="255"
                placeholder="Ex: Sans piment, cuisson bien cuite..."
                class="w-full px-3 py-2 bg-gray-50 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border rounded-brand text-xs text-brand-text dark:text-white focus-ring"
            >
        </div>

        <!-- Quantité et Total calculé -->
        <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-brand-dark-border">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-500">Quantité :</span>
                <div class="flex items-center gap-1">
                    <button type="button" id="btn-pos-opt-qty-minus" class="w-8 h-8 rounded-brand bg-gray-100 dark:bg-brand-dark border border-gray-300 dark:border-brand-dark-border font-bold text-sm text-brand-text dark:text-white flex items-center justify-center hover:bg-gray-200">
                        -
                    </button>
                    <span id="pos-opt-qty-display" class="w-8 text-center font-bold text-sm text-brand-text dark:text-white">
                        1
                    </span>
                    <button type="button" id="btn-pos-opt-qty-plus" class="w-8 h-8 rounded-brand bg-gray-100 dark:bg-brand-dark border border-gray-300 dark:border-brand-dark-border font-bold text-sm text-brand-text dark:text-white flex items-center justify-center hover:bg-gray-200">
                        +
                    </button>
                </div>
            </div>

            <div class="text-right">
                <span class="text-[10px] text-gray-400 block uppercase font-semibold">Total ligne</span>
                <span id="pos-opt-line-total-display" class="text-base font-black text-brand-orange font-mono">
                    0 FCFA
                </span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200 dark:border-brand-dark-border">
            <x-button type="button" variant="ghost" onclick="window.closeModal('pos-options-modal')">
                Annuler
            </x-button>
            <x-button type="button" id="btn-pos-add-to-cart-confirm" variant="primary" class="font-bold text-xs py-2 px-4">
                Ajouter au ticket
            </x-button>
        </div>
    </div>
</x-modal>

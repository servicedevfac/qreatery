<div class="h-full flex flex-col justify-between p-4 bg-white dark:bg-brand-dark-card">
    <!-- En-tête du ticket : Mode de service & Table -->
    <div class="space-y-3 pb-3 border-b border-gray-200 dark:border-brand-dark-border shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-black text-brand-text dark:text-white uppercase tracking-wider">
                    Ticket #<span id="pos-ticket-number">Nouveau</span>
                </span>
                <span id="pos-ticket-count-badge" class="px-2 py-0.5 rounded-full bg-brand-orange/15 text-brand-orange text-[11px] font-black">
                    0 article
                </span>
            </div>
            <button
                type="button"
                id="btn-pos-clear-cart"
                class="text-xs text-rose-500 hover:text-rose-600 font-semibold transition-colors disabled:opacity-40"
                disabled
            >
                Vider
            </button>
        </div>

        <!-- Bascule de mode de service : À emporter (Comptoir) vs Sur place -->
        <div class="grid grid-cols-2 gap-1.5 p-1 rounded-brand bg-gray-100 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border text-xs font-bold">
            <button
                type="button"
                id="btn-pos-mode-takeaway"
                class="pos-mode-btn active py-2 rounded-sm bg-brand-orange text-white shadow-2xs transition-all flex items-center justify-center gap-1.5"
            >
                <span>🛍️</span>
                <span>À emporter</span>
            </button>
            <button
                type="button"
                id="btn-pos-mode-dine-in"
                class="pos-mode-btn py-2 rounded-sm text-gray-600 dark:text-gray-400 hover:text-brand-text dark:hover:text-white transition-all flex items-center justify-center gap-1.5"
            >
                <span>🍽️</span>
                <span>Sur place</span>
            </button>
        </div>

        <!-- Sélecteur de table optionnel (affiché uniquement si 'Sur place' et si des tables existent) -->
        <div id="pos-table-selector-container" class="hidden flex-col gap-1.5 pt-1 text-xs">
            <div class="flex items-center justify-between gap-2">
                <label for="pos-table-select" class="font-bold text-gray-700 dark:text-gray-300">
                    Table :
                </label>
                <select id="pos-table-select" class="flex-1 px-2.5 py-1.5 bg-gray-50 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border rounded-brand text-xs font-semibold text-brand-text dark:text-white focus-ring">
                    <option value="">Sélectionner une table...</option>
                    <!-- Rempli en JS -->
                </select>
            </div>
            <div id="pos-table-warning" class="hidden text-[11px] text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 p-2 rounded border border-amber-200 dark:border-amber-800 leading-snug">
                <!-- Message dynamique en JS si aucune table active -->
            </div>
        </div>
    </div>

    <!-- Liste des lignes d'articles du ticket (Défilante) -->
    <div id="pos-ticket-items" class="flex-1 overflow-y-auto py-2 space-y-2 pr-1 divide-y divide-gray-100 dark:divide-brand-dark-border/40">
        <!-- État vide -->
        <div id="pos-ticket-empty" class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400">
            <span class="text-3xl block mb-2 opacity-50 select-none">🧾</span>
            <p class="text-xs font-bold text-gray-600 dark:text-gray-400">Ticket vierge</p>
            <p class="text-[11px] text-gray-400 mt-1">Touchez un produit dans le catalogue pour l'ajouter à la vente.</p>
        </div>
        <!-- Lignes injectées en JS -->
    </div>

    <!-- Zone de Totaux et Actions de caisse -->
    <div class="pt-3 border-t border-gray-200 dark:border-brand-dark-border space-y-3 shrink-0">
        <!-- Décomposition financière -->
        <div class="space-y-1.5 text-xs">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                <span>Sous-total</span>
                <span id="pos-summary-subtotal" class="font-bold font-mono">0 FCFA</span>
            </div>
            <div id="pos-tax-line" class="hidden flex items-center justify-between text-gray-500 dark:text-gray-400">
                <span>Taxes & Frais</span>
                <span id="pos-summary-tax" class="font-bold font-mono">0 FCFA</span>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-brand-dark-border/60 text-sm">
                <span class="font-black text-brand-text dark:text-white uppercase tracking-wider">Total TTC</span>
                <span id="pos-summary-total" class="text-lg font-black text-brand-orange font-mono">0 FCFA</span>
            </div>
        </div>

        <!-- Boutons d'action tactiles larges (>= 44px de hauteur) -->
        <div class="space-y-2">
            <button
                type="button"
                id="btn-pos-checkout"
                disabled
                class="w-full touch-target py-3.5 px-4 rounded-brand bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-40 disabled:pointer-events-none text-white font-black text-sm uppercase tracking-wider shadow-md transition-all flex items-center justify-center gap-2 focus-ring"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Encaisser (<span id="pos-btn-checkout-total">0 FCFA</span>)</span>
            </button>

            <!-- Option commande en cuisine différée (sans encaissement préalable) -->
            <button
                type="button"
                id="btn-pos-send-kitchen"
                disabled
                class="w-full touch-target py-2 px-3 rounded-brand bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark dark:hover:bg-gray-800 disabled:opacity-40 disabled:pointer-events-none text-gray-700 dark:text-gray-300 font-bold text-xs transition-colors flex items-center justify-center gap-1.5 focus-ring"
            >
                <span>🍳</span>
                <span>Envoyer en cuisine uniquement</span>
            </button>
        </div>
    </div>
</div>

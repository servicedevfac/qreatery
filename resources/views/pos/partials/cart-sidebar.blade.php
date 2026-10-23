<div class="h-full flex flex-col justify-between p-4 bg-white dark:bg-[#0B0F19]">
    <!-- En-tête du ticket : Mode de service & Table -->
    <div class="space-y-3 pb-3 border-b border-gray-200 dark:border-gray-800/80 shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-black text-brand-text dark:text-white uppercase tracking-wider font-mono">
                    Ticket #<span id="pos-ticket-number">Nouveau</span>
                </span>
                <span id="pos-ticket-count-badge" class="px-2.5 py-0.5 rounded-full bg-brand-orange/20 text-brand-orange text-[11px] font-black font-mono border border-brand-orange/30 shadow-2xs">
                    0 article
                </span>
            </div>
            <button
                type="button"
                id="btn-pos-clear-cart"
                class="text-xs text-rose-500 hover:text-rose-600 font-bold transition-colors disabled:opacity-30 disabled:pointer-events-none px-2 py-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/40"
                disabled
            >
                Vider
            </button>
        </div>

        <!-- Bascule de mode de service : À emporter (Comptoir) vs Sur place -->
        <div class="grid grid-cols-2 gap-1.5 p-1 rounded-brand bg-gray-100 dark:bg-[#111827] border border-gray-200 dark:border-gray-800 text-xs font-bold shadow-inner">
            <button
                type="button"
                id="btn-pos-mode-takeaway"
                class="pos-mode-btn active py-2 rounded-sm bg-brand-orange text-white shadow-xs transition-all flex items-center justify-center gap-1.5 font-bold"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>À emporter</span>
            </button>
            <button
                type="button"
                id="btn-pos-mode-dine-in"
                class="pos-mode-btn py-2 rounded-sm text-gray-600 dark:text-gray-400 hover:text-brand-text dark:hover:text-white transition-all flex items-center justify-center gap-1.5 font-medium"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>Sur place</span>
            </button>
        </div>

        <!-- Sélecteur de table optionnel (affiché uniquement si 'Sur place' et si des tables existent) -->
        <div id="pos-table-selector-container" class="hidden flex-col gap-1.5 pt-1 text-xs">
            <div class="flex items-center justify-between gap-2">
                <label for="pos-table-select" class="font-bold text-gray-700 dark:text-gray-300">
                    Table :
                </label>
                <select id="pos-table-select" class="flex-1 px-3 py-1.5 bg-gray-50 dark:bg-[#111827] border border-gray-200 dark:border-gray-700 rounded-brand text-xs font-semibold text-brand-text dark:text-white focus-ring">
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
    <div id="pos-ticket-items" class="flex-1 overflow-y-auto py-2 space-y-2 pr-1 divide-y divide-gray-100 dark:divide-gray-800/50">
        <!-- État vide -->
        <div id="pos-ticket-empty" class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400">
            <svg class="w-10 h-10 mb-2 opacity-40 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-xs font-bold text-gray-600 dark:text-gray-300">Ticket vierge</p>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Touchez un produit dans le catalogue pour l'ajouter à la vente.</p>
        </div>
        <!-- Lignes injectées en JS -->
    </div>

    <!-- Zone de Totaux et Actions de caisse -->
    <div class="pt-3.5 border-t border-gray-200 dark:border-gray-800/80 space-y-3.5 shrink-0">
        <!-- Décomposition financière -->
        <div class="space-y-1.5 text-xs bg-gray-50 dark:bg-[#111827] p-3 rounded-brand-lg border border-gray-200/80 dark:border-gray-800 shadow-xs">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 font-medium">
                <span>Sous-total</span>
                <span id="pos-summary-subtotal" class="font-bold font-mono text-gray-700 dark:text-gray-200">0 FCFA</span>
            </div>
            <div id="pos-tax-line" class="hidden flex items-center justify-between text-gray-500 dark:text-gray-400 font-medium">
                <span>Taxes & Frais</span>
                <span id="pos-summary-tax" class="font-bold font-mono text-gray-700 dark:text-gray-200">0 FCFA</span>
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-800 text-sm">
                <span class="font-black text-brand-text dark:text-white uppercase tracking-wider">Total TTC</span>
                <span id="pos-summary-total" class="text-xl font-black text-brand-orange font-mono">0 FCFA</span>
            </div>
        </div>

        <!-- Boutons d'action tactiles larges (>= 44px de hauteur) -->
        <div class="space-y-2">
            <button
                type="button"
                id="btn-pos-checkout"
                disabled
                class="w-full touch-target min-h-[48px] py-3.5 px-4 rounded-brand-lg bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 active:scale-[0.99] disabled:opacity-40 disabled:pointer-events-none text-white font-black text-sm uppercase tracking-wider shadow-lg shadow-emerald-950/40 transition-all flex items-center justify-center gap-2 focus-ring"
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
                class="w-full touch-target min-h-[44px] py-2.5 px-3 rounded-brand-lg bg-gray-100 hover:bg-gray-200 dark:bg-[#111827] dark:hover:bg-gray-800 active:scale-[0.99] border border-gray-200 dark:border-gray-800 disabled:opacity-40 disabled:pointer-events-none text-gray-700 dark:text-gray-300 font-bold text-xs transition-colors flex items-center justify-center gap-2 focus-ring"
            >
                <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                <span>Envoyer en cuisine uniquement</span>
            </button>
        </div>
    </div>
</div>

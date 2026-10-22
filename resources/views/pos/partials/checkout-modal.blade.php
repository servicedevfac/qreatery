<x-modal id="pos-checkout-modal" title="Encaissement de la Commande" maxWidth="lg">
    <form id="pos-checkout-form" class="space-y-4">
        <!-- Récapitulatif du montant à encaisser -->
        <div class="p-4 rounded-brand-lg bg-gray-50 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Total à encaisser</span>
                <span id="pos-pay-order-number" class="text-xs text-brand-orange font-bold font-mono">Commande en cours</span>
            </div>
            <div class="text-right">
                <span id="pos-pay-total-display" class="text-2xl font-black text-brand-text dark:text-white font-mono">
                    0 FCFA
                </span>
                <span id="pos-pay-remaining-badge" class="hidden block text-[11px] font-bold text-rose-500 mt-0.5">
                    Solde restant dû : <span id="pos-pay-remaining-amount">0 FCFA</span>
                </span>
            </div>
        </div>

        <div id="pos-checkout-error" class="hidden p-3 rounded bg-rose-950/70 border border-rose-800 text-rose-300 text-xs">
            <!-- Erreur d'encaissement -->
        </div>

        <!-- Choix du moyen de paiement (Tactile large) -->
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-brand-text dark:text-gray-300 uppercase tracking-wider block">
                Moyen de paiement <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <label class="pos-method-card flex flex-col items-center justify-center p-3.5 rounded-brand-lg border-2 border-brand-orange bg-brand-orange/10 cursor-pointer transition-all active:scale-95 shadow-xs">
                    <input type="radio" name="pos_payment_method" value="cash" checked class="sr-only">
                    <span class="text-2xl mb-1 select-none">💵</span>
                    <span class="text-xs font-bold text-brand-text dark:text-white">Espèces</span>
                </label>
                <label class="pos-method-card flex flex-col items-center justify-center p-3.5 rounded-brand-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-[#111827] cursor-pointer transition-all hover:border-brand-orange active:scale-95 shadow-2xs">
                    <input type="radio" name="pos_payment_method" value="card" class="sr-only">
                    <span class="text-2xl mb-1 select-none">💳</span>
                    <span class="text-xs font-bold text-brand-text dark:text-white">Carte Bancaire</span>
                </label>
                <label class="pos-method-card flex flex-col items-center justify-center p-3.5 rounded-brand-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-[#111827] cursor-pointer transition-all hover:border-brand-orange active:scale-95 shadow-2xs">
                    <input type="radio" name="pos_payment_method" value="mobile_money" class="sr-only">
                    <span class="text-2xl mb-1 select-none">📱</span>
                    <span class="text-xs font-bold text-brand-text dark:text-white">Mobile Money</span>
                </label>
                <label class="pos-method-card flex flex-col items-center justify-center p-3.5 rounded-brand-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-[#111827] cursor-pointer transition-all hover:border-brand-orange active:scale-95 shadow-2xs">
                    <input type="radio" name="pos_payment_method" value="other" class="sr-only">
                    <span class="text-2xl mb-1 select-none">⚙️</span>
                    <span class="text-xs font-bold text-brand-text dark:text-white">Autre</span>
                </label>
            </div>
        </div>

        <!-- Section spécifique : ESPÈCES (Calcul de la monnaie) -->
        <div id="pos-cash-calculator-section" class="p-3.5 rounded-brand-lg bg-gray-50 dark:bg-[#111827] border border-gray-200 dark:border-gray-800 space-y-3.5 shadow-xs">
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label for="pos-cash-tendered-input" class="text-xs font-bold text-gray-700 dark:text-gray-300">
                        Montant reçu du client :
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            id="pos-cash-tendered-input"
                            min="0"
                            step="any"
                            placeholder="0"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0B0F19] border border-gray-300 dark:border-gray-700 rounded-brand text-brand-text dark:text-white font-mono font-black text-base focus-ring shadow-inner"
                        >
                    </div>
                </div>

                <div class="space-y-1">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">
                        Monnaie à rendre :
                    </span>
                    <div class="px-3 py-2 rounded-brand bg-white dark:bg-[#0B0F19] border border-gray-300 dark:border-gray-700 flex items-center justify-between shadow-inner">
                        <span id="pos-cash-change-display" class="font-mono font-black text-base text-emerald-600 dark:text-emerald-400">
                            0 FCFA
                        </span>
                    </div>
                </div>
            </div>

            <!-- Raccourcis de coupures usuelles en FCFA -->
            <div class="space-y-1.5">
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Coupures rapides :</span>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button type="button" class="btn-pos-bill px-3 py-1.5 rounded-brand bg-white dark:bg-[#0B0F19] border border-gray-200 dark:border-gray-700 text-xs font-bold font-mono text-brand-text dark:text-white hover:border-brand-orange hover:text-brand-orange active:scale-95 transition-all shadow-2xs" data-bill-exact="true">
                        Compte exact
                    </button>
                    <button type="button" class="btn-pos-bill px-3 py-1.5 rounded-brand bg-white dark:bg-[#0B0F19] border border-gray-200 dark:border-gray-700 text-xs font-bold font-mono text-brand-text dark:text-white hover:border-brand-orange hover:text-brand-orange active:scale-95 transition-all shadow-2xs" data-bill-val="500">
                        500
                    </button>
                    <button type="button" class="btn-pos-bill px-3 py-1.5 rounded-brand bg-white dark:bg-[#0B0F19] border border-gray-200 dark:border-gray-700 text-xs font-bold font-mono text-brand-text dark:text-white hover:border-brand-orange hover:text-brand-orange active:scale-95 transition-all shadow-2xs" data-bill-val="1000">
                        1 000
                    </button>
                    <button type="button" class="btn-pos-bill px-3 py-1.5 rounded-brand bg-white dark:bg-[#0B0F19] border border-gray-200 dark:border-gray-700 text-xs font-bold font-mono text-brand-text dark:text-white hover:border-brand-orange hover:text-brand-orange active:scale-95 transition-all shadow-2xs" data-bill-val="2000">
                        2 000
                    </button>
                    <button type="button" class="btn-pos-bill px-3 py-1.5 rounded-brand bg-white dark:bg-[#0B0F19] border border-gray-200 dark:border-gray-700 text-xs font-bold font-mono text-brand-text dark:text-white hover:border-brand-orange hover:text-brand-orange active:scale-95 transition-all shadow-2xs" data-bill-val="5000">
                        5 000
                    </button>
                    <button type="button" class="btn-pos-bill px-3 py-1.5 rounded-brand bg-white dark:bg-[#0B0F19] border border-gray-200 dark:border-gray-700 text-xs font-bold font-mono text-brand-text dark:text-white hover:border-brand-orange hover:text-brand-orange active:scale-95 transition-all shadow-2xs" data-bill-val="10000">
                        10 000
                    </button>
                </div>
            </div>
        </div>

        <!-- Référence externe (Transaction terminal TPE ou SMS Mobile Money) -->
        <div id="pos-external-ref-section" class="hidden space-y-1">
            <label for="pos-external-ref-input" class="text-xs font-bold text-gray-700 dark:text-gray-300">
                Référence de transaction (N° TPE ou SMS transfert) :
            </label>
            <input
                type="text"
                id="pos-external-ref-input"
                maxlength="255"
                placeholder="Ex: TPE-987654 ou TXN-0123456"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border rounded-brand text-xs font-mono text-brand-text dark:text-white focus-ring"
            >
        </div>

        <!-- Option Paiement Partiel -->
        <div class="space-y-1 pt-1">
            <div class="flex items-center justify-between">
                <label for="pos-amount-to-charge-input" class="text-xs font-bold text-gray-700 dark:text-gray-300">
                    Montant débité pour ce règlement :
                </label>
                <span class="text-[11px] text-gray-400">Paiement partiel autorisé</span>
            </div>
            <input
                type="number"
                id="pos-amount-to-charge-input"
                required
                min="0.01"
                step="any"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border rounded-brand text-brand-text dark:text-white font-mono font-bold text-sm focus-ring"
            >
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200 dark:border-brand-dark-border">
            <x-button type="button" variant="ghost" onclick="window.closeModal('pos-checkout-modal')">
                Annuler
            </x-button>
            <x-button type="submit" id="btn-pos-submit-payment" variant="primary" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm px-6 py-2.5">
                Valider l'encaissement
            </x-button>
        </div>
    </form>
</x-modal>

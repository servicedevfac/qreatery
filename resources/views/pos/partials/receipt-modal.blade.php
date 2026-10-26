<x-modal id="pos-receipt-modal" title="Reçu de Caisse" maxWidth="sm">
    <div class="space-y-4">
        <!-- Ticket de caisse thermique (format ticket 80mm) -->
        <div id="pos-printable-receipt" class="p-4 bg-white text-gray-900 font-mono text-xs rounded border border-gray-300 shadow-inner space-y-3">
            <!-- En-tête ticket -->
            <div class="text-center space-y-1 pb-2 border-b border-dashed border-gray-400">
                <h3 id="pos-receipt-restaurant-name" class="font-black text-sm uppercase tracking-wider">
                    APP RESTAURANT
                </h3>
                <p id="pos-receipt-restaurant-info" class="text-[11px] text-gray-600">
                    Caisse Principale
                </p>
                <div class="text-[10px] text-gray-500 pt-1 flex items-center justify-between">
                    <span id="pos-receipt-date">--/--/---- --:--</span>
                    <span id="pos-receipt-order-num" class="font-bold">#---</span>
                </div>
                <div class="text-[10px] text-gray-600 flex items-center justify-between">
                    <span id="pos-receipt-mode">À emporter</span>
                    <span id="pos-receipt-operator">Opérateur: Caisse</span>
                </div>
            </div>

            <!-- Liste des articles commandés -->
            <div class="space-y-1 py-1 border-b border-dashed border-gray-400">
                <div class="flex items-center justify-between font-bold text-[11px] pb-1 border-b border-gray-200">
                    <span>Qte Plat</span>
                    <span>Total</span>
                </div>
                <div id="pos-receipt-items-list" class="space-y-1 divide-y divide-gray-100 text-[11px]">
                    <!-- Lignes injectées en JS -->
                </div>
            </div>

            <!-- Totaux financiers -->
            <div class="space-y-1 pt-1 text-xs">
                <div class="flex items-center justify-between">
                    <span>Sous-total HT :</span>
                    <span id="pos-receipt-subtotal" class="font-bold">0 FCFA</span>
                </div>
                <div id="pos-receipt-tax-row" class="hidden flex items-center justify-between text-gray-600 text-[11px]">
                    <span>Taxes & Frais :</span>
                    <span id="pos-receipt-tax">0 FCFA</span>
                </div>
                <div class="flex items-center justify-between text-sm font-black pt-1 border-t border-gray-800">
                    <span>TOTAL TTC :</span>
                    <span id="pos-receipt-total">0 FCFA</span>
                </div>
            </div>

            <!-- Règlement et monnaie rendue -->
            <div class="space-y-0.5 pt-2 border-t border-dashed border-gray-400 text-[11px] text-gray-700">
                <div class="flex items-center justify-between">
                    <span>Moyen :</span>
                    <span id="pos-receipt-method" class="font-bold uppercase">Espèces</span>
                </div>
                <div id="pos-receipt-tendered-row" class="flex items-center justify-between">
                    <span>Montant remis :</span>
                    <span id="pos-receipt-tendered">0 FCFA</span>
                </div>
                <div id="pos-receipt-change-row" class="flex items-center justify-between font-bold text-gray-900">
                    <span>Monnaie rendue :</span>
                    <span id="pos-receipt-change">0 FCFA</span>
                </div>
            </div>

            <!-- Pied de ticket -->
            <div class="text-center pt-2 text-[10px] text-gray-500 italic border-t border-gray-200">
                Merci de votre visite et à très bientôt !
            </div>
        </div>

        <!-- Actions de la modale -->
        <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-200 dark:border-brand-dark-border">
            <x-button type="button" variant="secondary" onclick="window.closeModal('pos-receipt-modal')">
                Fermer
            </x-button>
            <x-button type="button" id="btn-pos-print-receipt" onclick="window.printThermalReceipt ? window.printThermalReceipt() : window.print()" variant="primary" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zM7 7h10M7 11h10" />
                </svg>
                <span>Imprimer le ticket</span>
            </x-button>
        </div>
    </div>
</x-modal>

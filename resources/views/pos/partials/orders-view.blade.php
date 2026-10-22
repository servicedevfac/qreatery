<div class="h-full flex flex-col space-y-3">
    <!-- Barre de filtres des ventes -->
    <div class="flex items-center justify-between gap-3 bg-white dark:bg-brand-dark-card p-3 rounded-brand border border-gray-200 dark:border-brand-dark-border shrink-0">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-gray-500 uppercase">Filtrer par :</span>
            <div class="inline-flex rounded-brand p-0.5 bg-gray-100 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border text-xs font-semibold">
                <button type="button" class="pos-orders-filter active px-3 py-1 rounded-sm bg-brand-orange text-white" data-filter="all">
                    Toutes (<span id="pos-filter-count-all">0</span>)
                </button>
                <button type="button" class="pos-orders-filter px-3 py-1 rounded-sm text-gray-600 dark:text-gray-400 hover:text-white" data-filter="unpaid">
                    🔴 Impayées (<span id="pos-filter-count-unpaid">0</span>)
                </button>
                <button type="button" class="pos-orders-filter px-3 py-1 rounded-sm text-gray-600 dark:text-gray-400 hover:text-white" data-filter="paid">
                    🟢 Encaissées (<span id="pos-filter-count-paid">0</span>)
                </button>
            </div>
        </div>

        <button
            type="button"
            id="btn-pos-orders-refresh"
            class="touch-target px-3 py-1.5 rounded-brand bg-gray-100 dark:bg-brand-dark hover:bg-gray-200 dark:hover:bg-gray-800 text-xs font-bold text-brand-text dark:text-white flex items-center gap-1.5 focus-ring"
        >
            <span>🔄 Actualiser</span>
        </button>
    </div>

    <!-- Tableau / Liste défilante des commandes -->
    <div class="flex-1 overflow-y-auto bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-xs">
        <table class="w-full text-left border-collapse text-xs">
            <thead class="sticky top-0 bg-gray-50 dark:bg-brand-dark border-b border-gray-200 dark:border-brand-dark-border text-gray-500 uppercase tracking-wider font-bold">
                <tr>
                    <th class="p-3">Numéro</th>
                    <th class="p-3">Heure</th>
                    <th class="p-3">Service</th>
                    <th class="p-3">Total</th>
                    <th class="p-3">Paiement</th>
                    <th class="p-3">Cuisine</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="pos-orders-table-body" class="divide-y divide-gray-100 dark:divide-brand-dark-border/60">
                <!-- Lignes injectées en JS -->
            </tbody>
        </table>

        <!-- État vide -->
        <div id="pos-orders-empty" class="hidden p-12 text-center text-gray-400">
            <span class="text-3xl block mb-2">📋</span>
            <p class="text-sm font-bold text-gray-600 dark:text-gray-400">Aucune commande trouvée</p>
            <p class="text-xs text-gray-400 mt-1">Les ventes enregistrées au cours du service apparaîtront ici.</p>
        </div>
    </div>
</div>

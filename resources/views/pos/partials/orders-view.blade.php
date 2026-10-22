<div class="h-full flex flex-col space-y-3">
    <!-- Barre de filtres des ventes -->
    <div class="flex items-center justify-between gap-3 bg-white dark:bg-[#0B0F19] p-3.5 rounded-brand-lg border border-gray-200 dark:border-gray-800/80 shadow-sm shrink-0">
        <div class="flex items-center gap-2.5">
            <span class="text-xs font-black text-gray-500 uppercase tracking-wider">Filtrer par :</span>
            <div class="inline-flex rounded-brand p-1 bg-gray-100 dark:bg-[#111827] border border-gray-200 dark:border-gray-700 shadow-inner text-xs font-semibold">
                <button type="button" class="pos-orders-filter active px-3.5 py-1.5 rounded-sm bg-brand-orange text-white shadow-xs font-bold" data-filter="all">
                    Toutes (<span id="pos-filter-count-all" class="font-mono">0</span>)
                </button>
                <button type="button" class="pos-orders-filter px-3.5 py-1.5 rounded-sm text-gray-600 dark:text-gray-400 hover:text-white font-medium transition-all" data-filter="unpaid">
                    🔴 Impayées (<span id="pos-filter-count-unpaid" class="font-mono">0</span>)
                </button>
                <button type="button" class="pos-orders-filter px-3.5 py-1.5 rounded-sm text-gray-600 dark:text-gray-400 hover:text-white font-medium transition-all" data-filter="paid">
                    🟢 Encaissées (<span id="pos-filter-count-paid" class="font-mono">0</span>)
                </button>
            </div>
        </div>

        <button
            type="button"
            id="btn-pos-orders-refresh"
            class="touch-target px-3.5 py-1.5 rounded-brand bg-white hover:bg-gray-100 dark:bg-[#111827] dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-bold text-brand-text dark:text-white flex items-center gap-2 active:scale-95 transition-all shadow-xs focus-ring"
        >
            <span>🔄 Actualiser</span>
        </button>
    </div>

    <!-- Tableau / Liste défilante des commandes -->
    <div class="flex-1 overflow-y-auto bg-white dark:bg-[#0B0F19] rounded-brand-xl border border-gray-200 dark:border-gray-800/80 shadow-md">
        <table class="w-full text-left border-collapse text-xs">
            <thead class="sticky top-0 bg-gray-50/95 dark:bg-[#111827]/95 backdrop-blur-xs border-b border-gray-200 dark:border-gray-800 text-gray-500 dark:text-gray-400 uppercase tracking-wider font-black text-[11px] z-10">
                <tr>
                    <th class="p-3.5">Numéro</th>
                    <th class="p-3.5">Heure</th>
                    <th class="p-3.5">Service</th>
                    <th class="p-3.5">Total</th>
                    <th class="p-3.5">Paiement</th>
                    <th class="p-3.5">Cuisine</th>
                    <th class="p-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="pos-orders-table-body" class="divide-y divide-gray-100 dark:divide-gray-800/60">
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

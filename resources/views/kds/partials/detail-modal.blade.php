<x-modal id="kds-detail-modal" title="Détail de la Commande" maxWidth="lg">
    <div class="space-y-4">
        <!-- Métadonnées d'en-tête -->
        <div class="flex items-center justify-between gap-3 p-3 rounded-brand bg-brand-dark border border-brand-dark-border">
            <div>
                <span class="text-[11px] text-gray-400 font-semibold block uppercase">Numéro</span>
                <span id="kds-detail-number" class="text-base font-black text-white font-mono">#---</span>
            </div>
            <div class="text-right">
                <span class="text-[11px] text-gray-400 font-semibold block uppercase">Statut actuel</span>
                <span id="kds-detail-status-badge" class="px-2 py-0.5 rounded text-xs font-bold bg-brand-orange/20 text-brand-orange border border-brand-orange/30">
                    --
                </span>
            </div>
        </div>

        <!-- Informations client & service -->
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-2.5 rounded bg-brand-dark border border-brand-dark-border space-y-1">
                <span class="text-gray-400 font-semibold block uppercase text-[10px]">Service & Emplacement</span>
                <p id="kds-detail-mode" class="text-white font-bold">Sur place</p>
                <p id="kds-detail-table" class="text-brand-orange font-bold">Table --</p>
            </div>
            <div class="p-2.5 rounded bg-brand-dark border border-brand-dark-border space-y-1">
                <span class="text-gray-400 font-semibold block uppercase text-[10px]">Horodatages</span>
                <p id="kds-detail-time-created" class="text-gray-300">Reçue à : --:--</p>
                <p id="kds-detail-time-elapsed" class="text-emerald-400 font-bold">Temps écoulé : -- min</p>
            </div>
        </div>

        <!-- Remarque générale de la commande -->
        <div id="kds-detail-notes-container" class="hidden p-2.5 rounded bg-amber-950/40 border border-amber-900/50 text-xs">
            <span class="text-amber-400 font-bold block uppercase text-[10px]">Instructions client pour la commande :</span>
            <p id="kds-detail-notes" class="text-amber-200 mt-1 italic"></p>
        </div>

        <!-- Liste des articles -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold text-gray-300 uppercase tracking-wider">
                Articles à préparer
            </h3>
            <div id="kds-detail-items-list" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                <!-- Articles injectés en JS -->
            </div>
        </div>

        <!-- Historique des statuts -->
        <div class="space-y-2 pt-2 border-t border-brand-dark-border">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                Historique des étapes
            </h3>
            <div id="kds-detail-history-list" class="space-y-1 text-xs">
                <!-- Historique injecté en JS -->
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-brand-dark-border">
            <x-button type="button" variant="secondary" onclick="window.closeModal('kds-detail-modal')">
                Fermer
            </x-button>
        </div>
    </div>
</x-modal>

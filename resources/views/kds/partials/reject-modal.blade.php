<x-modal id="kds-reject-modal" title="Refuser ou Annuler la Commande" maxWidth="md">
    <form id="kds-reject-form" class="space-y-4">
        <input type="hidden" id="kds-reject-order-id" value="">
        <input type="hidden" id="kds-reject-target-status" value="rejected">

        <p class="text-xs text-gray-300">
            Cette action va interrompre le traitement de la commande <span id="kds-reject-order-number" class="font-bold text-white">#---</span>.
            Conformément aux règles de gestion, un motif explicite est obligatoire.
        </p>

        <div id="kds-reject-error" class="hidden p-3 rounded bg-rose-950/70 border border-rose-800 text-rose-300 text-xs">
            <!-- Message d'erreur -->
        </div>

        <div class="space-y-1">
            <label for="kds-reject-reason" class="text-xs font-bold text-gray-300 uppercase tracking-wider block">
                Motif du refus / de l'annulation <span class="text-rose-400">*</span>
            </label>
            <select id="kds-reject-reason-preset" class="w-full mb-2 px-3 py-2 bg-brand-dark border border-brand-dark-border rounded-brand text-white text-xs focus-ring">
                <option value="">Sélectionner un motif prédéfini...</option>
                <option value="Rupture de stock sur l'un des ingrédients">Rupture de stock sur un ingrédient</option>
                <option value="Fermeture imminente de la cuisine">Fermeture imminente de la cuisine</option>
                <option value="Surcharge temporaire du service">Surcharge temporaire du service</option>
                <option value="Demande d'annulation par le client">Demande d'annulation par le client</option>
                <option value="Autre raison">Autre motif personnalisé</option>
            </select>
            <textarea
                id="kds-reject-reason"
                required
                rows="3"
                maxlength="500"
                placeholder="Précisez la raison détaillée (transmise au client et dans les journaux d'audit)..."
                class="w-full px-3 py-2 bg-brand-dark border border-brand-dark-border rounded-brand text-white text-xs focus-ring placeholder-gray-500"
            ></textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-brand-dark-border">
            <x-button type="button" variant="ghost" onclick="window.closeModal('kds-reject-modal')">
                Annuler
            </x-button>
            <x-button type="submit" id="btn-kds-reject-confirm" variant="danger">
                Confirmer l'interruption
            </x-button>
        </div>
    </form>
</x-modal>

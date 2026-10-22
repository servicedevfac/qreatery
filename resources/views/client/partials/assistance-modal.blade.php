<div
    id="assistance-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="assistance-modal-title"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center hidden"
    data-modal
>
    <!-- Arrière-plan semi-transparent -->
    <div
        class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
        data-modal-backdrop="assistance-modal"
        aria-hidden="true"
    ></div>

    <!-- Conteneur modal -->
    <div class="relative w-full max-w-sm bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10">
        <!-- En-tête -->
        <div class="px-5 py-4 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">🛎️</span>
                <h3 id="assistance-modal-title" class="text-base font-bold text-brand-text dark:text-white">
                    Assistance à table
                </h3>
            </div>
            <button
                type="button"
                class="touch-target p-1.5 -mr-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-brand focus-ring"
                data-modal-close="assistance-modal"
                aria-label="Fermer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Corps formulaire assistance -->
        <form id="assistance-modal-form" class="p-5 space-y-4">
            <p class="text-xs text-gray-600 dark:text-gray-300">
                Sélectionnez l'objet de votre demande. L'équipe en salle en sera immédiatement notifiée.
            </p>

            <!-- Types d'assistance -->
            <div class="space-y-2">
                <label class="flex items-center gap-3 p-3 rounded-brand border border-brand-orange/40 bg-brand-light/50 dark:bg-brand-dark cursor-pointer hover:border-brand-orange transition-colors">
                    <input type="radio" name="assistance_type" value="waiter" checked class="w-4 h-4 text-brand-orange focus:ring-brand-orange" />
                    <div>
                        <span class="text-xs font-bold text-brand-text dark:text-white block">Appeler un serveur</span>
                        <span class="text-[11px] text-gray-500">Un membre de l'équipe va se présenter à votre table.</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark cursor-pointer hover:border-brand-orange transition-colors">
                    <input type="radio" name="assistance_type" value="bill" class="w-4 h-4 text-brand-orange focus:ring-brand-orange" />
                    <div>
                        <span class="text-xs font-bold text-brand-text dark:text-white block">Demander l'addition</span>
                        <span class="text-[11px] text-gray-500">Préparer l'encaissement (Espèces, Carte ou Mobile Money).</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark cursor-pointer hover:border-brand-orange transition-colors">
                    <input type="radio" name="assistance_type" value="other" class="w-4 h-4 text-brand-orange focus:ring-brand-orange" />
                    <div>
                        <span class="text-xs font-bold text-brand-text dark:text-white block">Autre demande spécifique</span>
                        <span class="text-[11px] text-gray-500">Précisez votre besoin dans le message.</span>
                    </div>
                </label>
            </div>

            <!-- Message facultatif / explicatif -->
            <div>
                <label for="assistance-message" class="text-xs font-medium text-gray-700 dark:text-gray-300 block mb-1">
                    Précision (facultatif)
                </label>
                <textarea
                    id="assistance-message"
                    name="message"
                    rows="2"
                    maxlength="500"
                    placeholder="Ex: Eau fraîche, couverts supplémentaires, serviettes..."
                    class="w-full text-xs p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-orange"
                ></textarea>
            </div>

            <div id="assistance-error-box" class="hidden p-2.5 rounded-brand bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs">
            </div>
        </form>

        <!-- Pied de page -->
        <div class="px-5 py-3.5 bg-gray-50 dark:bg-brand-dark/70 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end gap-2.5">
            <x-button type="button" variant="secondary" data-modal-close="assistance-modal" size="sm">
                Annuler
            </x-button>
            <x-button type="button" id="btn-submit-assistance" variant="primary" size="sm">
                Envoyer la demande
            </x-button>
        </div>
    </div>
</div>

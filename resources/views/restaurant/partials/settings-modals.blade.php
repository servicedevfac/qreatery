<!-- Modale Ajout de Membre d'Équipe -->
<div id="modal-create-member" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg max-w-md w-full shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-brand-dark-border">
            <h3 class="text-base font-bold text-brand-text dark:text-white">Ajouter un collaborateur</h3>
            <button type="button" class="btn-close-modal text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg p-1" data-modal="modal-create-member">
                ✕
            </button>
        </div>

        <div id="create-member-error" class="hidden p-3 rounded-brand bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-medium"></div>

        <form id="form-create-member" class="space-y-4 text-xs">
            <div>
                <label for="member-name" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Nom complet <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="member-name"
                    required
                    placeholder="Ex: Paul Martin, Awa Diallo..."
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                />
            </div>

            <div>
                <label for="member-email" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Adresse Email professionnelle <span class="text-red-500">*</span>
                </label>
                <input
                    type="email"
                    id="member-email"
                    required
                    placeholder="collaborateur@qreatery.test"
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                />
            </div>

            <div>
                <label for="member-role" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Rôle opérationnel <span class="text-red-500">*</span>
                </label>
                <select
                    id="member-role"
                    required
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white focus:outline-none focus:border-brand-orange"
                >
                    <option value="manager">Directeur / Manager (Gestion globale)</option>
                    <option value="cashier">Caissier (Poste Caisse POS)</option>
                    <option value="kitchen">Chef Cuisine (Poste Écran KDS)</option>
                    <option value="waiter">Serveur (Prise de commande &amp; Salle)</option>
                </select>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end gap-2">
                <button
                    type="button"
                    class="btn-close-modal px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold rounded-brand transition-colors"
                    data-modal="modal-create-member"
                >
                    Annuler
                </button>
                <button
                    type="submit"
                    id="btn-submit-create-member"
                    class="px-4 py-2 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold rounded-brand transition-colors shadow-sm"
                >
                    Ajouter le membre
                </button>
            </div>
        </form>
    </div>
</div>

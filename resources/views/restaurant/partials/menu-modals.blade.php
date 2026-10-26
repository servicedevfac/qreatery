@php
    $categories = $categories ?? ($restaurant ? $restaurant->categories()->whereNull('archived_at')->orderBy('position')->get() : collect());
@endphp

<!-- Modale Ajout de Produit / Plat -->
<div id="modal-create-product" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg max-w-lg w-full max-h-[92vh] overflow-y-auto shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-brand-dark-border">
            <h3 class="text-base font-bold text-brand-text dark:text-white">Ajouter un nouveau plat au menu</h3>
            <button type="button" class="btn-close-modal text-gray-400 hover:text-gray-600 dark:hover:text-white p-1" data-modal="modal-create-product">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="create-product-error" class="hidden p-3 rounded-brand bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-medium"></div>

        <form id="form-create-product" class="space-y-4 text-xs">
            <div>
                <label for="prod-name" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Nom du plat / produit <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="prod-name"
                    required
                    placeholder="Ex: Poulet Braisé Alloco, Burger Maison, Mojito..."
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="prod-category" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                        Catégorie
                    </label>
                    <select
                        id="prod-category"
                        class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white focus:outline-none focus:border-brand-orange"
                    >
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="prod-price" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                        Prix de vente ({{ $restaurant->currency }}) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        id="prod-price"
                        required
                        min="0"
                        step="50"
                        placeholder="Ex: 3500"
                        class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                    />
                </div>
            </div>

            <div>
                <label for="prod-prep-time" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Temps de préparation estimé (minutes)
                </label>
                <input
                    type="number"
                    id="prod-prep-time"
                    min="1"
                    max="180"
                    placeholder="Ex: 15"
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                />
            </div>

            <div>
                <label for="prod-desc" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Description &amp; Ingrédients
                </label>
                <textarea
                    id="prod-desc"
                    rows="3"
                    placeholder="Détaillez la composition, allergènes ou accompagnements..."
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                ></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input
                    type="checkbox"
                    id="prod-available"
                    checked
                    class="rounded text-brand-orange focus:ring-brand-orange h-4 w-4"
                />
                <label for="prod-available" class="text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                    Disponible immédiatement (En stock)
                </label>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end gap-2">
                <button
                    type="button"
                    class="btn-close-modal px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold rounded-brand transition-colors"
                    data-modal="modal-create-product"
                >
                    Annuler
                </button>
                <button
                    type="submit"
                    id="btn-submit-create-product"
                    class="px-4 py-2 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold rounded-brand transition-colors shadow-sm"
                >
                    Enregistrer le plat
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modale Modification de Produit / Plat -->
<div id="modal-edit-product" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg max-w-lg w-full max-h-[92vh] overflow-y-auto shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-brand-dark-border">
            <h3 class="text-base font-bold text-brand-text dark:text-white">Modifier le plat</h3>
            <button type="button" class="btn-close-modal text-gray-400 hover:text-gray-600 dark:hover:text-white p-1" data-modal="modal-edit-product">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="edit-product-error" class="hidden p-3 rounded-brand bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-medium"></div>

        <form id="form-edit-product" class="space-y-4 text-xs">
            <input type="hidden" id="edit-prod-id" />

            <div>
                <label for="edit-prod-name" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Nom du plat / produit <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="edit-prod-name"
                    required
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="edit-prod-category" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                        Catégorie
                    </label>
                    <select
                        id="edit-prod-category"
                        class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white focus:outline-none focus:border-brand-orange"
                    >
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="edit-prod-price" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                        Prix de vente ({{ $restaurant->currency }}) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        id="edit-prod-price"
                        required
                        min="0"
                        step="50"
                        class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                    />
                </div>
            </div>

            <div>
                <label for="edit-prod-prep-time" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Temps de préparation estimé (minutes)
                </label>
                <input
                    type="number"
                    id="edit-prod-prep-time"
                    min="1"
                    max="180"
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                />
            </div>

            <div>
                <label for="edit-prod-desc" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Description &amp; Ingrédients
                </label>
                <textarea
                    id="edit-prod-desc"
                    rows="3"
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                ></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input
                    type="checkbox"
                    id="edit-prod-available"
                    class="rounded text-brand-orange focus:ring-brand-orange h-4 w-4"
                />
                <label for="edit-prod-available" class="text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                    Disponible immédiatement (En stock)
                </label>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end gap-2">
                <button
                    type="button"
                    class="btn-close-modal px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold rounded-brand transition-colors"
                    data-modal="modal-edit-product"
                >
                    Annuler
                </button>
                <button
                    type="submit"
                    id="btn-submit-edit-product"
                    class="px-4 py-2 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold rounded-brand transition-colors shadow-sm"
                >
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modale Ajout de Catégorie -->
<div id="modal-create-category" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg max-w-md w-full shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-brand-dark-border">
            <h3 class="text-base font-bold text-brand-text dark:text-white">Nouvelle Catégorie de Menu</h3>
            <button type="button" class="btn-close-modal text-gray-400 hover:text-gray-600 dark:hover:text-white p-1" data-modal="modal-create-category">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="create-category-error" class="hidden p-3 rounded-brand bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-medium"></div>

        <form id="form-create-category" class="space-y-4 text-xs">
            <div>
                <label for="cat-name" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Nom de la catégorie <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="cat-name"
                    required
                    placeholder="Ex: Entrées, Grillades, Boissons fraîches, Desserts..."
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                />
            </div>

            <div>
                <label for="cat-desc" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Description (facultative)
                </label>
                <textarea
                    id="cat-desc"
                    rows="2"
                    placeholder="Brève description de la sélection..."
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                ></textarea>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end gap-2">
                <button
                    type="button"
                    class="btn-close-modal px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold rounded-brand transition-colors"
                    data-modal="modal-create-category"
                >
                    Annuler
                </button>
                <button
                    type="submit"
                    id="btn-submit-create-category"
                    class="px-4 py-2 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold rounded-brand transition-colors shadow-sm"
                >
                    Créer la catégorie
                </button>
            </div>
        </form>
    </div>
</div>

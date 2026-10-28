<x-layouts.super-admin
    title="Annuaire des Restaurants"
    headerTitle="Restaurants"
    active="restaurants"
    :adminUser="$adminUser ?? auth()->user()"
>
    <x-slot name="headerActions">
        <div class="flex items-center gap-3">
            <span id="restaurants-total-badge" class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 dark:bg-orange-950/80 text-brand-orange dark:text-orange-400 border border-orange-200 dark:border-orange-900/50">
                0 établissement
            </span>
            <button
                type="button"
                id="btn-refresh-restaurants"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-brand text-xs font-semibold bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-brand-dark transition-colors focus-ring touch-target"
                aria-label="Actualiser la liste des restaurants"
            >
                <svg id="restaurants-refresh-icon" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Actualiser</span>
            </button>

            <button
                type="button"
                id="btn-create-restaurant"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-brand text-xs font-semibold bg-brand-orange hover:bg-brand-orange-dark text-white shadow-sm transition-colors focus-ring touch-target"
                aria-label="Créer un nouvel établissement"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nouveau restaurant</span>
            </button>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- En-tête de section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-brand-dark-border pb-4">
            <div>
                <h2 class="text-xl font-black text-brand-text dark:text-white tracking-tight">
                    Annuaire Global des Établissements
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Supervision, recherche et gestion de l’état d’activité des restaurants de la plateforme.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-brand-dark-border">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Tenants Plateforme
                </span>
            </div>
        </div>

        <!-- Barre de recherche et filtres -->
        <div class="p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs space-y-3">
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Champ de recherche par nom ou slug -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="search"
                        id="restaurants-search-input"
                        placeholder="Rechercher par nom ou slug (ex: Gourmet, abidjan)..."
                        class="w-full pl-10 pr-9 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                        aria-label="Rechercher un établissement par nom ou slug"
                    >
                    <button
                        type="button"
                        id="restaurants-search-clear"
                        class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 touch-target"
                        aria-label="Effacer la recherche"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Filtre par statut -->
                <div class="flex items-center gap-2">
                    <label for="restaurants-status-filter" class="sr-only">Filtrer par statut</label>
                    <select
                        id="restaurants-status-filter"
                        class="px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all"
                    >
                        <option value="">Tous les statuts</option>
                        <option value="active">Actif</option>
                        <option value="suspended">Suspendu</option>
                        <option value="draft">Brouillon</option>
                        <option value="archived">Archivé</option>
                    </select>

                    <!-- Bouton réinitialiser filtres -->
                    <button
                        type="button"
                        id="btn-reset-filters"
                        class="px-3 py-2 rounded-brand text-xs font-semibold bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors focus-ring touch-target"
                        title="Réinitialiser les filtres"
                    >
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>

        <!-- ÉTAT 1 : Chargement (Skeleton loader) -->
        <div id="restaurants-loading-state" class="space-y-4 animate-pulse" aria-busy="true" aria-label="Chargement de l'annuaire des restaurants">
            <div class="h-64 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border p-4 space-y-3">
                <div class="h-6 bg-gray-200 dark:bg-gray-800 rounded w-1/4"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-1/2"></div>
                <div class="h-10 bg-gray-200 dark:bg-gray-800 rounded"></div>
                <div class="h-10 bg-gray-200 dark:bg-gray-800 rounded"></div>
                <div class="h-10 bg-gray-200 dark:bg-gray-800 rounded"></div>
            </div>
        </div>

        <!-- ÉTAT 2 : Erreur -->
        <div
            id="restaurants-error-state"
            class="hidden p-6 bg-rose-50 dark:bg-rose-950/40 rounded-brand-lg border border-rose-200 dark:border-rose-900/50 text-center space-y-4"
            role="alert"
        >
            <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-rose-900 dark:text-rose-200">
                    Erreur de chargement des restaurants
                </h3>
                <p id="restaurants-error-message" class="text-xs text-rose-700 dark:text-rose-300 max-w-md mx-auto">
                    Une erreur est survenue lors de la communication avec l'API Super-Admin.
                </p>
            </div>
            <div>
                <button
                    type="button"
                    id="btn-retry-restaurants"
                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-brand text-xs font-semibold shadow-sm focus-ring touch-target"
                >
                    Réessayer
                </button>
            </div>
        </div>

        <!-- ÉTAT 3 : Liste vide -->
        <div
            id="restaurants-empty-state"
            class="hidden p-12 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border text-center space-y-3"
        >
            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-brand-dark text-gray-400 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-brand-text dark:text-white">
                Aucun établissement trouvé
            </h3>
            <p id="restaurants-empty-message" class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                Aucun restaurant ne correspond aux critères de recherche ou de filtre sélectionnés.
            </p>
            <div class="flex items-center justify-center gap-2 pt-1">
                <button
                    type="button"
                    id="btn-empty-reset"
                    class="px-3.5 py-1.5 bg-brand-light dark:bg-brand-orange/10 text-brand-orange text-xs font-semibold rounded-brand hover:bg-brand-orange hover:text-white transition-colors"
                >
                    Effacer les filtres
                </button>
                <button
                    type="button"
                    id="btn-empty-create"
                    class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-semibold rounded-brand transition-colors"
                >
                    + Créer un établissement
                </button>
            </div>
        </div>

        <!-- ÉTAT 4 : Tableau & Cartes Succès -->
        <div id="restaurants-content" class="hidden space-y-4">
            <!-- Vue Desktop : Tableau complet -->
            <div class="hidden md:block bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300" aria-label="Liste des établissements">
                        <thead class="bg-gray-50 dark:bg-brand-dark border-b border-gray-200 dark:border-brand-dark-border text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="py-3 px-4">Établissement</th>
                                <th scope="col" class="py-3 px-4">Contact</th>
                                <th scope="col" class="py-3 px-4">Paramètres</th>
                                <th scope="col" class="py-3 px-4 text-center">Statut</th>
                                <th scope="col" class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="restaurants-table-body" class="divide-y divide-gray-100 dark:divide-brand-dark-border font-medium">
                            <!-- Injecté dynamiquement par admin.js -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vue Mobile : Cartes individuelles -->
            <div id="restaurants-mobile-cards" class="md:hidden space-y-3">
                <!-- Injecté dynamiquement par admin.js -->
            </div>

            <!-- Pagination -->
            <div id="restaurants-pagination" class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs text-xs">
                <div id="pagination-info" class="text-gray-500 dark:text-gray-400">
                    Affichage de 0 à 0 sur 0 résultat(s)
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        id="btn-pagination-prev"
                        class="px-3 py-1.5 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-white dark:bg-brand-dark text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus-ring touch-target"
                        disabled
                        aria-label="Page précédente"
                    >
                        &larr; Précédent
                    </button>
                    <span id="pagination-current-page" class="px-2 font-bold text-brand-orange">
                        1
                    </span>
                    <button
                        type="button"
                        id="btn-pagination-next"
                        class="px-3 py-1.5 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-white dark:bg-brand-dark text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors focus-ring touch-target"
                        disabled
                        aria-label="Page suivante"
                    >
                        Suivant &rarr;
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale de Confirmation de Suspension / Réactivation -->
    <div
        id="restaurant-status-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="status-modal-title"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center transition-all duration-200 hidden"
    >
        <!-- Arrière-plan flouté -->
        <div id="status-modal-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" aria-hidden="true"></div>

        <!-- Conteneur modal -->
        <div class="relative w-full max-w-md bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10">
            <div class="p-5 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div id="status-modal-icon-container" class="w-8 h-8 rounded-brand flex items-center justify-center">
                        <!-- Icône dynamique -->
                    </div>
                    <h3 id="status-modal-title" class="text-base font-bold text-brand-text dark:text-white">
                        Changer le statut
                    </h3>
                </div>
                <button
                    type="button"
                    id="btn-close-status-modal"
                    class="touch-target p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-brand focus-ring"
                    aria-label="Fermer la boîte de dialogue"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="restaurant-status-form" class="p-5 space-y-4">
                <input type="hidden" id="status-modal-restaurant-id">
                <input type="hidden" id="status-modal-target-status">

                <!-- Contexte restaurant -->
                <div class="p-3 rounded-brand bg-gray-50 dark:bg-brand-dark/50 border border-gray-100 dark:border-brand-dark-border">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Établissement concerné :</p>
                    <p id="status-modal-restaurant-name" class="text-sm font-bold text-brand-text dark:text-white mt-0.5"></p>
                    <p id="status-modal-restaurant-slug" class="text-[11px] text-gray-400 font-mono"></p>
                </div>

                <!-- Avertissement / Explication de l'action -->
                <p id="status-modal-explanation" class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed"></p>

                <!-- Motif (obligatoire en suspension selon Rule::requiredIf) -->
                <div id="status-reason-group" class="space-y-1.5">
                    <label for="status-modal-reason" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Motif de l’opération <span id="status-reason-required-badge" class="text-rose-500">*</span>
                    </label>
                    <textarea
                        id="status-modal-reason"
                        rows="3"
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                        placeholder="Précisez la justification (ex: défaut de paiement, signalement, fin de contrat)..."
                    ></textarea>
                    <p id="status-reason-error" class="hidden text-[11px] text-rose-500 font-medium"></p>
                </div>

                <!-- Boutons d'action -->
                <div class="pt-3 border-t border-gray-100 dark:border-brand-dark-border flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        id="btn-cancel-status-modal"
                        class="px-3.5 py-2 rounded-brand text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-brand-dark hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors focus-ring touch-target"
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        id="btn-submit-status-modal"
                        class="px-4 py-2 rounded-brand text-xs font-semibold text-white shadow-sm focus-ring touch-target flex items-center gap-1.5"
                    >
                        <span id="status-submit-spinner" class="hidden animate-spin w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span id="status-submit-text">Confirmer</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale de Création d'un Restaurant (Super-Admin) -->
    <div
        id="create-restaurant-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="create-restaurant-modal-title"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center transition-all duration-200 hidden"
    >
        <div id="create-restaurant-modal-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" aria-hidden="true"></div>

        <div class="relative w-full max-w-2xl bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10 max-h-[90vh] flex flex-col">
            <!-- Entête Modal -->
            <div class="p-5 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between shrink-0 bg-gray-50/50 dark:bg-brand-dark/40">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-brand bg-brand-orange/10 text-brand-orange flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="create-restaurant-modal-title" class="text-base font-bold text-brand-text dark:text-white">
                            Créer un nouvel établissement
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Enregistrez un nouveau restaurant et configurez ses accès initiaux.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    id="btn-close-create-restaurant-modal"
                    class="touch-target p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-brand focus-ring"
                    aria-label="Fermer la boîte de dialogue"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Formulaire scrollable -->
            <form id="create-restaurant-form" class="p-6 space-y-5 overflow-y-auto flex-1">
                <!-- Zone d'erreur générale -->
                <div id="create-resto-general-error" class="hidden p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 rounded-brand text-xs text-rose-700 dark:text-rose-300"></div>

                <!-- 1. IDENTITÉ DU RESTAURANT -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 border-b border-gray-100 dark:border-brand-dark-border pb-1.5">
                        <span class="w-2 h-2 rounded-full bg-brand-orange"></span>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Identité de l'établissement</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1 sm:col-span-1">
                            <label for="create-resto-name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Nom du restaurant <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="create-resto-name"
                                name="name"
                                required
                                placeholder="ex: Le Palais Gourmand"
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400 font-medium"
                            >
                            <p id="create-resto-error-name" class="hidden text-[11px] text-rose-500 font-medium"></p>
                        </div>

                        <div class="space-y-1 sm:col-span-1">
                            <label for="create-resto-slug" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Identifiant URL (Slug)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs text-gray-400 font-mono">/</span>
                                <input
                                    type="text"
                                    id="create-resto-slug"
                                    name="slug"
                                    placeholder="auto-généré si vide"
                                    class="w-full pl-6 pr-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400 font-mono"
                                >
                            </div>
                            <span class="text-[10px] text-gray-400">ex: le-palais-gourmand</span>
                            <p id="create-resto-error-slug" class="hidden text-[11px] text-rose-500 font-medium"></p>
                        </div>

                        <div class="space-y-1 sm:col-span-2">
                            <label for="create-resto-description" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Description ou Présentation
                            </label>
                            <textarea
                                id="create-resto-description"
                                name="description"
                                rows="2"
                                placeholder="Cuisine africaine & européenne raffinée au cœur de la ville..."
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                            ></textarea>
                            <p id="create-resto-error-description" class="hidden text-[11px] text-rose-500 font-medium"></p>
                        </div>
                    </div>
                </div>

                <!-- 2. COORDONNÉES & CONTACT -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-2 border-b border-gray-100 dark:border-brand-dark-border pb-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Contact &amp; Coordonnées</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1 sm:col-span-1">
                            <label for="create-resto-phone" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Téléphone
                            </label>
                            <input
                                type="tel"
                                id="create-resto-phone"
                                name="phone"
                                placeholder="+225 07 00 00 00"
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                            >
                            <p id="create-resto-error-phone" class="hidden text-[11px] text-rose-500 font-medium"></p>
                        </div>

                        <div class="space-y-1 sm:col-span-1">
                            <label for="create-resto-email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Email de contact
                            </label>
                            <input
                                type="email"
                                id="create-resto-email"
                                name="email"
                                placeholder="contact@palaisgourmand.ci"
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                            >
                            <p id="create-resto-error-email" class="hidden text-[11px] text-rose-500 font-medium"></p>
                        </div>

                        <div class="space-y-1 sm:col-span-1">
                            <label for="create-resto-address" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Adresse physique
                            </label>
                            <input
                                type="text"
                                id="create-resto-address"
                                name="address"
                                placeholder="Plateau, Rue du Commerce"
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                            >
                            <p id="create-resto-error-address" class="hidden text-[11px] text-rose-500 font-medium"></p>
                        </div>
                    </div>
                </div>

                <!-- 3. PARAMÈTRES RÉGIONAUX & STATUT -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-2 border-b border-gray-100 dark:border-brand-dark-border pb-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Devise, Fuseau &amp; Statut</h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label for="create-resto-currency" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Devise
                            </label>
                            <select
                                id="create-resto-currency"
                                name="currency"
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none"
                            >
                                <option value="XOF" selected>XOF (FCFA)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="USD">USD ($)</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label for="create-resto-timezone" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Fuseau horaire
                            </label>
                            <select
                                id="create-resto-timezone"
                                name="timezone"
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none"
                            >
                                <option value="Africa/Abidjan" selected>Africa/Abidjan (GMT+0)</option>
                                <option value="Africa/Dakar">Africa/Dakar (GMT+0)</option>
                                <option value="Africa/Lagos">Africa/Lagos (GMT+1)</option>
                                <option value="Europe/Paris">Europe/Paris (CET/CEST)</option>
                                <option value="UTC">UTC</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label for="create-resto-status" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Statut initial
                            </label>
                            <select
                                id="create-resto-status"
                                name="status"
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none font-semibold text-emerald-600 dark:text-emerald-400"
                            >
                                <option value="active" selected>Actif (Opérationnel)</option>
                                <option value="draft">Brouillon (Configuration)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 4. COMPTE PROPRIÉTAIRE (GÉRANT) -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-brand-dark-border pb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Gérant / Propriétaire</h4>
                        </div>
                        <span class="text-[10px] text-gray-400">Si non renseigné, le Super-Admin sera rattaché</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1 sm:col-span-1">
                            <label for="create-resto-owner-name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Nom du gérant
                            </label>
                            <input
                                type="text"
                                id="create-resto-owner-name"
                                name="owner_name"
                                placeholder="ex: Alexandre Danho"
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                            >
                        </div>

                        <div class="space-y-1 sm:col-span-1">
                            <label for="create-resto-owner-email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Email de connexion
                            </label>
                            <input
                                type="email"
                                id="create-resto-owner-email"
                                name="owner_email"
                                placeholder="owner@resto.test"
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                            >
                            <p id="create-resto-error-owner_email" class="hidden text-[11px] text-rose-500 font-medium"></p>
                        </div>

                        <div class="space-y-1 sm:col-span-1">
                            <label for="create-resto-owner-password" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Mot de passe initial
                            </label>
                            <input
                                type="text"
                                id="create-resto-owner-password"
                                name="owner_password"
                                placeholder="Défaut: password"
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400 font-mono text-xs"
                            >
                        </div>
                    </div>
                </div>

                <!-- 5. ABONNEMENT INITIAL -->
                @php
                    $availablePlans = $plans ?? \App\Models\Plan::query()->where('is_active', true)->orderBy('price_minor')->get();
                @endphp
                @if(count($availablePlans) > 0)
                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-2 border-b border-gray-100 dark:border-brand-dark-border pb-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Formule d'Abonnement Initiale</h4>
                    </div>

                    <div class="space-y-1">
                        <label for="create-resto-plan-id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Sélectionner un plan SaaS (optionnel)
                        </label>
                        <select
                            id="create-resto-plan-id"
                            name="plan_id"
                            class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none"
                        >
                            <option value="">-- Aucun abonnement immédiat --</option>
                            @foreach($availablePlans as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->name }} — {{ number_format($p->price_minor / 100, 0, ',', ' ') }} {{ strtoupper($p->currency) }} / {{ $p->billing_period }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-gray-400">Active immédiatement les fonctionnalités du plan choisi pour le restaurant.</span>
                    </div>
                </div>
                @endif

                <!-- Boutons d'action -->
                <div class="pt-4 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end gap-3 sticky bottom-0 bg-white dark:bg-brand-dark-card py-2">
                    <button
                        type="button"
                        id="btn-cancel-create-restaurant-modal"
                        class="px-4 py-2 rounded-brand text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-brand-dark hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors focus-ring touch-target"
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        id="btn-submit-create-restaurant-modal"
                        class="px-5 py-2 rounded-brand text-xs font-bold text-white bg-brand-orange hover:bg-brand-orange-dark shadow-sm focus-ring touch-target flex items-center gap-2 transition-all"
                    >
                        <span id="create-resto-submit-spinner" class="hidden animate-spin w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span id="create-resto-submit-text">Créer le restaurant</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.super-admin>


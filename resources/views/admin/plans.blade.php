<x-layouts.super-admin
    title="Plans SaaS"
    headerTitle="Plans SaaS"
    active="plans"
    :adminUser="$adminUser ?? auth()->user()"
>
    <x-slot name="headerActions">
        <div class="flex items-center gap-3">
            <span id="plans-total-badge" class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 dark:bg-orange-950/80 text-brand-orange dark:text-orange-400 border border-orange-200 dark:border-orange-900/50">
                0 plan
            </span>

            <button
                type="button"
                id="btn-refresh-plans"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-brand text-xs font-semibold bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-brand-dark transition-colors focus-ring touch-target"
                aria-label="Actualiser la liste des plans"
            >
                <svg id="plans-refresh-icon" class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Actualiser</span>
            </button>

            <button
                type="button"
                id="btn-create-plan"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-brand text-xs font-semibold bg-brand-orange hover:bg-brand-orange-dark text-white shadow-sm transition-colors focus-ring touch-target"
                aria-label="Créer un nouveau plan SaaS"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nouveau plan</span>
            </button>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- En-tête de section avec explications et badge d'autorité -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-brand-dark-border pb-4">
            <div>
                <h2 class="text-xl font-black text-brand-text dark:text-white tracking-tight">
                    Gestion des Offres & Grilles Tarifaires SaaS
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Définition des formules d'abonnements, tarifications, périodicités et fonctionnalités pour les restaurants.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-brand-dark-border">
                    <span class="w-2 h-2 rounded-full bg-brand-orange"></span>
                    Contrat API v1
                </span>
            </div>
        </div>

        <!-- Note d'information sur la gouvernance des plans (Intégrité référentielle) -->
        <div class="p-3.5 bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 rounded-brand text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2.5">
            <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="leading-relaxed">
                <span class="font-bold">Politique de suppression et intégrité :</span>
                Conformément aux règles comptables et à l'architecture API (`apiResource()->only(['index', 'store', 'show', 'update'])`), la suppression physique des plans est désactivée afin de préserver l'historique des abonnements existants. Vous pouvez désactiver un plan pour empêcher toute nouvelle souscription tout en protégeant les contrats en cours.
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
                        id="plans-search-input"
                        placeholder="Rechercher par nom ou code slug (ex: Pro, formule, essentiel)..."
                        class="w-full pl-10 pr-9 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all placeholder:text-gray-400"
                        aria-label="Rechercher un plan par nom ou slug"
                    >
                    <button
                        type="button"
                        id="plans-search-clear"
                        class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 touch-target"
                        aria-label="Effacer la recherche"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Filtre par statut d'activité -->
                <div class="flex items-center gap-2">
                    <label for="plans-active-filter" class="sr-only">Filtrer par disponibilité</label>
                    <select
                        id="plans-active-filter"
                        class="px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all"
                    >
                        <option value="">Tous les plans</option>
                        <option value="1">Actifs uniquement</option>
                        <option value="0">Inactifs uniquement</option>
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

        <!-- ÉTAT 1 : Chargement (Skeleton Loader Grid) -->
        <div id="plans-loading-state" class="space-y-4 animate-pulse" aria-busy="true" aria-label="Chargement des plans SaaS">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @for($i = 0; $i < 3; $i++)
                    <div class="bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border p-6 space-y-4">
                        <div class="flex justify-between items-start">
                            <div class="space-y-2 flex-1">
                                <div class="h-6 bg-gray-200 dark:bg-gray-800 rounded w-2/3"></div>
                                <div class="h-3 bg-gray-200 dark:bg-gray-800 rounded w-1/3"></div>
                            </div>
                            <div class="h-6 w-16 bg-gray-200 dark:bg-gray-800 rounded-full"></div>
                        </div>
                        <div class="h-10 bg-gray-200 dark:bg-gray-800 rounded w-1/2"></div>
                        <div class="h-12 bg-gray-200 dark:bg-gray-800 rounded w-full"></div>
                        <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-brand-dark-border">
                            <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-3/4"></div>
                            <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-5/6"></div>
                            <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-2/3"></div>
                        </div>
                        <div class="pt-4 flex gap-2">
                            <div class="h-9 bg-gray-200 dark:bg-gray-800 rounded flex-1"></div>
                            <div class="h-9 bg-gray-200 dark:bg-gray-800 rounded flex-1"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <!-- ÉTAT 2 : Erreur avec bouton réessayer -->
        <div id="plans-error-state" class="hidden p-8 text-center bg-white dark:bg-brand-dark-card rounded-brand-lg border border-rose-200 dark:border-rose-900/50 space-y-3">
            <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-base font-bold text-brand-text dark:text-white">
                Erreur de chargement des plans
            </h3>
            <p id="plans-error-message" class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                Une anomalie est survenue lors de la récupération des données.
            </p>
            <button
                type="button"
                id="btn-retry-plans"
                class="px-4 py-2 rounded-brand text-xs font-semibold bg-brand-orange text-white hover:bg-brand-orange-dark transition-colors focus-ring touch-target"
            >
                Réessayer
            </button>
        </div>

        <!-- ÉTAT 3 : Liste vide (Aucun plan ou aucun résultat de recherche) -->
        <div id="plans-empty-state" class="hidden p-12 text-center bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border space-y-4">
            <div class="w-14 h-14 rounded-full bg-orange-50 dark:bg-orange-950/40 text-brand-orange flex items-center justify-center mx-auto">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-brand-text dark:text-white">
                    Aucun plan SaaS trouvé
                </h3>
                <p id="plans-empty-subtitle" class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Aucune formule ne correspond à vos critères de recherche actuels.
                </p>
            </div>
            <div class="flex items-center justify-center gap-3 pt-2">
                <button
                    type="button"
                    id="btn-empty-reset"
                    class="px-3.5 py-2 rounded-brand text-xs font-semibold bg-gray-100 dark:bg-brand-dark text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors focus-ring"
                >
                    Effacer les filtres
                </button>
                <button
                    type="button"
                    id="btn-empty-create"
                    class="px-3.5 py-2 rounded-brand text-xs font-semibold bg-brand-orange hover:bg-brand-orange-dark text-white transition-colors focus-ring shadow-sm"
                >
                    Créer un nouveau plan
                </button>
            </div>
        </div>

        <!-- ÉTAT 4 : Conteneur principal des cartes (Grille de plans) -->
        <div id="plans-content" class="hidden space-y-6">
            <div id="plans-cards-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Rempli dynamiquement par admin.js (renderPlans) -->
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- MODALE DE CRÉATION & MODIFICATION DE PLAN                              -->
    <!-- ======================================================================= -->
    <div
        id="plan-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="plan-modal-title"
        class="hidden fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center transition-all duration-200"
    >
        <!-- Arrière-plan sombre -->
        <div id="plan-modal-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" aria-hidden="true"></div>

        <!-- Conteneur de la modale -->
        <div class="relative w-full max-w-2xl bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10 my-8">
            <!-- En-tête de la modale -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-full bg-brand-orange/15 text-brand-orange flex items-center justify-center font-bold text-sm shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </span>
                    <h3 id="plan-modal-title" class="text-base font-bold text-brand-text dark:text-white">
                        Nouveau Plan SaaS
                    </h3>
                </div>
                <button
                    type="button"
                    id="btn-close-plan-modal"
                    class="touch-target p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-brand focus-ring"
                    aria-label="Fermer la boîte de dialogue"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Formulaire -->
            <form id="plan-form" novalidate class="p-6 space-y-4 max-h-[calc(85vh-120px)] overflow-y-auto">
                <input type="hidden" id="plan-id" value="">
                <input type="hidden" id="plan-mode" value="create">

                <!-- Bannière d'erreur générale -->
                <div id="plan-form-general-error" class="hidden p-3 rounded-brand bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 text-xs text-rose-700 dark:text-rose-300"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nom du plan -->
                    <div class="space-y-1 sm:col-span-1">
                        <label for="plan-name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Nom du plan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="plan-name"
                            required
                            minlength="2"
                            maxlength="120"
                            placeholder="Ex : Formule Pro, Essentiel..."
                            class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all"
                        >
                        <p id="plan-error-name" class="hidden text-[11px] text-rose-500 font-medium"></p>
                    </div>

                    <!-- Slug (identifiant technique) -->
                    <div class="space-y-1 sm:col-span-1">
                        <label for="plan-slug" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Identifiant technique (slug) <span class="text-gray-400 font-normal">(auto-généré si vide)</span>
                        </label>
                        <input
                            type="text"
                            id="plan-slug"
                            maxlength="160"
                            placeholder="ex : formule-pro"
                            class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all font-mono"
                        >
                        <p id="plan-error-slug" class="hidden text-[11px] text-rose-500 font-medium"></p>
                    </div>
                </div>

                <!-- Tarification & Périodicité -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-3.5 bg-gray-50 dark:bg-brand-dark/50 rounded-brand border border-gray-200/80 dark:border-brand-dark-border">
                    <!-- Tarif principal (en FCFA / unité principale) -->
                    <div class="space-y-1 sm:col-span-1">
                        <label for="plan-price" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Tarif principal <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                id="plan-price"
                                required
                                min="0"
                                step="1"
                                placeholder="25000"
                                class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-white dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none font-semibold"
                            >
                        </div>
                        <span class="text-[10px] text-gray-400">Stocké en centimes (minor units)</span>
                        <p id="plan-error-price_minor" class="hidden text-[11px] text-rose-500 font-medium"></p>
                    </div>

                    <!-- Devise -->
                    <div class="space-y-1 sm:col-span-1">
                        <label for="plan-currency" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Devise
                        </label>
                        <select
                            id="plan-currency"
                            class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-white dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none"
                        >
                            <option value="XOF" selected>XOF (FCFA)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="USD">USD ($)</option>
                        </select>
                        <p id="plan-error-currency" class="hidden text-[11px] text-rose-500 font-medium"></p>
                    </div>

                    <!-- Période de facturation -->
                    <div class="space-y-1 sm:col-span-1">
                        <label for="plan-billing-period" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Période <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="plan-billing-period"
                            required
                            class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-white dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none"
                        >
                            <option value="month" selected>Mensuel (mois)</option>
                            <option value="year">Annuel (an)</option>
                            <option value="week">Hebdomadaire (semaine)</option>
                            <option value="day">Journalier (jour)</option>
                        </select>
                        <p id="plan-error-billing_period" class="hidden text-[11px] text-rose-500 font-medium"></p>
                    </div>

                    <!-- Intervalle de récurrence -->
                    <div class="space-y-1 sm:col-span-3">
                        <div class="flex items-center justify-between">
                            <label for="plan-billing-interval" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Intervalle de facturation (tous les X périodes)
                            </label>
                            <span class="text-[10px] text-gray-400">Défaut: 1 (ex: 1 = tous les mois)</span>
                        </div>
                        <input
                            type="number"
                            id="plan-billing-interval"
                            min="1"
                            max="36"
                            value="1"
                            class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-white dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none"
                        >
                        <p id="plan-error-billing_interval" class="hidden text-[11px] text-rose-500 font-medium"></p>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-1">
                    <label for="plan-description" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Description commerciale
                    </label>
                    <textarea
                        id="plan-description"
                        rows="2"
                        maxlength="3000"
                        placeholder="Brève description de la valeur du plan pour le restaurateur..."
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none transition-all"
                    ></textarea>
                    <p id="plan-error-description" class="hidden text-[11px] text-rose-500 font-medium"></p>
                </div>

                <!-- Fonctionnalités incluses -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label for="plan-features" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Fonctionnalités incluses (une par ligne ou séparées par des virgules)
                        </label>
                        <span class="text-[10px] text-gray-400">Array de chaînes (ex: Commandes QR, KDS cuisine...)</span>
                    </div>
                    <textarea
                        id="plan-features"
                        rows="3"
                        placeholder="Prise de commande QR table&#10;Écran cuisine KDS temps réel&#10;Gestion de caisse tactile POS&#10;Rapports financiers et export"
                        class="w-full px-3 py-2 rounded-brand text-xs sm:text-sm bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange focus:border-transparent outline-none font-sans"
                    ></textarea>
                    <p id="plan-error-features" class="hidden text-[11px] text-rose-500 font-medium"></p>
                </div>

                <!-- Limites du plan -->
                <div class="space-y-2 p-3 bg-gray-50 dark:bg-brand-dark/50 rounded-brand border border-gray-200/80 dark:border-brand-dark-border">
                    <span class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Plafonds & Limites d'utilisation
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="plan-limit-employees" class="block text-[11px] text-gray-500 dark:text-gray-400">
                                Max Employés autorisés
                            </label>
                            <input
                                type="number"
                                id="plan-limit-employees"
                                min="1"
                                placeholder="Illimité si vide (ex: 20)"
                                class="w-full px-2.5 py-1.5 rounded-brand text-xs bg-white dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange outline-none"
                            >
                        </div>
                        <div class="space-y-1">
                            <label for="plan-limit-tables" class="block text-[11px] text-gray-500 dark:text-gray-400">
                                Max Tables / Zones
                            </label>
                            <input
                                type="number"
                                id="plan-limit-tables"
                                min="1"
                                placeholder="Illimité si vide (ex: 50)"
                                class="w-full px-2.5 py-1.5 rounded-brand text-xs bg-white dark:bg-brand-dark text-brand-text dark:text-white border border-gray-200 dark:border-brand-dark-border focus:ring-2 focus:ring-brand-orange outline-none"
                            >
                        </div>
                    </div>
                </div>

                <!-- Statut Actif / Inactif -->
                <div class="flex items-center gap-2.5 pt-1">
                    <input
                        type="checkbox"
                        id="plan-is-active"
                        checked
                        class="w-4 h-4 rounded-brand-sm text-brand-orange focus:ring-brand-orange border-gray-300 dark:border-brand-dark-border dark:bg-brand-dark"
                    >
                    <label for="plan-is-active" class="text-xs font-medium text-gray-700 dark:text-gray-300 select-none">
                        Plan actif (disponible pour de nouvelles souscriptions par les restaurants)
                    </label>
                </div>

                <!-- Boutons d'action du formulaire -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-brand-dark-border">
                    <button
                        type="button"
                        id="btn-cancel-plan-modal"
                        class="px-4 py-2 rounded-brand text-xs font-semibold bg-gray-100 dark:bg-brand-dark text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors focus-ring touch-target"
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        id="btn-submit-plan-form"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-brand text-xs font-semibold bg-brand-orange hover:bg-brand-orange-dark text-white transition-colors focus-ring touch-target shadow-sm"
                    >
                        <svg id="plan-submit-spinner" class="hidden animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="plan-submit-text">Enregistrer le plan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- MODALE DE CONFIRMATION ACTIVATION / DÉSACTIVATION                      -->
    <!-- ======================================================================= -->
    <div
        id="plan-toggle-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="toggle-modal-title"
        class="hidden fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center transition-all duration-200"
    >
        <!-- Arrière-plan sombre -->
        <div id="toggle-modal-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" aria-hidden="true"></div>

        <!-- Conteneur de la modale -->
        <div class="relative w-full max-w-md bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10">
            <div class="p-6 space-y-4">
                <div class="flex items-start gap-3.5">
                    <div id="toggle-modal-icon-container" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0">
                        <!-- Icone injectée dynamiquement -->
                    </div>
                    <div class="space-y-1">
                        <h3 id="toggle-modal-title" class="text-base font-bold text-brand-text dark:text-white">
                            Confirmation de statut
                        </h3>
                        <p id="toggle-modal-plan-name" class="text-xs font-semibold text-brand-orange"></p>
                    </div>
                </div>

                <div class="p-3 bg-gray-50 dark:bg-brand-dark rounded-brand border border-gray-200/80 dark:border-brand-dark-border text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                    <p id="toggle-modal-explanation"></p>
                </div>

                <div id="toggle-modal-error" class="hidden p-2.5 rounded-brand bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 text-xs text-rose-700 dark:text-rose-300"></div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        id="btn-cancel-toggle-modal"
                        class="px-3.5 py-2 rounded-brand text-xs font-semibold bg-gray-100 dark:bg-brand-dark text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors focus-ring touch-target"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        id="btn-confirm-toggle"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-brand text-xs font-semibold text-white transition-colors focus-ring touch-target shadow-sm"
                    >
                        <svg id="toggle-submit-spinner" class="hidden animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="toggle-submit-text">Confirmer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast de retour d'action -->
    <div
        id="plans-toast"
        role="status"
        aria-live="polite"
        class="hidden fixed bottom-5 right-5 z-50 max-w-sm p-4 rounded-brand-lg shadow-lg border text-xs flex items-start gap-3 transition-all duration-200 transform translate-y-2 opacity-0"
    >
        <span id="plans-toast-icon" class="w-5 h-5 shrink-0 mt-0.5"></span>
        <div class="flex-1">
            <h4 id="plans-toast-title" class="font-bold text-sm"></h4>
            <p id="plans-toast-message" class="text-xs mt-0.5 opacity-90"></p>
        </div>
        <button type="button" id="btn-close-plans-toast" class="text-current opacity-60 hover:opacity-100 p-1 touch-target">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</x-layouts.super-admin>

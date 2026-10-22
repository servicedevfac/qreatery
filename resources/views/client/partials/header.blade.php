<header class="sticky top-0 z-30 bg-brand-light/95 dark:bg-brand-dark/95 backdrop-blur-md border-b border-brand-orange/20 dark:border-brand-dark-border px-4 py-3 transition-colors">
    <div class="max-w-md mx-auto flex items-center justify-between gap-3">
        <!-- Logo et nom du restaurant -->
        <div class="flex items-center gap-2.5 min-w-0">
            <div id="client-restaurant-logo" class="w-10 h-10 rounded-brand bg-brand-orange text-white flex items-center justify-center font-black text-xl shadow-sm shrink-0 overflow-hidden">
                <span id="client-restaurant-initial">R</span>
            </div>
            <div class="min-w-0">
                <h1 id="client-restaurant-name" class="text-sm font-bold truncate leading-tight text-brand-text dark:text-white">
                    Chargement...
                </h1>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span id="client-table-badge" class="hidden text-[11px] font-semibold text-brand-orange bg-brand-orange/10 px-2 py-0.5 rounded-full border border-brand-orange/20">
                        <!-- Rempli en JS: ex. Table 3 -->
                    </span>
                    <span id="client-mode-badge" class="text-[11px] text-gray-500 dark:text-gray-400">
                        Menu numérique
                    </span>
                </div>
            </div>
        </div>

        <!-- Actions d'en-tête : Bouton Appel Serveur, Espace Gestion & Suivi -->
        <div class="flex items-center gap-2 shrink-0">
            <!-- Accès rapide Espace Gestion Propriétaire / Staff -->
            <a
                href="/?view=restaurant&restaurant={{ request('restaurant', 'restaurant-demo') }}"
                class="touch-target p-2 rounded-brand bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border text-gray-500 hover:text-brand-orange dark:text-gray-400 dark:hover:text-brand-orange transition-colors focus-ring"
                title="Accéder à l'espace de gestion restaurant (Propriétaire / Personnel)"
                aria-label="Espace de gestion restaurant"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </a>

            <!-- Bouton Assistance / Appel Serveur (EF-Client-04) -->
            <button
                type="button"
                id="btn-call-waiter"
                class="touch-target p-2 rounded-brand bg-white dark:bg-brand-dark-card border border-brand-orange/30 text-brand-orange hover:bg-brand-orange/10 transition-colors focus-ring"
                data-modal-target="assistance-modal"
                title="Appeler le serveur ou demander l'addition"
                aria-label="Assistance serveur"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>

            <!-- Bouton Suivi de commande (si commande active) -->
            <button
                type="button"
                id="btn-view-active-order"
                class="hidden touch-target px-2.5 py-1.5 rounded-brand bg-brand-orange text-white text-xs font-bold shadow-xs hover:bg-brand-orange-dark transition-colors items-center gap-1.5"
                title="Voir le suivi de votre commande"
            >
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                <span>Suivi</span>
            </button>
        </div>
    </div>
</header>

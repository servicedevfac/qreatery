<div class="relative my-3">
    <label for="client-menu-search" class="sr-only">Rechercher dans le menu</label>
    <div class="relative flex items-center">
        <span class="absolute left-3.5 text-gray-400 pointer-events-none" aria-hidden="true">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </span>
        <input
            type="search"
            id="client-menu-search"
            placeholder="Rechercher un plat, une boisson..."
            autocomplete="off"
            class="w-full pl-10 pr-10 py-2.5 text-sm rounded-brand bg-white dark:bg-brand-dark-card border border-gray-300 dark:border-brand-dark-border text-brand-text dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:border-brand-orange focus:ring-1 focus:ring-brand-orange transition-all shadow-2xs"
        />
        <button
            type="button"
            id="client-search-clear"
            class="hidden absolute right-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1"
            aria-label="Effacer la recherche"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>

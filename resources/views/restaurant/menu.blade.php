@php
    $categories = $restaurant->categories()->withCount('products')->orderBy('position')->get();
    $products = $restaurant->products()->with(['category', 'variants', 'optionGroups.options'])->orderBy('position')->get();
    $optionGroups = $restaurant->optionGroups()->with('options')->orderBy('position')->get();
@endphp

<div class="space-y-6">
    <!-- Barre Supérieure : Navigation par Onglets & Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-brand-dark-border">
        <!-- Onglets Menu -->
        <div class="flex items-center gap-1 bg-gray-100 dark:bg-brand-dark p-1 rounded-brand-lg border border-gray-200 dark:border-brand-dark-border">
            <button type="button" class="menu-tab-btn px-4 py-2 rounded-brand text-xs font-bold transition-all bg-white dark:bg-brand-dark-card text-brand-orange shadow-sm" data-tab="tab-products">
                Plats &amp; Boissons ({{ $products->count() }})
            </button>
            <button type="button" class="menu-tab-btn px-4 py-2 rounded-brand text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-brand-orange transition-all" data-tab="tab-categories">
                Catégories ({{ $categories->count() }})
            </button>
            <button type="button" class="menu-tab-btn px-4 py-2 rounded-brand text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-brand-orange transition-all" data-tab="tab-options">
                Options &amp; Suppléments ({{ $optionGroups->count() }})
            </button>
        </div>

        <!-- Bouton Création selon onglet -->
        <div class="flex items-center gap-2">
            <button type="button" id="btn-open-create-product" class="px-3.5 py-2 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-sm flex items-center gap-2">
                <span>+</span>
                <span>Nouveau Plat</span>
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ONGLET 1 : PRODUITS (Plats, Boissons, etc.) -->
    <!-- ========================================== -->
    <div id="tab-products" class="menu-tab-content space-y-4">
        <!-- Barre de Filtres et Recherche -->
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-brand-dark-card p-3 rounded-brand-lg border border-gray-200 dark:border-brand-dark-border">
            <!-- Recherche textuelle -->
            <div class="relative w-full sm:w-72">
                <input type="text" id="filter-product-search" placeholder="Rechercher un plat..." class="w-full text-xs pl-8 pr-3 py-2 bg-gray-50 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border rounded-brand text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange">
                <span class="absolute left-2.5 top-2.5 text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
            </div>

            <!-- Filtre par Catégorie -->
            <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
                <button type="button" class="filter-cat-btn px-3 py-1.5 rounded-full text-xs font-bold bg-brand-orange text-white" data-category="">
                    Tous
                </button>
                @foreach($categories as $cat)
                    <button type="button" class="filter-cat-btn px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors whitespace-nowrap" data-category="{{ $cat->id }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Grille / Tableau des Produits -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="products-grid">
            @forelse($products as $prod)
                <div class="product-card bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-4 shadow-sm hover:border-brand-orange/40 transition-all flex flex-col justify-between"
                     data-id="{{ $prod->id }}"
                     data-name="{{ strtolower($prod->name) }}"
                     data-category-id="{{ $prod->category_id }}"
                     data-available="{{ $prod->is_available ? '1' : '0' }}">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-orange-100 dark:bg-brand-orange/15 text-brand-orange inline-block mb-1">
                                    {{ $prod->category?->name ?? 'Sans catégorie' }}
                                </span>
                                <h4 class="font-bold text-sm text-brand-text dark:text-white truncate" title="{{ $prod->name }}">
                                    {{ $prod->name }}
                                </h4>
                            </div>
                            <span class="font-black text-sm text-brand-text dark:text-white shrink-0">
                                {{ number_format($prod->price_minor / 100, 0, ',', ' ') }} <span class="text-[10px] text-gray-500 font-normal">{{ $restaurant->currency }}</span>
                            </span>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 line-clamp-2">
                            {{ $prod->description ?: 'Aucune description rédigée.' }}
                        </p>

                        <!-- Variantes & Options associées -->
                        <div class="mt-3 flex flex-wrap gap-1">
                            @if($prod->variants->isNotEmpty())
                                <span class="text-[10px] px-2 py-0.5 rounded bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-400 font-medium">
                                    {{ $prod->variants->count() }} variante(s)
                                </span>
                            @endif
                            @foreach($prod->optionGroups as $grp)
                                <span class="text-[10px] px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-300 font-medium">
                                    {{ $grp->name }}
                                </span>
                            @endforeach
                            @if($prod->preparation_minutes)
                                <span class="text-[10px] px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 font-medium inline-flex items-center gap-1">
                                    <svg class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $prod->preparation_minutes }} min</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Ligne d'action : Disponibilité & Actions -->
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-brand-dark-border flex items-center justify-between">
                        <!-- Switch Disponibilité Rapide -->
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only toggle-product-availability" data-id="{{ $prod->id }}" {{ $prod->is_available ? 'checked' : '' }}>
                            <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-2 text-xs font-semibold availability-label {{ $prod->is_available ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                {{ $prod->is_available ? 'En stock' : 'Rupture' }}
                            </span>
                        </label>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                class="btn-edit-product p-1.5 text-gray-500 hover:text-brand-orange rounded hover:bg-gray-100 dark:hover:bg-brand-dark transition-colors"
                                data-id="{{ $prod->id }}"
                                data-name="{{ $prod->name }}"
                                data-category-id="{{ $prod->category_id }}"
                                data-price="{{ (int)($prod->price_minor / 100) }}"
                                data-prep="{{ $prod->preparation_minutes }}"
                                data-desc="{{ $prod->description }}"
                                data-available="{{ $prod->is_available ? '1' : '0' }}"
                                title="Modifier"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button
                                type="button"
                                class="btn-delete-product p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors"
                                data-id="{{ $prod->id }}"
                                data-name="{{ $prod->name }}"
                                title="Supprimer le plat"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border text-gray-400 text-sm">
                    Aucun plat au menu pour le moment.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ONGLET 2 : CATÉGORIES                     -->
    <!-- ========================================== -->
    <div id="tab-categories" class="menu-tab-content space-y-4 hidden">
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-brand-text dark:text-white">Organisation des Catégories de Menu</h3>
                    <p class="text-xs text-gray-500">Ordre d'affichage sur la carte et le menu QR Code</p>
                </div>
                <button type="button" id="btn-open-create-category" class="px-3 py-1.5 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-xs flex items-center gap-1.5">
                    <span>+</span>
                    <span>Nouvelle Catégorie</span>
                </button>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-brand-dark-border">
                @foreach($categories as $index => $cat)
                    <div class="p-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:hover:bg-brand-dark/50 transition-colors" data-category-row="{{ $cat->id }}">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-brand bg-brand-light dark:bg-brand-orange/15 text-brand-orange font-bold text-xs flex items-center justify-center">
                                {{ $cat->position ?? ($index + 1) }}
                            </span>
                            <div>
                                <h4 class="font-bold text-sm text-brand-text dark:text-white">{{ $cat->name }}</h4>
                                <p class="text-xs text-gray-400 font-mono">{{ $cat->slug }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-300">
                                {{ $cat->products_count }} plat(s)
                            </span>
                            <span class="text-xs font-semibold {{ $cat->is_active ? 'text-emerald-600' : 'text-gray-400' }}">
                                {{ $cat->is_active ? 'Visible sur la carte' : 'Masquée' }}
                            </span>
                            <button
                                type="button"
                                class="btn-delete-category p-1 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors"
                                data-id="{{ $cat->id }}"
                                data-name="{{ $cat->name }}"
                                title="Supprimer la catégorie"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ONGLET 3 : GROUPES D'OPTIONS & SUPPLÉMENTS -->
    <!-- ========================================== -->
    <div id="tab-options" class="menu-tab-content space-y-4 hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($optionGroups as $grp)
                <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-4 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between">
                            <h4 class="font-bold text-sm text-brand-text dark:text-white">{{ $grp->name }}</h4>
                            <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $grp->minimum_selections > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-gray-100 text-gray-600' }}">
                                {{ $grp->minimum_selections > 0 ? 'Requis (Min ' . $grp->minimum_selections . ')' : 'Facultatif' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            Sélection : de {{ $grp->minimum_selections }} à {{ $grp->maximum_selections }} choix max
                        </p>

                        <!-- Liste des Options -->
                        <div class="mt-3 space-y-1.5 border-t border-gray-100 dark:border-brand-dark-border pt-3">
                            @foreach($grp->options as $opt)
                                <div class="flex items-center justify-between text-xs py-1">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $opt->name }}</span>
                                    <span class="font-semibold text-brand-orange">
                                        @if($opt->price_delta_minor > 0)
                                            +{{ number_format($opt->price_delta_minor / 100, 0, ',', ' ') }} {{ $restaurant->currency }}
                                        @else
                                            Inclus (0 F)
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

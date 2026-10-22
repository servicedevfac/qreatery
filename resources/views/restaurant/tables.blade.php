@php
    $tables = $restaurant->restaurantTables()->whereNull('archived_at')->with(['qrCodes'])->orderBy('id')->get();
    $takeawayQr = $restaurant->qrCodes()->where('type', 'takeaway')->where('status', 'active')->first();
    $qrConfig = $restaurant->settings['qr_options'] ?? [];
    
    // Commandes actives par table pour identifier l'occupation en temps réel
    $activeOrdersByTable = $restaurant->orders()
        ->whereIn('preparation_status', ['pending', 'accepted', 'preparing', 'ready'])
        ->whereNotNull('restaurant_table_id')
        ->get()
        ->groupBy('restaurant_table_id');
@endphp

<div class="space-y-6">
    <!-- Barre Supérieure d'Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-brand-dark-border">
        <div>
            <h3 class="text-sm font-bold text-brand-text dark:text-white flex items-center gap-2">
                <span>Plan de Salle &amp; QR Codes</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-brand-orange/10 text-brand-orange">
                    {{ $tables->count() }} tables
                </span>
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Gérez les tables physiques de votre établissement, personnalisez et imprimez leurs accès digitaux</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Bouton Options de Configuration QR Codes -->
            <button type="button" id="btn-open-qr-config" class="px-3.5 py-2 bg-white dark:bg-brand-dark-card hover:border-brand-orange border border-gray-200 dark:border-brand-dark-border text-brand-text dark:text-white text-xs font-bold rounded-brand transition-colors flex items-center gap-1.5 shadow-xs">
                <span>⚙️</span>
                <span>Configuration QR Codes</span>
            </button>

            <!-- Bouton Impression de la Planche Complète -->
            <a href="/api/v1/restaurants/{{ $restaurant->id }}/qr-codes/print-sheet" target="_blank" id="btn-print-qr-sheet" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-brand border border-gray-200 dark:border-brand-dark-border transition-colors flex items-center gap-1.5 shadow-xs">
                <span>🖨️</span>
                <span>Planche QR (PDF A4)</span>
            </a>

            <!-- Bouton Création Table -->
            <button type="button" id="btn-open-create-table" class="px-3.5 py-2 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-sm flex items-center gap-1.5">
                <span>+</span>
                <span>Nouvelle Table</span>
            </button>
        </div>
    </div>

    <!-- Bandeau Récapitulatif de la Configuration Active des QR Codes -->
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-4 flex flex-wrap items-center justify-between gap-4 text-xs shadow-xs">
        <div class="flex flex-wrap items-center gap-6">
            <div class="flex items-center gap-2">
                <span class="text-base">📱</span>
                <div>
                    <span class="font-bold text-gray-700 dark:text-gray-300">Commande à table :</span>
                    <span class="ml-1 font-semibold {{ ($qrConfig['allow_table_ordering'] ?? true) ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ ($qrConfig['allow_table_ordering'] ?? true) ? 'Activée (Commande directe)' : 'Menu digital seul (Consultation)' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-base">🛎️</span>
                <div>
                    <span class="font-bold text-gray-700 dark:text-gray-300">Appel serveur / Addition :</span>
                    <span class="ml-1 font-semibold {{ ($qrConfig['allow_waiter_call'] ?? true) ? 'text-emerald-600' : 'text-gray-400' }}">
                        {{ ($qrConfig['allow_waiter_call'] ?? true) ? 'Activé' : 'Désactivé' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-base">🎨</span>
                <div class="flex items-center gap-1.5">
                    <span class="font-bold text-gray-700 dark:text-gray-300">Teinte QR :</span>
                    <span class="w-3.5 h-3.5 rounded-full inline-block border border-gray-300" style="background-color: {{ $qrConfig['qr_color'] ?? '#F97316' }}"></span>
                    <span class="font-mono text-[11px] text-gray-500">{{ $qrConfig['qr_color'] ?? '#F97316' }}</span>
                </div>
            </div>
        </div>

        <button type="button" class="btn-trigger-qr-config text-brand-orange hover:underline font-bold text-xs flex items-center gap-1">
            <span>Modifier la configuration</span>
            <span>➔</span>
        </button>
    </div>

    <!-- Carte Spéciale : QR Code Général pour Commandes À Emporter (Takeaway) -->
    <div class="bg-gradient-to-r from-orange-500/10 via-amber-500/10 to-transparent border border-brand-orange/30 rounded-brand-lg p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-brand bg-white dark:bg-brand-dark-card border border-brand-orange/30 p-2 flex items-center justify-center shrink-0 shadow-sm">
                <!-- SVG QR Code Icon -->
                <svg class="w-12 h-12 text-brand-orange" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h6v6H3V3zm12 0h6v6h-6V3zm-12 12h6v6H3v-6zm15 3h.01M18 15h3v3h-3v-3zm-3 3v3h3v-3h-3zm0-3h3v-3h-3v3zM6 6h.01M18 6h.01M6 18h.01"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-orange">Accès Comptoir / Devanture</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Actif</span>
                </div>
                <h4 class="font-bold text-base text-brand-text dark:text-white mt-1">QR Code « À Emporter » (Takeaway)</h4>
                <p class="text-xs text-gray-500 mt-0.5 max-w-lg">
                    Placez ce QR Code au comptoir ou sur votre devanture. Les clients le scannent pour commander à emporter sans être rattachés à une table.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto shrink-0">
            @if($takeawayQr)
                <a href="/api/v1/restaurants/{{ $restaurant->id }}/qr-codes/{{ $takeawayQr->id }}/download?format=svg" class="px-3 py-1.5 bg-white dark:bg-brand-dark-card hover:border-brand-orange border border-gray-200 dark:border-brand-dark-border text-brand-text dark:text-white text-xs font-bold rounded-brand transition-colors flex items-center gap-1 shadow-xs" title="Télécharger le fichier vectoriel SVG">
                    <span>📥</span>
                    <span>SVG</span>
                </a>
                <a href="/api/v1/restaurants/{{ $restaurant->id }}/qr-codes/{{ $takeawayQr->id }}/download?format=pdf" class="px-3 py-1.5 bg-white dark:bg-brand-dark-card hover:border-brand-orange border border-gray-200 dark:border-brand-dark-border text-brand-text dark:text-white text-xs font-bold rounded-brand transition-colors flex items-center gap-1 shadow-xs" title="Télécharger l'affiche A5 PDF">
                    <span>📄</span>
                    <span>PDF A5</span>
                </a>
                <a href="/?restaurant={{ $restaurant->slug }}" target="_blank" class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-sm flex items-center gap-1">
                    <span>Tester le menu ↗</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Grille des Tables du Restaurant -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                Tables en Salle &amp; Terrasses ({{ $tables->count() }})
            </h4>
            <div class="flex items-center gap-4 text-xs font-medium text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Libre</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span> Occupée / En cours</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($tables as $table)
                @php
                    $isOccupied = isset($activeOrdersByTable[$table->id]) && $activeOrdersByTable[$table->id]->isNotEmpty();
                    $activeOrdersCount = $isOccupied ? $activeOrdersByTable[$table->id]->count() : 0;
                    $qr = $table->qrCodes->firstWhere('status', 'active');
                @endphp
                <div class="table-card bg-white dark:bg-brand-dark-card border {{ $isOccupied ? 'border-amber-400 dark:border-amber-500/50 shadow-amber-500/5' : 'border-gray-200 dark:border-brand-dark-border' }} rounded-brand-lg p-5 shadow-sm hover:border-brand-orange/40 transition-all flex flex-col justify-between"
                     data-id="{{ $table->id }}">
                    <div>
                        <!-- En-tête de la carte table -->
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    Table #{{ $table->id }}
                                </span>
                                <h4 class="font-bold text-lg text-brand-text dark:text-white truncate">
                                    {{ $table->label }}
                                </h4>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $isOccupied ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                                {{ $isOccupied ? 'Occupée (' . $activeOrdersCount . ')' : 'Disponible' }}
                            </span>
                        </div>

                        <!-- Caractéristiques -->
                        <div class="mt-3 flex items-center gap-3 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <span>👥</span>
                                <span>{{ $table->capacity }} couverts</span>
                            </span>
                            <span>•</span>
                            <span class="{{ $table->is_active ? 'text-emerald-600 font-medium' : 'text-gray-400' }}">
                                {{ $table->is_active ? 'Active' : 'Désactivée' }}
                            </span>
                        </div>

                        <!-- Bloc QR Code & Token -->
                        @if($qr)
                            <div class="mt-4 p-3 bg-gray-50 dark:bg-brand-dark rounded-brand border border-gray-100 dark:border-brand-dark-border flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <span class="font-bold text-gray-700 dark:text-gray-300">QR Code Table</span>
                                        <span class="text-emerald-600 font-semibold font-mono text-[10px]">● Actif</span>
                                    </div>
                                    <p class="text-[10px] font-mono text-gray-400 truncate mt-0.5" title="{{ $qr->token }}">
                                        {{ substr($qr->token, 0, 12) }}...{{ substr($qr->token, -6) }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="btn-config-table-qr px-2.5 py-1.5 rounded bg-brand-light dark:bg-brand-orange/15 hover:bg-brand-orange hover:text-white text-brand-orange text-xs font-bold transition-colors shrink-0"
                                    data-table-id="{{ $table->id }}"
                                    data-table-label="{{ $table->label }}"
                                    data-table-capacity="{{ $table->capacity }}"
                                    data-table-active="{{ $table->is_active ? '1' : '0' }}"
                                    data-qr-id="{{ $qr->id }}"
                                    data-qr-token="{{ $qr->token }}"
                                    title="Configurer et afficher le QR Code de cette table"
                                >
                                    ⚙️ Gérer QR
                                </button>
                            </div>
                        @else
                            <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-950/30 rounded-brand text-xs text-amber-700 dark:text-amber-300 flex items-center justify-between gap-2">
                                <span>Aucun QR actif.</span>
                                <button type="button" class="btn-create-table-qr text-xs font-bold text-brand-orange hover:underline" data-table-id="{{ $table->id }}">
                                    Générer +
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Actions Table -->
                    <div class="mt-5 pt-3 border-t border-gray-100 dark:border-brand-dark-border flex items-center justify-between gap-1.5 flex-wrap">
                        @if($qr)
                            <!-- Test du QR Code client -->
                            <a href="/?qr={{ $qr->token }}" target="_blank" class="px-2.5 py-1.5 bg-gray-50 dark:bg-brand-dark hover:bg-brand-orange hover:text-white text-gray-700 dark:text-gray-300 text-xs font-bold rounded border border-gray-200 dark:border-brand-dark-border transition-colors whitespace-nowrap" title="Tester le menu client sur cette table">
                                Tester ↗
                            </a>

                            <!-- Téléchargement rapide Chevalet PDF -->
                            <a href="/api/v1/restaurants/{{ $restaurant->id }}/qr-codes/{{ $qr->id }}/download?format=pdf" target="_blank" class="px-2 py-1.5 bg-gray-50 dark:bg-brand-dark hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 text-xs font-medium rounded border border-gray-200 dark:border-brand-dark-border transition-colors whitespace-nowrap" title="Télécharger le chevalet A5 PDF">
                                📄 PDF
                            </a>
                        @endif

                        @if($isOccupied)
                            <button
                                type="button"
                                class="btn-close-table px-2.5 py-1.5 text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 rounded transition-colors whitespace-nowrap"
                                data-id="{{ $table->id }}"
                                data-label="{{ $table->label }}"
                                title="Clôturer les commandes et libérer la table"
                            >
                                Libérer
                            </button>
                        @endif

                        <button
                            type="button"
                            class="btn-delete-table p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors ml-auto"
                            data-id="{{ $table->id }}"
                            data-label="{{ $table->label }}"
                            title="Supprimer la table"
                        >
                            🗑️
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-4 text-center py-12 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border text-gray-400 text-sm">
                    Aucune table enregistrée pour ce restaurant. Cliquez sur « + Nouvelle Table » pour en ajouter une.
                </div>
            @endforelse
        </div>
    </div>
</div>

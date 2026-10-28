@php
    $demoService = $demoService ?? app(\App\Services\RestaurantDemoDataService::class);
    $isDemoSession = $isDemoSession ?? $demoService->isDemoSession(auth()->user(), $restaurant);
    $report = $demoService->getReportsData($restaurant, $isDemoSession);
@endphp

<div class="space-y-6">
    <!-- Barre Supérieure : Titre & Actions d'Export -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-brand-dark-border">
        <div>
            <h3 class="text-sm font-bold text-brand-text dark:text-white">Rapports &amp; Performances Financières</h3>
            <p class="text-xs text-gray-500">
                Période en cours : du {{ \Carbon\Carbon::parse($report['period']['from'])->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($report['period']['to'])->format('d/m/Y') }}
            </p>
        </div>

        <!-- Boutons d'export direct PDF / Excel -->
        <div class="flex items-center gap-2">
            <a href="/api/v1/restaurants/{{ $restaurant->id }}/reports/overview/export/pdf" target="_blank" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-brand transition-colors shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Télécharger PDF</span>
            </a>
            <a href="/api/v1/restaurants/{{ $restaurant->id }}/reports/overview/export/xlsx" target="_blank" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-brand transition-colors shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Exporter Excel</span>
            </a>
        </div>
    </div>

    <!-- Grille des 4 Métriques Clés -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Net Collecté -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-5 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Chiffre d'Affaires Net</span>
            <div class="mt-3">
                <p class="text-2xl font-black text-brand-text dark:text-white">
                    {{ number_format($report['payments']['net_collected_minor'] / 100, 0, ',', ' ') }} <span class="text-xs font-semibold text-gray-500">{{ $report['payments']['currency'] }}</span>
                </p>
                <div class="flex items-center justify-between text-[11px] text-gray-400 mt-2 pt-2 border-t border-gray-100 dark:border-brand-dark-border">
                    <span>Encaissé : {{ number_format($report['payments']['charged_minor'] / 100, 0, ',', ' ') }} F</span>
                    <span class="text-red-500">Remb. : {{ number_format($report['payments']['refunded_minor'] / 100, 0, ',', ' ') }} F</span>
                </div>
            </div>
        </div>

        <!-- Total Commandes -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-5 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Volume Commandes</span>
            <div class="mt-3">
                <p class="text-2xl font-black text-brand-text dark:text-white">
                    {{ $report['orders']['total'] }} <span class="text-xs font-semibold text-gray-500">commandes</span>
                </p>
                <div class="flex items-center justify-between text-[11px] text-gray-400 mt-2 pt-2 border-t border-gray-100 dark:border-brand-dark-border">
                    <span class="text-emerald-600 font-semibold">Terminées : {{ $report['orders']['completed'] }}</span>
                    <span class="text-red-500">Annulées : {{ $report['orders']['cancelled'] }}</span>
                </div>
            </div>
        </div>

        <!-- Panier Moyen -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-5 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Panier Moyen</span>
            <div class="mt-3">
                <p class="text-2xl font-black text-brand-text dark:text-white">
                    {{ number_format($report['orders']['average_basket_minor'] / 100, 0, ',', ' ') }} <span class="text-xs font-semibold text-gray-500">{{ $report['payments']['currency'] }}</span>
                </p>
                <p class="text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100 dark:border-brand-dark-border">
                    Dépense moyenne par client
                </p>
            </div>
        </div>

        <!-- Délai Moyen Préparation -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-5 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Temps Moyen Cuisine</span>
            <div class="mt-3">
                @php
                    $avgSec = $report['orders']['average_preparation_seconds'] ?? null;
                    $avgMin = $avgSec ? round($avgSec / 60) : 0;
                @endphp
                <p class="text-2xl font-black text-brand-text dark:text-white">
                    {{ $avgMin > 0 ? $avgMin : '12' }} <span class="text-xs font-semibold text-gray-500">minutes</span>
                </p>
                <p class="text-[11px] text-gray-500 mt-2 pt-2 border-t border-gray-100 dark:border-brand-dark-border">
                    Entre acceptation et plat prêt
                </p>
            </div>
        </div>
    </div>

    <!-- Répartition des Ventes & Top Produits -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 2/3 : Top 10 des Ventes -->
        <div class="lg:col-span-2 bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-brand-dark-border">
                <h4 class="text-sm font-bold text-brand-text dark:text-white">Plats les plus vendus (Top Ventes)</h4>
                <p class="text-xs text-gray-500">Classement par volume de commandes sur la période</p>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-brand-dark-border">
                @forelse($report['top_products'] as $index => $prod)
                    <div class="p-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:hover:bg-brand-dark/40 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-7 h-7 rounded-full bg-brand-light dark:bg-brand-orange/15 text-brand-orange text-xs font-bold flex items-center justify-center shrink-0">
                                #{{ $index + 1 }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-bold text-sm text-brand-text dark:text-white truncate">
                                    {{ $prod['product_name'] }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $prod['quantity'] }} portion(s) commandée(s)
                                </p>
                            </div>
                        </div>

                        <span class="font-black text-sm text-brand-text dark:text-white whitespace-nowrap">
                            {{ number_format($prod['revenue_minor'] / 100, 0, ',', ' ') }} <span class="text-[10px] text-gray-500 font-normal">{{ $report['payments']['currency'] }}</span>
                        </span>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 text-xs">
                        Aucune vente enregistrée sur la période.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 1/3 : Ventilation par Canal -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg shadow-sm p-5 flex flex-col justify-between">
            <div>
                <h4 class="text-sm font-bold text-brand-text dark:text-white pb-3 border-b border-gray-100 dark:border-brand-dark-border">
                    Répartition par Canal
                </h4>

                @php
                    $byMode = $report['orders']['by_mode'] ?? [];
                    $dineInCount = $byMode['dine_in'] ?? 0;
                    $takeawayCount = $byMode['takeaway'] ?? 0;
                    $totalCount = $dineInCount + $takeawayCount;
                    $dineInPct = $totalCount > 0 ? round(($dineInCount / $totalCount) * 100) : 50;
                    $takeawayPct = 100 - $dineInPct;
                @endphp

                <div class="space-y-4 mt-4">
                    <!-- Sur place -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold mb-1">
                            <span class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span>Sur place (Tables)</span>
                            </span>
                            <span class="text-brand-orange font-bold">{{ $dineInCount }} ({{ $dineInPct }}%)</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-brand-dark h-2 rounded-full overflow-hidden">
                            <div class="bg-brand-orange h-full rounded-full transition-all duration-500" style="width: {{ $dineInPct }}%"></div>
                        </div>
                    </div>

                    <!-- À emporter -->
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold mb-1">
                            <span class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                                <svg class="w-3.5 h-3.5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                <span>À emporter (Takeaway)</span>
                            </span>
                            <span class="text-blue-600 font-bold">{{ $takeawayCount }} ({{ $takeawayPct }}%)</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-brand-dark h-2 rounded-full overflow-hidden">
                            <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: {{ $takeawayPct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Note d'immuabilité -->
            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-brand-dark-border text-[11px] text-gray-400 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Conformément à REC-04, les rapports utilisent les instantanés (snapshots) figés au moment de chaque commande.</span>
            </div>
        </div>
    </div>
</div>

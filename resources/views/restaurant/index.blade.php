@php
    $restaurantSlug = request('restaurant', 'restaurant-demo');
    $restaurant = \App\Models\Restaurant::query()->where('slug', $restaurantSlug)->first()
        ?? \App\Models\Restaurant::query()->where('status', 'active')->first()
        ?? \App\Models\Restaurant::query()->first();

    $allRestaurants = \App\Models\Restaurant::query()->orderBy('name')->get();

    $activeSection = request('section', 'dashboard');
    $sectionTitles = [
        'dashboard' => 'Tableau de bord',
        'orders' => 'Commandes & Suivi en direct',
        'menu' => 'Carte, Menu & Produits',
        'tables' => 'Plan de Salle & QR Codes',
        'reports' => 'Rapports & Analyses Financières',
        'settings' => 'Configuration & Équipe',
    ];
    $headerTitle = ($restaurant ? $restaurant->name . ' — ' : '') . ($sectionTitles[$activeSection] ?? 'Gestion Restaurant');

    // Vérification de l'état d'abonnement pour alerte éventuelle (DEC-07 / DEC-08)
    $activeSub = $restaurant?->subscriptions()->latest('id')->first();
    $graceAlert = null;
    if ($activeSub && $activeSub->status->value === 'grace_period') {
        $graceAlert = "Abonnement en période de grâce de 7 jours. Veuillez régulariser avant le " . ($activeSub->ends_at ? $activeSub->ends_at->copy()->addDays(7)->format('d/m/Y') : 'terme') . ".";
    } elseif ($activeSub && $activeSub->status->value === 'expired') {
        $graceAlert = "Abonnement expiré. Les nouvelles commandes clients sont suspendues. Les exports et le renouvellement restent accessibles.";
    }
@endphp

<x-layouts.admin :title="$headerTitle" :headerTitle="$headerTitle" :gracePeriodAlert="$graceAlert">
    <x-slot name="headerActions">
        <!-- Sélecteur d'établissement actif -->
        <div class="relative flex items-center gap-2">
            <label for="restaurant-switcher" class="text-xs font-medium text-gray-500 hidden sm:inline">Établissement :</label>
            <select id="restaurant-switcher" class="text-xs font-semibold bg-gray-50 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border text-brand-text dark:text-white rounded-brand px-2.5 py-1.5 focus:ring-1 focus:ring-brand-orange focus:border-brand-orange cursor-pointer">
                @foreach($allRestaurants as $resto)
                    <option value="{{ $resto->slug }}" {{ $restaurant && $restaurant->id === $resto->id ? 'selected' : '' }}>
                        {{ $resto->name }} ({{ strtoupper($resto->status->value) }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Authentification & Profil Propriétaire -->
        @if(auth()->check())
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="max-w-[120px] truncate" title="{{ auth()->user()->email }}">{{ auth()->user()->name }}</span>
                </span>
                <button type="button" id="btn-owner-logout" class="px-2.5 py-1 text-xs font-bold text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 bg-gray-100 dark:bg-brand-dark rounded-brand transition-colors" title="Se déconnecter">
                    Déconnexion
                </button>
            </div>
        @else
            <div class="flex items-center gap-2">
                <a href="/login" class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Se connecter</span>
                </a>
            </div>
        @endif

        <!-- Bouton Rafraîchir -->
        <button type="button" id="btn-refresh-restaurant" class="p-1.5 text-gray-500 hover:text-brand-orange dark:text-gray-400 rounded-brand hover:bg-gray-100 dark:hover:bg-brand-dark transition-colors" title="Actualiser les données">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </button>
    </x-slot>

    <!-- Bandeau Mode Consultation si non connecté -->
    @if(!auth()->check())
        <div class="mb-5 bg-gradient-to-r from-amber-500/10 via-brand-orange/10 to-transparent border border-amber-500/30 rounded-brand-lg p-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5">
                <span class="text-base">🔒</span>
                <div>
                    <span class="font-bold text-brand-text dark:text-white">Session invité (Consultation) :</span>
                    <span class="text-gray-600 dark:text-gray-400 ml-1">Connectez-vous pour ajouter des plats, créer des tables ou modifier la configuration.</span>
                </div>
            </div>
            <a href="/login" class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold rounded-brand transition-colors shrink-0 shadow-xs flex items-center gap-1.5">
                <span>Se connecter</span>
                <span>➔</span>
            </a>
        </div>
    @endif

    <!-- Contenu selon la section active -->
    @if($activeSection === 'orders')
        @include('restaurant.orders')
    @elseif($activeSection === 'menu')
        @include('restaurant.menu')
    @elseif($activeSection === 'tables')
        @include('restaurant.tables')
    @elseif($activeSection === 'reports')
        @include('restaurant.reports')
    @elseif($activeSection === 'settings')
        @include('restaurant.settings')
    @else
        @include('restaurant.dashboard')
    @endif

    <!-- Modales fonctionnelles de l'espace restaurant -->
    @push('modals')
        @include('restaurant.partials.auth-modal')
        @include('restaurant.partials.menu-modals')
        @include('restaurant.partials.table-modals')
        @include('restaurant.partials.settings-modals')
    @endpush

    <!-- Données de contexte pour les scripts front -->
    <script>
        window.QreateryContext = {
            restaurantId: {{ $restaurant->id ?? 1 }},
            restaurantSlug: '{{ $restaurant->slug ?? "restaurant-demo" }}',
            restaurantName: '{{ addslashes($restaurant->name ?? "Restaurant Démo") }}',
            currency: '{{ $restaurant->currency ?? "XOF" }}',
            activeSection: '{{ $activeSection }}',
            csrfToken: '{{ csrf_token() }}',
            isAuthenticated: {{ auth()->check() ? 'true' : 'false' }},
            userName: '{{ addslashes(auth()->user()->name ?? "") }}'
        };
    </script>

    @push('scripts')
    @vite(['resources/js/restaurant.js'])
    @endpush
</x-layouts.admin>

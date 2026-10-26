<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F97316">

    <title>{{ $title ?? 'Administration — ' . config('app.name', 'APP RESTAURANT') }}</title>

    @fonts

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-gray-100 font-sans antialiased flex flex-col md:flex-row selection:bg-brand-orange selection:text-white">
@php
    $activeSection = $activeSection ?? request('section', 'dashboard');
    $currentRestaurantSlug = request('restaurant', 'restaurant-demo');
    $queryResto = '/?view=restaurant' . ($currentRestaurantSlug ? '&restaurant=' . $currentRestaurantSlug : '');
    $user = auth()->user();
    $userRoleLabel = 'Invité';
    if ($user) {
        if ($user->is_platform_admin) {
            $userRoleLabel = 'Super-Admin';
        } else {
            $membership = $user->restaurantMemberships()->where('is_active', true)->first();
            $roleValue = $membership?->role?->value ?? (string) ($membership?->role ?? 'owner');
            $userRoleLabel = match($roleValue) {
                'owner' => 'Propriétaire',
                'manager' => 'Gérant',
                'cashier' => 'Caissier',
                'staff' => 'Personnel',
                default => ucfirst($roleValue),
            };
        }
    }
@endphp
    <!-- Barre latérale / Sidebar -->
    <aside id="admin-sidebar" class="w-64 bg-white dark:bg-brand-dark-card border-r border-gray-200 dark:border-brand-dark-border flex flex-col shrink-0 transition-transform duration-200 z-30 fixed inset-y-0 left-0 -translate-x-full md:relative md:translate-x-0">
        <!-- Logo / Marque -->
        <div class="h-16 px-5 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
            <a href="{{ $queryResto }}&section=dashboard" class="flex items-center gap-2.5 font-bold text-lg text-brand-text dark:text-white">
                <span class="w-8 h-8 rounded-brand bg-brand-orange text-white flex items-center justify-center font-black text-base shadow-sm">
                    Q
                </span>
                <span class="tracking-tight font-extrabold">QREATERY</span>
            </a>
            <button type="button" id="admin-close-sidebar" class="md:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 p-1 rounded-brand" aria-label="Fermer le menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation principale -->
        <nav class="flex-1 p-3 space-y-1 overflow-y-auto" aria-label="Navigation latérale">
            <!-- Tableau de bord -->
            <a href="{{ $queryResto }}&section=dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-brand text-sm font-semibold transition-colors {{ $activeSection === 'dashboard' ? 'text-brand-orange bg-brand-light dark:bg-brand-orange/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-brand-dark' }}">
                <svg class="w-5 h-5 {{ $activeSection === 'dashboard' ? 'text-brand-orange' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Tableau de bord</span>
            </a>

            <!-- Commandes -->
            <a href="{{ $queryResto }}&section=orders" class="flex items-center gap-3 px-3 py-2.5 rounded-brand text-sm font-medium transition-colors {{ $activeSection === 'orders' ? 'text-brand-orange bg-brand-light dark:bg-brand-orange/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-brand-dark' }}">
                <svg class="w-5 h-5 {{ $activeSection === 'orders' ? 'text-brand-orange' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Commandes</span>
            </a>

            <!-- Menu & Produits -->
            <a href="{{ $queryResto }}&section=menu" class="flex items-center gap-3 px-3 py-2.5 rounded-brand text-sm font-medium transition-colors {{ $activeSection === 'menu' ? 'text-brand-orange bg-brand-light dark:bg-brand-orange/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-brand-dark' }}">
                <svg class="w-5 h-5 {{ $activeSection === 'menu' ? 'text-brand-orange' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Menu &amp; Produits</span>
            </a>

            <!-- Tables & QR Codes -->
            <a href="{{ $queryResto }}&section=tables" class="flex items-center gap-3 px-3 py-2.5 rounded-brand text-sm font-medium transition-colors {{ $activeSection === 'tables' ? 'text-brand-orange bg-brand-light dark:bg-brand-orange/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-brand-dark' }}">
                <svg class="w-5 h-5 {{ $activeSection === 'tables' ? 'text-brand-orange' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>Tables &amp; QR Codes</span>
            </a>

            <!-- Rapports & Ventes -->
            <a href="{{ $queryResto }}&section=reports" class="flex items-center gap-3 px-3 py-2.5 rounded-brand text-sm font-medium transition-colors {{ $activeSection === 'reports' ? 'text-brand-orange bg-brand-light dark:bg-brand-orange/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-brand-dark' }}">
                <svg class="w-5 h-5 {{ $activeSection === 'reports' ? 'text-brand-orange' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Rapports &amp; Ventes</span>
            </a>

            <!-- Équipe & Paramètres -->
            <a href="{{ $queryResto }}&section=settings" class="flex items-center gap-3 px-3 py-2.5 rounded-brand text-sm font-medium transition-colors {{ $activeSection === 'settings' ? 'text-brand-orange bg-brand-light dark:bg-brand-orange/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-brand-dark' }}">
                <svg class="w-5 h-5 {{ $activeSection === 'settings' ? 'text-brand-orange' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Équipe &amp; Paramètres</span>
            </a>

            <div class="pt-4 pb-1 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Postes Opérationnels
            </div>

            <!-- Caisse POS -->
            <a href="/?view=pos&restaurant={{ $currentRestaurantSlug }}" target="_blank" class="flex items-center justify-between px-3 py-2.5 rounded-brand text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-brand-dark transition-colors group">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Caisse (POS)</span>
                </div>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 font-mono">↗</span>
            </a>

            <!-- Écran Cuisine KDS -->
            <a href="/?view=kds&restaurant={{ $currentRestaurantSlug }}" target="_blank" class="flex items-center justify-between px-3 py-2.5 rounded-brand text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-brand-dark transition-colors group">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Écran Cuisine (KDS)</span>
                </div>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 font-mono">↗</span>
            </a>

            <!-- Menu Public Client -->
            <a href="/?restaurant={{ $currentRestaurantSlug }}" target="_blank" class="flex items-center justify-between px-3 py-2.5 rounded-brand text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-brand-dark transition-colors group">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Menu Client (Web)</span>
                </div>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 font-mono">↗</span>
            </a>
        </nav>

        <!-- Bas de barre latérale : Utilisateur & Thème -->
        <div class="p-3 border-t border-gray-200 dark:border-brand-dark-border space-y-2">
            <div class="px-3 py-1.5 flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-bold text-brand-text dark:text-white truncate" id="sidebar-user-name">{{ $user->name ?? 'Propriétaire Démo' }}</p>
                    <p class="text-[11px] text-gray-500 truncate" id="sidebar-user-role">{{ $userRoleLabel }}</p>
                </div>
                <button type="button" id="admin-theme-toggle" class="p-1.5 text-gray-500 hover:text-brand-orange dark:text-gray-400 rounded-brand hover:bg-gray-100 dark:hover:bg-brand-dark" title="Basculer le thème">
                    <svg class="w-4 h-4 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg class="w-4 h-4 hidden dark:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
            </div>
            <div class="flex items-center gap-1.5 pt-1 border-t border-gray-100 dark:border-brand-dark-border">
                <a href="/login" class="flex items-center justify-center gap-1 flex-1 py-1 text-[11px] font-semibold text-gray-600 dark:text-gray-400 hover:text-brand-orange hover:bg-gray-50 dark:hover:bg-brand-dark rounded transition-colors" title="Changer de rôle / Portail central">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span>Portail Rôles</span>
                </a>
                <a href="/logout" class="flex items-center justify-center py-1 px-2 text-[11px] font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded transition-colors" title="Se déconnecter">
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Fond sombre pour mobile quand la sidebar est ouverte -->
    <div id="admin-sidebar-backdrop" class="fixed inset-0 bg-black/50 z-20 hidden md:hidden"></div>

    <!-- Conteneur de page principal -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Bandeau d'alerte période de grâce / abonnement (DEC-07 / DEC-08) -->
        @if(isset($gracePeriodAlert))
            <div class="bg-amber-500 text-white px-4 py-2 text-xs font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $gracePeriodAlert }}</span>
                </div>
                <a href="#" class="underline hover:text-amber-100 font-bold">Renouveler</a>
            </div>
        @endif

        <!-- En-tête supérieur Admin -->
        <header class="h-16 bg-white dark:bg-brand-dark-card border-b border-gray-200 dark:border-brand-dark-border px-4 sm:px-6 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <button type="button" id="admin-open-sidebar" class="md:hidden text-gray-600 dark:text-gray-300 p-2 rounded-brand hover:bg-gray-100 dark:hover:bg-brand-dark" aria-label="Ouvrir le menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <h2 class="text-base font-bold text-brand-text dark:text-white truncate">
                        {{ $headerTitle ?? 'Administration' }}
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if(isset($headerActions))
                    {{ $headerActions }}
                @endif
            </div>
        </header>

        <!-- Contenu de la page -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @if(session('success'))
                <x-toast type="success" :message="session('success')" class="mb-4" />
            @endif

            @if(session('error'))
                <x-toast type="error" :message="session('error')" class="mb-4" />
            @endif

            {{ $slot }}
        </main>
    </div>

    @stack('modals')

    @stack('scripts')
    <script>
        // Gestion ouverture/fermeture sidebar mobile
        var openBtn = document.getElementById('admin-open-sidebar');
        var closeBtn = document.getElementById('admin-close-sidebar');
        var sidebar = document.getElementById('admin-sidebar');
        var backdrop = document.getElementById('admin-sidebar-backdrop');

        function toggleSidebar() {
            if (sidebar && backdrop) {
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
            }
        }

        if (openBtn) openBtn.addEventListener('click', toggleSidebar);
        if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if (backdrop) backdrop.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>

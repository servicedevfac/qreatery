@props([
    'title' => null,
    'headerTitle' => null,
    'active' => request('section', 'dashboard'),
    'adminUser' => auth()->user(),
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F97316">

    <title>{{ $title ? $title . ' — Super-Admin Qreatery' : 'Super-Administration SaaS — ' . config('app.name', 'Qreatery') }}</title>

    @fonts

    <!-- Styles & Scripts Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
    @stack('styles')
</head>
<body class="h-full bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-gray-100 font-sans antialiased flex flex-col md:flex-row selection:bg-brand-orange selection:text-white">
    <!-- Barre latérale / Sidebar Super-Admin dédiée -->
    @include('admin.partials.sidebar', ['active' => $active, 'adminUser' => $adminUser])

    <!-- Fond sombre pour mobile quand la sidebar est ouverte -->
    <div
        id="super-admin-sidebar-backdrop"
        class="fixed inset-0 bg-black/60 backdrop-blur-xs z-20 hidden md:hidden transition-opacity duration-200"
        aria-hidden="true"
    ></div>

    <!-- Conteneur principal de travail -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- En-tête supérieur Super-Admin -->
        <header class="h-16 bg-white dark:bg-brand-dark-card border-b border-gray-200 dark:border-brand-dark-border px-4 sm:px-6 flex items-center justify-between shrink-0 sticky top-0 z-10 shadow-2xs">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Bouton burger mobile -->
                <button
                    type="button"
                    id="super-admin-open-sidebar"
                    class="md:hidden touch-target p-2 text-gray-600 dark:text-gray-300 hover:text-brand-orange dark:hover:text-brand-orange rounded-brand hover:bg-gray-100 dark:hover:bg-brand-dark transition-colors focus-ring"
                    aria-controls="super-admin-sidebar"
                    aria-expanded="false"
                    aria-label="Ouvrir le menu de navigation"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <div class="min-w-0 flex items-center gap-2.5">
                    <h1 class="text-base sm:text-lg font-bold text-brand-text dark:text-white truncate">
                        {{ $headerTitle ?? $title ?? 'Super-Administration SaaS' }}
                    </h1>
                    <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-100 dark:bg-orange-950/80 text-brand-orange dark:text-orange-400 border border-orange-200 dark:border-orange-900/50">
                        Plateforme
                    </span>
                </div>
            </div>

            <!-- Actions d'en-tête -->
            <div class="flex items-center gap-3">
                @if(isset($headerActions))
                    {{ $headerActions }}
                @endif

                <!-- Indicateur statut backend -->
                <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-brand bg-gray-100 dark:bg-brand-dark text-xs text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-brand-dark-border">
                    <span class="w-2 h-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                    <span class="font-medium">SaaS Opérationnel</span>
                </div>
            </div>
        </header>

        <!-- Zone de contenu principal -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            @if(session('success'))
                <x-toast type="success" :message="session('success')" class="mb-4" />
            @endif

            @if(session('error'))
                <x-toast type="error" :message="session('error')" class="mb-4" />
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>

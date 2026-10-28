<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#111827">
    <meta name="reverb-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <meta name="reverb-scheme" content="{{ config('broadcasting.connections.reverb.options.scheme') }}">

    <title>{{ $title ?? 'Caisse POS — ' . config('app.name', 'APP RESTAURANT') }}</title>

    @fonts

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-gray-100 dark:bg-brand-dark text-brand-text dark:text-gray-100 font-sans antialiased flex flex-col overflow-hidden selection:bg-brand-orange selection:text-white">
    <!-- Barre supérieure Caisse POS -->
    <header class="bg-brand-dark-card border-b border-brand-dark-border text-white px-4 py-2.5 flex items-center justify-between shrink-0 select-none">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-brand bg-brand-orange flex items-center justify-center font-bold text-lg shadow-sm">
                {{ substr($restaurantName ?? config('app.name', 'P'), 0, 1) }}
            </div>
            <div>
                <h1 class="text-sm font-bold tracking-tight leading-none">
                    {{ $restaurantName ?? config('app.name', 'APP RESTAURANT') }} — POS
                </h1>
                <p class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-medium text-emerald-400">En ligne</span>
                    </span>
                    <span>•</span>
                    <span>Opérateur: <strong class="text-gray-200">{{ $operatorName ?? 'Caisse Principale' }}</strong></span>
                </p>
            </div>
        </div>

        @php
            $currentRestoSlug = request('restaurant', 'restaurant-repos');
        @endphp

        <!-- Centre : Raccourcis navigation Propriétaire et filtres POS -->
        <div class="flex items-center gap-3">
            <!-- Navigation transversale Propriétaire -->
            <div class="hidden md:flex items-center gap-1 bg-brand-dark px-2 py-1 rounded-brand border border-brand-dark-border text-xs">
                <a href="/?view=restaurant&restaurant={{ $currentRestoSlug }}" class="px-2 py-0.5 rounded text-gray-300 hover:text-white hover:bg-gray-800 transition-colors flex items-center gap-1" title="Espace Gestion & Paramètres">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Gestion</span>
                </a>
                <span class="text-gray-600">|</span>
                <a href="/?view=kds&restaurant={{ $currentRestoSlug }}" class="px-2 py-0.5 rounded text-gray-300 hover:text-white hover:bg-gray-800 transition-colors flex items-center gap-1" title="Écran Cuisine KDS">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>Cuisine</span>
                </a>
                <span class="text-gray-600">|</span>
                <a href="/?restaurant={{ $currentRestoSlug }}" target="_blank" class="px-2 py-0.5 rounded text-gray-300 hover:text-white hover:bg-gray-800 transition-colors flex items-center gap-1" title="Menu Client Web">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Menu Client</span>
                </a>
                <span class="text-gray-600">|</span>
                <a href="/login" class="px-2 py-0.5 rounded text-gray-300 hover:text-white hover:bg-gray-800 transition-colors flex items-center gap-1" title="Portail central par rôle">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span>Rôles</span>
                </a>
            </div>

            @if(isset($headerCenter))
                <div class="flex items-center gap-2">
                    {{ $headerCenter }}
                </div>
            @endif
        </div>

        <!-- Droite : Actions rapides & Horloge -->
        <div class="flex items-center gap-3">
            @if(isset($headerActions))
                {{ $headerActions }}
            @endif

            <div id="pos-live-clock" class="text-right text-xs text-gray-300 font-mono hidden md:block">
                {{ now()->format('H:i') }}
            </div>
        </div>
    </header>

    <!-- Zone de travail POS (Plein écran tactile) -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Zone principale (Catalogue / Commandes) -->
        <main class="flex-1 flex flex-col overflow-y-auto p-3 sm:p-4 bg-gray-50 dark:bg-brand-dark">
            @if(session('success'))
                <x-toast type="success" :message="session('success')" class="mb-3" />
            @endif

            @if(session('error'))
                <x-toast type="error" :message="session('error')" class="mb-3" />
            @endif

            {{ $slot }}
        </main>

        <!-- Barre latérale droite optionnelle (Ticket en cours / Encaissement) -->
        @if(isset($sidebar))
            <aside class="w-96 lg:w-[420px] bg-white dark:bg-brand-dark-card border-l border-gray-200 dark:border-brand-dark-border flex flex-col shrink-0 shadow-lg select-none">
                {{ $sidebar }}
            </aside>
        @endif
    </div>

    @stack('modals')

    @stack('scripts')
    <script>
        // Mise à jour de l'horloge POS
        setInterval(function() {
            var clock = document.getElementById('pos-live-clock');
            if (clock) {
                var now = new Date();
                clock.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        }, 30000);
    </script>
</body>
</html>

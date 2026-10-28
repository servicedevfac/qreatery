<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B0F19">
    <meta name="reverb-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <meta name="reverb-scheme" content="{{ config('broadcasting.connections.reverb.options.scheme') }}">

    <title>{{ $title ?? 'Écran Cuisine (KDS) — ' . config('app.name', 'APP RESTAURANT') }}</title>

    @fonts

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-brand-dark text-gray-100 font-sans antialiased flex flex-col overflow-hidden select-none selection:bg-brand-orange selection:text-white">
    <!-- Barre KDS supérieure haute visibilité -->
    <header class="bg-brand-dark-card border-b border-brand-dark-border px-4 py-3 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-brand-orange animate-ping"></span>
                <span class="w-3 h-3 rounded-full bg-brand-orange -ml-5"></span>
                <h1 class="text-lg font-black tracking-wide text-white flex items-center gap-2">
                    <span>KDS CUISINE</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-brand-dark border border-brand-dark-border text-brand-orange">
                        {{ $restaurantName ?? config('app.name', 'Restaurant') }}
                    </span>
                </h1>
            </div>

            <!-- Indicateur de flux temps réel / polling REC-13 -->
            <div id="kds-connection-badge" class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-950/60 border border-emerald-800 text-emerald-400 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span id="kds-connection-label">Temps réel actif</span>
            </div>
        </div>

        @php
            $currentRestoSlug = request('restaurant', 'restaurant-repos');
        @endphp

        <!-- Centre : Raccourcis navigation Propriétaire et Compteurs KDS -->
        <div class="flex items-center gap-4">
            <!-- Navigation transversale Propriétaire -->
            <div class="hidden md:flex items-center gap-1 bg-brand-dark px-2 py-1 rounded-brand border border-brand-dark-border text-xs">
                <a href="/?view=restaurant&restaurant={{ $currentRestoSlug }}" class="px-2 py-0.5 rounded text-gray-300 hover:text-white hover:bg-gray-800 transition-colors flex items-center gap-1" title="Espace Gestion & Paramètres">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Gestion</span>
                </a>
                <span class="text-gray-600">|</span>
                <a href="/?view=pos&restaurant={{ $currentRestoSlug }}" class="px-2 py-0.5 rounded text-gray-300 hover:text-white hover:bg-gray-800 transition-colors flex items-center gap-1" title="Caisse Tactile POS">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Caisse</span>
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

            @if(isset($headerMetrics))
                <div class="flex items-center gap-3">
                    {{ $headerMetrics }}
                </div>
            @else
                <div class="flex items-center gap-3 text-xs">
                    <div class="flex items-center gap-1.5 bg-brand-dark px-3 py-1.5 rounded-brand border border-brand-dark-border">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-gray-400">&lt; 15 min</span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-brand-dark px-3 py-1.5 rounded-brand border border-brand-dark-border">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-orange"></span>
                        <span class="text-gray-400">15 - 30 min</span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-brand-dark px-3 py-1.5 rounded-brand border border-brand-dark-border">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                        <span class="text-rose-400 font-bold">&gt; 45 min (Retard)</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Droite : Son et Horloge KDS -->
        <div class="flex items-center gap-3">
            <button
                type="button"
                id="kds-audio-toggle"
                class="touch-target px-3 py-1.5 bg-brand-dark hover:bg-gray-800 border border-brand-dark-border rounded-brand text-xs font-medium text-gray-300 flex items-center gap-2 transition-colors focus-ring"
                aria-label="Activer ou désactiver les alertes sonores"
            >
                <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                </svg>
                <span id="kds-audio-label">Son activé</span>
            </button>

            <div id="kds-live-clock" class="text-sm font-mono font-bold text-white bg-brand-dark px-3 py-1.5 rounded-brand border border-brand-dark-border">
                {{ now()->format('H:i:s') }}
            </div>
        </div>
    </header>

    <!-- Vue principale KDS (Grille fluide pour colonnes Kanban) -->
    <main class="flex-1 overflow-x-auto overflow-y-hidden p-4 bg-brand-dark">
        {{ $slot }}
    </main>

    @stack('modals')

    @stack('scripts')
    <script>
        // Horloge KDS en secondes
        setInterval(function() {
            var clock = document.getElementById('kds-live-clock');
            if (clock) {
                var now = new Date();
                clock.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        }, 1000);
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B0F19">

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

        <!-- Centre : Compteurs d'état -->
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

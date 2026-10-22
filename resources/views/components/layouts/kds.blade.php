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
<body class="h-full bg-[#080C14] text-gray-100 font-sans antialiased flex flex-col overflow-hidden select-none selection:bg-brand-orange selection:text-white">
    <!-- Barre KDS supérieure haute visibilité avec effet glassmorphism -->
    <header class="bg-[#0B0F19]/95 backdrop-blur-md border-b border-gray-800/80 px-4 py-3 flex items-center justify-between shrink-0 shadow-lg z-20">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2.5">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-orange opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-orange shadow-xs"></span>
                </span>
                <h1 class="text-lg font-black tracking-wider text-white flex items-center gap-2.5">
                    <span class="bg-gradient-to-r from-white via-gray-100 to-gray-300 bg-clip-text text-transparent">KDS CUISINE</span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-[#111827] border border-gray-700/80 text-brand-orange tracking-normal shadow-2xs">
                        {{ $restaurantName ?? config('app.name', 'Restaurant') }}
                    </span>
                </h1>
            </div>

            <!-- Indicateur de flux temps réel / polling REC-13 -->
            <div id="kds-connection-badge" class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-950/60 border border-emerald-700/60 text-emerald-400 text-xs font-semibold shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
                <span id="kds-connection-label">Temps réel actif</span>
            </div>
        </div>

        <!-- Centre : Compteurs d'état & Légende sévérité -->
        @if(isset($headerMetrics))
            <div class="flex items-center gap-3">
                {{ $headerMetrics }}
            </div>
        @else
            <div class="hidden lg:flex items-center gap-2.5 text-xs">
                <div class="flex items-center gap-1.5 bg-[#111827] px-3 py-1 rounded-full border border-gray-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-gray-400 font-medium">&lt; 15 min</span>
                </div>
                <div class="flex items-center gap-1.5 bg-[#111827] px-3 py-1 rounded-full border border-gray-800">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span class="text-gray-400 font-medium">15 - 30 min</span>
                </div>
                <div class="flex items-center gap-1.5 bg-[#111827] px-3 py-1 rounded-full border border-gray-800">
                    <span class="w-2 h-2 rounded-full bg-brand-orange"></span>
                    <span class="text-gray-400 font-medium">30 - 45 min</span>
                </div>
                <div class="flex items-center gap-1.5 bg-rose-950/50 px-3 py-1 rounded-full border border-rose-800/60">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    <span class="text-rose-400 font-bold">&gt; 45 min (Retard)</span>
                </div>
            </div>
        @endif

        <!-- Droite : Son et Horloge KDS haute visibilité -->
        <div class="flex items-center gap-3">
            <button
                type="button"
                id="kds-audio-toggle"
                class="touch-target px-3.5 py-1.5 bg-[#111827] hover:bg-gray-800 border border-gray-700/80 rounded-brand text-xs font-semibold text-gray-200 flex items-center gap-2 transition-all active:scale-95 focus-ring shadow-xs"
                aria-label="Activer ou désactiver les alertes sonores"
            >
                <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                </svg>
                <span id="kds-audio-label">Son activé</span>
            </button>

            <div id="kds-live-clock" class="text-sm font-mono font-bold text-amber-400 bg-[#111827] px-3.5 py-1.5 rounded-brand border border-gray-700/80 shadow-xs tracking-wider">
                {{ now()->format('H:i:s') }}
            </div>
        </div>
    </header>

    <!-- Vue principale KDS (Grille fluide pour colonnes Kanban) -->
    <main class="flex-1 overflow-x-auto overflow-y-hidden p-3.5 sm:p-4 bg-[#080C14]">
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

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#111827">

    <title>{{ $title ?? 'Caisse POS — ' . config('app.name', 'APP RESTAURANT') }}</title>

    @fonts

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-gray-100 dark:bg-[#080C14] text-brand-text dark:text-gray-100 font-sans antialiased flex flex-col overflow-hidden selection:bg-brand-orange selection:text-white">
    <!-- Barre supérieure Caisse POS -->
    <header class="bg-[#0B0F19]/95 backdrop-blur-md border-b border-gray-800/80 text-white px-4 py-2.5 flex items-center justify-between shrink-0 select-none shadow-md z-20">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-brand-lg bg-gradient-to-br from-brand-orange to-brand-orange-dark text-white font-black text-lg shadow-md ring-1 ring-white/10 flex items-center justify-center">
                {{ substr($restaurantName ?? config('app.name', 'P'), 0, 1) }}
            </div>
            <div>
                <h1 class="text-sm font-black tracking-tight leading-none flex items-center gap-2">
                    <span>{{ $restaurantName ?? config('app.name', 'APP RESTAURANT') }}</span>
                    <span class="text-[10px] font-black uppercase px-1.5 py-0.5 rounded bg-brand-orange/20 text-brand-orange border border-brand-orange/40">POS CAISSE</span>
                </h1>
                <p class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-950/60 border border-emerald-700/60 text-[11px] font-semibold text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.8)] animate-pulse"></span>
                        <span>En ligne</span>
                    </span>
                    <span>•</span>
                    <span>Opérateur: <strong class="text-gray-200">{{ $operatorName ?? 'Caisse Principale' }}</strong></span>
                </p>
            </div>
        </div>

        <!-- Centre : Raccourcis et filtres -->
        @if(isset($headerCenter))
            <div class="flex items-center gap-2">
                {{ $headerCenter }}
            </div>
        @endif

        <!-- Droite : Actions rapides & Horloge -->
        <div class="flex items-center gap-3">
            @if(isset($headerActions))
                {{ $headerActions }}
            @endif

            <div id="pos-live-clock" class="text-right text-xs text-amber-400 font-mono font-bold bg-[#111827] px-3 py-1 rounded-brand border border-gray-700/80 shadow-xs hidden md:block">
                {{ now()->format('H:i') }}
            </div>
        </div>
    </header>

    <!-- Zone de travail POS (Plein écran tactile) -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Zone principale (Catalogue / Commandes) -->
        <main class="flex-1 flex flex-col overflow-y-auto p-3 sm:p-4 bg-gray-50/80 dark:bg-[#080C14]">
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
            <aside class="w-96 lg:w-[430px] bg-white dark:bg-[#0B0F19] border-l border-gray-200 dark:border-gray-800/80 flex flex-col shrink-0 shadow-2xl select-none z-10">
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

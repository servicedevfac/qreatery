<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F97316">
    <meta name="color-scheme" content="light dark">
    <meta name="reverb-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
    <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
    <meta name="reverb-scheme" content="{{ config('broadcasting.connections.reverb.options.scheme') }}">

    <title>{{ $title ?? config('app.name', 'APP RESTAURANT') }}</title>
    <meta name="description" content="{{ $description ?? 'Consultez le menu et commandez en toute simplicité.' }}">

    @fonts

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-full bg-brand-light dark:bg-brand-dark text-brand-text dark:text-gray-100 font-sans antialiased flex flex-col selection:bg-brand-orange selection:text-white">
    <!-- Lien d'évitement accessible -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:px-4 focus:py-2 focus:bg-brand-orange focus:text-white focus:rounded-lg focus:shadow-lg focus:outline-none">
        Aller au contenu principal
    </a>

    <!-- En-tête mobile -->
    @if(isset($header))
        {{ $header }}
    @else
        <header class="sticky top-0 z-30 bg-brand-light/90 dark:bg-brand-dark/90 backdrop-blur-md border-b border-brand-orange/15 dark:border-brand-dark-border px-4 py-3 transition-colors">
            <div class="max-w-md mx-auto flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-brand bg-brand-orange text-white flex items-center justify-center font-bold text-lg shadow-sm shrink-0">
                        {{ substr($restaurantName ?? config('app.name', 'R'), 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-sm font-semibold truncate leading-tight">
                            {{ $restaurantName ?? config('app.name', 'APP RESTAURANT') }}
                        </h1>
                        @if(isset($tableLabel))
                            <p class="text-xs text-brand-orange font-medium flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-orange inline-block"></span>
                                Table {{ $tableLabel }}
                            </p>
                        @else
                            <p class="text-xs text-gray-500 dark:text-gray-400">Commande en ligne</p>
                        @endif
                    </div>
                </div>

                @if(isset($headerActions))
                    {{ $headerActions }}
                @endif
            </div>
        </header>
    @endif

    <!-- Conteneur principal Mobile First -->
    <main id="main-content" class="flex-1 w-full max-w-md mx-auto px-4 py-4 pb-28 focus:outline-none" tabindex="-1">
        @if(session('success'))
            <x-toast type="success" :message="session('success')" class="mb-4" />
        @endif

        @if(session('error'))
            <x-toast type="error" :message="session('error')" class="mb-4" />
        @endif

        {{ $slot }}
    </main>

    <!-- Zone flottante persistante (Panier, Appel serveur, etc.) -->
    @if(isset($floating))
        <div class="fixed bottom-0 inset-x-0 z-40 p-4 pointer-events-none">
            <div class="max-w-md mx-auto pointer-events-auto">
                {{ $floating }}
            </div>
        </div>
    @endif

    <!-- Modales & Toasts -->
    @stack('modals')

    @stack('scripts')
</body>
</html>

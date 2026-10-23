<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F97316">
    <meta name="color-scheme" content="light dark">

    <title>{{ $title ?? 'Qreatery — Plateforme SaaS de Gestion des Restaurants et Maquis' }}</title>
    <meta name="description" content="{{ $description ?? 'Solution complète de digitalisation pour restaurants : Menus QR code, commande à table, écran cuisine KDS en temps réel, caisse tactile POS et suivi des ventes.' }}">

    <!-- Open Graph / Meta Réseaux -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Qreatery — Votre restaurant. Toute votre gestion. Un seul espace.">
    <meta property="og:description" content="Des commandes à la caisse, de la cuisine au suivi des ventes, Qreatery vous aide à piloter votre restaurant plus simplement.">
    <meta property="og:site_name" content="Qreatery">

    @fonts

    <!-- Styles & Scripts Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/landing.js'])

    <style>
        /* Gradients et finesses visuelles SaaS */
        .glow-orange {
            box-shadow: 0 0 35px -5px rgba(249, 115, 22, 0.25);
        }
        .glow-card {
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.45);
        }
    </style>
</head>
<body class="min-h-full bg-[#0B0F19] text-gray-100 font-sans antialiased selection:bg-brand-orange selection:text-white overflow-x-hidden">
    <!-- Lien d'évitement accessibilité -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:px-4 focus:py-2.5 focus:bg-brand-orange focus:text-white focus:rounded-brand focus:font-bold focus:shadow-xl focus:outline-none">
        Aller au contenu principal
    </a>

    <!-- Navigation principale -->
    @include('landing.partials.nav')

    <!-- Conteneur principal de la page -->
    <main id="main-content" class="focus:outline-none" tabindex="-1">
        {{ $slot }}
    </main>

    <!-- Pied de page -->
    @include('landing.partials.footer')
</body>
</html>

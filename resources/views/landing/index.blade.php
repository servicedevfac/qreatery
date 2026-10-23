<x-layouts.landing
    title="Qreatery — Solution SaaS Complète pour Restaurants et Maquis"
    description="Digitalisez votre restaurant : menus par QR code, commande en ligne, écran cuisine KDS en temps réel, caisse tactile POS et suivi des ventes en FCFA."
>
    <!-- Section 2 : Hero Principal -->
    @include('landing.partials.hero')

    <!-- Section 3 : Problèmes rencontrés par les restaurateurs & Solutions -->
    @include('landing.partials.problems')

    <!-- Section 4 : Fonctionnalités principales -->
    @include('landing.partials.features')

    <!-- Section 5 : Présentation visuelle interactive du produit (Showcase) -->
    @include('landing.partials.showcase')

    <!-- Section 6 : Comment ça marche (3 étapes simples) -->
    @include('landing.partials.how-it-works')

    <!-- Section 7 : Les avantages clés de Qreatery -->
    @include('landing.partials.benefits')

    <!-- Section 8 : Grille des Tarifs & Abonnements -->
    @include('landing.partials.pricing')

    <!-- Section 9 : Sécurité, Fiabilité & Confiance -->
    @include('landing.partials.security')

    <!-- Section 10 : Foire Aux Questions (FAQ accessible) -->
    @include('landing.partials.faq')

    <!-- Section 11 : Appel à l'action final (CTA) -->
    @include('landing.partials.cta')
</x-layouts.landing>

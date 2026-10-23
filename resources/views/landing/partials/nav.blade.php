<header id="landing-navbar" class="sticky top-0 z-40 w-full bg-[#0B0F19]/90 backdrop-blur-md border-b border-gray-800/80 transition-all duration-300 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <!-- Logo Qreatery -->
            <a href="/" class="flex items-center gap-3 group rounded-brand px-1 py-0.5" aria-label="Qreatery Accueil">
                <span class="w-10 h-10 rounded-brand bg-brand-orange text-white flex items-center justify-center font-black text-xl shadow-md group-hover:scale-105 transition-transform duration-200">
                    Q
                </span>
                <div class="flex flex-col">
                    <span class="font-extrabold text-xl tracking-tight text-white group-hover:text-brand-orange transition-colors">
                        QREATERY
                    </span>
                    <span class="text-[10px] tracking-wider uppercase text-gray-400 font-semibold -mt-1">
                        SaaS Restauration
                    </span>
                </div>
            </a>

            <!-- Liens de navigation Desktop -->
            <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-gray-300" aria-label="Navigation principale">
                <a href="#features" class="hover:text-brand-orange transition-colors rounded px-2 py-1">
                    Fonctionnalités
                </a>
                <a href="#showcase" class="hover:text-brand-orange transition-colors rounded px-2 py-1">
                    En action
                </a>
                <a href="#how-it-works" class="hover:text-brand-orange transition-colors rounded px-2 py-1">
                    Comment ça marche
                </a>
                <a href="#benefits" class="hover:text-brand-orange transition-colors rounded px-2 py-1">
                    Avantages
                </a>
                <a href="#pricing" class="hover:text-brand-orange transition-colors rounded px-2 py-1">
                    Tarifs
                </a>
                <a href="#faq" class="hover:text-brand-orange transition-colors rounded px-2 py-1">
                    FAQ
                </a>
            </nav>

            <!-- Actions Desktop -->
            <div class="hidden sm:flex items-center gap-3.5">
                <a
                    href="/login"
                    class="text-sm font-semibold text-gray-300 hover:text-white px-3.5 py-2 rounded-brand hover:bg-gray-800/60 transition-colors"
                >
                    Se connecter
                </a>
                <a
                    href="#features"
                    class="text-sm font-bold text-white bg-brand-orange hover:bg-brand-orange-dark px-4 py-2.5 rounded-brand transition-all shadow-sm active:scale-95 flex items-center gap-1.5"
                >
                    <span>Découvrir Qreatery</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>

            <!-- Bouton Burger Mobile -->
            <div class="flex items-center sm:hidden">
                <button
                    type="button"
                    id="landing-mobile-menu-btn"
                    class="p-2.5 rounded-brand bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition-colors touch-target flex items-center justify-center"
                    aria-label="Ouvrir le menu de navigation"
                    aria-expanded="false"
                    aria-controls="landing-mobile-menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Tiroir de Navigation Mobile -->
    <div id="landing-mobile-menu" class="fixed inset-0 z-50 hidden sm:hidden" role="dialog" aria-modal="true" aria-label="Menu mobile">
        <!-- Fond sombre avec flou -->
        <div id="landing-mobile-menu-backdrop" class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity"></div>

        <!-- Panneau coulissant -->
        <div class="fixed inset-y-0 right-0 max-w-xs w-full bg-[#111827] border-l border-gray-800 p-6 flex flex-col justify-between shadow-2xl">
            <div class="space-y-6">
                <!-- En-tête panneau mobile -->
                <div class="flex items-center justify-between border-b border-gray-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-brand bg-brand-orange text-white flex items-center justify-center font-black text-sm">
                            Q
                        </span>
                        <span class="font-extrabold text-white text-lg tracking-tight">QREATERY</span>
                    </div>
                    <button
                        type="button"
                        id="landing-mobile-menu-close"
                        class="p-2 rounded-brand text-gray-400 hover:text-white hover:bg-gray-800 touch-target"
                        aria-label="Fermer le menu"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Liens du menu -->
                <nav class="flex flex-col space-y-3" aria-label="Navigation mobile">
                    <a href="#features" class="px-3 py-2 text-base font-medium text-gray-200 hover:text-brand-orange hover:bg-gray-800/50 rounded-brand transition-colors">
                        Fonctionnalités
                    </a>
                    <a href="#showcase" class="px-3 py-2 text-base font-medium text-gray-200 hover:text-brand-orange hover:bg-gray-800/50 rounded-brand transition-colors">
                        En action
                    </a>
                    <a href="#how-it-works" class="px-3 py-2 text-base font-medium text-gray-200 hover:text-brand-orange hover:bg-gray-800/50 rounded-brand transition-colors">
                        Comment ça marche
                    </a>
                    <a href="#benefits" class="px-3 py-2 text-base font-medium text-gray-200 hover:text-brand-orange hover:bg-gray-800/50 rounded-brand transition-colors">
                        Avantages
                    </a>
                    <a href="#pricing" class="px-3 py-2 text-base font-medium text-gray-200 hover:text-brand-orange hover:bg-gray-800/50 rounded-brand transition-colors">
                        Tarifs
                    </a>
                    <a href="#faq" class="px-3 py-2 text-base font-medium text-gray-200 hover:text-brand-orange hover:bg-gray-800/50 rounded-brand transition-colors">
                        FAQ
                    </a>
                </nav>
            </div>

            <!-- Actions au bas du menu mobile -->
            <div class="border-t border-gray-800 pt-5 space-y-3">
                <a
                    href="/login"
                    class="w-full text-center block px-4 py-3 rounded-brand bg-gray-800 hover:bg-gray-700 text-white font-semibold text-sm transition-colors"
                >
                    Se connecter
                </a>
                <a
                    href="#features"
                    class="w-full text-center block px-4 py-3 rounded-brand bg-brand-orange hover:bg-brand-orange-dark text-white font-bold text-sm shadow-md transition-colors"
                >
                    Découvrir Qreatery
                </a>
            </div>
        </div>
    </div>
</header>

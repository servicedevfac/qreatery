<footer class="bg-[#0B0F19] border-t border-gray-800 text-gray-400 py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-gray-800">
            
            <!-- Colonne 1 & 2 : Marque & Présentation -->
            <div class="lg:col-span-2 space-y-4">
                <a href="/" class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-brand bg-brand-orange text-white flex items-center justify-center font-black text-lg shadow-sm">
                        Q
                    </span>
                    <span class="font-black text-xl text-white tracking-tight">QREATERY</span>
                </a>
                <p class="text-xs sm:text-sm text-gray-400 leading-relaxed max-w-sm">
                    La plateforme SaaS tout-en-un pour la restauration moderne. Menus par QR code, commande en ligne, écran cuisine KDS en direct, caisse tactile POS et pilotage des ventes.
                </p>
                <div class="pt-2 text-xs text-gray-400">
                    🌍 Conçu pour les restaurants et maquis en Côte d'Ivoire et en Afrique de l'Ouest.
                </div>
            </div>

            <!-- Colonne 3 : Navigation principale -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Navigation</h3>
                <ul class="space-y-2 text-xs">
                    <li><a href="#features" class="hover:text-brand-orange transition-colors">Fonctionnalités</a></li>
                    <li><a href="#showcase" class="hover:text-brand-orange transition-colors">Démonstration</a></li>
                    <li><a href="#how-it-works" class="hover:text-brand-orange transition-colors">Comment ça marche</a></li>
                    <li><a href="#benefits" class="hover:text-brand-orange transition-colors">Avantages</a></li>
                    <li><a href="#pricing" class="hover:text-brand-orange transition-colors">Tarifs & Abonnements</a></li>
                    <li><a href="#faq" class="hover:text-brand-orange transition-colors">Questions fréquentes</a></li>
                </ul>
            </div>

            <!-- Colonne 4 : Démonstrations en direct -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Accès Démo</h3>
                <ul class="space-y-2 text-xs">
                    <li><a href="/?restaurant=restaurant-demo" class="hover:text-brand-orange transition-colors flex items-center gap-1.5"><span>📱</span> Menu Client Mobile</a></li>
                    <li><a href="/?view=pos" class="hover:text-brand-orange transition-colors flex items-center gap-1.5"><span>💳</span> Caisse Tactile POS</a></li>
                    <li><a href="/?view=kds" class="hover:text-brand-orange transition-colors flex items-center gap-1.5"><span>👨‍🍳</span> Écran Cuisine KDS</a></li>
                    <li><a href="/?view=restaurant" class="hover:text-brand-orange transition-colors flex items-center gap-1.5"><span>📊</span> Gestion Restaurant</a></li>
                </ul>
            </div>

            <!-- Colonne 5 : Connexion & Plateforme -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Espace Pro</h3>
                <ul class="space-y-2 text-xs">
                    <li>
                        <a href="/login" class="text-brand-orange hover:text-brand-orange-dark font-bold transition-colors">
                            Connexion à votre espace →
                        </a>
                    </li>
                    <li class="pt-2 text-[11px] text-gray-400">
                        Accès réservé aux propriétaires, gestionnaires, caissiers et équipes en cuisine.
                    </li>
                </ul>
            </div>

        </div>

        <!-- Bas de page : Copyright -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-400">
            <div>
                © {{ date('Y') }} Qreatery. Tous droits réservés.
            </div>
            <div class="flex items-center gap-4">
                <span>Plateforme SaaS Restauration</span>
                <span>•</span>
                <span>Version 1.0</span>
            </div>
        </div>
    </div>
</footer>

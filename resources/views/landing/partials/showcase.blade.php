<section id="showcase" class="py-16 sm:py-24 bg-[#0B0F19] border-t border-gray-800/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- En-tête de section -->
        <div class="text-center max-w-3xl mx-auto mb-12 space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-orange/10 border border-brand-orange/30 text-brand-orange text-xs font-bold tracking-wide">
                <span>Démonstration du produit</span>
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight">
                Une interface dédiée et pensée pour chaque métier
            </h2>
            <p class="text-gray-400 text-sm sm:text-base leading-relaxed">
                Explorez les écrans concrets utilisés au quotidien par vos clients, vos serveurs, vos cuisiniers et vos caissiers.
            </p>
        </div>

        <!-- Sélecteur d'onglets produit -->
        <div class="flex items-center justify-center mb-10 overflow-x-auto pb-2 scrollbar-none">
            <div class="inline-flex p-1.5 rounded-brand-lg bg-[#111827] border border-gray-800 shadow-inner" role="tablist" aria-label="Aperçus des interfaces de Qreatery">
                
                <button
                    type="button"
                    role="tab"
                    id="tab-client"
                    data-showcase-tab="client"
                    aria-selected="true"
                    aria-controls="panel-client"
                    class="px-4 py-2.5 rounded-brand text-xs sm:text-sm font-bold bg-brand-orange text-white shadow-sm transition-all whitespace-nowrap flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Menu Client Mobile</span>
                </button>

                <button
                    type="button"
                    role="tab"
                    id="tab-kds"
                    data-showcase-tab="kds"
                    aria-selected="false"
                    aria-controls="panel-kds"
                    class="px-4 py-2.5 rounded-brand text-xs sm:text-sm font-medium text-gray-400 hover:text-white transition-all whitespace-nowrap flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/></svg>
                    <span>Écran Cuisine KDS</span>
                </button>

                <button
                    type="button"
                    role="tab"
                    id="tab-pos"
                    data-showcase-tab="pos"
                    aria-selected="false"
                    aria-controls="panel-pos"
                    class="px-4 py-2.5 rounded-brand text-xs sm:text-sm font-medium text-gray-400 hover:text-white transition-all whitespace-nowrap flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span>Caisse Tactile POS</span>
                </button>

                <button
                    type="button"
                    role="tab"
                    id="tab-admin"
                    data-showcase-tab="admin"
                    aria-selected="false"
                    aria-controls="panel-admin"
                    class="px-4 py-2.5 rounded-brand text-xs sm:text-sm font-medium text-gray-400 hover:text-white transition-all whitespace-nowrap flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Gestion Restaurant</span>
                </button>

            </div>
        </div>

        <!-- Panneaux de présentation avec fidélité aux vues de l'application -->
        <div class="bg-[#111827] border border-gray-800 rounded-brand-xl p-4 sm:p-8 shadow-2xl relative">

            <!-- 1. Panneau Client Mobile -->
            <div id="panel-client" data-showcase-panel="client" role="tabpanel" aria-labelledby="tab-client" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <!-- Colonne explicative -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-orange-500/10 text-brand-orange text-xs font-bold">
                            Parcours Client QR
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-white">
                            Consultation du menu et commande instantanée sur smartphone
                        </h3>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            Le convive scanne le QR Code situé sur sa table. Il explore immédiatement vos catégories, personnalise ses sauces ou cuissons, et valide sa commande en toute autonomie.
                        </p>
                        <ul class="space-y-2 text-xs text-gray-400">
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Pas d'application à télécharger ni de formulaire obligatoire</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Panier dynamique avec calcul précis des montants en FCFA</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Suivi en direct du statut de préparation du plat</span>
                            </li>
                        </ul>
                        <div class="pt-2">
                            <a
                                href="/?restaurant=restaurant-demo"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-sm"
                            >
                                <span>Tester le menu client en direct</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Maquette visuelle du smartphone client -->
                    <div class="lg:col-span-7 flex justify-center">
                        <div class="w-full max-w-sm bg-[#0B0F19] border-4 border-gray-700/80 rounded-[2.5rem] p-4 shadow-2xl space-y-4">
                            <!-- Barre d'écouteur smartphone -->
                            <div class="w-20 h-1.5 bg-gray-700 rounded-full mx-auto"></div>

                            <!-- En-tête mobile client -->
                            <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-brand bg-brand-orange text-white flex items-center justify-center font-bold text-sm">Q</div>
                                    <div>
                                        <div class="text-xs font-bold text-white">Qreatery Restaurant</div>
                                        <div class="text-[10px] text-brand-orange font-semibold">Table 03 • Sur place</div>
                                    </div>
                                </div>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800 font-bold">Ouvert</span>
                            </div>

                            <!-- Catégories défilantes -->
                            <div class="flex items-center gap-1.5 overflow-x-auto text-[11px] pb-1 scrollbar-none">
                                <span class="px-2.5 py-1 bg-brand-orange text-white rounded-brand font-bold shrink-0">Tout</span>
                                <span class="px-2.5 py-1 bg-[#111827] text-gray-400 rounded-brand font-medium shrink-0">Grillades</span>
                                <span class="px-2.5 py-1 bg-[#111827] text-gray-400 rounded-brand font-medium shrink-0">Street Food</span>
                                <span class="px-2.5 py-1 bg-[#111827] text-gray-400 rounded-brand font-medium shrink-0">Boissons</span>
                            </div>

                            <!-- Carte produit 1 -->
                            <div class="bg-[#111827] border border-gray-800 rounded-brand p-3 flex gap-3 items-center">
                                <div class="w-16 h-16 rounded-brand bg-brand-orange/20 border border-brand-orange/30 flex items-center justify-center text-brand-orange shrink-0">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-bold text-white truncate">Poulet Braisé & Alloco</div>
                                    <div class="text-[10px] text-gray-400 line-clamp-1">Poulet mariné grillé aux épices locales</div>
                                    <div class="text-xs font-bold text-brand-orange mt-1 font-mono">4 500 FCFA</div>
                                </div>
                                <span class="px-2.5 py-1 rounded-brand bg-brand-orange text-white text-[11px] font-bold shrink-0">+</span>
                            </div>

                            <!-- Carte produit 2 -->
                            <div class="bg-[#111827] border border-gray-800 rounded-brand p-3 flex gap-3 items-center">
                                <div class="w-16 h-16 rounded-brand bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shrink-0">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-bold text-white truncate">Jus de Bissap Maison</div>
                                    <div class="text-[10px] text-gray-400 line-clamp-1">Fleur d'hibiscus fraîche et menthe</div>
                                    <div class="text-xs font-bold text-brand-orange mt-1 font-mono">1 500 FCFA</div>
                                </div>
                                <span class="px-2.5 py-1 rounded-brand bg-brand-orange text-white text-[11px] font-bold shrink-0">+</span>
                            </div>

                            <!-- Barre panier dynamique -->
                            <div class="bg-brand-orange rounded-brand p-3 flex items-center justify-between text-white shadow-lg">
                                <div>
                                    <div class="text-[10px] uppercase font-semibold text-orange-200">2 articles au panier</div>
                                    <div class="text-sm font-bold font-mono">6 000 FCFA</div>
                                </div>
                                <div class="text-xs font-bold bg-white text-brand-orange px-3 py-1.5 rounded-brand flex items-center gap-1">
                                    <span>Commander</span>
                                    <span>→</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 2. Panneau Écran Cuisine KDS -->
            <div id="panel-kds" data-showcase-panel="kds" role="tabpanel" aria-labelledby="tab-kds" class="hidden space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <!-- Explication KDS -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-brand-orange/10 text-brand-orange text-xs font-bold">
                            Poste Cuisine
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-white">
                            Affichage fluide des bons de préparation sans papier
                        </h3>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            Les commandes validées par QR code ou saisies en caisse apparaissent en temps réel sur l'écran cuisine. La brigade classe par ancienneté et signale les plats prêts en une touche.
                        </p>
                        <ul class="space-y-2 text-xs text-gray-400">
                            <li class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                Vert : Moins de 15 min d'attente
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                Orange : 15 à 30 min d'attente
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                                Rouge clignotant : Alerte de retard critique (+45 min)
                            </li>
                        </ul>
                        <div class="pt-2">
                            <a
                                href="/?view=kds"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-sm"
                            >
                                <span>Accéder à l'écran cuisine KDS</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Maquette visuelle de l'écran KDS -->
                    <div class="lg:col-span-7 bg-[#0B0F19] border border-gray-800 rounded-brand-xl p-4 sm:p-5 shadow-inner">
                        <!-- En-tête KDS -->
                        <div class="flex items-center justify-between border-b border-gray-800 pb-3 mb-4">
                            <div class="flex items-center gap-2 text-xs font-bold text-white">
                                <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                                <span>File Cuisine active</span>
                                <span class="px-2 py-0.5 rounded-full bg-brand-orange text-white text-[10px] font-mono">3 commandes</span>
                            </div>
                            <div class="flex items-center gap-2 text-[11px] text-gray-400">
                                <span class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> WebSocket Reverb OK
                                </span>
                            </div>
                        </div>

                        <!-- Colonnes KDS -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Bon 1 -->
                            <div class="bg-[#111827] border-2 border-emerald-500/60 rounded-brand p-3 space-y-2">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="text-white">Table 05</span>
                                    <span class="text-emerald-400 font-mono text-[11px] bg-emerald-950 px-2 py-0.5 rounded flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        06 min
                                    </span>
                                </div>
                                <div class="text-xs space-y-1 text-gray-200">
                                    <p class="font-semibold">• 1x Garba Royal (Thon braisé)</p>
                                    <p class="font-semibold">• 1x Alloco grande portion</p>
                                </div>
                                <button type="button" class="w-full py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-xs font-bold flex items-center justify-center gap-1.5">
                                    <span>Marquer comme prête</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>

                            <!-- Bon 2 -->
                            <div class="bg-[#111827] border-2 border-amber-500/60 rounded-brand p-3 space-y-2">
                                <div class="flex items-center justify-between text-xs font-bold">
                                    <span class="text-white">Table 02</span>
                                    <span class="text-amber-400 font-mono text-[11px] bg-amber-950 px-2 py-0.5 rounded flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        22 min
                                    </span>
                                </div>
                                <div class="text-xs space-y-1 text-gray-200">
                                    <p class="font-semibold">• 2x Capitaine braisé aux aromates</p>
                                    <p class="font-semibold">• 1x Frites d'igname</p>
                                </div>
                                <button type="button" class="w-full text-center py-1.5 bg-brand-orange hover:bg-brand-orange-dark text-white rounded text-xs font-bold">
                                    En cours de cuisson
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 3. Panneau Caisse Tactile POS -->
            <div id="panel-pos" data-showcase-panel="pos" role="tabpanel" aria-labelledby="tab-pos" class="hidden space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <!-- Explication POS -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-brand-orange/10 text-brand-orange text-xs font-bold">
                            Poste Caisse & Encaissements
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-white">
                            Caisse tactile ultra-rapide adaptée aux coups de feu
                        </h3>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            Prise de commande au comptoir, gestion des tables, division de note en quelques secondes et encaissement multi-moyens (Espèces, Wave, Orange Money, MTN, Cartes bancaires).
                        </p>
                        <ul class="space-y-2 text-xs text-gray-400">
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Calcul instantané de la monnaie à rendre pour les espèces</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Division de note par parts égales ou au choix des convives</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Impression thermique directe 80mm</span>
                            </li>
                        </ul>
                        <div class="pt-2">
                            <a
                                href="/?view=pos"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-sm"
                            >
                                <span>Ouvrir la caisse tactile POS</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Maquette visuelle de la caisse POS -->
                    <div class="lg:col-span-7 bg-[#0B0F19] border border-gray-800 rounded-brand-xl p-4 sm:p-5 shadow-inner space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                            <div class="text-xs font-bold text-white flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Vente en cours</span>
                                <span class="px-2 py-0.5 rounded bg-gray-800 text-gray-300 text-[10px]">Table 04</span>
                            </div>
                            <span class="text-xs font-mono font-bold text-brand-orange">Ticket #1042</span>
                        </div>

                        <!-- Ticket et total -->
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="bg-[#111827] rounded-brand p-3 space-y-2 border border-gray-800">
                                <div class="text-gray-400 font-semibold border-b border-gray-800 pb-1">Lignes du ticket</div>
                                <div class="flex justify-between text-gray-200">
                                    <span>1x Poulet braisé</span>
                                    <span class="font-mono">4 500 F</span>
                                </div>
                                <div class="flex justify-between text-gray-200">
                                    <span>2x Alloco mûres</span>
                                    <span class="font-mono">2 000 F</span>
                                </div>
                                <div class="flex justify-between text-gray-200">
                                    <span>2x Jus de Gingembre</span>
                                    <span class="font-mono">2 000 F</span>
                                </div>
                                <div class="pt-2 border-t border-gray-800 flex justify-between font-bold text-white">
                                    <span>Total Net</span>
                                    <span class="text-brand-orange font-mono">8 500 FCFA</span>
                                </div>
                            </div>

                            <!-- Clavier de règlement -->
                            <div class="bg-[#111827] rounded-brand p-3 space-y-2 border border-gray-800">
                                <div class="text-gray-400 font-semibold border-b border-gray-800 pb-1">Moyen de règlement</div>
                                <div class="grid grid-cols-2 gap-1.5 pt-1">
                                    <button type="button" class="p-2 rounded bg-brand-orange text-white text-[11px] font-bold text-center flex items-center justify-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span>Espèces</span>
                                    </button>
                                    <button type="button" class="p-2 rounded bg-gray-800 text-gray-200 hover:text-white text-[11px] font-medium text-center flex items-center justify-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        <span>Mobile</span>
                                    </button>
                                    <button type="button" class="p-2 rounded bg-gray-800 text-gray-200 hover:text-white text-[11px] font-medium text-center flex items-center justify-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        <span>Carte</span>
                                    </button>
                                    <button type="button" class="p-2 rounded bg-gray-800 text-gray-200 hover:text-white text-[11px] font-medium text-center flex items-center justify-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        <span>Diviser</span>
                                    </button>
                                </div>
                                <div class="pt-1 text-[10px] text-gray-400 text-center">
                                    Reçu conforme 80mm prêt à imprimer
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 4. Panneau Espace Gestion Restaurant -->
            <div id="panel-admin" data-showcase-panel="admin" role="tabpanel" aria-labelledby="tab-admin" class="hidden space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <!-- Explication Gestion -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-brand-orange/10 text-brand-orange text-xs font-bold">
                            Pilotage Restaurateur
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-white">
                            Visibilité intégrale sur vos chiffres et opérations
                        </h3>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            Consultez en temps réel vos ventes, modifiez votre carte, attribuez les permissions de vos collaborateurs et exportez vos données financières en toute sérénité.
                        </p>
                        <ul class="space-y-2 text-xs text-gray-400">
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Tableau de bord des ventes quotidiennes et historique complet</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Gestion du plan de table et génération des planches de QR Codes PDF</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Rapports périodiques téléchargeables en PDF et Excel</span>
                            </li>
                        </ul>
                        <div class="pt-2">
                            <a
                                href="/?view=restaurant"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-sm"
                            >
                                <span>Découvrir l'espace de gestion</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Maquette visuelle du tableau de bord gérant -->
                    <div class="lg:col-span-7 bg-[#0B0F19] border border-gray-800 rounded-brand-xl p-4 sm:p-5 shadow-inner space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                            <div class="text-xs font-bold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                <span>Tableau de Bord du Jour</span>
                            </div>
                            <span class="text-[11px] text-gray-400">Qreatery Restaurant</span>
                        </div>

                        <!-- 3 indicateurs clés réels -->
                        <div class="grid grid-cols-3 gap-2 sm:gap-3 text-center">
                            <div class="bg-[#111827] p-3 rounded-brand border border-gray-800">
                                <div class="text-[10px] text-gray-400 font-semibold">Chiffre du jour</div>
                                <div class="text-xs sm:text-sm font-black text-white font-mono mt-1">185 000 F</div>
                                <div class="text-[9px] text-emerald-400 font-bold mt-0.5">Encaissé</div>
                            </div>
                            <div class="bg-[#111827] p-3 rounded-brand border border-gray-800">
                                <div class="text-[10px] text-gray-400 font-semibold">Commandes</div>
                                <div class="text-xs sm:text-sm font-black text-brand-orange font-mono mt-1">28</div>
                                <div class="text-[9px] text-gray-400 mt-0.5">Sur table & emporter</div>
                            </div>
                            <div class="bg-[#111827] p-3 rounded-brand border border-gray-800">
                                <div class="text-[10px] text-gray-400 font-semibold">Panier moyen</div>
                                <div class="text-xs sm:text-sm font-black text-white font-mono mt-1">6 600 F</div>
                                <div class="text-[9px] text-gray-400 mt-0.5">Par table</div>
                            </div>
                        </div>

                        <!-- Tableau synthétique des sections -->
                        <div class="bg-[#111827] rounded-brand p-3 border border-gray-800 space-y-2">
                            <div class="text-[11px] font-bold text-gray-300">Modules opérationnels actifs :</div>
                            <div class="grid grid-cols-2 gap-2 text-[10px] text-gray-300">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>Plan de salle (10 tables configurées)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>Planche QR Codes prête à imprimer</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>Équipe (6 collaborateurs actifs)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>Rapports PDF & Excel disponibles</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>

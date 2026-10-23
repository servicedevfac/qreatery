<section id="faq" class="py-16 sm:py-24 bg-[#0B0F19] border-t border-gray-800/80">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- En-tête de section -->
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-orange/10 border border-brand-orange/30 text-brand-orange text-xs font-bold tracking-wide">
                <span>Foire Aux Questions</span>
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight">
                Questions fréquemment posées
            </h2>
            <p class="text-gray-400 text-sm sm:text-base leading-relaxed">
                Retrouvez toutes les réponses essentielles pour démarrer avec Qreatery dans votre établissement.
            </p>
        </div>

        <!-- Liste des questions sous forme d'accordéon accessible -->
        <div class="space-y-4" role="region" aria-label="Questions fréquentes">

            <!-- Q1 -->
            <div class="bg-[#111827] border border-gray-800 rounded-brand-lg overflow-hidden transition-colors">
                <button
                    type="button"
                    class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 focus:outline-none focus:bg-gray-800"
                    data-faq-trigger
                    aria-expanded="false"
                    aria-controls="faq-answer-1"
                    id="faq-question-1"
                >
                    <span class="text-sm sm:text-base font-bold text-white">
                        À quels types de restaurants Qreatery s'adresse-t-il ?
                    </span>
                    <span data-faq-icon class="w-6 h-6 rounded-full bg-gray-800 text-gray-300 flex items-center justify-center shrink-0 transition-transform duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </button>
                <div id="faq-answer-1" role="region" aria-labelledby="faq-question-1" class="hidden px-6 pb-5 pt-1 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-gray-800/80">
                    Qreatery est conçu pour tous les établissements de restauration : restaurants traditionnels, maquis, bistrots, bars lounge, glaciers et services de restauration rapide (fast-food, à emporter). Que vous disposiez de 5 tables ou d'un grand complexe multi-salles, la plateforme s'adapte à vos besoins.
                </div>
            </div>

            <!-- Q2 -->
            <div class="bg-[#111827] border border-gray-800 rounded-brand-lg overflow-hidden transition-colors">
                <button
                    type="button"
                    class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 focus:outline-none focus:bg-gray-800"
                    data-faq-trigger
                    aria-expanded="false"
                    aria-controls="faq-answer-2"
                    id="faq-question-2"
                >
                    <span class="text-sm sm:text-base font-bold text-white">
                        Les clients doivent-ils installer une application pour voir le menu ?
                    </span>
                    <span data-faq-icon class="w-6 h-6 rounded-full bg-gray-800 text-gray-300 flex items-center justify-center shrink-0 transition-transform duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </button>
                <div id="faq-answer-2" role="region" aria-labelledby="faq-question-2" class="hidden px-6 pb-5 pt-1 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-gray-800/80">
                    Absolument pas. Le client scanne simplement le QR Code situé sur sa table avec l'appareil photo de son smartphone (iOS ou Android). Le menu s'ouvre instantanément dans son navigateur web habituel, sans création de compte obligatoire ni téléchargement préalable.
                </div>
            </div>

            <!-- Q3 -->
            <div class="bg-[#111827] border border-gray-800 rounded-brand-lg overflow-hidden transition-colors">
                <button
                    type="button"
                    class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 focus:outline-none focus:bg-gray-800"
                    data-faq-trigger
                    aria-expanded="false"
                    aria-controls="faq-answer-3"
                    id="faq-question-3"
                >
                    <span class="text-sm sm:text-base font-bold text-white">
                        Comment les commandes sont-elles transmises et suivies en cuisine ?
                    </span>
                    <span data-faq-icon class="w-6 h-6 rounded-full bg-gray-800 text-gray-300 flex items-center justify-center shrink-0 transition-transform duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </button>
                <div id="faq-answer-3" role="region" aria-labelledby="faq-question-3" class="hidden px-6 pb-5 pt-1 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-gray-800/80">
                    Dès qu'une commande est confirmée (par le client à table ou par le serveur/caissier), elle apparaît instantanément sur l'écran cuisine KDS grâce à notre liaison WebSocket temps réel. Chaque bon affiche la table, les plats, les notes spéciales et un chronomètre d'attente avec alertes de couleur (vert, orange et rouge).
                </div>
            </div>

            <!-- Q4 -->
            <div class="bg-[#111827] border border-gray-800 rounded-brand-lg overflow-hidden transition-colors">
                <button
                    type="button"
                    class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 focus:outline-none focus:bg-gray-800"
                    data-faq-trigger
                    aria-expanded="false"
                    aria-controls="faq-answer-4"
                    id="faq-question-4"
                >
                    <span class="text-sm sm:text-base font-bold text-white">
                        Quels moyens de paiement sont gérés sur la caisse POS ?
                    </span>
                    <span data-faq-icon class="w-6 h-6 rounded-full bg-gray-800 text-gray-300 flex items-center justify-center shrink-0 transition-transform duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </button>
                <div id="faq-answer-4" role="region" aria-labelledby="faq-question-4" class="hidden px-6 pb-5 pt-1 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-gray-800/80">
                    La caisse tactile POS gère les paiements en Espèces (avec calcul instantané de la monnaie à rendre), les règlements par Mobile Money (Wave, Orange Money, MTN MoMo) ainsi que les cartes bancaires (Visa, Mastercard). Elle permet également la division de note par table.
                </div>
            </div>

            <!-- Q5 -->
            <div class="bg-[#111827] border border-gray-800 rounded-brand-lg overflow-hidden transition-colors">
                <button
                    type="button"
                    class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 focus:outline-none focus:bg-gray-800"
                    data-faq-trigger
                    aria-expanded="false"
                    aria-controls="faq-answer-5"
                    id="faq-question-5"
                >
                    <span class="text-sm sm:text-base font-bold text-white">
                        Comment découvrir ou tester la plateforme avant de s'abonner ?
                    </span>
                    <span data-faq-icon class="w-6 h-6 rounded-full bg-gray-800 text-gray-300 flex items-center justify-center shrink-0 transition-transform duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </button>
                <div id="faq-answer-5" role="region" aria-labelledby="faq-question-5" class="hidden px-6 pb-5 pt-1 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-gray-800/80">
                    Vous pouvez explorer directement nos espaces de démonstration en direct : le menu client mobile, la caisse tactile POS, l'écran cuisine KDS et l'espace de gestion. Vous pouvez également vous connecter avec nos comptes de démonstration pour tester chaque rôle en situation réelle.
                </div>
            </div>

            <!-- Q6 -->
            <div class="bg-[#111827] border border-gray-800 rounded-brand-lg overflow-hidden transition-colors">
                <button
                    type="button"
                    class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 focus:outline-none focus:bg-gray-800"
                    data-faq-trigger
                    aria-expanded="false"
                    aria-controls="faq-answer-6"
                    id="faq-question-6"
                >
                    <span class="text-sm sm:text-base font-bold text-white">
                        Comment choisir la formule la plus adaptée à mon restaurant ?
                    </span>
                    <span data-faq-icon class="w-6 h-6 rounded-full bg-gray-800 text-gray-300 flex items-center justify-center shrink-0 transition-transform duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </button>
                <div id="faq-answer-6" role="region" aria-labelledby="faq-question-6" class="hidden px-6 pb-5 pt-1 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-gray-800/80">
                    La formule Starter (15 000 FCFA/mois) convient parfaitement aux petits établissements démarrant avec la commande par QR Code. La formule Pro (35 000 FCFA/mois) est recommandée pour les établissements actifs ayant besoin de l'écran cuisine KDS et de la caisse tactile. La formule Entreprise (75 000 FCFA/mois) accompagne les groupes et franchises multi-sites.
                </div>
            </div>

        </div>

    </div>
</section>

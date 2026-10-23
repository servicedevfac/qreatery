@php
    // Récupération des plans dynamiques ou fallback conforme aux seeds officiels du projet
    $displayPlans = (isset($plans) && count($plans) > 0) ? $plans : [
        [
            'name' => 'Formule Starter',
            'slug' => 'starter',
            'description' => 'Idéal pour les petits restaurants et bars démarrant avec les commandes par QR code.',
            'price_formatted' => '15 000 FCFA',
            'period' => 'par mois',
            'is_featured' => false,
            'limits' => 'Jusqu\'à 5 employés • 10 tables',
            'features' => [
                'Prise de commande QR à table',
                'Gestion de plan de salle et tables',
                'Suivi de commande client en direct',
                'Support par email 5j/7',
            ],
        ],
        [
            'name' => 'Formule Pro',
            'slug' => 'pro',
            'description' => 'La solution complète pour restaurants dynamiques : KDS cuisine temps réel et caisse POS.',
            'price_formatted' => '35 000 FCFA',
            'period' => 'par mois',
            'is_featured' => true,
            'limits' => 'Jusqu\'à 20 employés • 40 tables',
            'features' => [
                'Prise de commande QR table & à emporter',
                'Écran cuisine KDS en temps réel (WebSocket)',
                'Caisse tactile POS et division de note',
                'Paiements Espèces, Mobile Money et Cartes',
                'Rapports financiers et exports PDF/XLSX',
                'Support prioritaire 7j/7',
            ],
        ],
        [
            'name' => 'Formule Entreprise',
            'slug' => 'enterprise',
            'description' => 'Accompagnement sur-mesure pour franchises, groupes et établissements à fort volume.',
            'price_formatted' => '75 000 FCFA',
            'period' => 'par mois',
            'is_featured' => false,
            'limits' => 'Jusqu\'à 100 employés • 200 tables',
            'features' => [
                'Toutes les fonctionnalités Pro incluses',
                'Gestion multi-établissements centralisée',
                'Accompagnement et onboarding dédié',
                'Rapports analytiques personnalisés',
                'Assistance dédiée',
            ],
        ],
    ];
@endphp

<section id="pricing" class="py-16 sm:py-24 bg-[#0B0F19] border-t border-gray-800/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- En-tête de section -->
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-orange/10 border border-brand-orange/30 text-brand-orange text-xs font-bold tracking-wide">
                <span>Tarification Claire & Transparente</span>
            </div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight">
                Des formules simples adaptées à votre établissement
            </h2>
            <p class="text-gray-400 text-sm sm:text-base leading-relaxed">
                Facturation mensuelle sans engagement de durée. Choisissez l'offre qui correspond à la taille de votre salle et à vos besoins opérationnels.
            </p>
        </div>

        <!-- Grille des plans tarifaires -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">

            @foreach($displayPlans as $plan)
                @php
                    $isPro = is_array($plan) ? ($plan['slug'] === 'pro' || !empty($plan['is_featured'])) : ($plan->slug === 'pro');
                    $planName = is_array($plan) ? $plan['name'] : $plan->name;
                    $planDesc = is_array($plan) ? $plan['description'] : $plan->description;
                    
                    if (is_array($plan)) {
                        $priceStr = $plan['price_formatted'];
                        $limitsStr = $plan['limits'];
                        $planFeatures = $plan['features'];
                    } else {
                        $priceUnits = number_format($plan->price_minor / 100, 0, ',', ' ');
                        $priceStr = $priceUnits . ' ' . $plan->currency;
                        $limitsStr = 'Jusqu\'à ' . ($plan->limits['employees'] ?? 10) . ' employés • ' . ($plan->limits['tables'] ?? 20) . ' tables';
                        $planFeatures = is_array($plan->features) ? $plan->features : [];
                    }
                @endphp

                <div class="relative bg-[#111827] border {{ $isPro ? 'border-brand-orange shadow-2xl glow-orange' : 'border-gray-800' }} rounded-brand-xl p-6 sm:p-8 flex flex-col justify-between transition-all duration-300 hover:border-brand-orange/70">
                    
                    @if($isPro)
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3.5 py-1 bg-brand-orange text-white text-[11px] font-black uppercase tracking-wider rounded-full shadow-md">
                            Formule Recommandée
                        </div>
                    @endif

                    <!-- Détails du plan -->
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <h3 class="text-xl font-bold text-white">{{ $planName }}</h3>
                            <p class="text-xs text-gray-400 leading-relaxed min-h-[36px]">{{ $planDesc }}</p>
                        </div>

                        <!-- Prix -->
                        <div class="pt-4 border-t border-gray-800/80">
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-3xl sm:text-4xl font-extrabold text-white font-mono tracking-tight">{{ $priceStr }}</span>
                                <span class="text-xs text-gray-400 font-medium">/ mois</span>
                            </div>
                            <div class="text-[11px] text-brand-orange font-semibold mt-1">
                                {{ $limitsStr }}
                            </div>
                        </div>

                        <!-- Liste des fonctionnalités incluses -->
                        <ul class="space-y-3 text-xs text-gray-300 pt-2 border-t border-gray-800/80">
                            @foreach($planFeatures as $feat)
                                <li class="flex items-start gap-2.5">
                                    <svg class="w-4 h-4 text-brand-orange shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{{ $feat }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Bouton CTA du plan -->
                    <div class="pt-8 mt-6 border-t border-gray-800/80">
                        <a
                            href="/login"
                            class="w-full block text-center py-3 px-4 rounded-brand text-xs font-bold transition-all {{ $isPro ? 'bg-brand-orange hover:bg-brand-orange-dark text-white shadow-md' : 'bg-gray-800 hover:bg-gray-700 text-white' }}"
                        >
                            Choisir cette formule
                        </a>
                    </div>

                </div>
            @endforeach

        </div>

        <!-- Note de réassurance sous les tarifs -->
        <div class="mt-12 text-center text-xs text-gray-400 max-w-xl mx-auto space-y-1">
            <p class="flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-brand-orange shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Tous les tarifs sont indiqués en Francs CFA (XOF) hors taxes applicables.</span>
            </p>
            <p>Aucun frais d'installation masqué. Assistance et formation au démarrage incluses.</p>
        </div>

    </div>
</section>

<div id="client-tracking-view" class="hidden space-y-5">
    <!-- Carte d'en-tête de commande -->
    <div class="p-5 rounded-brand-lg bg-white dark:bg-brand-dark-card border border-brand-orange/20 dark:border-brand-dark-border shadow-md">
        <div class="flex items-center justify-between gap-3 mb-3">
            <div>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">
                    Numéro de commande
                </span>
                <h2 id="track-order-number" class="text-xl font-black text-brand-text dark:text-white font-mono">
                    #---
                </h2>
            </div>
            <div id="track-payment-badge-container">
                <!-- Rempli dynamiquement en JS -->
            </div>
        </div>

        <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300 pt-2 border-t border-gray-100 dark:border-brand-dark-border">
            <span id="track-order-mode">🍽️ Sur place</span>
            <span>•</span>
            <span id="track-order-table" class="font-semibold text-brand-orange">Table --</span>
            <span>•</span>
            <span id="track-order-time" class="text-gray-400">12:30</span>
        </div>
    </div>

    <!-- Carte de progression des statuts -->
    <div class="p-5 rounded-brand-lg bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold text-brand-text dark:text-white uppercase tracking-wider">
                État de préparation
            </h3>
            <span id="track-live-polling-dot" class="inline-flex items-center gap-1.5 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>En direct</span>
            </span>
        </div>

        <!-- Stepper visuel interactif -->
        <div id="track-stepper" class="space-y-4">
            <!-- Étape 1 : En attente -->
            <div id="step-pending" class="flex items-start gap-3.5 relative pb-2">
                <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-xs shrink-0 z-10 shadow-sm">
                    1
                </div>
                <div class="min-w-0 pt-0.5">
                    <h4 class="text-xs font-bold text-brand-text dark:text-white">Commande reçue</h4>
                    <p class="text-[11px] text-gray-500">En attente de validation par l'équipe du restaurant.</p>
                </div>
            </div>

            <!-- Étape 2 : Acceptée -->
            <div id="step-accepted" class="flex items-start gap-3.5 relative pb-2 opacity-40">
                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center font-bold text-xs shrink-0 z-10">
                    2
                </div>
                <div class="min-w-0 pt-0.5">
                    <h4 class="text-xs font-bold text-brand-text dark:text-white">Commande acceptée</h4>
                    <p class="text-[11px] text-gray-500">La commande a été transmise en cuisine.</p>
                </div>
            </div>

            <!-- Étape 3 : En préparation -->
            <div id="step-preparing" class="flex items-start gap-3.5 relative pb-2 opacity-40">
                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center font-bold text-xs shrink-0 z-10">
                    3
                </div>
                <div class="min-w-0 pt-0.5">
                    <h4 class="text-xs font-bold text-brand-text dark:text-white">En préparation</h4>
                    <p class="text-[11px] text-gray-500">Le chef prépare soigneusement vos plats.</p>
                </div>
            </div>

            <!-- Étape 4 : Prête -->
            <div id="step-ready" class="flex items-start gap-3.5 relative opacity-40">
                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center font-bold text-xs shrink-0 z-10">
                    4
                </div>
                <div class="min-w-0 pt-0.5">
                    <h4 class="text-xs font-bold text-brand-text dark:text-white">Commande prête !</h4>
                    <p class="text-[11px] text-gray-500">Vos plats sont prêts à être servis à table ou retirés.</p>
                </div>
            </div>
        </div>

        <!-- Alerte commande refusée ou annulée -->
        <div id="track-rejected-box" class="hidden mt-4 p-4 rounded-brand bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200">
            <h4 class="text-xs font-bold uppercase tracking-wider mb-1">Commande non honorée</h4>
            <p id="track-rejected-reason" class="text-xs">
                Le restaurant n'a pas pu valider votre commande.
            </p>
        </div>
    </div>

    <!-- Récapitulatif des articles commandés -->
    <div class="p-5 rounded-brand-lg bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border shadow-sm">
        <h3 class="text-xs font-bold text-brand-text dark:text-white uppercase tracking-wider mb-3">
            Détail de votre commande
        </h3>
        <div id="track-items-list" class="space-y-2.5 text-xs">
            <!-- Injecté en JS -->
        </div>

        <div class="pt-3 mt-3 border-t border-gray-100 dark:border-brand-dark-border flex items-center justify-between text-sm font-black text-brand-text dark:text-white">
            <span>Total</span>
            <span id="track-order-total" class="text-brand-orange text-base">0 FCFA</span>
        </div>
    </div>

    <!-- Actions rapides de suivi : Assistance table & Retour au menu -->
    <div class="space-y-2.5">
        <x-button
            type="button"
            id="btn-track-call-waiter"
            variant="secondary"
            class="w-full text-xs font-semibold"
            data-modal-target="assistance-modal"
        >
            🛎️ Demander l'assistance d'un serveur ou l'addition
        </x-button>

        <x-button
            type="button"
            id="btn-track-back-to-menu"
            variant="ghost"
            class="w-full text-xs font-semibold"
        >
            ← Consulter à nouveau le menu
        </x-button>
    </div>
</div>

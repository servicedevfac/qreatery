<!-- Modale Connexion Propriétaire / Personnel Restaurant -->
<div id="modal-owner-auth" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- En-tête -->
        <div class="p-5 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-brand bg-brand-orange text-white flex items-center justify-center font-black text-sm">
                    Q
                </span>
                <div>
                    <h3 class="text-sm font-bold text-brand-text dark:text-white">Espace Propriétaire &amp; Gestion</h3>
                    <p class="text-[11px] text-gray-500">Authentification sécurisée du restaurant</p>
                </div>
            </div>
            <button type="button" id="btn-close-owner-auth" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg p-1">
                ✕
            </button>
        </div>

        <div class="p-5 space-y-4">
            <!-- Alerte Erreur -->
            <div id="owner-auth-error" class="hidden p-3 rounded-brand bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-medium"></div>

            <!-- Bouton Connexion 1-Clic Démo -->
            <div class="bg-brand-light dark:bg-brand-orange/10 border border-brand-orange/30 p-3.5 rounded-brand space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-brand-orange">⚡ Accès Démo Rapide</span>
                    <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Recommandé</span>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-300">
                    Connectez-vous en 1 clic avec le compte propriétaire préconfiguré pour débloquer toutes les fonctionnalités (Menu, Tables, Commandes, Paramètres).
                </p>
                <button
                    type="button"
                    id="btn-quick-login-owner"
                    class="w-full py-2 px-3 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-sm flex items-center justify-center gap-2"
                >
                    <span>👤</span>
                    <span>Se connecter comme Propriétaire Démo</span>
                </button>
            </div>

            <div class="relative flex py-1 items-center">
                <div class="flex-grow border-t border-gray-200 dark:border-brand-dark-border"></div>
                <span class="flex-shrink mx-3 text-[11px] uppercase font-bold text-gray-400">ou vos identifiants</span>
                <div class="flex-grow border-t border-gray-200 dark:border-brand-dark-border"></div>
            </div>

            <!-- Formulaire Classique -->
            <form id="owner-login-form" class="space-y-3">
                <div>
                    <label for="owner-email" class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">
                        Adresse Email
                    </label>
                    <input
                        type="email"
                        id="owner-email"
                        placeholder="owner@qreatery.test"
                        required
                        class="w-full text-xs p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                    />
                </div>

                <div>
                    <label for="owner-password" class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">
                        Mot de passe
                    </label>
                    <input
                        type="password"
                        id="owner-password"
                        placeholder="••••••••"
                        required
                        class="w-full text-xs p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                    />
                </div>

                <button
                    type="submit"
                    id="btn-owner-login-submit"
                    class="w-full py-2.5 px-3 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 dark:text-brand-dark text-white text-xs font-bold rounded-brand transition-colors shadow-sm"
                >
                    Connexion
                </button>
            </form>
        </div>
    </div>
</div>

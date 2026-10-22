<x-modal id="pos-login-modal" title="Connexion Opérateur Caisse" maxWidth="md">
    <form id="pos-login-form" class="space-y-4">
        <p class="text-xs text-gray-400">
            L'accès à la caisse et à l'encaissement nécessite une session active de personnel de restaurant.
        </p>

        <div id="pos-login-error" class="hidden p-3 rounded bg-rose-950/70 border border-rose-800 text-rose-300 text-xs">
            <!-- Message d'erreur -->
        </div>

        <div class="space-y-1">
            <label for="pos-auth-email" class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider block">
                Adresse email
            </label>
            <input
                type="email"
                id="pos-auth-email"
                required
                autocomplete="email"
                placeholder="caissier@restaurant.com"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-brand-dark border border-gray-300 dark:border-brand-dark-border rounded-brand text-brand-text dark:text-white text-sm focus-ring placeholder-gray-500"
            >
        </div>

        <div class="space-y-1">
            <label for="pos-auth-password" class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider block">
                Mot de passe
            </label>
            <input
                type="password"
                id="pos-auth-password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full px-3 py-2 bg-gray-50 dark:bg-brand-dark border border-gray-300 dark:border-brand-dark-border rounded-brand text-brand-text dark:text-white text-sm focus-ring placeholder-gray-500"
            >
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200 dark:border-brand-dark-border">
            <x-button type="submit" id="btn-pos-login-submit" variant="primary" class="w-full py-2.5">
                Connexion Caisse
            </x-button>
        </div>
    </form>
</x-modal>

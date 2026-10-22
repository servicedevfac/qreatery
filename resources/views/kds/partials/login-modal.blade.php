<x-modal id="kds-login-modal" title="Authentification Cuisine" maxWidth="md">
    <form id="kds-login-form" class="space-y-4">
        <p class="text-xs text-gray-400">
            L'accès à l'écran de cuisine nécessite un compte membre avec les autorisations de gestion des commandes.
        </p>

        <div id="kds-login-error" class="hidden p-3 rounded bg-rose-950/70 border border-rose-800 text-rose-300 text-xs">
            <!-- Message d'erreur -->
        </div>

        <div class="space-y-1">
            <label for="kds-auth-email" class="text-xs font-bold text-gray-300 uppercase tracking-wider block">
                Adresse email
            </label>
            <input
                type="email"
                id="kds-auth-email"
                required
                autocomplete="email"
                placeholder="cuisine@restaurant.com"
                class="w-full px-3 py-2 bg-brand-dark border border-brand-dark-border rounded-brand text-white text-sm focus-ring placeholder-gray-500"
            >
        </div>

        <div class="space-y-1">
            <label for="kds-auth-password" class="text-xs font-bold text-gray-300 uppercase tracking-wider block">
                Mot de passe
            </label>
            <input
                type="password"
                id="kds-auth-password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full px-3 py-2 bg-brand-dark border border-brand-dark-border rounded-brand text-white text-sm focus-ring placeholder-gray-500"
            >
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-brand-dark-border">
            <x-button type="submit" id="btn-kds-login-submit" variant="primary" class="w-full">
                Connexion KDS
            </x-button>
        </div>
    </form>
</x-modal>

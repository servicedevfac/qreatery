<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F97316">

    <title>Connexion — {{ config('app.name', 'Qreatery') }}</title>

    @fonts

    <!-- Styles & Scripts Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-gray-100 font-sans antialiased flex flex-col justify-between selection:bg-brand-orange selection:text-white">
    <!-- Barre supérieure de navigation -->
    <header class="w-full bg-white/80 dark:bg-brand-dark-card/80 backdrop-blur-md border-b border-gray-200 dark:border-brand-dark-border px-4 sm:px-6 py-3.5 sticky top-0 z-20">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5 font-black text-lg tracking-tight text-brand-text dark:text-white group">
                <span class="w-9 h-9 rounded-brand bg-brand-orange text-white flex items-center justify-center text-lg shadow-sm group-hover:scale-105 transition-transform">
                    Q
                </span>
                <span>QREATERY</span>
            </a>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    id="portal-theme-toggle"
                    class="p-2 text-gray-500 hover:text-brand-orange dark:text-gray-400 rounded-brand hover:bg-gray-100 dark:hover:bg-brand-dark transition-colors"
                    title="Basculer le thème clair / sombre"
                    aria-label="Basculer le mode sombre"
                >
                    <span class="dark:hidden text-sm">🌙</span>
                    <span class="hidden dark:inline text-sm">☀️</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Conteneur principal (Centré) -->
    <main class="flex-1 max-w-lg w-full mx-auto px-4 py-8 sm:py-16 flex flex-col justify-center">
        <!-- En-tête titre -->
        <div class="text-center mb-8">
            <div class="w-12 h-12 mx-auto rounded-brand bg-brand-orange text-white flex items-center justify-center text-xl font-black shadow-md mb-4">
                Q
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-text dark:text-white tracking-tight">
                Connexion à votre espace
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                Saisissez vos identifiants pour être automatiquement dirigé vers votre espace de travail selon votre rôle.
            </p>
        </div>

        <!-- Formulaire de connexion -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg p-6 sm:p-8 shadow-sm">
            <!-- Messages Flash & Erreurs -->
            @if(session('success'))
                <div class="mb-4 p-3.5 rounded-brand bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-2">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3.5 rounded-brand bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="portal-ajax-alert" class="hidden mb-4 p-3.5 rounded-brand text-xs flex items-center gap-2"></div>

            <form id="central-login-form" action="/login" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="portal-email" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Adresse Email
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 text-xs">
                            ✉️
                        </span>
                        <input
                            type="email"
                            name="email"
                            id="portal-email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="votre.email@restaurant.com"
                            class="w-full pl-9 pr-3.5 py-2.5 bg-gray-50 dark:bg-brand-dark border border-gray-300 dark:border-brand-dark-border rounded-brand text-brand-text dark:text-white text-sm focus:ring-2 focus:ring-brand-orange focus:border-brand-orange transition-colors"
                        >
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="portal-password" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Mot de passe
                        </label>
                        <button type="button" id="btn-toggle-password" class="text-[11px] text-brand-orange hover:underline">
                            Afficher
                        </button>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 text-xs">
                            🔒
                        </span>
                        <input
                            type="password"
                            name="password"
                            id="portal-password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-9 pr-3.5 py-2.5 bg-gray-50 dark:bg-brand-dark border border-gray-300 dark:border-brand-dark-border rounded-brand text-brand-text dark:text-white text-sm focus:ring-2 focus:ring-brand-orange focus:border-brand-orange transition-colors"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-gray-600 dark:text-gray-400 cursor-pointer">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="rounded border-gray-300 dark:border-brand-dark-border text-brand-orange focus:ring-brand-orange"
                        >
                        <span>Se souvenir de moi</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        id="btn-portal-submit"
                        class="w-full py-3 px-4 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold rounded-brand transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow active:scale-[0.99] focus-ring"
                    >
                        <span id="portal-submit-text">Se connecter &amp; Accéder à mon espace</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Pied de page -->
    <footer class="w-full border-t border-gray-200 dark:border-brand-dark-border py-4 px-4 text-center text-xs text-gray-500 dark:text-gray-400">
        <p>&copy; {{ date('Y') }} Qreatery — Plateforme de commande &amp; gestion de restaurant.</p>
    </footer>

    <!-- Script de gestion interactive -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('central-login-form');
            const emailInput = document.getElementById('portal-email');
            const passwordInput = document.getElementById('portal-password');
            const submitBtn = document.getElementById('btn-portal-submit');
            const submitText = document.getElementById('portal-submit-text');
            const alertBox = document.getElementById('portal-ajax-alert');
            const togglePasswordBtn = document.getElementById('btn-toggle-password');

            // Basculer l'affichage du mot de passe
            if (togglePasswordBtn && passwordInput) {
                togglePasswordBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    togglePasswordBtn.textContent = isPassword ? 'Masquer' : 'Afficher';
                });
            }

            // Gestion du mode sombre
            const themeToggle = document.getElementById('portal-theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function () {
                    document.documentElement.classList.toggle('dark');
                    const isDark = document.documentElement.classList.contains('dark');
                    localStorage.setItem('qreatery_theme', isDark ? 'dark' : 'light');
                });
            }

            // Soumission asynchrone pour feedback et routage ultra-rapide
            if (form) {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const email = emailInput ? emailInput.value.trim() : '';
                    const password = passwordInput ? passwordInput.value : '';
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                    if (!email || !password) return;

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-75');
                    }
                    if (submitText) {
                        submitText.textContent = 'Connexion en cours...';
                    }
                    if (alertBox) alertBox.classList.add('hidden');

                    try {
                        const res = await fetch('/api/v1/auth/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ email, password }),
                        });

                        if (!res.ok) {
                            const errData = await res.json().catch(() => ({}));
                            const msg = errData.message || (errData.errors ? Object.values(errData.errors).flat().join(', ') : 'Identifiants incorrects.');
                            if (alertBox) {
                                alertBox.className = 'mb-4 p-3.5 rounded-brand bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-2';
                                alertBox.innerHTML = `<span>❌</span> <span>${msg}</span>`;
                                alertBox.classList.remove('hidden');
                            }
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('opacity-75');
                            }
                            if (submitText) {
                                submitText.textContent = 'Se connecter & Accéder à mon espace';
                            }
                            return;
                        }

                        const json = await res.json();
                        const user = json.data || {};
                        const redirectUrl = user.redirect_url || '/';

                        if (alertBox) {
                            alertBox.className = 'mb-4 p-3.5 rounded-brand bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-2';
                            alertBox.innerHTML = `<span>✅</span> <span>Connexion réussie ! Redirection en cours...</span>`;
                            alertBox.classList.remove('hidden');
                        }

                        if (submitText) {
                            submitText.textContent = 'Redirection vers votre espace...';
                        }

                        setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 300);

                    } catch (error) {
                        form.submit();
                    }
                });
            }
        });
    </script>
</body>
</html>

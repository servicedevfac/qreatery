@php
    $memberships = $restaurant->memberships()->with('user')->get();
    $openingHours = $restaurant->openingHours()->orderBy('day_of_week')->get();
    $subscription = $restaurant->subscriptions()->latest('id')->with('plan')->first();
    
    $days = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche',
    ];
@endphp

<div class="space-y-6">
    <!-- Barre Supérieure -->
    <div class="pb-4 border-b border-gray-200 dark:border-brand-dark-border">
        <h3 class="text-sm font-bold text-brand-text dark:text-white">Configuration de l'Établissement</h3>
        <p class="text-xs text-gray-500">Paramètres opérationnels, équipe, horaires d'ouverture et statut SaaS</p>
    </div>

    <!-- 1. Équipe & Permissions du Personnel (RBAC) -->
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-brand-text dark:text-white">Équipe du Restaurant</h4>
                <p class="text-xs text-gray-500">Comptes utilisateurs et rôles opérationnels affectés à cet établissement</p>
            </div>
            <button type="button" id="btn-open-create-member" class="px-3 py-1.5 bg-brand-orange hover:bg-brand-orange-dark text-white text-xs font-bold rounded-brand transition-colors shadow-xs flex items-center gap-1.5">
                <span>+</span>
                <span>Ajouter un membre</span>
            </button>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-brand-dark-border">
            @forelse($memberships as $membership)
                <div class="p-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:hover:bg-brand-dark/40 transition-colors" data-member-row="{{ $membership->id }}">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-brand-orange/15 text-brand-orange font-bold text-xs flex items-center justify-center shrink-0">
                            {{ substr($membership->user?->name ?: 'U', 0, 2) }}
                        </div>
                        <div>
                            <p class="font-bold text-sm text-brand-text dark:text-white">{{ $membership->user?->name }}</p>
                            <p class="text-xs text-gray-500">{{ $membership->user?->email }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @php
                            $roleBadges = [
                                'owner' => 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300',
                                'manager' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                                'cashier' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
                                'waiter' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                                'kitchen' => 'bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300',
                            ];
                            $roleLabels = [
                                'owner' => 'Propriétaire',
                                'manager' => 'Directeur / Manager',
                                'cashier' => 'Caissier (POS)',
                                'waiter' => 'Serveur en salle',
                                'kitchen' => 'Chef Cuisine (KDS)',
                            ];
                            $roleVal = $membership->role?->value ?? (string) $membership->role;
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $roleBadges[$roleVal] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $roleLabels[$roleVal] ?? ucfirst($roleVal) }}
                        </span>

                        <span class="text-xs {{ $membership->is_active ? 'text-emerald-600' : 'text-gray-400' }} font-semibold">
                            {{ $membership->is_active ? 'Actif' : 'Suspendu' }}
                        </span>

                        @if($roleVal !== 'owner')
                            <button
                                type="button"
                                class="btn-delete-member p-1 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors"
                                data-id="{{ $membership->id }}"
                                data-name="{{ $membership->user?->name }}"
                                title="Révoquer l'accès"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-400 text-xs">
                    Aucun membre rattaché à cet établissement.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2. Grille Deux Colonnes : Horaires d'Ouverture & Paramètres Établissement -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Horaires d'Ouverture Hebdomadaires -->
        <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg shadow-sm p-5">
            <h4 class="text-sm font-bold text-brand-text dark:text-white pb-3 border-b border-gray-100 dark:border-brand-dark-border">
                Horaires de Service Hebdomadaires
            </h4>

            <div class="divide-y divide-gray-100 dark:divide-brand-dark-border mt-3 text-xs">
                @for($d = 1; $d <= 7; $d++)
                    @php
                        $dayHour = $openingHours->firstWhere('day_of_week', $d);
                    @endphp
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="font-bold text-brand-text dark:text-white w-28">{{ $days[$d] }}</span>
                        @if($dayHour && !$dayHour->is_closed)
                            <span class="font-mono text-gray-600 dark:text-gray-300">
                                {{ substr($dayHour->opens_at, 0, 5) }} — {{ substr($dayHour->closes_at, 0, 5) }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400">
                                Ouvert
                            </span>
                        @else
                            <span class="font-mono text-gray-600 dark:text-gray-300">
                                11:30 — 23:00
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400">
                                Ouvert
                            </span>
                        @endif
                    </div>
                @endfor
            </div>
        </div>

        <!-- Informations & Paramètres Opérationnels -->
        <div class="space-y-6">
            <!-- Formulaire de Configuration Opérationnelle -->
            <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-brand-dark-border">
                    <h4 class="text-sm font-bold text-brand-text dark:text-white">
                        Paramètres de Facturation &amp; Service
                    </h4>
                    <span class="text-[10px] font-bold text-brand-orange uppercase">Temps Réel</span>
                </div>

                <div id="settings-save-alert" class="hidden p-2.5 rounded text-xs"></div>

                <form id="form-restaurant-settings" class="space-y-3 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="setting-tax-rate" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                                Taux de TVA (%)
                            </label>
                            <input
                                type="number"
                                id="setting-tax-rate"
                                min="0"
                                max="100"
                                step="0.5"
                                value="{{ (float)($restaurant->settings['tax_rate'] ?? 0) }}"
                                class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white focus:outline-none focus:border-brand-orange"
                            />
                        </div>

                        <div>
                            <label for="setting-service-fee" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                                Frais de service fixes ({{ $restaurant->currency }})
                            </label>
                            <input
                                type="number"
                                id="setting-service-fee"
                                min="0"
                                step="50"
                                value="{{ (int)(($restaurant->settings['service_fee_minor'] ?? 0) / 100) }}"
                                class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white focus:outline-none focus:border-brand-orange"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="setting-prep-time" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                            Temps de préparation cuisine par défaut (minutes)
                        </label>
                        <input
                            type="number"
                            id="setting-prep-time"
                            min="1"
                            max="180"
                            value="{{ (int)($restaurant->settings['estimated_preparation_minutes'] ?? 20) }}"
                            class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white focus:outline-none focus:border-brand-orange"
                        />
                    </div>

                    <button
                        type="submit"
                        id="btn-save-settings"
                        class="w-full py-2.5 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold rounded-brand transition-colors shadow-xs"
                    >
                        Enregistrer la configuration
                    </button>
                </form>
            </div>

                <div class="mt-3 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Nom commercial :</span>
                        <span class="font-bold text-brand-text dark:text-white">{{ $restaurant->name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Slug URL :</span>
                        <span class="font-mono text-brand-orange font-semibold">{{ $restaurant->slug }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Devise de facturation :</span>
                        <span class="font-bold text-brand-text dark:text-white">{{ $restaurant->currency }} (Franc CFA)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Fuseau horaire :</span>
                        <span class="font-mono text-gray-700 dark:text-gray-300">{{ $restaurant->timezone }}</span>
                    </div>
                </div>
            </div>

            <!-- Abonnement SaaS -->
            <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg shadow-sm p-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-brand-dark-border">
                    <h4 class="text-sm font-bold text-brand-text dark:text-white">Abonnement SaaS</h4>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                        {{ strtoupper($subscription?->status->value ?? 'ACTIF') }}
                    </span>
                </div>

                <div class="mt-3 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Formule active :</span>
                        <span class="font-bold text-brand-orange text-sm">{{ $subscription?->plan?->name ?? 'Plan Pro Qreatery' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Tarif récurrent :</span>
                        <span class="font-bold text-brand-text dark:text-white">
                            {{ number_format(($subscription?->plan?->price_minor ?? 3500000) / 100, 0, ',', ' ') }} {{ $restaurant->currency }} / mois
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Échéance de renouvellement :</span>
                        <span class="font-semibold text-gray-700 dark:text-gray-300">
                            {{ $subscription?->ends_at ? $subscription->ends_at->format('d/m/Y') : now()->addMonths(1)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

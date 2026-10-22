@props([
    'active' => request('section', 'dashboard'),
    'adminUser' => auth()->user(),
])

@php
    $navItems = [
        [
            'id' => 'dashboard',
            'label' => 'Vue globale SaaS',
            'url' => Route::has('admin.dashboard') ? route('admin.dashboard') : (Route::has('admin.index') ? route('admin.index') : (request()->has('view') ? url('/?view=admin&section=dashboard') : url('/admin?section=dashboard'))),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        ],
        [
            'id' => 'restaurants',
            'label' => 'Restaurants',
            'url' => Route::has('admin.restaurants') ? route('admin.restaurants') : (request()->has('view') ? url('/?view=admin&section=restaurants') : url('/admin?section=restaurants')),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        ],
        [
            'id' => 'plans',
            'label' => 'Plans SaaS',
            'url' => Route::has('admin.plans') ? route('admin.plans') : (request()->has('view') ? url('/?view=admin&section=plans') : url('/admin?section=plans')),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>',
        ],
        [
            'id' => 'subscriptions',
            'label' => 'Abonnements et règlements',
            'url' => Route::has('admin.subscriptions') ? route('admin.subscriptions') : (request()->has('view') ? url('/?view=admin&section=subscriptions') : url('/admin?section=subscriptions')),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
        ],
        [
            'id' => 'audit-logs',
            'label' => "Journal d'audit",
            'url' => Route::has('admin.audit-logs') ? route('admin.audit-logs') : (request()->has('view') ? url('/?view=admin&section=audit-logs') : url('/admin?section=audit-logs')),
            'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
        ],
    ];
@endphp

<!-- Barre latérale / Sidebar Super-Administration SaaS -->
<aside
    id="super-admin-sidebar"
    class="w-64 bg-white dark:bg-brand-dark-card border-r border-gray-200 dark:border-brand-dark-border flex flex-col shrink-0 transition-transform duration-200 z-30 fixed inset-y-0 left-0 -translate-x-full md:relative md:translate-x-0"
    aria-label="Navigation Super-Administration SaaS"
>
    <!-- Logo & Marque Plateforme SaaS -->
    <div class="h-16 px-5 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
        <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : (Route::has('admin.index') ? route('admin.index') : url('/admin')) }}" class="flex items-center gap-2.5 font-bold text-lg text-brand-text dark:text-white group">
            <span class="w-8 h-8 rounded-brand bg-brand-orange text-white flex items-center justify-center font-black text-base shadow-sm group-hover:bg-brand-orange-dark transition-colors">
                Q
            </span>
            <div class="flex flex-col leading-tight">
                <span class="tracking-tight text-sm font-bold">QREATE<span class="text-brand-orange">RY</span></span>
                <span class="text-[10px] font-semibold uppercase tracking-wider text-brand-orange">Super-Admin SaaS</span>
            </div>
        </a>

        <!-- Bouton fermer la sidebar (mobile uniquement) -->
        <button
            type="button"
            id="super-admin-close-sidebar"
            class="md:hidden touch-target p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-brand focus-ring"
            aria-label="Fermer le menu de navigation"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- Badge visuel d'espace Super-Admin distinctif -->
    <div class="px-4 py-2.5 bg-brand-light dark:bg-brand-orange/5 border-b border-orange-100 dark:border-brand-orange/10 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-brand-orange animate-pulse" aria-hidden="true"></span>
        <span class="text-xs font-semibold text-brand-orange">Console Plateforme SaaS</span>
    </div>

    <!-- Navigation principale Super-Admin -->
    <nav class="flex-1 p-3 space-y-1.5 overflow-y-auto" aria-label="Menu principal Super-Admin">
        @foreach($navItems as $item)
            @php
                $isActive = ($active === $item['id']) || (request('section') === $item['id']);
            @endphp
            <a
                href="{{ $item['url'] }}"
                data-nav-id="{{ $item['id'] }}"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-brand text-sm font-medium transition-all duration-150 focus-ring touch-target {{ $isActive ? 'text-white bg-brand-orange font-semibold shadow-sm shadow-brand-orange/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-brand-dark/80 hover:text-brand-text dark:hover:text-white' }}"
                @if($isActive) aria-current="page" @endif
            >
                <span class="{{ $isActive ? 'text-white' : 'text-gray-400 group-hover:text-brand-orange' }}">
                    {!! $item['icon'] !!}
                </span>
                <span class="truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <!-- Pied de barre latérale : Profil Super-Administrateur -->
    <div class="p-3 border-t border-gray-200 dark:border-brand-dark-border bg-gray-50/50 dark:bg-brand-dark/30">
        <div class="px-3 py-2 flex items-center justify-between gap-3">
            <div class="min-w-0 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-brand-orange/10 text-brand-orange flex items-center justify-center font-bold text-xs shrink-0 border border-brand-orange/20">
                    {{ strtoupper(substr($adminUser?->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-brand-text dark:text-white truncate">
                        {{ $adminUser?->name ?? 'Super-Administrateur' }}
                    </p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                        {{ $adminUser?->email ?? 'admin@qreatery.com' }}
                    </p>
                </div>
            </div>

            <!-- Déconnexion / Sortie -->
            @if(Route::has('admin.logout'))
                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="p-1.5 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-brand-sm transition-colors focus-ring"
                        title="Se déconnecter de la plateforme"
                        aria-label="Se déconnecter"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            @endif
        </div>
    </div>
</aside>

@props([
    'status' => null,
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
])

@php
    $statusMap = [
        // Statuts de préparation (Annexe A.1)
        'pending' => ['label' => 'En attente', 'style' => 'bg-amber-100 text-amber-900 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800', 'dotColor' => 'bg-amber-500'],
        'accepted' => ['label' => 'Acceptée', 'style' => 'bg-blue-100 text-blue-900 border-blue-300 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800', 'dotColor' => 'bg-blue-500'],
        'preparing' => ['label' => 'En préparation', 'style' => 'bg-brand-orange/15 text-brand-orange-dark border-brand-orange/40 dark:bg-brand-orange/20 dark:text-brand-orange dark:border-brand-orange/50', 'dotColor' => 'bg-brand-orange'],
        'ready' => ['label' => 'Prête', 'style' => 'bg-emerald-100 text-emerald-900 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800', 'dotColor' => 'bg-emerald-500'],
        'served' => ['label' => 'Servie', 'style' => 'bg-purple-100 text-purple-900 border-purple-300 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800', 'dotColor' => 'bg-purple-500'],
        'completed' => ['label' => 'Terminée', 'style' => 'bg-gray-100 text-gray-800 border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700', 'dotColor' => 'bg-gray-400'],
        'rejected' => ['label' => 'Refusée', 'style' => 'bg-rose-100 text-rose-900 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800', 'dotColor' => 'bg-rose-500'],
        'cancelled' => ['label' => 'Annulée', 'style' => 'bg-rose-100 text-rose-900 border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800', 'dotColor' => 'bg-rose-500'],

        // Statuts de paiement (Annexe A.2)
        'unpaid' => ['label' => 'Impayé', 'style' => 'bg-rose-50 text-rose-700 border-rose-300 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800', 'dotColor' => 'bg-rose-500'],
        'partially_paid' => ['label' => 'Paiement partiel', 'style' => 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800', 'dotColor' => 'bg-amber-500'],
        'paid' => ['label' => 'Payé', 'style' => 'bg-emerald-50 text-emerald-800 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800', 'dotColor' => 'bg-emerald-500'],
        'refunded' => ['label' => 'Remboursé', 'style' => 'bg-gray-100 text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700', 'dotColor' => 'bg-gray-400'],
        'failed' => ['label' => 'Échec paiement', 'style' => 'bg-red-100 text-red-800 border-red-300 dark:bg-red-950/60 dark:text-red-300 dark:border-red-800', 'dotColor' => 'bg-red-500'],
    ];

    $variantMap = [
        'default' => 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-brand-dark-card dark:text-gray-300 dark:border-brand-dark-border',
        'primary' => 'bg-brand-light text-brand-orange-dark border-brand-orange/30 dark:bg-brand-orange/15 dark:text-brand-orange dark:border-brand-orange/40',
        'success' => 'bg-emerald-100 text-emerald-900 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
        'warning' => 'bg-amber-100 text-amber-900 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800',
        'danger' => 'bg-rose-100 text-rose-900 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800',
        'info' => 'bg-sky-100 text-sky-900 border-sky-200 dark:bg-sky-950/50 dark:text-sky-300 dark:border-sky-800',
    ];

    $resolvedStyle = $status && isset($statusMap[$status])
        ? $statusMap[$status]['style']
        : ($variantMap[$variant] ?? $variantMap['default']);

    $resolvedDot = $status && isset($statusMap[$status])
        ? $statusMap[$status]['dotColor']
        : 'bg-current';

    $sizes = [
        'sm' => 'px-2 py-0.5 text-[11px] gap-1',
        'md' => 'px-2.5 py-1 text-xs gap-1.5',
        'lg' => 'px-3 py-1.5 text-sm gap-2',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-semibold rounded-full border transition-colors select-none {$resolvedStyle} {$sizeClass}"]) }}>
    @if($dot || $status)
        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $resolvedDot }}" aria-hidden="true"></span>
    @endif
    <span>
        {{ $slot->isNotEmpty() ? $slot : ($status && isset($statusMap[$status]) ? $statusMap[$status]['label'] : '') }}
    </span>
</span>

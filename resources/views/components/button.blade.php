@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'as' => 'button',
    'href' => null,
    'disabled' => false,
    'loading' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-150 select-none focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none';

    $variants = [
        'primary' => 'bg-brand-orange hover:bg-brand-orange-dark text-white shadow-sm focus:ring-brand-orange active:bg-brand-orange-dark',
        'secondary' => 'bg-white dark:bg-brand-dark-card border border-gray-300 dark:border-brand-dark-border hover:bg-gray-50 dark:hover:bg-brand-dark text-brand-text dark:text-gray-100 shadow-2xs focus:ring-brand-orange',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm focus:ring-rose-500 active:bg-rose-800',
        'dark' => 'bg-brand-dark-card hover:bg-black text-white border border-brand-dark-border shadow-sm focus:ring-gray-700',
        'ghost' => 'bg-transparent hover:bg-brand-orange/10 text-brand-orange dark:text-brand-orange focus:ring-brand-orange',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs rounded-brand-sm gap-1.5',
        'md' => 'px-4 py-2.5 text-sm rounded-brand gap-2 touch-target', // Cible tactile >= 44x44px
        'lg' => 'px-6 py-3.5 text-base font-semibold rounded-brand gap-2.5 min-h-[48px]',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($as === 'a' || $href)
    <a
        href="{{ $disabled ? '#' : $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled) aria-disabled="true" tabindex="-1" @endif
    >
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled || $loading) disabled @endif
        @if($loading) aria-busy="true" @endif
    >
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif

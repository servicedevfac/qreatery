@props([
    'type' => 'success',
    'title' => null,
    'message' => null,
    'dismissible' => true,
    'timeout' => 5000,
    'id' => 'toast-' . uniqid(),
])

@php
    $types = [
        'success' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-950/70 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200',
            'icon' => '<svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        'error' => [
            'bg' => 'bg-rose-50 dark:bg-rose-950/70 border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-200',
            'icon' => '<svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 dark:bg-amber-950/70 border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200',
            'icon' => '<svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
        ],
        'info' => [
            'bg' => 'bg-sky-50 dark:bg-sky-950/70 border-sky-300 dark:border-sky-800 text-sky-900 dark:text-sky-200',
            'icon' => '<svg class="w-5 h-5 text-sky-600 dark:text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ],
    ];

    $config = $types[$type] ?? $types['success'];
@endphp

<div
    id="{{ $id }}"
    role="alert"
    aria-live="polite"
    {{ $attributes->merge(['class' => "p-4 rounded-brand border shadow-md flex items-start gap-3 transition-all duration-300 {$config['bg']}"]) }}
    data-toast
    @if($timeout > 0) data-toast-timeout="{{ $timeout }}" @endif
>
    {!! $config['icon'] !!}

    <div class="flex-1 min-w-0 pt-0.5">
        @if($title)
            <h4 class="text-xs font-bold uppercase tracking-wider mb-0.5">{{ $title }}</h4>
        @endif
        <p class="text-sm font-medium leading-relaxed">
            {{ $message ?? $slot }}
        </p>
    </div>

    @if($dismissible)
        <button
            type="button"
            class="touch-target p-1 -mr-1 -mt-1 text-current opacity-60 hover:opacity-100 rounded-brand focus-ring"
            aria-label="Fermer la notification"
            onclick="document.getElementById('{{ $id }}')?.remove()"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>

@if($timeout > 0)
    @once
        @push('scripts')
        <script>
            document.querySelectorAll('[data-toast][data-toast-timeout]').forEach(function(el) {
                var timeout = parseInt(el.getAttribute('data-toast-timeout'), 10);
                if (timeout > 0) {
                    setTimeout(function() {
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(-8px)';
                        setTimeout(function() { el.remove(); }, 300);
                    }, timeout);
                }
            });
        </script>
        @endpush
    @endonce
@endif

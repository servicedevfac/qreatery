@props([
    'id',
    'title' => null,
    'maxWidth' => 'md',
    'show' => false,
])

@php
    $maxWidths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        'full' => 'max-w-full m-4',
    ];

    $maxWidthClass = $maxWidths[$maxWidth] ?? $maxWidths['md'];
@endphp

<div
    id="{{ $id }}"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center transition-all duration-200 {{ $show ? '' : 'hidden' }}"
    data-modal
>
    <!-- Arrière-plan semi-transparent -->
    <div
        class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"
        data-modal-backdrop="{{ $id }}"
        aria-hidden="true"
    ></div>

    <!-- Conteneur modal -->
    <div class="relative w-full {{ $maxWidthClass }} bg-white dark:bg-brand-dark-card rounded-brand-lg shadow-2xl border border-gray-200 dark:border-brand-dark-border overflow-hidden z-10 transition-transform transform scale-100">
        <!-- En-tête -->
        @if($title || isset($header))
            <div class="px-5 py-4 border-b border-gray-200 dark:border-brand-dark-border flex items-center justify-between">
                @if(isset($header))
                    {{ $header }}
                @else
                    <h3 id="{{ $id }}-title" class="text-base font-bold text-brand-text dark:text-white truncate">
                        {{ $title }}
                    </h3>
                @endif
                <button
                    type="button"
                    class="touch-target p-1.5 -mr-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-brand focus-ring"
                    data-modal-close="{{ $id }}"
                    aria-label="Fermer la boîte de dialogue"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <!-- Corps du modal -->
        <div class="p-5 overflow-y-auto max-h-[75vh]">
            {{ $slot }}
        </div>

        <!-- Pied de page optionnel -->
        @if(isset($footer))
            <div class="px-5 py-3.5 bg-gray-50 dark:bg-brand-dark/50 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end gap-2.5">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>

@once
    @push('scripts')
    <script>
        // Gestionnaire accessible natif pour tous les modaux Blade
        window.openModal = function(id) {
            var modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            var firstInput = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstInput) firstInput.focus();
        };

        window.closeModal = function(id) {
            var modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('hidden');
            if (!document.querySelector('[data-modal]:not(.hidden)')) {
                document.body.classList.remove('overflow-hidden');
            }
        };

        document.addEventListener('click', function(e) {
            var targetClose = e.target.closest('[data-modal-close]');
            if (targetClose) {
                var modalId = targetClose.getAttribute('data-modal-close');
                window.closeModal(modalId);
                return;
            }

            var targetOpen = e.target.closest('[data-modal-target]');
            if (targetOpen) {
                var openId = targetOpen.getAttribute('data-modal-target');
                window.openModal(openId);
                return;
            }

            var backdrop = e.target.closest('[data-modal-backdrop]');
            if (backdrop) {
                var backdropId = backdrop.getAttribute('data-modal-backdrop');
                window.closeModal(backdropId);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                var openModals = document.querySelectorAll('[data-modal]:not(.hidden)');
                openModals.forEach(function(m) {
                    window.closeModal(m.id);
                });
            }
        });
    </script>
    @endpush
@endonce

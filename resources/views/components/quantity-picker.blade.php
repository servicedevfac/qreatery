@props([
    'name' => 'quantity',
    'value' => 1,
    'min' => 1,
    'max' => 99,
    'id' => 'qty-' . uniqid(),
    'disabled' => false,
])

<div
    {{ $attributes->merge(['class' => 'inline-flex items-center rounded-brand border border-gray-300 dark:border-brand-dark-border bg-white dark:bg-brand-dark-card shadow-2xs select-none']) }}
    data-quantity-picker
>
    <!-- Bouton décrémenter -->
    <button
        type="button"
        class="touch-target w-11 h-11 flex items-center justify-center text-brand-text dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-brand-dark rounded-l-brand focus-ring disabled:opacity-30 disabled:pointer-events-none transition-colors"
        data-action="decrement"
        aria-label="Diminuer la quantité"
        @if($disabled || $value <= $min) disabled @endif
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
        </svg>
    </button>

    <!-- Champ numérique accessible -->
    <input
        type="text"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $value }}"
        min="{{ $min }}"
        max="{{ $max }}"
        inputmode="numeric"
        pattern="[0-9]*"
        role="spinbutton"
        aria-valuenow="{{ $value }}"
        aria-valuemin="{{ $min }}"
        aria-valuemax="{{ $max }}"
        class="w-12 h-11 text-center font-bold text-base text-brand-text dark:text-white bg-transparent border-none focus:outline-none focus:ring-0 p-0"
        @if($disabled) disabled @endif
        readonly
    />

    <!-- Bouton incrémenter -->
    <button
        type="button"
        class="touch-target w-11 h-11 flex items-center justify-center text-brand-text dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-brand-dark rounded-r-brand focus-ring disabled:opacity-30 disabled:pointer-events-none transition-colors"
        data-action="increment"
        aria-label="Augmenter la quantité"
        @if($disabled || $value >= $max) disabled @endif
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
    </button>
</div>

@once
    @push('scripts')
    <script>
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-quantity-picker] button[data-action]');
            if (!btn || btn.disabled) return;

            var container = btn.closest('[data-quantity-picker]');
            var input = container.querySelector('input');
            if (!input) return;

            var currentVal = parseInt(input.value, 10) || 1;
            var minVal = parseInt(input.getAttribute('min'), 10) || 1;
            var maxVal = parseInt(input.getAttribute('max'), 10) || 99;
            var action = btn.getAttribute('data-action');

            if (action === 'increment' && currentVal < maxVal) {
                currentVal++;
            } else if (action === 'decrement' && currentVal > minVal) {
                currentVal--;
            }

            input.value = currentVal;
            input.setAttribute('aria-valuenow', currentVal);

            var decBtn = container.querySelector('[data-action="decrement"]');
            var incBtn = container.querySelector('[data-action="increment"]');

            if (decBtn) decBtn.disabled = (currentVal <= minVal);
            if (incBtn) incBtn.disabled = (currentVal >= maxVal);

            // Déclencher un événement change natif
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    </script>
    @endpush
@endonce

/**
 * APP RESTAURANT — Frontend Core (Vanilla JavaScript)
 * Standard Blade + Vanilla JS Architecture
 */

// Récupération sécurisée du jeton CSRF
window.csrfToken = function() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
};

// Fonction utilitaire pour requêtes Fetch authentifiées avec CSRF
window.fetchWithCsrf = function(url, options) {
    options = options || {};
    options.headers = options.headers || {};
    options.headers['X-CSRF-TOKEN'] = window.csrfToken();
    options.headers['Accept'] = 'application/json';
    options.headers['X-Requested-With'] = 'XMLHttpRequest';
    if (!(options.body instanceof FormData) && typeof options.body === 'object') {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(options.body);
    }
    return fetch(url, options);
};

// Déclenchement programmatique d'un toast accessible
window.showToast = function(message, type, timeout) {
    type = type || 'success';
    timeout = timeout !== undefined ? timeout : 4000;

    var container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 right-4 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none px-4';
        document.body.appendChild(container);
    }

    var typeStyles = {
        success: 'bg-emerald-50 dark:bg-emerald-950/80 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200',
        error: 'bg-rose-50 dark:bg-rose-950/80 border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-200',
        warning: 'bg-amber-50 dark:bg-amber-950/80 border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200',
        info: 'bg-sky-50 dark:bg-sky-950/80 border-sky-300 dark:border-sky-800 text-sky-900 dark:text-sky-200'
    };

    var toast = document.createElement('div');
    toast.role = 'alert';
    toast.className = 'pointer-events-auto p-4 rounded-brand border shadow-lg flex items-start justify-between gap-3 transition-all duration-300 transform translate-y-0 ' + (typeStyles[type] || typeStyles.success);
    toast.innerHTML = '<p class="text-sm font-medium">' + message + '</p>' +
        '<button type="button" class="touch-target p-1 -mr-1 -mt-1 opacity-60 hover:opacity-100" aria-label="Fermer">' +
        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
        '</button>';

    var closeBtn = toast.querySelector('button');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            toast.remove();
        });
    }

    container.appendChild(toast);

    if (timeout > 0) {
        setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(function() { toast.remove(); }, 300);
        }, timeout);
    }
};

// Gestionnaire global accessible pour tous les modaux de l'application
window.openModal = window.openModal || function(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    var firstInput = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (firstInput) firstInput.focus();
};

window.closeModal = window.closeModal || function(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('hidden');
    if (!document.querySelector('[data-modal]:not(.hidden), [role="dialog"]:not(.hidden)')) {
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
        var openModals = document.querySelectorAll('[data-modal]:not(.hidden), [role="dialog"]:not(.hidden)');
        openModals.forEach(function(m) {
            window.closeModal(m.id);
        });
    }
});


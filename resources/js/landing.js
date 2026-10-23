/**
 * QREATEY — Landing Page Interactive Scripts
 * Pure Native JavaScript (No external framework dependencies)
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    initTheme();
    initNavbarScroll();
    initMobileMenu();
    initShowcaseTabs();
    initFaqAccordion();
    initSmoothAnchors();
});

/**
 * 1. Gestion du thème Sombre / Clair
 */
function initTheme() {
    var toggleBtn = document.getElementById('landing-theme-toggle');
    var mobileToggleBtn = document.getElementById('landing-mobile-theme-toggle');

    function applyTheme(isDark) {
        if (isDark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    }

    function toggleTheme() {
        var isCurrentDark = document.documentElement.classList.contains('dark');
        applyTheme(!isCurrentDark);
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleTheme);
    }
    if (mobileToggleBtn) {
        mobileToggleBtn.addEventListener('click', toggleTheme);
    }
}

/**
 * 2. Réduction / Style compact de la barre de navigation au défilement
 */
function initNavbarScroll() {
    var navbar = document.getElementById('landing-navbar');
    if (!navbar) return;

    var handleScroll = function () {
        if (window.scrollY > 20) {
            navbar.classList.add('shadow-md', 'bg-white/95', 'dark:bg-brand-dark/95', 'py-3');
            navbar.classList.remove('py-4');
        } else {
            navbar.classList.remove('shadow-md', 'py-3');
            navbar.classList.add('py-4');
        }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
}

/**
 * 3. Menu mobile accessible avec gestion clavier et focus
 */
function initMobileMenu() {
    var menuBtn = document.getElementById('landing-mobile-menu-btn');
    var menuContainer = document.getElementById('landing-mobile-menu');
    var backdrop = document.getElementById('landing-mobile-menu-backdrop');
    var closeBtn = document.getElementById('landing-mobile-menu-close');

    if (!menuBtn || !menuContainer) return;

    function openMenu() {
        menuContainer.classList.remove('hidden');
        menuBtn.setAttribute('aria-expanded', 'true');
        document.body.classList.add('overflow-hidden');

        // Focus sur le bouton de fermeture ou premier lien
        var firstInteractive = menuContainer.querySelector('a, button');
        if (firstInteractive) {
            firstInteractive.focus();
        }
    }

    function closeMenu() {
        menuContainer.classList.add('hidden');
        menuBtn.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('overflow-hidden');
        menuBtn.focus();
    }

    menuBtn.addEventListener('click', function () {
        var isOpen = menuBtn.getAttribute('aria-expanded') === 'true';
        if (isOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closeMenu);
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeMenu);
    }

    // Fermeture automatique lors du clic sur un lien interne
    var navLinks = menuContainer.querySelectorAll('a[href^="#"], a[href^="/"]');
    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            closeMenu();
        });
    });

    // Échap pour fermer le menu
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && menuBtn.getAttribute('aria-expanded') === 'true') {
            closeMenu();
        }
    });
}

/**
 * 4. Onglets interactifs de la présentation produit (Showcase)
 */
function initShowcaseTabs() {
    var tabButtons = document.querySelectorAll('[data-showcase-tab]');
    var panels = document.querySelectorAll('[data-showcase-panel]');

    if (!tabButtons.length || !panels.length) return;

    tabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.getAttribute('data-showcase-tab');

            // Mise à jour de l'état des boutons
            tabButtons.forEach(function (b) {
                var isActive = b === btn;
                b.setAttribute('aria-selected', isActive ? 'true' : 'false');
                if (isActive) {
                    b.classList.add('bg-brand-orange', 'text-white', 'shadow-sm');
                    b.classList.remove('text-gray-400', 'hover:text-white', 'bg-transparent');
                } else {
                    b.classList.remove('bg-brand-orange', 'text-white', 'shadow-sm');
                    b.classList.add('text-gray-400', 'hover:text-white', 'bg-transparent');
                }
            });

            // Affichage du panneau correspondant avec transition fluide
            panels.forEach(function (panel) {
                if (panel.getAttribute('data-showcase-panel') === target) {
                    panel.classList.remove('hidden');
                    panel.classList.add('animate-fadeIn');
                } else {
                    panel.classList.add('hidden');
                    panel.classList.remove('animate-fadeIn');
                }
            });
        });
    });
}

/**
 * 5. Accordéon FAQ accessible (ARIA + Clavier)
 */
function initFaqAccordion() {
    var triggers = document.querySelectorAll('[data-faq-trigger]');

    triggers.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var isExpanded = btn.getAttribute('aria-expanded') === 'true';
            var answerId = btn.getAttribute('aria-controls');
            var answerEl = document.getElementById(answerId);
            var icon = btn.querySelector('[data-faq-icon]');

            // Fermer tous les autres éléments (mode accordéon exclusif ou optionnel)
            triggers.forEach(function (otherBtn) {
                if (otherBtn !== btn) {
                    otherBtn.setAttribute('aria-expanded', 'false');
                    var otherId = otherBtn.getAttribute('aria-controls');
                    var otherAnswer = document.getElementById(otherId);
                    var otherIcon = otherBtn.querySelector('[data-faq-icon]');
                    if (otherAnswer) otherAnswer.classList.add('hidden');
                    if (otherIcon) otherIcon.classList.remove('rotate-180');
                }
            });

            // Basculer l'élément sélectionné
            if (isExpanded) {
                btn.setAttribute('aria-expanded', 'false');
                if (answerEl) answerEl.classList.add('hidden');
                if (icon) icon.classList.remove('rotate-180');
            } else {
                btn.setAttribute('aria-expanded', 'true');
                if (answerEl) answerEl.classList.remove('hidden');
                if (icon) icon.classList.add('rotate-180');
            }
        });
    });
}

/**
 * 6. Défilement fluide pour les liens internes avec ancres
 */
function initSmoothAnchors() {
    var anchorLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');

    anchorLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            var targetId = link.getAttribute('href').substring(1);
            var targetElement = document.getElementById(targetId);

            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                history.pushState(null, '', '#' + targetId);
            }
        });
    });
}

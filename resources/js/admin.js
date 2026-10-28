/**
 * QREATERY — Super-Administration SaaS
 * Lot 6.1 : Layout & Navigation Interactions (Vanilla JS)
 * Lot 6.2 : Tableau de bord SaaS (KPIs, Abonnements, Finances & Alertes)
 * Lot 6.3 : Annuaire des Restaurants (Recherche, Filtres, Pagination & Statuts)
 */

document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------------------
    // 1. Navigation & Tiroir Mobile (Lot 6.1)
    // -------------------------------------------------------------------------
    var openBtn = document.getElementById('super-admin-open-sidebar');
    var closeBtn = document.getElementById('super-admin-close-sidebar');
    var sidebar = document.getElementById('super-admin-sidebar');
    var backdrop = document.getElementById('super-admin-sidebar-backdrop');

    if (sidebar && backdrop) {
        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            if (openBtn) {
                openBtn.setAttribute('aria-expanded', 'true');
            }
            if (closeBtn) {
                closeBtn.focus();
            }
            document.body.classList.add('overflow-hidden', 'md:overflow-auto');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
            if (openBtn) {
                openBtn.setAttribute('aria-expanded', 'false');
                openBtn.focus();
            }
            document.body.classList.remove('overflow-hidden', 'md:overflow-auto');
        }

        if (openBtn) {
            openBtn.addEventListener('click', function (e) {
                e.preventDefault();
                openSidebar();
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                closeSidebar();
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', function () {
                closeSidebar();
            });
        }

        // Fermeture à la touche Échap
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
                closeSidebar();
            }
        });

        // Fermeture automatique au clic sur un lien mobile
        var navLinks = sidebar.querySelectorAll('a[data-nav-id]');
        navLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 768) {
                    closeSidebar();
                }
            });
        });
    }

    // -------------------------------------------------------------------------
    // 2. Tableau de bord Super-Admin (Lot 6.2)
    // -------------------------------------------------------------------------
    var dashboardContent = document.getElementById('dashboard-content');
    if (dashboardContent) {
        initDashboard();
    }

    function initDashboard() {
        var loadingState = document.getElementById('dashboard-loading-state');
        var errorState = document.getElementById('dashboard-error-state');
        var errorMessage = document.getElementById('dashboard-error-message');
        var emptyState = document.getElementById('dashboard-empty-state');
        var retryBtn = document.getElementById('btn-retry-dashboard');
        var refreshBtn = document.getElementById('btn-refresh-dashboard');
        var refreshIcon = document.getElementById('dashboard-refresh-icon');
        var lastUpdatedLabel = document.getElementById('dashboard-last-updated');

        // KPI Elements
        var kpiRestaurantsTotal = document.getElementById('kpi-restaurants-total');
        var kpiRestaurantsActive = document.getElementById('kpi-restaurants-active');
        var kpiRestaurantsSuspended = document.getElementById('kpi-restaurants-suspended');

        var kpiSubscriptionsActive = document.getElementById('kpi-subscriptions-active');
        var kpiSubscriptionsGrace = document.getElementById('kpi-subscriptions-grace');
        var kpiSubscriptionsExpired = document.getElementById('kpi-subscriptions-expired');
        var kpiSubscriptionsExpiring = document.getElementById('kpi-subscriptions-expiring');

        var kpiRevenueCurrentMonth = document.getElementById('kpi-revenue-current-month');
        var kpiRevenueTotal = document.getElementById('kpi-revenue-total');

        // Bannière d'alerte échéances
        var bannerExpiring = document.getElementById('banner-expiring-alert');
        var bannerExpiringTitle = document.getElementById('banner-expiring-title');
        var bannerExpiringDesc = document.getElementById('banner-expiring-desc');
        var bannerExpiringIcon = document.getElementById('banner-expiring-icon');

        var isFetching = false;

        function formatCurrency(amountMinor, currency) {
            currency = currency || 'XOF';
            var units = (amountMinor || 0) / 100;
            var formatted = new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(units);

            return currency === 'XOF' ? formatted + ' FCFA' : formatted + ' ' + currency;
        }

        function setDashboardState(state, message) {
            if (loadingState) loadingState.classList.toggle('hidden', state !== 'loading');
            if (errorState) errorState.classList.toggle('hidden', state !== 'error');
            if (emptyState) emptyState.classList.toggle('hidden', state !== 'empty');
            if (dashboardContent) dashboardContent.classList.toggle('hidden', state !== 'success');

            if (state === 'error' && errorMessage && message) {
                errorMessage.textContent = message;
            }

            if (state === 'loading') {
                if (refreshIcon) refreshIcon.classList.add('animate-spin');
            } else {
                if (refreshIcon) refreshIcon.classList.remove('animate-spin');
            }
        }

        async function fetchDashboardMetrics() {
            if (isFetching) return;
            isFetching = true;
            setDashboardState('loading');

            try {
                var headers = {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };

                if (typeof window.csrfToken === 'function') {
                    var token = window.csrfToken();
                    if (token) headers['X-CSRF-TOKEN'] = token;
                }

                var authToken = window.sessionStorage ? window.sessionStorage.getItem('auth_token') : null;
                if (authToken) {
                    headers['Authorization'] = 'Bearer ' + authToken;
                }

                var response = await fetch('/api/v1/admin/dashboard', {
                    method: 'GET',
                    headers: headers,
                    credentials: 'same-origin'
                });

                if (response.status === 401) {
                    setDashboardState('error', 'Session expirée ou non authentifiée. Redirection vers la page de connexion...');
                    isFetching = false;
                    setTimeout(function() { window.location.href = '/login'; }, 1000);
                    return;
                }

                if (response.status === 403) {
                    setDashboardState('error', 'Accès refusé (403) : Ce compte ne dispose pas des privilèges de Super-Administrateur plateforme.');
                    isFetching = false;
                    return;
                }

                if (!response.ok) {
                    setDashboardState('error', 'Erreur serveur (' + response.status + ') lors du chargement des indicateurs.');
                    isFetching = false;
                    return;
                }

                var json = await response.json();
                var data = json ? json.data : null;

                if (!data) {
                    setDashboardState('empty');
                    isFetching = false;
                    return;
                }

                renderDashboard(data);
                setDashboardState('success');

                var now = new Date();
                var timeStr = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                if (lastUpdatedLabel) {
                    lastUpdatedLabel.textContent = 'Actualisé à ' + timeStr;
                }

            } catch (err) {
                console.error('Erreur API Dashboard Admin:', err);
                setDashboardState('error', 'Impossible de contacter le serveur. Vérifiez votre connexion réseau.');
            } finally {
                isFetching = false;
            }
        }

        function renderDashboard(data) {
            var restaurants = data.restaurants || {};
            var subscriptions = data.subscriptions || {};
            var revenue = data.subscription_revenue || {};

            if (kpiRestaurantsTotal) kpiRestaurantsTotal.textContent = (restaurants.total || 0).toLocaleString('fr-FR');
            if (kpiRestaurantsActive) kpiRestaurantsActive.textContent = (restaurants.active || 0).toLocaleString('fr-FR');
            if (kpiRestaurantsSuspended) kpiRestaurantsSuspended.textContent = (restaurants.suspended || 0).toLocaleString('fr-FR');

            if (kpiSubscriptionsActive) kpiSubscriptionsActive.textContent = (subscriptions.active || 0).toLocaleString('fr-FR');
            if (kpiSubscriptionsGrace) kpiSubscriptionsGrace.textContent = (subscriptions.grace_period || 0).toLocaleString('fr-FR');
            if (kpiSubscriptionsExpired) kpiSubscriptionsExpired.textContent = (subscriptions.expired || 0).toLocaleString('fr-FR');
            if (kpiSubscriptionsExpiring) kpiSubscriptionsExpiring.textContent = (subscriptions.expiring_within_7_days || 0).toLocaleString('fr-FR');

            if (kpiRevenueCurrentMonth) {
                kpiRevenueCurrentMonth.textContent = formatCurrency(revenue.current_month_minor || 0);
            }
            if (kpiRevenueTotal) {
                kpiRevenueTotal.textContent = formatCurrency(revenue.total_minor || 0);
            }

            var expiringCount = subscriptions.expiring_within_7_days || 0;
            if (bannerExpiring) {
                if (expiringCount > 0) {
                    bannerExpiring.className = 'p-4 rounded-brand-lg border transition-all duration-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-100';
                    if (bannerExpiringIcon) bannerExpiringIcon.className = 'w-9 h-9 rounded-brand flex items-center justify-center shrink-0 bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300';
                    if (bannerExpiringTitle) bannerExpiringTitle.textContent = expiringCount + ' abonnement' + (expiringCount > 1 ? 's arrivent' : ' arrive') + ' à échéance dans les 7 jours';
                    if (bannerExpiringDesc) bannerExpiringDesc.textContent = 'La période de grâce de 7 jours s’appliquera dès la date d’expiration.';
                } else {
                    bannerExpiring.className = 'p-4 rounded-brand-lg border transition-all duration-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-900 text-emerald-900 dark:text-emerald-100';
                    if (bannerExpiringIcon) bannerExpiringIcon.className = 'w-9 h-9 rounded-brand flex items-center justify-center shrink-0 bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300';
                    if (bannerExpiringTitle) bannerExpiringTitle.textContent = 'Aucun abonnement en fin d’échéance imminente';
                    if (bannerExpiringDesc) bannerExpiringDesc.textContent = 'Tous les forfaits actifs ont une date de fin supérieure à 7 jours.';
                }
            }
        }

        if (retryBtn) retryBtn.addEventListener('click', fetchDashboardMetrics);
        if (refreshBtn) refreshBtn.addEventListener('click', fetchDashboardMetrics);

        fetchDashboardMetrics();
    }

    // -------------------------------------------------------------------------
    // 3. Annuaire des Restaurants (Lot 6.3)
    // -------------------------------------------------------------------------
    var restaurantsContent = document.getElementById('restaurants-content');
    if (restaurantsContent) {
        initRestaurantsDirectory();
    }

    function initRestaurantsDirectory() {
        var loadingState = document.getElementById('restaurants-loading-state');
        var errorState = document.getElementById('restaurants-error-state');
        var errorMessage = document.getElementById('restaurants-error-message');
        var emptyState = document.getElementById('restaurants-empty-state');
        var retryBtn = document.getElementById('btn-retry-restaurants');
        var refreshBtn = document.getElementById('btn-refresh-restaurants');
        var refreshIcon = document.getElementById('restaurants-refresh-icon');
        var totalBadge = document.getElementById('restaurants-total-badge');

        var searchInput = document.getElementById('restaurants-search-input');
        var searchClear = document.getElementById('restaurants-search-clear');
        var statusFilter = document.getElementById('restaurants-status-filter');
        var resetFiltersBtn = document.getElementById('btn-reset-filters');
        var emptyResetBtn = document.getElementById('btn-empty-reset');

        var tableBody = document.getElementById('restaurants-table-body');
        var mobileCards = document.getElementById('restaurants-mobile-cards');

        // Pagination elements
        var paginationInfo = document.getElementById('pagination-info');
        var prevPageBtn = document.getElementById('btn-pagination-prev');
        var nextPageBtn = document.getElementById('btn-pagination-next');
        var currentPageLabel = document.getElementById('pagination-current-page');

        // Modal elements
        var modal = document.getElementById('restaurant-status-modal');
        var modalBackdrop = document.getElementById('status-modal-backdrop');
        var modalCloseBtn = document.getElementById('btn-close-status-modal');
        var modalCancelBtn = document.getElementById('btn-cancel-status-modal');
        var modalForm = document.getElementById('restaurant-status-form');
        var modalIdInput = document.getElementById('status-modal-restaurant-id');
        var modalStatusInput = document.getElementById('status-modal-target-status');
        var modalName = document.getElementById('status-modal-restaurant-name');
        var modalSlug = document.getElementById('status-modal-restaurant-slug');
        var modalTitle = document.getElementById('status-modal-title');
        var modalExplanation = document.getElementById('status-modal-explanation');
        var modalIconContainer = document.getElementById('status-modal-icon-container');
        var modalReason = document.getElementById('status-modal-reason');
        var modalReasonError = document.getElementById('status-reason-error');
        var modalReasonBadge = document.getElementById('status-reason-required-badge');
        var submitBtn = document.getElementById('btn-submit-status-modal');
        var submitSpinner = document.getElementById('status-submit-spinner');
        var submitText = document.getElementById('status-submit-text');

        // State local
        var state = {
            page: 1,
            search: '',
            status: '',
            isLoading: false,
            searchTimer: null,
            currentRequestId: 0,
            activeRestaurants: []
        };

        function setDirectoryState(s, msg) {
            if (loadingState) loadingState.classList.toggle('hidden', s !== 'loading');
            if (errorState) errorState.classList.toggle('hidden', s !== 'error');
            if (emptyState) emptyState.classList.toggle('hidden', s !== 'empty');
            if (restaurantsContent) restaurantsContent.classList.toggle('hidden', s !== 'success');

            if (s === 'error' && errorMessage && msg) {
                errorMessage.textContent = msg;
            }

            if (s === 'loading') {
                if (refreshIcon) refreshIcon.classList.add('animate-spin');
            } else {
                if (refreshIcon) refreshIcon.classList.remove('animate-spin');
            }
        }

        async function fetchRestaurants() {
            if (state.isLoading) return;
            state.isLoading = true;
            setDirectoryState('loading');

            var requestId = ++state.currentRequestId;

            try {
                var params = new URLSearchParams();
                if (state.page > 1) params.set('page', state.page);
                if (state.search) params.set('search', state.search);
                if (state.status) params.set('status', state.status);

                var url = '/api/v1/admin/restaurants' + (params.toString() ? '?' + params.toString() : '');

                var headers = {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };

                if (typeof window.csrfToken === 'function') {
                    var token = window.csrfToken();
                    if (token) headers['X-CSRF-TOKEN'] = token;
                }

                var authToken = window.sessionStorage ? window.sessionStorage.getItem('auth_token') : null;
                if (authToken) {
                    headers['Authorization'] = 'Bearer ' + authToken;
                }

                var response = await fetch(url, {
                    method: 'GET',
                    headers: headers,
                    credentials: 'same-origin'
                });

                // Si une requête plus récente a déjà été lancée (anti-race condition)
                if (requestId !== state.currentRequestId) {
                    return;
                }

                if (response.status === 401) {
                    setDirectoryState('error', 'Session expirée ou non authentifiée. Veuillez vous connecter en tant que Super-Administrateur.');
                    state.isLoading = false;
                    return;
                }

                if (response.status === 403) {
                    setDirectoryState('error', 'Accès refusé (403) : Ce compte ne dispose pas des privilèges de Super-Administrateur.');
                    state.isLoading = false;
                    return;
                }

                if (!response.ok) {
                    setDirectoryState('error', 'Erreur serveur (' + response.status + ') lors du chargement des restaurants.');
                    state.isLoading = false;
                    return;
                }

                var json = await response.json();
                var items = json ? json.data : [];
                var meta = json ? json.meta : null;
                var links = json ? json.links : null;

                state.activeRestaurants = items || [];

                if (!items || items.length === 0) {
                    setDirectoryState('empty');
                    if (totalBadge) totalBadge.textContent = '0 établissement';
                    state.isLoading = false;
                    return;
                }

                renderRestaurants(items, meta, links);
                setDirectoryState('success');

            } catch (err) {
                if (requestId === state.currentRequestId) {
                    console.error('Erreur API Restaurants Admin:', err);
                    setDirectoryState('error', 'Impossible de contacter le serveur. Vérifiez votre connexion.');
                }
            } finally {
                if (requestId === state.currentRequestId) {
                    state.isLoading = false;
                }
            }
        }

        function renderRestaurants(items, meta, links) {
            // Mise à jour du compteur total
            if (meta && totalBadge) {
                totalBadge.textContent = meta.total + ' établissement' + (meta.total > 1 ? 's' : '');
            }

            // Rendu Tableau Desktop
            if (tableBody) {
                tableBody.innerHTML = '';
                items.forEach(function (restaurant) {
                    var tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50/80 dark:hover:bg-brand-dark/40 transition-colors';
                    tr.innerHTML = buildTableRowHtml(restaurant);
                    tableBody.appendChild(tr);
                });
            }

            // Rendu Cartes Mobile
            if (mobileCards) {
                mobileCards.innerHTML = '';
                items.forEach(function (restaurant) {
                    var card = document.createElement('div');
                    card.className = 'p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs space-y-3';
                    card.innerHTML = buildMobileCardHtml(restaurant);
                    mobileCards.appendChild(card);
                });
            }

            // Rendu Pagination
            renderPagination(meta, links);
            bindActionButtons();
        }

        function getStatusBadgeHtml(status) {
            status = status || 'draft';
            var map = {
                active: '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Actif</span>',
                suspended: '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Suspendu</span>',
                draft: '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Brouillon</span>',
                archived: '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700"><span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>Archivé</span>'
            };
            return map[status] || map.draft;
        }

        function buildTableRowHtml(r) {
            var isSuspended = r.status === 'suspended';
            var actionBtnClass = isSuspended
                ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900 border border-emerald-200 dark:border-emerald-800'
                : 'text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900 border border-rose-200 dark:border-rose-800';

            var actionLabel = isSuspended ? 'Réactiver' : 'Suspendre';
            var targetStatus = isSuspended ? 'active' : 'suspended';

            var logoHtml = r.logo_url
                ? '<img src="' + r.logo_url + '" alt="" class="w-9 h-9 rounded-brand object-cover shrink-0 border border-gray-200 dark:border-brand-dark-border">'
                : '<div class="w-9 h-9 rounded-brand bg-brand-orange/10 text-brand-orange flex items-center justify-center font-bold text-xs shrink-0 border border-brand-orange/20">' + (r.name ? r.name.charAt(0).toUpperCase() : 'R') + '</div>';

            return '<td class="py-3 px-4">' +
                '<div class="flex items-center gap-3">' +
                    logoHtml +
                    '<div class="min-w-0">' +
                        '<p class="text-sm font-bold text-brand-text dark:text-white truncate">' + escapeHtml(r.name) + '</p>' +
                        '<p class="text-[11px] text-gray-400 font-mono truncate">/' + escapeHtml(r.slug) + '</p>' +
                    '</div>' +
                '</div>' +
            '</td>' +
            '<td class="py-3 px-4">' +
                '<p class="text-xs text-gray-700 dark:text-gray-300 truncate">' + (r.email ? escapeHtml(r.email) : '<span class="text-gray-400">—</span>') + '</p>' +
                '<p class="text-[11px] text-gray-400 truncate">' + (r.phone ? escapeHtml(r.phone) : 'Sans téléphone') + '</p>' +
            '</td>' +
            '<td class="py-3 px-4">' +
                '<span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-300">' + (r.currency || 'XOF') + '</span>' +
                '<p class="text-[11px] text-gray-400 mt-0.5 truncate">' + (r.timezone || 'Africa/Abidjan') + '</p>' +
            '</td>' +
            '<td class="py-3 px-4 text-center">' +
                getStatusBadgeHtml(r.status) +
            '</td>' +
            '<td class="py-3 px-4 text-right">' +
                '<button type="button" class="btn-toggle-status touch-target px-3 py-1.5 rounded-brand text-xs font-semibold transition-colors focus-ring ' + actionBtnClass + '" data-id="' + r.id + '" data-name="' + escapeHtml(r.name) + '" data-slug="' + escapeHtml(r.slug) + '" data-status="' + r.status + '" data-target="' + targetStatus + '">' +
                    actionLabel +
                '</button>' +
            '</td>';
        }

        function buildMobileCardHtml(r) {
            var isSuspended = r.status === 'suspended';
            var actionBtnClass = isSuspended
                ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900 border border-emerald-200 dark:border-emerald-800'
                : 'text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900 border border-rose-200 dark:border-rose-800';

            var actionLabel = isSuspended ? 'Réactiver l’établissement' : 'Suspendre l’établissement';
            var targetStatus = isSuspended ? 'active' : 'suspended';

            return '<div class="flex items-start justify-between gap-3">' +
                '<div class="min-w-0 flex items-center gap-2.5">' +
                    '<div class="w-8 h-8 rounded-brand bg-brand-orange/10 text-brand-orange flex items-center justify-center font-bold text-xs shrink-0">' + (r.name ? r.name.charAt(0).toUpperCase() : 'R') + '</div>' +
                    '<div class="min-w-0">' +
                        '<h4 class="text-sm font-bold text-brand-text dark:text-white truncate">' + escapeHtml(r.name) + '</h4>' +
                        '<p class="text-[11px] text-gray-400 font-mono truncate">/' + escapeHtml(r.slug) + '</p>' +
                    '</div>' +
                '</div>' +
                '<div>' + getStatusBadgeHtml(r.status) + '</div>' +
            '</div>' +
            '<div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-300 pt-2 border-t border-gray-100 dark:border-brand-dark-border">' +
                '<div><span class="text-gray-400 block text-[10px] uppercase">Contact</span>' + (r.phone ? escapeHtml(r.phone) : (r.email ? escapeHtml(r.email) : '—')) + '</div>' +
                '<div><span class="text-gray-400 block text-[10px] uppercase">Devise / Fuseau</span>' + (r.currency || 'XOF') + ' (' + (r.timezone ? r.timezone.split('/')[1] || r.timezone : 'UTC') + ')</div>' +
            '</div>' +
            '<div class="pt-2">' +
                '<button type="button" class="btn-toggle-status w-full touch-target px-3 py-2 rounded-brand text-xs font-semibold text-center transition-colors focus-ring ' + actionBtnClass + '" data-id="' + r.id + '" data-name="' + escapeHtml(r.name) + '" data-slug="' + escapeHtml(r.slug) + '" data-status="' + r.status + '" data-target="' + targetStatus + '">' +
                    actionLabel +
                '</button>' +
            '</div>';
        }

        function renderPagination(meta, links) {
            if (!meta) return;

            var from = meta.from || 0;
            var to = meta.to || 0;
            var total = meta.total || 0;

            if (paginationInfo) {
                paginationInfo.textContent = 'Affichage de ' + from + ' à ' + to + ' sur ' + total + ' restaurant' + (total > 1 ? 's' : '');
            }

            if (currentPageLabel) {
                currentPageLabel.textContent = meta.current_page || 1;
            }

            if (prevPageBtn) {
                prevPageBtn.disabled = !links || !links.prev;
            }

            if (nextPageBtn) {
                nextPageBtn.disabled = !links || !links.next;
            }
        }

        function bindActionButtons() {
            var buttons = document.querySelectorAll('.btn-toggle-status');
            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = btn.getAttribute('data-id');
                    var name = btn.getAttribute('data-name');
                    var slug = btn.getAttribute('data-slug');
                    var currentStatus = btn.getAttribute('data-status');
                    var targetStatus = btn.getAttribute('data-target');

                    openStatusModal(id, name, slug, currentStatus, targetStatus);
                });
            });
        }

        function openStatusModal(id, name, slug, currentStatus, targetStatus) {
            if (!modal) return;

            modalIdInput.value = id;
            modalStatusInput.value = targetStatus;
            modalName.textContent = name;
            modalSlug.textContent = '/' + slug;
            modalReason.value = '';
            if (modalReasonError) modalReasonError.classList.add('hidden');

            var isSuspending = targetStatus === 'suspended';

            if (isSuspending) {
                modalTitle.textContent = 'Suspendre l’établissement';
                modalExplanation.textContent = 'Attention : la suspension désactive immédiatement l’accès aux menus, aux commandes et suspend l’activité commerciale de ce restaurant.';
                modalIconContainer.className = 'w-8 h-8 rounded-brand flex items-center justify-center bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400';
                modalIconContainer.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
                submitBtn.className = 'px-4 py-2 rounded-brand text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-sm focus-ring touch-target flex items-center gap-1.5';
                submitText.textContent = 'Confirmer la suspension';
                if (modalReasonBadge) modalReasonBadge.classList.remove('hidden');
                modalReason.setAttribute('placeholder', 'Motif obligatoire de la suspension (ex: défaut de paiement, infraction aux conditions)...');
            } else {
                modalTitle.textContent = 'Réactiver l’établissement';
                modalExplanation.textContent = 'La réactivation rétablira immédiatement l’accès complet au restaurant, l’affichage public du menu et la prise de commandes.';
                modalIconContainer.className = 'w-8 h-8 rounded-brand flex items-center justify-center bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400';
                modalIconContainer.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                submitBtn.className = 'px-4 py-2 rounded-brand text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm focus-ring touch-target flex items-center gap-1.5';
                submitText.textContent = 'Confirmer la réactivation';
                if (modalReasonBadge) modalReasonBadge.classList.add('hidden');
                modalReason.setAttribute('placeholder', 'Motif facultatif de la réactivation (ex: régularisation du compte)...');
            }

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            setTimeout(function () { modalReason.focus(); }, 100);
        }

        function closeStatusModal() {
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            if (submitSpinner) submitSpinner.classList.add('hidden');
            if (submitBtn) submitBtn.disabled = false;
        }

        // Événements Modal
        if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeStatusModal);
        if (modalCancelBtn) modalCancelBtn.addEventListener('click', closeStatusModal);
        if (modalBackdrop) modalBackdrop.addEventListener('click', closeStatusModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeStatusModal();
            }
        });

        // Soumission du changement de statut (PATCH /api/v1/admin/restaurants/{id}/status)
        if (modalForm) {
            modalForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                var restaurantId = modalIdInput.value;
                var targetStatus = modalStatusInput.value;
                var reason = modalReason.value.trim();

                // Validation frontend du motif obligatoire en cas de suspension (Rule::requiredIf backend)
                if (targetStatus === 'suspended' && !reason) {
                    if (modalReasonError) {
                        modalReasonError.textContent = 'Le motif est obligatoire pour suspendre un établissement.';
                        modalReasonError.classList.remove('hidden');
                    }
                    modalReason.focus();
                    return;
                }

                if (modalReasonError) modalReasonError.classList.add('hidden');

                // Verrouillage du bouton pendant la requête
                if (submitBtn) submitBtn.disabled = true;
                if (submitSpinner) submitSpinner.classList.remove('hidden');

                try {
                    var headers = {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    };

                    if (typeof window.csrfToken === 'function') {
                        var token = window.csrfToken();
                        if (token) headers['X-CSRF-TOKEN'] = token;
                    }

                    var authToken = window.sessionStorage ? window.sessionStorage.getItem('auth_token') : null;
                    if (authToken) {
                        headers['Authorization'] = 'Bearer ' + authToken;
                    }

                    var payload = {
                        status: targetStatus
                    };
                    if (reason) {
                        payload.reason = reason;
                    }

                    var response = await fetch('/api/v1/admin/restaurants/' + encodeURIComponent(restaurantId) + '/status', {
                        method: 'PATCH',
                        headers: headers,
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });

                    if (response.status === 401) {
                        alert('Session expirée. Veuillez vous reconnecter en tant que Super-Administrateur.');
                        closeStatusModal();
                        return;
                    }

                    if (response.status === 403) {
                        alert('Accès refusé (403) : Privilèges Super-Administrateur requis.');
                        closeStatusModal();
                        return;
                    }

                    if (response.status === 422) {
                        var errorJson = await response.json();
                        var errText = (errorJson && errorJson.message) ? errorJson.message : 'Erreur de validation du statut ou du motif.';
                        if (modalReasonError) {
                            modalReasonError.textContent = errText;
                            modalReasonError.classList.remove('hidden');
                        }
                        return;
                    }

                    if (!response.ok) {
                        alert('Erreur serveur lors de la mise à jour (' + response.status + ').');
                        return;
                    }

                    // Succès
                    closeStatusModal();
                    if (typeof window.showToast === 'function') {
                        var msg = targetStatus === 'suspended' ? 'Établissement suspendu avec succès.' : 'Établissement réactivé avec succès.';
                        window.showToast(msg, 'success', 4000);
                    }

                    // Rafraîchissement de la liste
                    fetchRestaurants();

                } catch (err) {
                    console.error('Erreur PATCH status restaurant:', err);
                    alert('Erreur réseau. Impossible de contacter le serveur.');
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                    if (submitSpinner) submitSpinner.classList.add('hidden');
                }
            });
        }

        // Événements Recherche avec anti-rebond (debounce 300ms)
        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                var val = e.target.value.trim();
                if (searchClear) searchClear.classList.toggle('hidden', val === '');

                clearTimeout(state.searchTimer);
                state.searchTimer = setTimeout(function () {
                    state.search = val;
                    state.page = 1;
                    fetchRestaurants();
                }, 300);
            });
        }

        if (searchClear) {
            searchClear.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                searchClear.classList.add('hidden');
                state.search = '';
                state.page = 1;
                fetchRestaurants();
            });
        }

        // Événement Filtre Statut
        if (statusFilter) {
            statusFilter.addEventListener('change', function () {
                state.status = this.value;
                state.page = 1;
                fetchRestaurants();
            });
        }

        // Événements Réinitialisation
        function resetFilters() {
            if (searchInput) searchInput.value = '';
            if (searchClear) searchClear.classList.add('hidden');
            if (statusFilter) statusFilter.value = '';
            state.search = '';
            state.status = '';
            state.page = 1;
            fetchRestaurants();
        }

        if (resetFiltersBtn) resetFiltersBtn.addEventListener('click', resetFilters);
        if (emptyResetBtn) emptyResetBtn.addEventListener('click', resetFilters);
        if (retryBtn) retryBtn.addEventListener('click', fetchRestaurants);
        if (refreshBtn) refreshBtn.addEventListener('click', fetchRestaurants);

        // Événements Pagination
        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', function () {
                if (state.page > 1) {
                    state.page--;
                    fetchRestaurants();
                }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', function () {
                state.page++;
                fetchRestaurants();
            });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // =====================================================================
        // Gestion de la Modale de Création de Restaurant (Super-Admin)
        // =====================================================================
        var btnCreateRestaurant = document.getElementById('btn-create-restaurant');
        var btnEmptyCreateRestaurant = document.getElementById('btn-empty-create');
        var createModal = document.getElementById('create-restaurant-modal');
        var createModalBackdrop = document.getElementById('create-restaurant-modal-backdrop');
        var btnCloseCreateModal = document.getElementById('btn-close-create-restaurant-modal');
        var btnCancelCreateModal = document.getElementById('btn-cancel-create-restaurant-modal');
        var createForm = document.getElementById('create-restaurant-form');
        var btnSubmitCreateModal = document.getElementById('btn-submit-create-restaurant-modal');
        var createSubmitSpinner = document.getElementById('create-resto-submit-spinner');
        var createSubmitText = document.getElementById('create-resto-submit-text');
        var createGeneralError = document.getElementById('create-resto-general-error');

        var nameInput = document.getElementById('create-resto-name');
        var slugInput = document.getElementById('create-resto-slug');
        var slugManuallyEdited = false;

        function clearCreateErrors() {
            if (createGeneralError) {
                createGeneralError.classList.add('hidden');
                createGeneralError.textContent = '';
            }
            var errElements = document.querySelectorAll('[id^="create-resto-error-"]');
            errElements.forEach(function (el) {
                el.classList.add('hidden');
                el.textContent = '';
            });
        }

        function openCreateModal() {
            if (!createModal) return;
            clearCreateErrors();
            if (createForm) createForm.reset();
            slugManuallyEdited = false;
            createModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            setTimeout(function () {
                if (nameInput) nameInput.focus();
            }, 100);
        }

        function closeCreateModal() {
            if (!createModal) return;
            createModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            if (btnSubmitCreateModal) btnSubmitCreateModal.disabled = false;
            if (createSubmitSpinner) createSubmitSpinner.classList.add('hidden');
            if (createSubmitText) createSubmitText.textContent = 'Créer le restaurant';
        }

        if (slugInput) {
            slugInput.addEventListener('input', function () {
                slugManuallyEdited = this.value.trim() !== '';
            });
        }

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function () {
                if (!slugManuallyEdited) {
                    slugInput.value = nameInput.value
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/(^-|-$)+/g, '');
                }
            });
        }

        if (btnCreateRestaurant) btnCreateRestaurant.addEventListener('click', openCreateModal);
        if (btnEmptyCreateRestaurant) btnEmptyCreateRestaurant.addEventListener('click', openCreateModal);
        if (btnCloseCreateModal) btnCloseCreateModal.addEventListener('click', closeCreateModal);
        if (btnCancelCreateModal) btnCancelCreateModal.addEventListener('click', closeCreateModal);
        if (createModalBackdrop) createModalBackdrop.addEventListener('click', closeCreateModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && createModal && !createModal.classList.contains('hidden')) {
                closeCreateModal();
            }
        });

        if (createForm) {
            createForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                clearCreateErrors();

                var nameVal = nameInput ? nameInput.value.trim() : '';
                if (!nameVal || nameVal.length < 2) {
                    var errName = document.getElementById('create-resto-error-name');
                    if (errName) {
                        errName.textContent = 'Le nom du restaurant doit comporter au moins 2 caractères.';
                        errName.classList.remove('hidden');
                    }
                    if (nameInput) nameInput.focus();
                    return;
                }

                var formData = new FormData(createForm);
                var payload = {};
                formData.forEach(function (val, key) {
                    var trimmed = typeof val === 'string' ? val.trim() : val;
                    if (trimmed !== '') {
                        payload[key] = trimmed;
                    }
                });

                if (btnSubmitCreateModal) btnSubmitCreateModal.disabled = true;
                if (createSubmitSpinner) createSubmitSpinner.classList.remove('hidden');
                if (createSubmitText) createSubmitText.textContent = 'Création en cours...';

                try {
                    var headers = {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    };

                    if (typeof window.csrfToken === 'function') {
                        var token = window.csrfToken();
                        if (token) headers['X-CSRF-TOKEN'] = token;
                    } else {
                        var metaCsrf = document.querySelector('meta[name="csrf-token"]');
                        if (metaCsrf) headers['X-CSRF-TOKEN'] = metaCsrf.getAttribute('content');
                    }

                    var authToken = window.sessionStorage ? window.sessionStorage.getItem('auth_token') : null;
                    if (authToken) {
                        headers['Authorization'] = 'Bearer ' + authToken;
                    }

                    var response = await fetch('/api/v1/admin/restaurants', {
                        method: 'POST',
                        headers: headers,
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });

                    if (response.status === 401) {
                        alert('Session expirée. Veuillez vous reconnecter en tant que Super-Administrateur.');
                        closeCreateModal();
                        return;
                    }

                    if (response.status === 403) {
                        alert('Accès refusé (403) : Privilèges Super-Administrateur requis.');
                        closeCreateModal();
                        return;
                    }

                    if (response.status === 422) {
                        var errData = await response.json();
                        var errors = errData && errData.errors ? errData.errors : {};

                        Object.keys(errors).forEach(function (field) {
                            var el = document.getElementById('create-resto-error-' + field);
                            if (el) {
                                el.textContent = errors[field][0];
                                el.classList.remove('hidden');
                            }
                        });

                        if (createGeneralError) {
                            createGeneralError.textContent = errData.message || 'Veuillez corriger les erreurs dans le formulaire.';
                            createGeneralError.classList.remove('hidden');
                        }
                        return;
                    }

                    if (!response.ok) {
                        if (createGeneralError) {
                            createGeneralError.textContent = 'Erreur serveur lors de la création (' + response.status + ').';
                            createGeneralError.classList.remove('hidden');
                        }
                        return;
                    }

                    var createdResult = await response.json();
                    var createdResto = createdResult && createdResult.data ? createdResult.data : null;

                    closeCreateModal();

                    if (typeof window.showToast === 'function') {
                        var msg = createdResto ? 'Le restaurant « ' + escapeHtml(createdResto.name) + ' » a été créé avec succès !' : 'Restaurant créé avec succès.';
                        window.showToast(msg, 'success', 5000);
                    }

                    // Rafraîchir l'annuaire
                    state.page = 1;
                    fetchRestaurants();

                } catch (err) {
                    console.error('Erreur POST create restaurant:', err);
                    if (createGeneralError) {
                        createGeneralError.textContent = 'Erreur réseau. Impossible de contacter le serveur.';
                        createGeneralError.classList.remove('hidden');
                    }
                } finally {
                    if (btnSubmitCreateModal) btnSubmitCreateModal.disabled = false;
                    if (createSubmitSpinner) createSubmitSpinner.classList.add('hidden');
                    if (createSubmitText) createSubmitText.textContent = 'Créer le restaurant';
                }
            });
        }

        // Chargement initial
        fetchRestaurants();
    }

    // -------------------------------------------------------------------------
    // 4. Gestion des Plans SaaS (Lot 6.4)
    // -------------------------------------------------------------------------
    var plansContent = document.getElementById('plans-content');
    if (plansContent) {
        initPlansManagement();
    }

    function initPlansManagement() {
        var loadingState = document.getElementById('plans-loading-state');
        var errorState = document.getElementById('plans-error-state');
        var errorMessage = document.getElementById('plans-error-message');
        var emptyState = document.getElementById('plans-empty-state');
        var emptySubtitle = document.getElementById('plans-empty-subtitle');
        var retryBtn = document.getElementById('btn-retry-plans');
        var refreshBtn = document.getElementById('btn-refresh-plans');
        var refreshIcon = document.getElementById('plans-refresh-icon');
        var totalBadge = document.getElementById('plans-total-badge');

        var searchInput = document.getElementById('plans-search-input');
        var searchClear = document.getElementById('plans-search-clear');
        var activeFilter = document.getElementById('plans-active-filter');
        var resetFiltersBtn = document.getElementById('btn-reset-filters');
        var emptyResetBtn = document.getElementById('btn-empty-reset');
        var createBtn = document.getElementById('btn-create-plan');
        var emptyCreateBtn = document.getElementById('btn-empty-create');

        var cardsGrid = document.getElementById('plans-cards-grid');

        // Modale Plan (Création / Édition)
        var planModal = document.getElementById('plan-modal');
        var planModalBackdrop = document.getElementById('plan-modal-backdrop');
        var planModalTitle = document.getElementById('plan-modal-title');
        var closePlanModalBtn = document.getElementById('btn-close-plan-modal');
        var cancelPlanModalBtn = document.getElementById('btn-cancel-plan-modal');
        var planForm = document.getElementById('plan-form');
        var planIdInput = document.getElementById('plan-id');
        var planModeInput = document.getElementById('plan-mode');
        var planNameInput = document.getElementById('plan-name');
        var planSlugInput = document.getElementById('plan-slug');
        var planPriceInput = document.getElementById('plan-price');
        var planCurrencyInput = document.getElementById('plan-currency');
        var planBillingPeriodInput = document.getElementById('plan-billing-period');
        var planBillingIntervalInput = document.getElementById('plan-billing-interval');
        var planDescInput = document.getElementById('plan-description');
        var planFeaturesInput = document.getElementById('plan-features');
        var planLimitEmployeesInput = document.getElementById('plan-limit-employees');
        var planLimitTablesInput = document.getElementById('plan-limit-tables');
        var planIsActiveInput = document.getElementById('plan-is-active');
        var planFormGeneralError = document.getElementById('plan-form-general-error');
        var submitPlanFormBtn = document.getElementById('btn-submit-plan-form');
        var planSubmitSpinner = document.getElementById('plan-submit-spinner');
        var planSubmitText = document.getElementById('plan-submit-text');

        // Modale Confirmation Statut
        var toggleModal = document.getElementById('plan-toggle-modal');
        var toggleModalBackdrop = document.getElementById('toggle-modal-backdrop');
        var toggleModalTitle = document.getElementById('toggle-modal-title');
        var toggleModalPlanName = document.getElementById('toggle-modal-plan-name');
        var toggleModalExplanation = document.getElementById('toggle-modal-explanation');
        var toggleModalIconContainer = document.getElementById('toggle-modal-icon-container');
        var toggleModalError = document.getElementById('toggle-modal-error');
        var cancelToggleModalBtn = document.getElementById('btn-cancel-toggle-modal');
        var confirmToggleBtn = document.getElementById('btn-confirm-toggle');
        var toggleSubmitSpinner = document.getElementById('toggle-submit-spinner');
        var toggleSubmitText = document.getElementById('toggle-submit-text');

        // Toast
        var toast = document.getElementById('plans-toast');
        var toastTitle = document.getElementById('plans-toast-title');
        var toastMessage = document.getElementById('plans-toast-message');
        var toastIcon = document.getElementById('plans-toast-icon');
        var closeToastBtn = document.getElementById('btn-close-plans-toast');
        var toastTimeout = null;

        // State local
        var state = {
            search: '',
            active: '',
            isLoading: false,
            searchTimer: null,
            currentRequestId: 0,
            plans: [],
            targetPlan: null
        };

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatCurrency(amountMinor, currency) {
            currency = (currency || 'XOF').toUpperCase();
            var units = (amountMinor || 0) / 100;
            var formatted = new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(units);

            return currency === 'XOF' ? formatted + ' FCFA' : formatted + ' ' + currency;
        }

        function formatBilling(period, interval) {
            interval = parseInt(interval, 10) || 1;
            var periods = {
                'month': { single: 'mois', plural: 'mois' },
                'year': { single: 'an', plural: 'ans' },
                'week': { single: 'semaine', plural: 'semaines' },
                'day': { single: 'jour', plural: 'jours' }
            };
            var p = periods[period] || { single: period, plural: period };
            if (interval === 1) {
                return '/ ' + p.single;
            }
            return 'tous les ' + interval + ' ' + p.plural;
        }

        function showToast(title, message, variant) {
            if (!toast) return;
            if (toastTimeout) clearTimeout(toastTimeout);

            if (toastTitle) toastTitle.textContent = title;
            if (toastMessage) toastMessage.textContent = message;

            toast.className = 'fixed bottom-5 right-5 z-50 max-w-sm p-4 rounded-brand-lg shadow-lg border text-xs flex items-start gap-3 transition-all duration-200 transform translate-y-0 opacity-100';

            if (variant === 'success') {
                toast.classList.add('bg-emerald-50', 'dark:bg-emerald-950/90', 'border-emerald-200', 'dark:border-emerald-800', 'text-emerald-900', 'dark:text-emerald-200');
                if (toastIcon) {
                    toastIcon.innerHTML = '<svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                }
            } else if (variant === 'danger') {
                toast.classList.add('bg-rose-50', 'dark:bg-rose-950/90', 'border-rose-200', 'dark:border-rose-800', 'text-rose-900', 'dark:text-rose-200');
                if (toastIcon) {
                    toastIcon.innerHTML = '<svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                }
            } else {
                toast.classList.add('bg-orange-50', 'dark:bg-brand-dark-card', 'border-orange-200', 'dark:border-brand-dark-border', 'text-brand-text', 'dark:text-gray-100');
                if (toastIcon) {
                    toastIcon.innerHTML = '<svg class="w-5 h-5 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                }
            }

            toast.classList.remove('hidden');

            toastTimeout = setTimeout(function () {
                hideToast();
            }, 4000);
        }

        function hideToast() {
            if (!toast) return;
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(function () {
                toast.classList.add('hidden');
            }, 200);
        }

        if (closeToastBtn) {
            closeToastBtn.addEventListener('click', hideToast);
        }

        function setPlansState(s, msg) {
            if (loadingState) loadingState.classList.toggle('hidden', s !== 'loading');
            if (errorState) errorState.classList.toggle('hidden', s !== 'error');
            if (emptyState) emptyState.classList.toggle('hidden', s !== 'empty');
            if (plansContent) plansContent.classList.toggle('hidden', s !== 'success');

            if (s === 'error' && errorMessage && msg) {
                errorMessage.textContent = msg;
            }

            if (s === 'loading') {
                if (refreshIcon) refreshIcon.classList.add('animate-spin');
            } else {
                if (refreshIcon) refreshIcon.classList.remove('animate-spin');
            }
        }

        async function fetchPlans() {
            if (state.isLoading) return;
            state.isLoading = true;
            setPlansState('loading');

            var requestId = ++state.currentRequestId;

            try {
                var params = new URLSearchParams();
                if (state.search) params.set('search', state.search);
                if (state.active !== '') params.set('active', state.active);

                var url = '/api/v1/admin/plans' + (params.toString() ? '?' + params.toString() : '');

                var headers = {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };

                if (typeof window.csrfToken === 'function') {
                    var token = window.csrfToken();
                    if (token) headers['X-CSRF-TOKEN'] = token;
                }

                var authToken = window.sessionStorage ? window.sessionStorage.getItem('auth_token') : null;
                if (authToken) {
                    headers['Authorization'] = 'Bearer ' + authToken;
                }

                var response = await fetch(url, {
                    method: 'GET',
                    headers: headers,
                    credentials: 'same-origin'
                });

                if (requestId !== state.currentRequestId) return;

                if (response.status === 401) {
                    setPlansState('error', 'Session expirée ou non authentifiée. Veuillez vous connecter en tant que Super-Administrateur.');
                    state.isLoading = false;
                    return;
                }

                if (response.status === 403) {
                    setPlansState('error', 'Accès refusé (403) : Ce compte ne dispose pas des privilèges de Super-Administrateur.');
                    state.isLoading = false;
                    return;
                }

                if (!response.ok) {
                    setPlansState('error', 'Erreur serveur (' + response.status + ') lors de la récupération des plans.');
                    state.isLoading = false;
                    return;
                }

                var json = await response.json();
                var items = json ? json.data : [];
                state.plans = items || [];

                if (totalBadge) {
                    totalBadge.textContent = state.plans.length + (state.plans.length > 1 ? ' plans' : ' plan');
                }

                if (!items || items.length === 0) {
                    setPlansState('empty');
                    if (emptySubtitle) {
                        if (state.search || state.active !== '') {
                            emptySubtitle.textContent = 'Aucun plan ne correspond aux filtres appliqués.';
                        } else {
                            emptySubtitle.textContent = 'Aucun plan SaaS n’est actuellement configuré sur la plateforme.';
                        }
                    }
                    state.isLoading = false;
                    return;
                }

                renderPlans(items);
                setPlansState('success');

            } catch (err) {
                if (requestId === state.currentRequestId) {
                    console.error('Erreur API Plans Admin:', err);
                    setPlansState('error', 'Impossible de contacter le serveur. Vérifiez votre connexion.');
                }
            } finally {
                if (requestId === state.currentRequestId) {
                    state.isLoading = false;
                }
            }
        }

        function renderPlans(items) {
            if (!cardsGrid) return;
            cardsGrid.innerHTML = '';

            items.forEach(function (plan) {
                var card = document.createElement('div');
                card.className = 'bg-white dark:bg-brand-dark-card rounded-brand-lg border ' +
                    (plan.is_active ? 'border-gray-200 dark:border-brand-dark-border' : 'border-dashed border-gray-300 dark:border-gray-800 opacity-80') +
                    ' p-6 flex flex-col justify-between shadow-2xs hover:shadow-md transition-all duration-150 relative group';

                // En-tête : Nom, slug & badge statut
                var headerDiv = document.createElement('div');
                headerDiv.className = 'flex items-start justify-between gap-3';

                var titleDiv = document.createElement('div');
                titleDiv.className = 'min-w-0 flex-1';

                var nameEl = document.createElement('h3');
                nameEl.className = 'text-lg font-black text-brand-text dark:text-white truncate';
                nameEl.textContent = plan.name;

                var slugEl = document.createElement('span');
                slugEl.className = 'inline-block mt-0.5 text-[11px] font-mono text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-brand-dark px-2 py-0.5 rounded';
                slugEl.textContent = plan.slug;

                titleDiv.appendChild(nameEl);
                titleDiv.appendChild(slugEl);

                var badgeDiv = document.createElement('div');
                badgeDiv.className = 'shrink-0';
                if (plan.is_active) {
                    badgeDiv.innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Actif</span>';
                } else {
                    badgeDiv.innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-300 dark:border-gray-700"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactif</span>';
                }

                headerDiv.appendChild(titleDiv);
                headerDiv.appendChild(badgeDiv);

                // Prix & Récurrence
                var priceDiv = document.createElement('div');
                priceDiv.className = 'mt-4 pt-3 border-t border-gray-100 dark:border-brand-dark-border/80 flex items-baseline gap-2';

                var priceAmount = document.createElement('span');
                priceAmount.className = 'text-2xl font-black text-brand-text dark:text-white tracking-tight';
                priceAmount.textContent = formatCurrency(plan.price_minor, plan.currency);

                var billingPeriod = document.createElement('span');
                billingPeriod.className = 'text-xs text-gray-500 dark:text-gray-400 font-medium';
                billingPeriod.textContent = formatBilling(plan.billing_period, plan.billing_interval);

                priceDiv.appendChild(priceAmount);
                priceDiv.appendChild(billingPeriod);

                // Description
                var descEl = document.createElement('p');
                descEl.className = 'mt-2 text-xs text-gray-600 dark:text-gray-300 line-clamp-2 leading-relaxed min-h-[32px]';
                descEl.textContent = plan.description || 'Aucune description saisie pour cette formule.';

                // Plafonds & Limites (chips)
                var limitsDiv = document.createElement('div');
                limitsDiv.className = 'mt-3 flex flex-wrap gap-1.5';
                if (plan.limits && typeof plan.limits === 'object') {
                    if (plan.limits.employees) {
                        var empLimit = document.createElement('span');
                        empLimit.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900/50';
                        empLimit.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg> Max ' + escapeHtml(plan.limits.employees) + ' employés';
                        limitsDiv.appendChild(empLimit);
                    }
                    if (plan.limits.tables) {
                        var tabLimit = document.createElement('span');
                        tabLimit.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-900/50';
                        tabLimit.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg> Max ' + escapeHtml(plan.limits.tables) + ' tables';
                        limitsDiv.appendChild(tabLimit);
                    }
                }

                // Liste des fonctionnalités incluses
                var featuresContainer = document.createElement('div');
                featuresContainer.className = 'mt-4 pt-3 border-t border-gray-100 dark:border-brand-dark-border/80 flex-1';

                var featuresTitle = document.createElement('h4');
                featuresTitle.className = 'text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2';
                featuresTitle.textContent = 'Fonctionnalités incluses';

                var featuresList = document.createElement('ul');
                featuresList.className = 'space-y-1.5';

                var feats = Array.isArray(plan.features) ? plan.features : [];
                if (feats.length > 0) {
                    feats.forEach(function (f) {
                        var li = document.createElement('li');
                        li.className = 'flex items-center gap-2 text-xs text-gray-700 dark:text-gray-200';
                        li.innerHTML = '<svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span class="truncate">' + escapeHtml(f) + '</span>';
                        featuresList.appendChild(li);
                    });
                } else {
                    var emptyLi = document.createElement('li');
                    emptyLi.className = 'text-xs text-gray-400 italic';
                    emptyLi.textContent = 'Modules de base du restaurant';
                    featuresList.appendChild(emptyLi);
                }

                featuresContainer.appendChild(featuresTitle);
                featuresContainer.appendChild(featuresList);

                // Actions de gestion
                var actionsDiv = document.createElement('div');
                actionsDiv.className = 'mt-6 pt-4 border-t border-gray-100 dark:border-brand-dark-border flex items-center gap-2';

                // Bouton Modifier
                var editBtn = document.createElement('button');
                editBtn.type = 'button';
                editBtn.className = 'flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-brand text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark dark:hover:bg-brand-dark/80 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-brand-dark-border transition-colors focus-ring touch-target';
                editBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Modifier';
                editBtn.addEventListener('click', function () {
                    openEditModal(plan);
                });

                // Bouton Basculer Statut (Activer / Désactiver)
                var toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                if (plan.is_active) {
                    toggleBtn.className = 'flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-brand text-xs font-semibold bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/40 transition-colors focus-ring touch-target';
                    toggleBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg> Désactiver';
                } else {
                    toggleBtn.className = 'flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-brand text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/40 transition-colors focus-ring touch-target';
                    toggleBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Réactiver';
                }
                toggleBtn.addEventListener('click', function () {
                    openToggleModal(plan);
                });

                actionsDiv.appendChild(editBtn);
                actionsDiv.appendChild(toggleBtn);

                // Assemblage de la carte
                card.appendChild(headerDiv);
                card.appendChild(priceDiv);
                card.appendChild(descEl);
                if (limitsDiv.children.length > 0) {
                    card.appendChild(limitsDiv);
                }
                card.appendChild(featuresContainer);
                card.appendChild(actionsDiv);

                cardsGrid.appendChild(card);
            });
        }

        // =====================================================================
        // Gestion de la modale Plan (Création & Édition)
        // =====================================================================
        function clearPlanErrors() {
            if (planFormGeneralError) {
                planFormGeneralError.textContent = '';
                planFormGeneralError.classList.add('hidden');
            }
            var errorFields = [
                'name', 'slug', 'price_minor', 'currency', 'billing_period',
                'billing_interval', 'description', 'features'
            ];
            errorFields.forEach(function (f) {
                var el = document.getElementById('plan-error-' + f);
                if (el) {
                    el.textContent = '';
                    el.classList.add('hidden');
                }
            });
        }

        function openCreateModal() {
            clearPlanErrors();
            if (planForm) planForm.reset();
            if (planIdInput) planIdInput.value = '';
            if (planModeInput) planModeInput.value = 'create';
            if (planModalTitle) planModalTitle.textContent = 'Nouveau Plan SaaS';
            if (planSubmitText) planSubmitText.textContent = 'Enregistrer le plan';
            if (planIsActiveInput) planIsActiveInput.checked = true;
            if (planBillingPeriodInput) planBillingPeriodInput.value = 'month';
            if (planBillingIntervalInput) planBillingIntervalInput.value = '1';
            if (planCurrencyInput) planCurrencyInput.value = 'XOF';

            if (planModal) planModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            if (planNameInput) planNameInput.focus();
        }

        function openEditModal(plan) {
            clearPlanErrors();
            if (planForm) planForm.reset();
            if (planIdInput) planIdInput.value = plan.id;
            if (planModeInput) planModeInput.value = 'edit';
            if (planModalTitle) planModalTitle.textContent = 'Modifier le plan : ' + plan.name;
            if (planSubmitText) planSubmitText.textContent = 'Mettre à jour';

            if (planNameInput) planNameInput.value = plan.name || '';
            if (planSlugInput) planSlugInput.value = plan.slug || '';
            if (planPriceInput) planPriceInput.value = (plan.price_minor || 0) / 100;
            if (planCurrencyInput) planCurrencyInput.value = (plan.currency || 'XOF').toUpperCase();
            if (planBillingPeriodInput) planBillingPeriodInput.value = plan.billing_period || 'month';
            if (planBillingIntervalInput) planBillingIntervalInput.value = plan.billing_interval || '1';
            if (planDescInput) planDescInput.value = plan.description || '';

            if (planFeaturesInput) {
                var feats = Array.isArray(plan.features) ? plan.features : [];
                planFeaturesInput.value = feats.join('\n');
            }

            if (planLimitEmployeesInput) {
                planLimitEmployeesInput.value = (plan.limits && plan.limits.employees) ? plan.limits.employees : '';
            }
            if (planLimitTablesInput) {
                planLimitTablesInput.value = (plan.limits && plan.limits.tables) ? plan.limits.tables : '';
            }

            if (planIsActiveInput) {
                planIsActiveInput.checked = plan.is_active === true || plan.is_active === 1;
            }

            if (planModal) planModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            if (planNameInput) planNameInput.focus();
        }

        function closePlanModal() {
            if (planModal) planModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            clearPlanErrors();
        }

        if (createBtn) createBtn.addEventListener('click', openCreateModal);
        if (emptyCreateBtn) emptyCreateBtn.addEventListener('click', openCreateModal);
        if (closePlanModalBtn) closePlanModalBtn.addEventListener('click', closePlanModal);
        if (cancelPlanModalBtn) cancelPlanModalBtn.addEventListener('click', closePlanModal);
        if (planModalBackdrop) planModalBackdrop.addEventListener('click', closePlanModal);

        // Soumission du formulaire Plan
        if (planForm) {
            planForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                clearPlanErrors();

                var mode = planModeInput ? planModeInput.value : 'create';
                var planId = planIdInput ? planIdInput.value : '';

                // Validation préalable côté client
                var nameVal = planNameInput ? planNameInput.value.trim() : '';
                var priceVal = planPriceInput ? parseFloat(planPriceInput.value) : NaN;
                var periodVal = planBillingPeriodInput ? planBillingPeriodInput.value : 'month';
                var intervalVal = planBillingIntervalInput ? parseInt(planBillingIntervalInput.value, 10) : 1;

                var hasClientErrors = false;

                if (!nameVal || nameVal.length < 2) {
                    var errName = document.getElementById('plan-error-name');
                    if (errName) {
                        errName.textContent = 'Le nom du plan doit comporter au moins 2 caractères.';
                        errName.classList.remove('hidden');
                    }
                    hasClientErrors = true;
                }

                if (isNaN(priceVal) || priceVal < 0) {
                    var errPrice = document.getElementById('plan-error-price_minor');
                    if (errPrice) {
                        errPrice.textContent = 'Veuillez saisir un tarif valide (nombre positif ou zéro).';
                        errPrice.classList.remove('hidden');
                    }
                    hasClientErrors = true;
                }

                if (!['day', 'week', 'month', 'year'].includes(periodVal)) {
                    var errPeriod = document.getElementById('plan-error-billing_period');
                    if (errPeriod) {
                        errPeriod.textContent = 'Veuillez sélectionner une période valide.';
                        errPeriod.classList.remove('hidden');
                    }
                    hasClientErrors = true;
                }

                if (isNaN(intervalVal) || intervalVal < 1 || intervalVal > 36) {
                    var errInterval = document.getElementById('plan-error-billing_interval');
                    if (errInterval) {
                        errInterval.textContent = "L'intervalle doit être compris entre 1 et 36.";
                        errInterval.classList.remove('hidden');
                    }
                    hasClientErrors = true;
                }

                if (hasClientErrors) return;

                // Construction du payload selon les conventions API
                var payload = {
                    name: nameVal,
                    price_minor: Math.round(priceVal * 100),
                    currency: planCurrencyInput ? planCurrencyInput.value.toLowerCase() : 'xof',
                    billing_period: periodVal,
                    billing_interval: intervalVal,
                    is_active: planIsActiveInput ? planIsActiveInput.checked : true
                };

                if (planSlugInput && planSlugInput.value.trim()) {
                    payload.slug = planSlugInput.value.trim();
                }

                if (planDescInput && planDescInput.value.trim()) {
                    payload.description = planDescInput.value.trim();
                }

                // Features : parsing par ligne ou virgule
                if (planFeaturesInput && planFeaturesInput.value.trim()) {
                    var lines = planFeaturesInput.value.split(/[\r\n,]+/);
                    var feats = [];
                    lines.forEach(function (l) {
                        var trimmed = l.trim();
                        if (trimmed && feats.indexOf(trimmed) === -1) {
                            feats.push(trimmed);
                        }
                    });
                    if (feats.length > 0) {
                        payload.features = feats;
                    }
                }

                // Limits
                var limits = {};
                if (planLimitEmployeesInput && planLimitEmployeesInput.value) {
                    limits.employees = parseInt(planLimitEmployeesInput.value, 10);
                }
                if (planLimitTablesInput && planLimitTablesInput.value) {
                    limits.tables = parseInt(planLimitTablesInput.value, 10);
                }
                if (Object.keys(limits).length > 0) {
                    payload.limits = limits;
                }

                // Envoi API
                if (submitPlanFormBtn) submitPlanFormBtn.disabled = true;
                if (planSubmitSpinner) planSubmitSpinner.classList.remove('hidden');

                try {
                    var url = mode === 'create'
                        ? '/api/v1/admin/plans'
                        : '/api/v1/admin/plans/' + planId;

                    var method = mode === 'create' ? 'POST' : 'PATCH';

                    var headers = {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    };

                    if (typeof window.csrfToken === 'function') {
                        var token = window.csrfToken();
                        if (token) headers['X-CSRF-TOKEN'] = token;
                    }

                    var authToken = window.sessionStorage ? window.sessionStorage.getItem('auth_token') : null;
                    if (authToken) {
                        headers['Authorization'] = 'Bearer ' + authToken;
                    }

                    var response = await fetch(url, {
                        method: method,
                        headers: headers,
                        body: JSON.stringify(payload),
                        credentials: 'same-origin'
                    });

                    if (response.status === 422) {
                        var errData = await response.json();
                        var errors = errData && errData.errors ? errData.errors : {};

                        Object.keys(errors).forEach(function (field) {
                            var el = document.getElementById('plan-error-' + field);
                            if (el) {
                                el.textContent = errors[field][0];
                                el.classList.remove('hidden');
                            }
                        });

                        if (errData.message && planFormGeneralError) {
                            planFormGeneralError.textContent = errData.message;
                            planFormGeneralError.classList.remove('hidden');
                        }
                        return;
                    }

                    if (!response.ok) {
                        var errText = 'Erreur (' + response.status + ') lors de l’enregistrement du plan.';
                        if (planFormGeneralError) {
                            planFormGeneralError.textContent = errText;
                            planFormGeneralError.classList.remove('hidden');
                        }
                        return;
                    }

                    // Succès
                    closePlanModal();
                    showToast(
                        'Opération réussie',
                        mode === 'create' ? 'Le nouveau plan SaaS a été créé avec succès.' : 'Le plan a été mis à jour avec succès.',
                        'success'
                    );
                    fetchPlans();

                } catch (err) {
                    console.error('Erreur soumission plan:', err);
                    if (planFormGeneralError) {
                        planFormGeneralError.textContent = 'Impossible de contacter le serveur. Veuillez réessayer.';
                        planFormGeneralError.classList.remove('hidden');
                    }
                } finally {
                    if (submitPlanFormBtn) submitPlanFormBtn.disabled = false;
                    if (planSubmitSpinner) planSubmitSpinner.classList.add('hidden');
                }
            });
        }

        // =====================================================================
        // Gestion de la modale d'activation / désactivation
        // =====================================================================
        function openToggleModal(plan) {
            state.targetPlan = plan;
            if (toggleModalError) {
                toggleModalError.textContent = '';
                toggleModalError.classList.add('hidden');
            }

            var willBeActive = !plan.is_active;

            if (toggleModalPlanName) {
                toggleModalPlanName.textContent = plan.name + ' (' + plan.slug + ')';
            }

            if (willBeActive) {
                if (toggleModalTitle) toggleModalTitle.textContent = 'Réactiver le plan SaaS';
                if (toggleModalExplanation) {
                    toggleModalExplanation.textContent = 'En réactivant ce plan, il redeviendra immédiatement accessible et souscriptible par les nouveaux restaurants lors de leur abonnement.';
                }
                if (toggleModalIconContainer) {
                    toggleModalIconContainer.className = 'w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0';
                    toggleModalIconContainer.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                }
                if (confirmToggleBtn) {
                    confirmToggleBtn.className = 'inline-flex items-center gap-2 px-4 py-2 rounded-brand text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors focus-ring touch-target shadow-sm';
                }
                if (toggleSubmitText) toggleSubmitText.textContent = 'Confirmer la réactivation';
            } else {
                if (toggleModalTitle) toggleModalTitle.textContent = 'Désactiver le plan SaaS';
                if (toggleModalExplanation) {
                    toggleModalExplanation.textContent = 'En désactivant ce plan, aucun nouveau restaurant ne pourra y souscrire. Les abonnements actuellement en cours sur ce plan restent inchangés et continueront de fonctionner conformément aux engagements.';
                }
                if (toggleModalIconContainer) {
                    toggleModalIconContainer.className = 'w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0';
                    toggleModalIconContainer.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>';
                }
                if (confirmToggleBtn) {
                    confirmToggleBtn.className = 'inline-flex items-center gap-2 px-4 py-2 rounded-brand text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white transition-colors focus-ring touch-target shadow-sm';
                }
                if (toggleSubmitText) toggleSubmitText.textContent = 'Confirmer la désactivation';
            }

            if (toggleModal) toggleModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            if (confirmToggleBtn) confirmToggleBtn.focus();
        }

        function closeToggleModal() {
            if (toggleModal) toggleModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            state.targetPlan = null;
        }

        if (cancelToggleModalBtn) cancelToggleModalBtn.addEventListener('click', closeToggleModal);
        if (toggleModalBackdrop) toggleModalBackdrop.addEventListener('click', closeToggleModal);

        if (confirmToggleBtn) {
            confirmToggleBtn.addEventListener('click', async function () {
                if (!state.targetPlan) return;

                var targetId = state.targetPlan.id;
                var nextActiveState = !state.targetPlan.is_active;

                confirmToggleBtn.disabled = true;
                if (toggleSubmitSpinner) toggleSubmitSpinner.classList.remove('hidden');
                if (toggleModalError) toggleModalError.classList.add('hidden');

                try {
                    var headers = {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    };

                    if (typeof window.csrfToken === 'function') {
                        var token = window.csrfToken();
                        if (token) headers['X-CSRF-TOKEN'] = token;
                    }

                    var authToken = window.sessionStorage ? window.sessionStorage.getItem('auth_token') : null;
                    if (authToken) {
                        headers['Authorization'] = 'Bearer ' + authToken;
                    }

                    var response = await fetch('/api/v1/admin/plans/' + targetId, {
                        method: 'PATCH',
                        headers: headers,
                        body: JSON.stringify({ is_active: nextActiveState }),
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        var errJson = await response.json().catch(function () { return null; });
                        var errMsg = (errJson && errJson.message) ? errJson.message : 'Échec de la mise à jour du statut (' + response.status + ').';
                        if (toggleModalError) {
                            toggleModalError.textContent = errMsg;
                            toggleModalError.classList.remove('hidden');
                        }
                        return;
                    }

                    closeToggleModal();
                    showToast(
                        'Statut mis à jour',
                        nextActiveState ? 'Le plan a été réactivé avec succès.' : 'Le plan a été désactivé avec succès.',
                        'success'
                    );
                    fetchPlans();

                } catch (err) {
                    console.error('Erreur bascule statut plan:', err);
                    if (toggleModalError) {
                        toggleModalError.textContent = 'Erreur réseau lors de la mise à jour. Veuillez vérifier votre connexion.';
                        toggleModalError.classList.remove('hidden');
                    }
                } finally {
                    confirmToggleBtn.disabled = false;
                    if (toggleSubmitSpinner) toggleSubmitSpinner.classList.add('hidden');
                }
            });
        }

        // =====================================================================
        // Événements Filtres & Recherche
        // =====================================================================
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var val = this.value.trim();
                if (searchClear) {
                    searchClear.classList.toggle('hidden', val.length === 0);
                }

                if (state.searchTimer) clearTimeout(state.searchTimer);
                state.searchTimer = setTimeout(function () {
                    state.search = val;
                    fetchPlans();
                }, 300);
            });
        }

        if (searchClear) {
            searchClear.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                searchClear.classList.add('hidden');
                state.search = '';
                fetchPlans();
            });
        }

        if (activeFilter) {
            activeFilter.addEventListener('change', function () {
                state.active = this.value;
                fetchPlans();
            });
        }

        function resetFilters() {
            if (searchInput) searchInput.value = '';
            if (searchClear) searchClear.classList.add('hidden');
            if (activeFilter) activeFilter.value = '';
            state.search = '';
            state.active = '';
            fetchPlans();
        }

        if (resetFiltersBtn) resetFiltersBtn.addEventListener('click', resetFilters);
        if (emptyResetBtn) emptyResetBtn.addEventListener('click', resetFilters);
        if (retryBtn) retryBtn.addEventListener('click', fetchPlans);
        if (refreshBtn) refreshBtn.addEventListener('click', fetchPlans);

        // Fermeture des modales avec la touche Échap
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (planModal && !planModal.classList.contains('hidden')) {
                    closePlanModal();
                }
                if (toggleModal && !toggleModal.classList.contains('hidden')) {
                    closeToggleModal();
                }
            }
        });

        // Chargement initial
        fetchPlans();
    }

    // -------------------------------------------------------------------------
    // 5. Gestion des Abonnements & Règlements SaaS (Lot 6.5)
    // -------------------------------------------------------------------------
    var subscriptionsContent = document.getElementById('subscriptions-content');
    if (subscriptionsContent) {
        initSubscriptionsManagement();
    }

    function initSubscriptionsManagement() {
        var loadingState = document.getElementById('subscriptions-loading-state');
        var errorState = document.getElementById('subscriptions-error-state');
        var errorMessage = document.getElementById('subscriptions-error-message');
        var emptyState = document.getElementById('subscriptions-empty-state');
        var retryBtn = document.getElementById('btn-retry-subscriptions');
        var refreshBtn = document.getElementById('btn-refresh-subscriptions');
        var refreshIcon = document.getElementById('subscriptions-refresh-icon');
        var totalBadge = document.getElementById('subscriptions-total-badge');

        var searchInput = document.getElementById('subscriptions-search-input');
        var searchClear = document.getElementById('subscriptions-search-clear');
        var statusFilter = document.getElementById('subscriptions-status-filter');
        var planFilter = document.getElementById('subscriptions-plan-filter');
        var resetFiltersBtn = document.getElementById('btn-reset-subscription-filters');
        var emptyResetBtn = document.getElementById('btn-empty-reset-sub');
        var emptyCreateBtn = document.getElementById('btn-empty-create-sub');

        var tableBody = document.getElementById('subscriptions-table-body');
        var mobileCards = document.getElementById('subscriptions-mobile-cards');

        var prevPageBtn = document.getElementById('btn-sub-pagination-prev');
        var nextPageBtn = document.getElementById('btn-sub-pagination-next');
        var paginationInfo = document.getElementById('sub-pagination-info');
        var currentPageLabel = document.getElementById('sub-pagination-current-page');

        var kpiActive = document.getElementById('kpi-active-count');
        var kpiGrace = document.getElementById('kpi-grace-count');
        var kpiSuspended = document.getElementById('kpi-suspended-count');
        var kpiPayments = document.getElementById('kpi-payments-total');

        // Modales
        var btnCreateSub = document.getElementById('btn-create-subscription');
        var createSubModal = document.getElementById('create-subscription-modal');
        var createSubBackdrop = document.getElementById('create-sub-modal-backdrop');
        var btnCloseCreateSub = document.getElementById('btn-close-create-sub-modal');
        var btnCancelCreateSub = document.getElementById('btn-cancel-create-sub-modal');
        var createSubForm = document.getElementById('create-subscription-form');
        var btnSubmitCreateSub = document.getElementById('btn-submit-create-sub-modal');
        var createSubSpinner = document.getElementById('create-sub-submit-spinner');
        var createSubGeneralError = document.getElementById('create-sub-general-error');

        // Modale Statut
        var statusModal = document.getElementById('subscription-status-modal');
        var statusBackdrop = document.getElementById('sub-status-modal-backdrop');
        var btnCloseStatusModal = document.getElementById('btn-close-sub-status-modal');
        var btnCancelStatusModal = document.getElementById('btn-cancel-sub-status-modal');
        var statusForm = document.getElementById('subscription-status-form');
        var statusModalId = document.getElementById('sub-status-modal-id');
        var statusModalResto = document.getElementById('sub-status-modal-restaurant-name');
        var statusModalPlan = document.getElementById('sub-status-modal-plan-info');
        var statusModalTarget = document.getElementById('sub-status-modal-target-status');
        var statusModalReason = document.getElementById('sub-status-modal-reason');
        var statusModalError = document.getElementById('sub-status-modal-error');
        var btnSubmitStatusModal = document.getElementById('btn-submit-sub-status-modal');
        var statusSubmitSpinner = document.getElementById('sub-status-submit-spinner');

        // Modale Paiement
        var paymentModal = document.getElementById('subscription-payment-modal');
        var paymentBackdrop = document.getElementById('sub-payment-modal-backdrop');
        var btnClosePaymentModal = document.getElementById('btn-close-sub-payment-modal');
        var btnCancelPaymentModal = document.getElementById('btn-cancel-sub-payment-modal');
        var paymentForm = document.getElementById('subscription-payment-form');
        var paymentModalId = document.getElementById('sub-payment-modal-id');
        var paymentModalResto = document.getElementById('sub-payment-modal-restaurant-name');
        var paymentModalPlan = document.getElementById('sub-payment-modal-plan-info');
        var paymentModalAmount = document.getElementById('sub-payment-modal-amount');
        var paymentModalCurrency = document.getElementById('sub-payment-modal-currency');
        var paymentModalMethod = document.getElementById('sub-payment-modal-method');
        var paymentModalRef = document.getElementById('sub-payment-modal-reference');
        var paymentModalStatus = document.getElementById('sub-payment-modal-status');
        var paymentModalError = document.getElementById('sub-payment-modal-error');
        var btnSubmitPaymentModal = document.getElementById('btn-submit-sub-payment-modal');
        var paymentSubmitSpinner = document.getElementById('sub-payment-submit-spinner');

        // Modale Détails
        var detailsModal = document.getElementById('subscription-details-modal');
        var detailsBackdrop = document.getElementById('sub-details-modal-backdrop');
        var btnCloseDetailsModal = document.getElementById('btn-close-sub-details-modal');
        var btnDismissDetailsModal = document.getElementById('btn-dismiss-sub-details-modal');
        var detailsSubtitle = document.getElementById('sub-details-modal-subtitle');
        var detailsStatus = document.getElementById('sub-details-status');
        var detailsPlan = document.getElementById('sub-details-plan');
        var detailsStarts = document.getElementById('sub-details-starts');
        var detailsEnds = document.getElementById('sub-details-ends');
        var detailsPaymentsCount = document.getElementById('sub-details-payments-count');
        var detailsPaymentsList = document.getElementById('sub-details-payments-list');
        var detailsHistoryList = document.getElementById('sub-details-history-list');

        var state = {
            page: 1,
            search: '',
            status: '',
            plan_id: '',
            isLoading: false,
            searchTimer: null,
            currentRequestId: 0,
            activeSubscriptions: []
        };

        function setSubscriptionsState(s, msg) {
            if (loadingState) loadingState.classList.toggle('hidden', s !== 'loading');
            if (errorState) errorState.classList.toggle('hidden', s !== 'error');
            if (emptyState) emptyState.classList.toggle('hidden', s !== 'empty');
            if (subscriptionsContent) subscriptionsContent.classList.toggle('hidden', s !== 'success');

            if (s === 'error' && errorMessage && msg) {
                errorMessage.textContent = msg;
            }

            if (s === 'loading') {
                if (refreshIcon) refreshIcon.classList.add('animate-spin');
            } else {
                if (refreshIcon) refreshIcon.classList.remove('animate-spin');
            }
        }

        function getAuthHeaders() {
            var headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };

            if (typeof window.csrfToken === 'function') {
                var token = window.csrfToken();
                if (token) headers['X-CSRF-TOKEN'] = token;
            } else {
                var metaCsrf = document.querySelector('meta[name="csrf-token"]');
                if (metaCsrf) headers['X-CSRF-TOKEN'] = metaCsrf.getAttribute('content');
            }

            var authToken = window.sessionStorage ? window.sessionStorage.getItem('auth_token') : null;
            if (authToken) {
                headers['Authorization'] = 'Bearer ' + authToken;
            }

            return headers;
        }

        async function fetchSubscriptions() {
            if (state.isLoading) return;
            state.isLoading = true;
            setSubscriptionsState('loading');

            var requestId = ++state.currentRequestId;

            try {
                var params = new URLSearchParams();
                if (state.page > 1) params.set('page', state.page);
                if (state.search) params.set('search', state.search);
                if (state.status) params.set('status', state.status);
                if (state.plan_id) params.set('plan_id', state.plan_id);

                var url = '/api/v1/admin/subscriptions' + (params.toString() ? '?' + params.toString() : '');

                var response = await fetch(url, {
                    method: 'GET',
                    headers: getAuthHeaders(),
                    credentials: 'same-origin'
                });

                if (requestId !== state.currentRequestId) return;

                if (!response.ok) {
                    setSubscriptionsState('error', 'Impossible de récupérer la liste des abonnements (' + response.status + ').');
                    state.isLoading = false;
                    return;
                }

                var json = await response.json();
                var items = json && json.data ? json.data : [];
                var meta = json ? json.meta : null;
                var links = json ? json.links : null;

                state.activeSubscriptions = items;

                // Calcul métriques KPI
                updateKpis(items);

                if (!items || items.length === 0) {
                    setSubscriptionsState('empty');
                    if (totalBadge) totalBadge.textContent = '0 contrat';
                    state.isLoading = false;
                    return;
                }

                renderSubscriptions(items, meta, links);
                setSubscriptionsState('success');

            } catch (err) {
                if (requestId === state.currentRequestId) {
                    console.error('Erreur API Subscriptions:', err);
                    setSubscriptionsState('error', 'Erreur réseau. Impossible de contacter le serveur.');
                }
            } finally {
                if (requestId === state.currentRequestId) {
                    state.isLoading = false;
                }
            }
        }

        function updateKpis(items) {
            var activeCount = 0;
            var graceCount = 0;
            var suspendedCount = 0;
            var totalPaymentsCount = 0;

            items.forEach(function (sub) {
                if (sub.status === 'active') activeCount++;
                else if (sub.status === 'grace_period') graceCount++;
                else if (sub.status === 'suspended' || sub.status === 'expired') suspendedCount++;

                if (sub.payments && Array.isArray(sub.payments)) {
                    totalPaymentsCount += sub.payments.length;
                }
            });

            if (kpiActive) kpiActive.textContent = activeCount;
            if (kpiGrace) kpiGrace.textContent = graceCount;
            if (kpiSuspended) kpiSuspended.textContent = suspendedCount;
            if (kpiPayments) kpiPayments.textContent = totalPaymentsCount;
        }

        function getStatusBadge(status) {
            switch (status) {
                case 'active':
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Actif</span>';
                case 'grace_period':
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 animate-pulse"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Grâce (7j)</span>';
                case 'suspended':
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Suspendu</span>';
                case 'trialing':
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Essai</span>';
                case 'cancelled':
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Résilié</span>';
                default:
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-brand-dark text-gray-600 dark:text-gray-400"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>' + (status || 'Inconnu') + '</span>';
            }
        }

        function formatDate(dStr) {
            if (!dStr) return '—';
            var d = new Date(dStr);
            if (isNaN(d.getTime())) return dStr;
            return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        function renderSubscriptions(items, meta, links) {
            if (meta && totalBadge) {
                totalBadge.textContent = meta.total + ' contrat' + (meta.total > 1 ? 's' : '');
            }

            if (tableBody) {
                tableBody.innerHTML = '';
                items.forEach(function (sub) {
                    var tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50/80 dark:hover:bg-brand-dark/40 transition-colors';
                    tr.innerHTML = buildTableRow(sub);
                    tableBody.appendChild(tr);
                });
            }

            if (mobileCards) {
                mobileCards.innerHTML = '';
                items.forEach(function (sub) {
                    var card = document.createElement('div');
                    card.className = 'p-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border shadow-2xs space-y-3';
                    card.innerHTML = buildMobileCard(sub);
                    mobileCards.appendChild(card);
                });
            }

            // Pagination
            if (meta && paginationInfo) {
                paginationInfo.textContent = 'Affichage de ' + (meta.from || 0) + ' à ' + (meta.to || 0) + ' sur ' + (meta.total || 0) + ' contrat(s)';
            }
            if (meta && currentPageLabel) {
                currentPageLabel.textContent = meta.current_page || 1;
            }
            if (prevPageBtn) prevPageBtn.disabled = !links || !links.prev;
            if (nextPageBtn) nextPageBtn.disabled = !links || !links.next;

            bindRowActions();
        }

        function buildTableRow(sub) {
            var resto = sub.restaurant || {};
            var plan = sub.plan || {};
            var paymentsCount = sub.payments ? sub.payments.length : 0;
            var price = plan.price_minor ? (plan.price_minor / 100).toLocaleString('fr-FR') : '0';

            return '<td class="py-3 px-4">' +
                '<div class="flex items-center gap-2.5">' +
                    '<div class="w-8 h-8 rounded-brand bg-brand-orange/10 text-brand-orange flex items-center justify-center font-bold text-xs shrink-0">' +
                        (resto.name ? escapeHtml(resto.name.charAt(0).toUpperCase()) : 'R') +
                    '</div>' +
                    '<div class="min-w-0">' +
                        '<div class="font-bold text-brand-text dark:text-white truncate">' + escapeHtml(resto.name || 'Restaurant') + '</div>' +
                        '<div class="text-[11px] text-gray-400 font-mono truncate">/' + escapeHtml(resto.slug || '') + '</div>' +
                    '</div>' +
                '</div>' +
            '</td>' +
            '<td class="py-3 px-4">' +
                '<span class="font-bold text-brand-orange">' + escapeHtml(plan.name || 'Plan Inconnu') + '</span>' +
                '<div class="text-[11px] text-gray-500 dark:text-gray-400">' + price + ' ' + (plan.currency || 'XOF').toUpperCase() + ' / ' + (plan.billing_period || 'mois') + '</div>' +
            '</td>' +
            '<td class="py-3 px-4">' +
                '<div class="text-xs text-brand-text dark:text-gray-200">Du ' + formatDate(sub.starts_at) + ' au ' + formatDate(sub.ends_at) + '</div>' +
                (sub.grace_ends_at ? '<div class="text-[10px] text-amber-600 dark:text-amber-400">Grâce jusqu\'au ' + formatDate(sub.grace_ends_at) + '</div>' : '') +
            '</td>' +
            '<td class="py-3 px-4 text-center">' +
                getStatusBadge(sub.status) +
            '</td>' +
            '<td class="py-3 px-4">' +
                '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 dark:bg-brand-dark text-gray-700 dark:text-gray-300">' +
                    paymentsCount + ' règlement' + (paymentsCount > 1 ? 's' : '') +
                '</span>' +
            '</td>' +
            '<td class="py-3 px-4 text-right">' +
                '<div class="flex items-center justify-end gap-1.5">' +
                    '<button type="button" class="btn-sub-status px-2.5 py-1 text-xs font-semibold rounded-brand bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark dark:hover:bg-brand-dark-border text-gray-700 dark:text-gray-200 transition-colors" data-id="' + sub.id + '" title="Changer le statut">' +
                        'Statut' +
                    '</button>' +
                    '<button type="button" class="btn-sub-pay px-2.5 py-1 text-xs font-semibold rounded-brand bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition-colors" data-id="' + sub.id + '" title="Encaisser">' +
                        '+ Règlement' +
                    '</button>' +
                    '<button type="button" class="btn-sub-details px-2.5 py-1 text-xs font-semibold rounded-brand bg-brand-light dark:bg-brand-orange/10 hover:bg-brand-orange hover:text-white text-brand-orange transition-colors" data-id="' + sub.id + '" title="Détails & Historique">' +
                        'Détails' +
                    '</button>' +
                '</div>' +
            '</td>';
        }

        function buildMobileCard(sub) {
            var resto = sub.restaurant || {};
            var plan = sub.plan || {};
            var paymentsCount = sub.payments ? sub.payments.length : 0;
            var price = plan.price_minor ? (plan.price_minor / 100).toLocaleString('fr-FR') : '0';

            return '<div class="flex items-start justify-between gap-3">' +
                '<div>' +
                    '<h4 class="text-sm font-bold text-brand-text dark:text-white">' + escapeHtml(resto.name || 'Restaurant') + '</h4>' +
                    '<p class="text-xs text-brand-orange font-semibold">' + escapeHtml(plan.name || 'Plan') + ' (' + price + ' ' + (plan.currency || 'XOF') + ')</p>' +
                '</div>' +
                '<div>' + getStatusBadge(sub.status) + '</div>' +
            '</div>' +
            '<div class="text-xs text-gray-500 pt-1 border-t border-gray-100 dark:border-brand-dark-border">' +
                '<div>Période : ' + formatDate(sub.starts_at) + ' au ' + formatDate(sub.ends_at) + '</div>' +
                '<div>Règlements enregistrés : ' + paymentsCount + '</div>' +
            '</div>' +
            '<div class="flex items-center gap-2 pt-2">' +
                '<button type="button" class="btn-sub-status flex-1 py-1.5 text-xs font-semibold rounded-brand bg-gray-100 dark:bg-brand-dark text-gray-700 dark:text-gray-200" data-id="' + sub.id + '">Statut</button>' +
                '<button type="button" class="btn-sub-pay flex-1 py-1.5 text-xs font-semibold rounded-brand bg-emerald-600 text-white" data-id="' + sub.id + '">+ Règlement</button>' +
                '<button type="button" class="btn-sub-details flex-1 py-1.5 text-xs font-semibold rounded-brand bg-brand-orange text-white" data-id="' + sub.id + '">Détails</button>' +
            '</div>';
        }

        function bindRowActions() {
            document.querySelectorAll('.btn-sub-status').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = parseInt(this.getAttribute('data-id'), 10);
                    var sub = state.activeSubscriptions.find(function (s) { return s.id === id; });
                    if (sub) openStatusModal(sub);
                });
            });

            document.querySelectorAll('.btn-sub-pay').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = parseInt(this.getAttribute('data-id'), 10);
                    var sub = state.activeSubscriptions.find(function (s) { return s.id === id; });
                    if (sub) openPaymentModal(sub);
                });
            });

            document.querySelectorAll('.btn-sub-details').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = parseInt(this.getAttribute('data-id'), 10);
                    var sub = state.activeSubscriptions.find(function (s) { return s.id === id; });
                    if (sub) openDetailsModal(sub);
                });
            });
        }

        // =====================================================================
        // Gestion Modale Création Abonnement
        // =====================================================================
        function openCreateSubModal() {
            if (!createSubModal) return;
            if (createSubForm) createSubForm.reset();
            if (createSubGeneralError) createSubGeneralError.classList.add('hidden');
            createSubModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeCreateSubModal() {
            if (!createSubModal) return;
            createSubModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            if (btnSubmitCreateSub) btnSubmitCreateSub.disabled = false;
            if (createSubSpinner) createSubSpinner.classList.add('hidden');
        }

        if (btnCreateSub) btnCreateSub.addEventListener('click', openCreateSubModal);
        if (emptyCreateBtn) emptyCreateBtn.addEventListener('click', openCreateSubModal);
        if (btnCloseCreateSub) btnCloseCreateSub.addEventListener('click', closeCreateSubModal);
        if (btnCancelCreateSub) btnCancelCreateSub.addEventListener('click', closeCreateSubModal);
        if (createSubBackdrop) createSubBackdrop.addEventListener('click', closeCreateSubModal);

        if (createSubForm) {
            createSubForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                var restoId = document.getElementById('create-sub-restaurant-id').value;
                var planId = document.getElementById('create-sub-plan-id').value;
                var startsAt = document.getElementById('create-sub-starts-at').value;
                var graceDays = document.getElementById('create-sub-grace-days').value;
                var status = document.getElementById('create-sub-status').value;
                var notes = document.getElementById('create-sub-notes').value;

                if (!restoId || !planId) {
                    alert('Veuillez sélectionner un établissement et une formule SaaS.');
                    return;
                }

                var payload = {
                    restaurant_id: parseInt(restoId, 10),
                    plan_id: parseInt(planId, 10),
                    status: status,
                    grace_days: parseInt(graceDays, 10) || 7,
                    notes: notes || null
                };

                if (startsAt) {
                    payload.starts_at = startsAt;
                }

                if (btnSubmitCreateSub) btnSubmitCreateSub.disabled = true;
                if (createSubSpinner) createSubSpinner.classList.remove('hidden');

                try {
                    var response = await fetch('/api/v1/admin/subscriptions', {
                        method: 'POST',
                        headers: Object.assign({ 'Content-Type': 'application/json' }, getAuthHeaders()),
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });

                    if (response.status === 422) {
                        var errData = await response.json();
                        var msg = errData && errData.message ? errData.message : 'Erreur de validation.';
                        if (createSubGeneralError) {
                            createSubGeneralError.textContent = msg;
                            createSubGeneralError.classList.remove('hidden');
                        }
                        return;
                    }

                    if (!response.ok) {
                        alert('Erreur lors de la création de l\'abonnement (' + response.status + ').');
                        return;
                    }

                    closeCreateSubModal();
                    if (typeof window.showToast === 'function') {
                        window.showToast('Abonnement activé avec succès !', 'success', 4000);
                    }
                    state.page = 1;
                    fetchSubscriptions();

                } catch (err) {
                    console.error('Erreur POST subscription:', err);
                    alert('Erreur réseau.');
                } finally {
                    if (btnSubmitCreateSub) btnSubmitCreateSub.disabled = false;
                    if (createSubSpinner) createSubSpinner.classList.add('hidden');
                }
            });
        }

        // =====================================================================
        // Gestion Modale Statut Abonnement
        // =====================================================================
        function openStatusModal(sub) {
            if (!statusModal) return;
            statusModalId.value = sub.id;
            statusModalResto.textContent = (sub.restaurant ? sub.restaurant.name : 'Restaurant');
            statusModalPlan.textContent = 'Formule : ' + (sub.plan ? sub.plan.name : 'N/A') + ' (Statut actuel : ' + sub.status + ')';
            statusModalTarget.value = sub.status;
            statusModalReason.value = '';
            if (statusModalError) statusModalError.classList.add('hidden');

            statusModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeStatusModal() {
            if (!statusModal) return;
            statusModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            if (btnSubmitStatusModal) btnSubmitStatusModal.disabled = false;
            if (statusSubmitSpinner) statusSubmitSpinner.classList.add('hidden');
        }

        if (btnCloseStatusModal) btnCloseStatusModal.addEventListener('click', closeStatusModal);
        if (btnCancelStatusModal) btnCancelStatusModal.addEventListener('click', closeStatusModal);
        if (statusBackdrop) statusBackdrop.addEventListener('click', closeStatusModal);

        if (statusForm) {
            statusForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                var subId = statusModalId.value;
                var targetStatus = statusModalTarget.value;
                var reason = statusModalReason.value.trim();

                if ((targetStatus === 'suspended' || targetStatus === 'cancelled') && !reason) {
                    if (statusModalError) {
                        statusModalError.textContent = 'Le motif est obligatoire pour suspendre ou résilier un abonnement.';
                        statusModalError.classList.remove('hidden');
                    }
                    return;
                }

                if (btnSubmitStatusModal) btnSubmitStatusModal.disabled = true;
                if (statusSubmitSpinner) statusSubmitSpinner.classList.remove('hidden');

                try {
                    var response = await fetch('/api/v1/admin/subscriptions/' + subId + '/status', {
                        method: 'PATCH',
                        headers: Object.assign({ 'Content-Type': 'application/json' }, getAuthHeaders()),
                        credentials: 'same-origin',
                        body: JSON.stringify({ status: targetStatus, reason: reason || null })
                    });

                    if (response.status === 422) {
                        var errData = await response.json();
                        var msg = errData && errData.message ? errData.message : 'Transition de statut refusée.';
                        if (statusModalError) {
                            statusModalError.textContent = msg;
                            statusModalError.classList.remove('hidden');
                        }
                        return;
                    }

                    if (!response.ok) {
                        alert('Erreur lors de la mise à jour (' + response.status + ').');
                        return;
                    }

                    closeStatusModal();
                    if (typeof window.showToast === 'function') {
                        window.showToast('Statut de l\'abonnement mis à jour !', 'success', 4000);
                    }
                    fetchSubscriptions();

                } catch (err) {
                    console.error('Erreur PATCH subscription status:', err);
                    alert('Erreur réseau.');
                } finally {
                    if (btnSubmitStatusModal) btnSubmitStatusModal.disabled = false;
                    if (statusSubmitSpinner) statusSubmitSpinner.classList.add('hidden');
                }
            });
        }

        // =====================================================================
        // Gestion Modale Enregistrement Paiement
        // =====================================================================
        function openPaymentModal(sub) {
            if (!paymentModal) return;
            paymentModalId.value = sub.id;
            paymentModalResto.textContent = (sub.restaurant ? sub.restaurant.name : 'Restaurant');
            var plan = sub.plan || {};
            paymentModalPlan.textContent = (plan.name || 'Formule') + ' — ' + (plan.price_minor ? (plan.price_minor / 100).toLocaleString('fr-FR') : '0') + ' ' + (plan.currency || 'XOF');
            paymentModalAmount.value = plan.price_minor ? (plan.price_minor / 100) : 35000;
            paymentModalCurrency.value = (plan.currency || 'XOF').toUpperCase();
            paymentModalRef.value = 'REG-' + new Date().getFullYear() + '-' + Math.floor(Math.random() * 89999 + 10000);
            if (paymentModalError) paymentModalError.classList.add('hidden');

            paymentModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closePaymentModal() {
            if (!paymentModal) return;
            paymentModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            if (btnSubmitPaymentModal) btnSubmitPaymentModal.disabled = false;
            if (paymentSubmitSpinner) paymentSubmitSpinner.classList.add('hidden');
        }

        if (btnClosePaymentModal) btnClosePaymentModal.addEventListener('click', closePaymentModal);
        if (btnCancelPaymentModal) btnCancelPaymentModal.addEventListener('click', closePaymentModal);
        if (paymentBackdrop) paymentBackdrop.addEventListener('click', closePaymentModal);

        if (paymentForm) {
            paymentForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                var subId = paymentModalId.value;
                var amountVal = parseFloat(paymentModalAmount.value);
                var currency = paymentModalCurrency.value;
                var method = paymentModalMethod.value;
                var ref = paymentModalRef.value.trim();
                var status = paymentModalStatus.value;

                if (isNaN(amountVal) || amountVal <= 0) {
                    alert('Veuillez saisir un montant valide.');
                    return;
                }

                var payload = {
                    amount_minor: Math.round(amountVal * 100),
                    currency: currency.toLowerCase(),
                    method: method,
                    transaction_reference: ref || null,
                    idempotency_key: 'PAY-SUB-' + subId + '-' + Date.now() + '-' + Math.floor(Math.random() * 1000),
                    status: status,
                    paid_at: new Date().toISOString()
                };

                if (btnSubmitPaymentModal) btnSubmitPaymentModal.disabled = true;
                if (paymentSubmitSpinner) paymentSubmitSpinner.classList.remove('hidden');

                try {
                    var response = await fetch('/api/v1/admin/subscriptions/' + subId + '/payments', {
                        method: 'POST',
                        headers: Object.assign({ 'Content-Type': 'application/json' }, getAuthHeaders()),
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });

                    if (response.status === 422) {
                        var errData = await response.json();
                        var msg = errData && errData.message ? errData.message : 'Erreur d\'enregistrement du règlement.';
                        if (paymentModalError) {
                            paymentModalError.textContent = msg;
                            paymentModalError.classList.remove('hidden');
                        }
                        return;
                    }

                    if (!response.ok) {
                        alert('Erreur serveur lors de l\'enregistrement du règlement (' + response.status + ').');
                        return;
                    }

                    closePaymentModal();
                    if (typeof window.showToast === 'function') {
                        window.showToast('Règlement enregistré avec succès !', 'success', 4000);
                    }
                    fetchSubscriptions();

                } catch (err) {
                    console.error('Erreur POST subscription payment:', err);
                    alert('Erreur réseau.');
                } finally {
                    if (btnSubmitPaymentModal) btnSubmitPaymentModal.disabled = false;
                    if (paymentSubmitSpinner) paymentSubmitSpinner.classList.add('hidden');
                }
            });
        }

        // =====================================================================
        // Gestion Modale Détails & Historique
        // =====================================================================
        async function openDetailsModal(sub) {
            if (!detailsModal) return;

            var resto = sub.restaurant || {};
            var plan = sub.plan || {};

            if (detailsSubtitle) detailsSubtitle.textContent = (resto.name || 'Restaurant') + ' — ' + (plan.name || 'Formule');
            if (detailsStatus) detailsStatus.innerHTML = getStatusBadge(sub.status);
            if (detailsPlan) detailsPlan.textContent = (plan.name || 'N/A') + ' (' + (plan.price_minor ? (plan.price_minor / 100).toLocaleString('fr-FR') : '0') + ' ' + (plan.currency || 'XOF') + ')';
            if (detailsStarts) detailsStarts.textContent = formatDate(sub.starts_at);
            if (detailsEnds) detailsEnds.textContent = formatDate(sub.ends_at);

            detailsModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            // Chargement complet du contrat avec historique
            try {
                var res = await fetch('/api/v1/admin/subscriptions/' + sub.id, {
                    method: 'GET',
                    headers: getAuthHeaders(),
                    credentials: 'same-origin'
                });

                if (res.ok) {
                    var fullSub = (await res.json()).data;
                    renderDetailsContent(fullSub);
                } else {
                    renderDetailsContent(sub);
                }
            } catch (e) {
                renderDetailsContent(sub);
            }
        }

        function renderDetailsContent(sub) {
            var payments = sub.payments || [];
            var histories = sub.status_history || [];

            if (detailsPaymentsCount) detailsPaymentsCount.textContent = payments.length + ' règlement(s)';

            if (detailsPaymentsList) {
                detailsPaymentsList.innerHTML = '';
                if (payments.length === 0) {
                    detailsPaymentsList.innerHTML = '<p class="text-xs text-gray-400 italic py-2">Aucun règlement enregistré pour le moment.</p>';
                } else {
                    payments.forEach(function (p) {
                        var div = document.createElement('div');
                        div.className = 'p-2.5 rounded-brand bg-gray-50 dark:bg-brand-dark/50 border border-gray-100 dark:border-brand-dark-border flex items-center justify-between text-xs';
                        var amount = (p.amount_minor / 100).toLocaleString('fr-FR') + ' ' + (p.currency || 'XOF').toUpperCase();
                        div.innerHTML = '<div>' +
                            '<span class="font-bold text-brand-text dark:text-white">' + amount + '</span>' +
                            '<span class="text-[11px] text-gray-400 block">' + (p.method || 'Moyen') + ' — Ref: ' + (p.transaction_reference || 'N/A') + '</span>' +
                        '</div>' +
                        '<div class="text-right">' +
                            '<span class="px-2 py-0.5 rounded text-[10px] font-bold ' + (p.status === 'succeeded' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-gray-100 text-gray-700') + '">' + p.status + '</span>' +
                            '<span class="text-[10px] text-gray-400 block mt-0.5">' + formatDate(p.created_at) + '</span>' +
                        '</div>';
                        detailsPaymentsList.appendChild(div);
                    });
                }
            }

            if (detailsHistoryList) {
                detailsHistoryList.innerHTML = '';
                if (histories.length === 0) {
                    detailsHistoryList.innerHTML = '<p class="text-xs text-gray-400 italic py-2">Aucune transition enregistrée.</p>';
                } else {
                    histories.forEach(function (h) {
                        var div = document.createElement('div');
                        div.className = 'p-2.5 rounded-brand bg-gray-50 dark:bg-brand-dark/50 border border-gray-100 dark:border-brand-dark-border text-xs space-y-1';
                        var actorName = h.actor ? h.actor.name : 'Système';
                        div.innerHTML = '<div class="flex items-center justify-between">' +
                            '<span class="font-bold text-brand-text dark:text-white">' + (h.from_status || 'Création') + ' &rarr; <span class="text-brand-orange">' + h.to_status + '</span></span>' +
                            '<span class="text-[10px] text-gray-400">' + formatDate(h.created_at) + ' par ' + escapeHtml(actorName) + '</span>' +
                        '</div>' +
                        (h.reason ? '<p class="text-[11px] text-gray-500 italic">« ' + escapeHtml(h.reason) + ' »</p>' : '');
                        detailsHistoryList.appendChild(div);
                    });
                }
            }
        }

        function closeDetailsModal() {
            if (!detailsModal) return;
            detailsModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        if (btnCloseDetailsModal) btnCloseDetailsModal.addEventListener('click', closeDetailsModal);
        if (btnDismissDetailsModal) btnDismissDetailsModal.addEventListener('click', closeDetailsModal);
        if (detailsBackdrop) detailsBackdrop.addEventListener('click', closeDetailsModal);

        // =====================================================================
        // Filtres & Recherche
        // =====================================================================
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var val = this.value.trim();
                if (searchClear) searchClear.classList.toggle('hidden', val.length === 0);

                if (state.searchTimer) clearTimeout(state.searchTimer);
                state.searchTimer = setTimeout(function () {
                    state.search = val;
                    state.page = 1;
                    fetchSubscriptions();
                }, 300);
            });
        }

        if (searchClear) {
            searchClear.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                searchClear.classList.add('hidden');
                state.search = '';
                state.page = 1;
                fetchSubscriptions();
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', function () {
                state.status = this.value;
                state.page = 1;
                fetchSubscriptions();
            });
        }

        if (planFilter) {
            planFilter.addEventListener('change', function () {
                state.plan_id = this.value;
                state.page = 1;
                fetchSubscriptions();
            });
        }

        function resetAllFilters() {
            if (searchInput) searchInput.value = '';
            if (searchClear) searchClear.classList.add('hidden');
            if (statusFilter) statusFilter.value = '';
            if (planFilter) planFilter.value = '';
            state.search = '';
            state.status = '';
            state.plan_id = '';
            state.page = 1;
            fetchSubscriptions();
        }

        if (resetFiltersBtn) resetFiltersBtn.addEventListener('click', resetAllFilters);
        if (emptyResetBtn) emptyResetBtn.addEventListener('click', resetAllFilters);
        if (retryBtn) retryBtn.addEventListener('click', fetchSubscriptions);
        if (refreshBtn) refreshBtn.addEventListener('click', fetchSubscriptions);

        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', function () {
                if (state.page > 1) {
                    state.page--;
                    fetchSubscriptions();
                }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', function () {
                state.page++;
                fetchSubscriptions();
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (createSubModal && !createSubModal.classList.contains('hidden')) closeCreateSubModal();
                if (statusModal && !statusModal.classList.contains('hidden')) closeStatusModal();
                if (paymentModal && !paymentModal.classList.contains('hidden')) closePaymentModal();
                if (detailsModal && !detailsModal.classList.contains('hidden')) closeDetailsModal();
            }
        });

        // Chargement initial
        fetchSubscriptions();
    }
});

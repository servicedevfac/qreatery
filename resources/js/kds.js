import { createRealtimeClient } from './realtime.js';

(function () {
    'use strict';

    // État local du KDS
    const state = {
        restaurantId: null,
        restaurantSlug: null,
        restaurants: [],
        orders: [],
        filterMode: 'all', // 'all' | 'dine_in' | 'takeaway'
        isFetching: false,
        isDegraded: false,
        pollingInterval: null,
        timerInterval: null,
        soundEnabled: localStorage.getItem('app_restaurant_kds_sound') !== 'false',
        knownPendingOrderIds: new Set(),
        handledAssistanceIds: new Set(),
        audioContext: null,
        realtimeClient: null,
        realtimeSub: null,
    };

    // Formatage monétaire
    function formatMoney(amountMinor, currency) {
        currency = currency || 'XOF';
        const units = (amountMinor || 0) / 100;
        const formatted = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(units);
        return currency === 'XOF' ? `${formatted} FCFA` : `${formatted} ${currency}`;
    }

    // Calcul du temps écoulé en minutes
    function getElapsedMinutes(isoDateString) {
        if (!isoDateString) return 0;
        const created = new Date(isoDateString).getTime();
        const now = Date.now();
        const diffMs = Math.max(0, now - created);
        return Math.floor(diffMs / 60000);
    }

    // Détermination de la sévérité du retard (Codes couleur conformes EF-Cmd-04)
    function getDelaySeverity(elapsedMinutes) {
        if (elapsedMinutes < 15) {
            return {
                label: `${elapsedMinutes}m`,
                badgeClass: 'bg-emerald-950/60 border-emerald-700/60 text-emerald-400',
                cardBorder: 'border-brand-dark-border',
                isUrgent: false,
            };
        }
        if (elapsedMinutes < 30) {
            return {
                label: `${elapsedMinutes}m`,
                badgeClass: 'bg-amber-950/60 border-amber-600/70 text-amber-400',
                cardBorder: 'border-amber-700/40',
                isUrgent: false,
            };
        }
        if (elapsedMinutes <= 45) {
            return {
                label: `⚠️ ${elapsedMinutes}m`,
                badgeClass: 'bg-orange-950/80 border-orange-500 text-orange-300 font-bold',
                cardBorder: 'border-orange-500/70',
                isUrgent: false,
            };
        }
        // Retard critique > 45 minutes (Rouge clignotant EF-Cmd-04)
        return {
            label: `🚨 RETARD (${elapsedMinutes}m)`,
            badgeClass: 'bg-rose-950 border-rose-500 text-rose-300 font-black animate-pulse shadow-sm',
            cardBorder: 'border-rose-500 shadow-rose-950/40 shadow-lg',
            isUrgent: true,
        };
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function () {
        initKds();
        bindEvents();
    });

    async function initKds() {
        showLoading(true);

        // Détection des paramètres d'URL
        const urlParams = new URLSearchParams(window.location.search);
        state.restaurantId = urlParams.get('restaurant_id') || null;
        state.restaurantSlug = urlParams.get('restaurant') || 'restaurant-demo';

        // 1. Tenter de charger les restaurants de l'utilisateur pour vérifier la session
        const authOk = await resolveUserRestaurants();

        if (!authOk) {
            showLoading(false);
            window.openModal('kds-login-modal');
            return;
        }

        // 2. Charger les commandes initiales
        await fetchOrders();

        // 3. Initialiser le temps réel Reverb (WebSocket) et la résilience REC-13
        initRealtime();
        startTimerTicker();

        showLoading(false);
    }

    function bindEvents() {
        // Bouton rafraîchissement manuel
        const refreshBtn = document.getElementById('btn-kds-refresh');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', async function () {
                refreshBtn.disabled = true;
                const spinner = document.getElementById('kds-refresh-spinner');
                if (spinner) spinner.classList.add('animate-spin');
                await fetchOrders();
                refreshBtn.disabled = false;
                if (spinner) spinner.classList.remove('animate-spin');
            });
        }

        // Filtre de mode (Tous / Sur place / À emporter)
        document.querySelectorAll('.kds-filter-mode').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.kds-filter-mode').forEach(b => {
                    b.classList.remove('active', 'bg-brand-orange', 'text-white');
                    b.classList.add('text-gray-400');
                });
                btn.classList.add('active', 'bg-brand-orange', 'text-white');
                btn.classList.remove('text-gray-400');
                state.filterMode = btn.getAttribute('data-mode') || 'all';
                renderBoard();
            });
        });

        // Sélecteur de restaurant
        const select = document.getElementById('kds-restaurant-select');
        if (select) {
            select.addEventListener('change', function () {
                state.restaurantId = this.value;
                const matched = state.restaurants.find(r => String(r.id) === String(state.restaurantId));
                if (matched) state.restaurantSlug = matched.slug;
                initRealtime();
                fetchOrders();
            });
        }

        // Bascule de son
        const soundBtn = document.getElementById('kds-audio-toggle');
        if (soundBtn) {
            updateAudioButtonUi();
            soundBtn.addEventListener('click', function () {
                state.soundEnabled = !state.soundEnabled;
                localStorage.setItem('app_restaurant_kds_sound', String(state.soundEnabled));
                updateAudioButtonUi();
                if (state.soundEnabled) playChime();
            });
        }

        // Formulaire de connexion KDS
        const loginForm = document.getElementById('kds-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', handleLoginSubmit);
        }

        const kdsQuickOwnerBtn = document.getElementById('btn-kds-quick-login-owner');
        if (kdsQuickOwnerBtn) {
            kdsQuickOwnerBtn.addEventListener('click', function () {
                const emailInput = document.getElementById('kds-auth-email');
                const passInput = document.getElementById('kds-auth-password');
                if (emailInput) emailInput.value = 'owner@qreatery.test';
                if (passInput) passInput.value = 'Password123!';
                if (loginForm) {
                    loginForm.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            });
        }

        // Formulaire de refus / annulation
        const rejectForm = document.getElementById('kds-reject-form');
        if (rejectForm) {
            rejectForm.addEventListener('submit', handleRejectSubmit);
        }

        // Preset de motif de refus
        const presetSelect = document.getElementById('kds-reject-reason-preset');
        const reasonTextarea = document.getElementById('kds-reject-reason');
        if (presetSelect && reasonTextarea) {
            presetSelect.addEventListener('change', function () {
                if (this.value && this.value !== 'Autre raison') {
                    reasonTextarea.value = this.value;
                }
            });
        }

        // Réessayer en cas d'erreur
        const retryBtn = document.getElementById('btn-kds-error-retry');
        if (retryBtn) {
            retryBtn.addEventListener('click', () => fetchOrders());
        }

        // Gestion de la visibilité de l'onglet (ralentir le polling si masqué)
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                // Ralentir à 30 secondes
                startPolling(30000);
            } else {
                // Réactiver à 10 secondes et actualiser immédiatement
                startPolling(10000);
                fetchOrders();
            }
        });
    }

    // Résolution des restaurants autorisés de l'utilisateur
    async function resolveUserRestaurants() {
        try {
            const res = await fetch('/api/v1/restaurants', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (res.status === 401) {
                return false;
            }

            if (!res.ok) {
                return false;
            }

            const json = await res.json();
            state.restaurants = json.data || [];

            if (state.restaurants.length === 0) {
                showError('Aucun restaurant actif n\'est associé à votre compte.');
                return false;
            }

            // Sélectionner le restaurant
            let selected = null;
            if (state.restaurantId) {
                selected = state.restaurants.find(r => String(r.id) === String(state.restaurantId));
            }
            if (!selected && state.restaurantSlug) {
                selected = state.restaurants.find(r => r.slug === state.restaurantSlug);
            }
            if (!selected) {
                selected = state.restaurants[0];
            }

            state.restaurantId = selected.id;
            state.restaurantSlug = selected.slug;

            // Remplir le select si plusieurs restaurants
            const selectContainer = document.getElementById('kds-restaurant-selector-container');
            const selectEl = document.getElementById('kds-restaurant-select');
            if (state.restaurants.length > 1 && selectContainer && selectEl) {
                selectEl.innerHTML = state.restaurants.map(r => `
                    <option value="${r.id}" ${r.id === state.restaurantId ? 'selected' : ''}>
                        ${escapeHtml(r.name)}
                    </option>
                `).join('');
                selectContainer.classList.remove('hidden');
                selectContainer.classList.add('flex');
            }

            return true;
        } catch (error) {
            console.warn('Erreur résolution restaurants:', error);
            return false;
        }
    }

    // Récupération des commandes en cuisine via GET /api/v1/restaurants/{id}/orders
    async function fetchOrders() {
        if (!state.restaurantId || state.isFetching) return;
        state.isFetching = true;

        const errorBanner = document.getElementById('kds-error-banner');
        const connLabel = document.getElementById('kds-connection-label');
        const connBadge = document.getElementById('kds-connection-badge');

        try {
            const response = await fetch(`/api/v1/restaurants/${encodeURIComponent(state.restaurantId)}/orders`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (response.status === 401) {
                window.openModal('kds-login-modal');
                state.isFetching = false;
                return;
            }

            if (!response.ok) {
                if (errorBanner) {
                    errorBanner.classList.remove('hidden');
                    document.getElementById('kds-error-message').textContent =
                        `Erreur ${response.status} lors de la récupération des commandes.`;
                }
                if (connLabel) connLabel.textContent = 'Connexion interrompue';
                if (connBadge) {
                    connBadge.className = 'flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-950/60 border border-rose-800 text-rose-400 text-xs font-semibold';
                }
                state.isFetching = false;
                return;
            }

            if (errorBanner) errorBanner.classList.add('hidden');
            if (connLabel) connLabel.textContent = 'Temps réel actif';
            if (connBadge) {
                connBadge.className = 'flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-950/60 border border-emerald-800 text-emerald-400 text-xs font-semibold';
            }

            const json = await response.json();
            const fetchedOrders = json.data || [];

            // Détection de nouvelles commandes 'pending' pour alerte sonore
            let hasNewPending = false;
            fetchedOrders.forEach(o => {
                if (o.preparation_status === 'pending') {
                    if (!state.knownPendingOrderIds.has(o.id)) {
                        state.knownPendingOrderIds.add(o.id);
                        hasNewPending = true;
                    }
                }
            });

            if (hasNewPending && state.soundEnabled) {
                playChime();
            }

            state.orders = fetchedOrders;
            state.lastSyncTime = new Date();

            updateLastSyncUi();
            renderBoard();
        } catch (error) {
            if (errorBanner) {
                errorBanner.classList.remove('hidden');
                document.getElementById('kds-error-message').textContent =
                    'Impossible de joindre le serveur. Nouvelle tentative dans quelques instants.';
            }
            if (connLabel) connLabel.textContent = 'Hors ligne';
            if (connBadge) {
                connBadge.className = 'flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-950/60 border border-rose-800 text-rose-400 text-xs font-semibold';
            }
        } finally {
            state.isFetching = false;
        }
    }

    // Rendu des 3 colonnes Kanban
    function renderBoard() {
        const colPending = document.getElementById('kds-col-pending');
        const colPreparing = document.getElementById('kds-col-preparing');
        const colReady = document.getElementById('kds-col-ready');

        if (!colPending || !colPreparing || !colReady) return;

        // Vider les colonnes
        colPending.innerHTML = '';
        colPreparing.innerHTML = '';
        colReady.innerHTML = '';

        // Filtrage selon le mode sélectionné (Sur place / À emporter)
        const visibleOrders = state.orders.filter(o => {
            if (state.filterMode !== 'all' && o.mode !== state.filterMode) {
                return false;
            }
            return true;
        });

        // Répartition dans les colonnes
        const pendingList = visibleOrders.filter(o => o.preparation_status === 'pending');
        const preparingList = visibleOrders.filter(o => ['accepted', 'preparing'].includes(o.preparation_status));
        const readyList = visibleOrders.filter(o => o.preparation_status === 'ready');

        // Mise à jour des compteurs
        updateMetric('kds-metric-total', pendingList.length + preparingList.length + readyList.length);
        updateMetric('kds-metric-pending', pendingList.length);
        updateMetric('kds-metric-preparing', preparingList.length);
        updateMetric('kds-metric-ready', readyList.length);

        updateMetric('col-count-pending', pendingList.length);
        updateMetric('col-count-preparing', preparingList.length);
        updateMetric('col-count-ready', readyList.length);

        // Rendu Colonne 1 : En attente
        if (pendingList.length === 0) {
            colPending.innerHTML = renderEmptyColumnState('Aucune commande en attente');
        } else {
            pendingList.forEach(order => colPending.appendChild(createOrderCard(order)));
        }

        // Rendu Colonne 2 : En préparation
        if (preparingList.length === 0) {
            colPreparing.innerHTML = renderEmptyColumnState('Aucune commande en préparation');
        } else {
            preparingList.forEach(order => colPreparing.appendChild(createOrderCard(order)));
        }

        // Rendu Colonne 3 : Prêtes
        if (readyList.length === 0) {
            colReady.innerHTML = renderEmptyColumnState('Aucune commande prête');
        } else {
            readyList.forEach(order => colReady.appendChild(createOrderCard(order)));
        }
    }

    // Création du DOM pour un ticket de commande
    function createOrderCard(order) {
        const elapsed = getElapsedMinutes(order.created_at);
        const delayInfo = getDelaySeverity(elapsed);

        const card = document.createElement('article');
        card.className = `p-3.5 rounded-brand-lg bg-brand-dark border ${delayInfo.cardBorder} flex flex-col justify-between gap-3 transition-all hover:border-brand-orange/60 shadow-xs relative`;
        card.setAttribute('data-order-id', order.id);

        const isTakeaway = order.mode === 'takeaway';
        const modeBadge = isTakeaway
            ? '<span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-950/60 text-purple-300 border border-purple-800">🛍️ À emporter</span>'
            : `<span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-blue-950/60 text-blue-300 border border-blue-800">🍽️ Table ${escapeHtml(order.table?.label || order.table?.id || '--')}</span>`;

        // Articles
        const itemsHtml = (order.items || []).map(item => {
            const options = (item.options || []).map(o => escapeHtml(o.option_name || o.name)).join(', ');
            return `
                <li class="py-1 border-b border-gray-800/80 last:border-0 text-xs">
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-bold text-white leading-tight">
                            <span class="inline-block px-1.5 py-0.5 rounded bg-brand-dark-card border border-brand-dark-border text-brand-orange font-black text-[11px] mr-1">
                                ${item.quantity}x
                            </span>
                            ${escapeHtml(item.product_name)}
                        </span>
                    </div>
                    ${item.variant_name ? `<span class="text-[10px] font-semibold text-brand-orange block pl-6">${escapeHtml(item.variant_name)}</span>` : ''}
                    ${options ? `<span class="text-[10px] text-gray-400 block pl-6 leading-tight">+ ${options}</span>` : ''}
                    ${item.instructions ? `<span class="text-[10px] text-amber-400 italic block pl-6 mt-0.5">Note: ${escapeHtml(item.instructions)}</span>` : ''}
                </li>
            `;
        }).join('');

        // Boutons d'action selon le statut
        const actionsHtml = renderOrderActions(order);

        card.innerHTML = `
            <!-- En-tête ticket -->
            <div class="flex items-center justify-between gap-2 border-b border-gray-800 pb-2">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-black text-white font-mono tracking-tight">
                        #${escapeHtml(order.number || order.id)}
                    </h3>
                    ${modeBadge}
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold border ${delayInfo.badgeClass}">
                        ${delayInfo.label}
                    </span>
                    <button type="button" class="btn-order-detail p-1 text-gray-400 hover:text-white" title="Voir le détail complet" aria-label="Détail commande #${order.id}">
                        ℹ️
                    </button>
                </div>
            </div>

            <!-- Notes de commande globale -->
            ${order.notes ? `
                <div class="px-2.5 py-1.5 rounded bg-amber-950/40 border border-amber-900/60 text-[11px] text-amber-200 italic leading-snug">
                    💬 ${escapeHtml(order.notes)}
                </div>
            ` : ''}

            <!-- Liste des articles commandés -->
            <ul class="space-y-0.5 max-h-48 overflow-y-auto pr-1">
                ${itemsHtml}
            </ul>

            <!-- Barre d'actions tactiles KDS -->
            <div class="pt-2 border-t border-gray-800/80 flex items-center gap-2">
                ${actionsHtml}
            </div>
        `;

        // Écouteur pour détail modal
        const detailBtn = card.querySelector('.btn-order-detail');
        if (detailBtn) {
            detailBtn.addEventListener('click', () => openOrderDetailModal(order));
        }

        // Écouteurs pour transitions de statut
        card.querySelectorAll('[data-transition-status]').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetStatus = this.getAttribute('data-transition-status');
                if (targetStatus === 'rejected' || targetStatus === 'cancelled') {
                    openRejectModal(order, targetStatus);
                } else {
                    executeStatusTransition(order, targetStatus, null, btn, card);
                }
            });
        });

        return card;
    }

    // Rendu des boutons d'action KDS selon la colonne
    function renderOrderActions(order) {
        const s = order.preparation_status;

        if (s === 'pending') {
            return `
                <button
                    type="button"
                    class="flex-1 touch-target py-2 px-3 rounded-brand bg-brand-orange hover:bg-brand-orange-dark text-white font-black text-xs uppercase tracking-wider transition-all shadow-xs focus-ring"
                    data-transition-status="accepted"
                >
                    Accepter
                </button>
                <button
                    type="button"
                    class="touch-target py-2 px-3 rounded-brand bg-gray-800 hover:bg-rose-950 border border-gray-700 hover:border-rose-800 text-gray-300 hover:text-rose-300 font-bold text-xs transition-all focus-ring"
                    data-transition-status="rejected"
                    title="Refuser la commande avec motif"
                >
                    Refuser
                </button>
            `;
        }

        if (s === 'accepted') {
            return `
                <button
                    type="button"
                    class="flex-1 touch-target py-2 px-3 rounded-brand bg-amber-600 hover:bg-amber-700 text-white font-black text-xs uppercase tracking-wider transition-all shadow-xs focus-ring"
                    data-transition-status="preparing"
                >
                    Démarrer Prépa
                </button>
                <button
                    type="button"
                    class="touch-target py-2 px-2.5 rounded-brand bg-gray-800 hover:bg-rose-950 text-gray-400 hover:text-rose-300 text-xs transition-all"
                    data-transition-status="cancelled"
                    title="Annuler avec motif"
                >
                    ✕
                </button>
            `;
        }

        if (s === 'preparing') {
            return `
                <button
                    type="button"
                    class="flex-1 touch-target py-2 px-3 rounded-brand bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider transition-all shadow-xs focus-ring"
                    data-transition-status="ready"
                >
                    ✓ Prête au passe
                </button>
                <button
                    type="button"
                    class="touch-target py-2 px-2.5 rounded-brand bg-gray-800 hover:bg-rose-950 text-gray-400 hover:text-rose-300 text-xs transition-all"
                    data-transition-status="cancelled"
                    title="Annuler avec motif"
                >
                    ✕
                </button>
            `;
        }

        if (s === 'ready') {
            return `
                <button
                    type="button"
                    class="flex-1 touch-target py-2 px-3 rounded-brand bg-brand-dark-card hover:bg-gray-800 border border-emerald-500/50 text-emerald-300 font-black text-xs uppercase tracking-wider transition-all shadow-xs focus-ring"
                    data-transition-status="served"
                >
                    Remise / Servie
                </button>
                <button
                    type="button"
                    class="touch-target py-2 px-3 rounded-brand bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold text-xs transition-all"
                    data-transition-status="completed"
                    title="Clôturer la commande"
                >
                    Terminer
                </button>
            `;
        }

        return '';
    }

    // Exécution d'une transition de statut via PATCH /api/v1/restaurants/{id}/orders/{orderId}/status
    async function executeStatusTransition(order, targetStatus, reason, triggerBtn, cardEl) {
        if (!state.restaurantId) return;

        // Verrouillage de la carte et du bouton (prévention des doubles clics)
        if (triggerBtn) {
            triggerBtn.disabled = true;
            triggerBtn.dataset.originalText = triggerBtn.textContent;
            triggerBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white inline-block" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                En cours...
            `;
        }
        if (cardEl) {
            cardEl.classList.add('opacity-70', 'pointer-events-none');
        }

        const payload = {
            status: targetStatus,
        };
        if (reason) {
            payload.reason = reason;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch(`/api/v1/restaurants/${encodeURIComponent(state.restaurantId)}/orders/${encodeURIComponent(order.id)}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                let errorMsg = data.message || `Impossible de passer la commande à l'état ${targetStatus}.`;
                if (data.errors && data.errors.status) {
                    errorMsg = data.errors.status.join(' ');
                }
                window.showToast(errorMsg, 'error', 4000);

                // Recharger pour resynchroniser l'état réel
                await fetchOrders();
                return;
            }

            const updatedOrder = (await res.json()).data;
            window.showToast(`Commande #${updatedOrder.number || updatedOrder.id} : ${targetStatus}`, 'success', 2500);

            // Recharger la liste depuis le serveur
            await fetchOrders();
        } catch (error) {
            window.showToast('Erreur réseau lors du changement d\'état.', 'error', 3000);
            await fetchOrders();
        } finally {
            if (triggerBtn) triggerBtn.disabled = false;
            if (cardEl) cardEl.classList.remove('opacity-70', 'pointer-events-none');
        }
    }

    // Ouverture de la modale de refus/annulation
    function openRejectModal(order, targetStatus) {
        document.getElementById('kds-reject-order-id').value = order.id;
        document.getElementById('kds-reject-target-status').value = targetStatus;
        document.getElementById('kds-reject-order-number').textContent = `#${order.number || order.id}`;
        document.getElementById('kds-reject-reason').value = '';
        document.getElementById('kds-reject-reason-preset').value = '';
        document.getElementById('kds-reject-error')?.classList.add('hidden');

        window.openModal('kds-reject-modal');
    }

    // Traitement du refus avec motif
    async function handleRejectSubmit(e) {
        e.preventDefault();
        const orderId = document.getElementById('kds-reject-order-id').value;
        const targetStatus = document.getElementById('kds-reject-target-status').value;
        const reason = (document.getElementById('kds-reject-reason').value || '').trim();
        const errorBox = document.getElementById('kds-reject-error');
        const submitBtn = document.getElementById('btn-kds-reject-confirm');

        if (!reason) {
            if (errorBox) {
                errorBox.textContent = 'Le motif est obligatoire pour refuser ou annuler une commande.';
                errorBox.classList.remove('hidden');
            }
            return;
        }

        const order = state.orders.find(o => String(o.id) === String(orderId));
        if (!order) {
            window.closeModal('kds-reject-modal');
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Validation...';
        }

        await executeStatusTransition(order, targetStatus, reason, submitBtn, null);
        window.closeModal('kds-reject-modal');

        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirmer l\'interruption';
        }
    }

    // Détail complet d'une commande
    function openOrderDetailModal(order) {
        document.getElementById('kds-detail-number').textContent = `#${order.number || order.id}`;
        document.getElementById('kds-detail-status-badge').textContent = order.preparation_status;
        document.getElementById('kds-detail-mode').textContent = order.mode === 'takeaway' ? '🛍️ À emporter' : '🍽️ Sur place';
        document.getElementById('kds-detail-table').textContent = order.table ? `Table ${order.table.label || order.table.id}` : 'Sans table';

        const createdDate = order.created_at ? new Date(order.created_at) : null;
        document.getElementById('kds-detail-time-created').textContent = createdDate
            ? `Reçue à : ${createdDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`
            : '--:--';

        const elapsed = getElapsedMinutes(order.created_at);
        document.getElementById('kds-detail-time-elapsed').textContent = `Temps écoulé : ${elapsed} min`;

        const notesBox = document.getElementById('kds-detail-notes-container');
        if (order.notes && notesBox) {
            document.getElementById('kds-detail-notes').textContent = order.notes;
            notesBox.classList.remove('hidden');
        } else if (notesBox) {
            notesBox.classList.add('hidden');
        }

        // Articles
        const itemsList = document.getElementById('kds-detail-items-list');
        if (itemsList) {
            itemsList.innerHTML = (order.items || []).map(item => {
                const options = (item.options || []).map(o => escapeHtml(o.option_name || o.name)).join(', ');
                return `
                    <div class="p-2.5 rounded bg-brand-dark border border-brand-dark-border text-xs flex items-start justify-between gap-2">
                        <div>
                            <span class="font-bold text-white">${item.quantity}x ${escapeHtml(item.product_name)}</span>
                            ${item.variant_name ? `<span class="text-brand-orange block text-[11px]">${escapeHtml(item.variant_name)}</span>` : ''}
                            ${options ? `<span class="text-gray-400 block text-[10px]">+ ${options}</span>` : ''}
                            ${item.instructions ? `<span class="text-amber-300 block text-[10px] italic mt-0.5">Note: ${escapeHtml(item.instructions)}</span>` : ''}
                        </div>
                        <span class="font-bold text-gray-300">${formatMoney(item.line_total_minor, order.currency)}</span>
                    </div>
                `;
            }).join('');
        }

        // Historique
        const historyList = document.getElementById('kds-detail-history-list');
        if (historyList) {
            const histories = order.status_history || [];
            if (histories.length === 0) {
                historyList.innerHTML = '<span class="text-gray-500 italic">Aucun historique d\'étape disponible</span>';
            } else {
                historyList.innerHTML = histories.map(h => `
                    <div class="flex items-center justify-between py-1 border-b border-gray-800 last:border-0">
                        <span class="text-gray-300 font-semibold">${h.from_status || 'création'} → <span class="text-brand-orange">${h.to_status}</span></span>
                        <span class="text-gray-500 text-[11px]">${h.created_at ? new Date(h.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''}</span>
                    </div>
                `).join('');
            }
        }

        window.openModal('kds-detail-modal');
    }

    // Gestion de la connexion KDS
    async function handleLoginSubmit(e) {
        e.preventDefault();
        const email = document.getElementById('kds-auth-email').value;
        const password = document.getElementById('kds-auth-password').value;
        const errorBox = document.getElementById('kds-login-error');
        const submitBtn = document.getElementById('btn-kds-login-submit');

        if (errorBox) errorBox.classList.add('hidden');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Connexion en cours...';
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch('/api/v1/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ email, password }),
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                if (errorBox) {
                    errorBox.textContent = data.message || 'Identifiants invalides ou compte inactif.';
                    errorBox.classList.remove('hidden');
                }
                return;
            }

            window.closeModal('kds-login-modal');
            window.showToast('Connexion réussie', 'success', 2000);

            // Relancer l'init KDS
            await initKds();
        } catch (error) {
            if (errorBox) {
                errorBox.textContent = 'Erreur réseau lors de la tentative de connexion.';
                errorBox.classList.remove('hidden');
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Connexion KDS';
            }
        }
    }

    // Son d'alerte KDS (Web Audio API synthétisé)
    function playChime() {
        try {
            if (!state.audioContext) {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                state.audioContext = new AudioCtx();
            }
            if (state.audioContext.state === 'suspended') {
                state.audioContext.resume();
            }

            const ctx = state.audioContext;
            const now = ctx.currentTime;

            // Deux bips harmonieux : D5 (587.33Hz) puis A5 (880Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, now);
            gain1.gain.setValueAtTime(0.25, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.3);

            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.15);
            gain2.gain.setValueAtTime(0.3, now + 0.15);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.15);
            osc2.stop(now + 0.6);
        } catch (e) {
            console.warn('Audio API non disponible ou bloquée:', e);
        }
    }

    function updateAudioButtonUi() {
        const label = document.getElementById('kds-audio-label');
        if (label) {
            label.textContent = state.soundEnabled ? 'Son activé' : 'Son coupé';
        }
    }

    function startPolling(intervalMs = 10000) {
        if (state.pollingInterval) clearInterval(state.pollingInterval);
        state.pollingInterval = setInterval(fetchOrders, intervalMs);
    }

    function stopPolling() {
        if (state.pollingInterval) {
            clearInterval(state.pollingInterval);
            state.pollingInterval = null;
        }
    }

    // ARB-02 / REC-13 : Initialisation du WebSocket et gestion de la résilience
    function initRealtime() {
        if (!state.restaurantId) return;

        if (state.realtimeClient) {
            state.realtimeClient.disconnect();
            state.realtimeClient = null;
        }

        const connLabel = document.getElementById('kds-connection-label');
        const connBadge = document.getElementById('kds-connection-badge');

        function setBadgeConnected() {
            if (connLabel) connLabel.textContent = 'Temps réel actif';
            if (connBadge) {
                connBadge.className = 'flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-950/60 border border-emerald-800 text-emerald-400 text-xs font-semibold';
            }
        }

        function setBadgeDegraded() {
            if (connLabel) connLabel.textContent = '⚠️ Secours actif (10s)';
            if (connBadge) {
                connBadge.className = 'flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-950/80 border border-amber-600 text-amber-300 text-xs font-bold animate-pulse';
            }
        }

        function setBadgeConnecting() {
            if (connLabel) connLabel.textContent = 'Connexion en cours...';
            if (connBadge) {
                connBadge.className = 'flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-900 border border-gray-700 text-gray-400 text-xs font-medium';
            }
        }

        state.realtimeClient = createRealtimeClient({
            onStateChange(st) {
                if (st === 'connected') {
                    setBadgeConnected();
                } else if (st === 'connecting') {
                    setBadgeConnecting();
                }
            },
            onDegraded(isDegraded) {
                state.isDegraded = isDegraded;
                if (isDegraded) {
                    setBadgeDegraded();
                    startPolling(10000);
                } else {
                    stopPolling();
                    setBadgeConnected();
                }
            },
            onReconnect() {
                // Arrêt immédiat du polling de secours et resynchronisation intégrale (REC-13)
                stopPolling();
                setBadgeConnected();
                fetchOrders();
            }
        });

        // Souscription au canal privé du restaurant
        const channelName = `private-restaurants.${state.restaurantId}`;
        state.realtimeSub = state.realtimeClient.subscribe(channelName, {
            'orders.created': handleRealtimeOrderCreated,
            'orders.status.updated': handleRealtimeOrderStatusUpdated,
            'assistance.requested': handleRealtimeAssistance,
        });
    }

    // ARB-01 / CA-01 : Création de commande dans le KDS
    async function handleRealtimeOrderCreated(data) {
        if (!data || !data.id) return;
        const orderId = data.id;

        try {
            // Appel REST unitaire pour obtenir les lignes et options complètes (ARB-01)
            const res = await fetch(`/api/v1/restaurants/${encodeURIComponent(state.restaurantId)}/orders/${encodeURIComponent(orderId)}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (!res.ok) return;

            const json = await res.json();
            const fullOrder = json.data;
            if (!fullOrder) return;

            // Déduplication & idempotence
            const existingIdx = state.orders.findIndex(o => o.id === fullOrder.id);
            if (existingIdx !== -1) {
                state.orders[existingIdx] = fullOrder;
            } else {
                state.orders.unshift(fullOrder);
            }

            if (fullOrder.preparation_status === 'pending') {
                if (!state.knownPendingOrderIds.has(fullOrder.id)) {
                    state.knownPendingOrderIds.add(fullOrder.id);
                    if (state.soundEnabled) playChime();
                }
            }

            renderBoard();
            window.showToast(`Nouvelle commande #${fullOrder.number || fullOrder.id} reçue !`, 'info', 4000);
        } catch (e) {
            console.warn('[KDS] Erreur récupération détail commande créée:', e);
        }
    }

    // CA-02 : Évolution des statuts dans le KDS
    async function handleRealtimeOrderStatusUpdated(data) {
        if (!data || !data.id || !data.to_status) return;

        const order = state.orders.find(o => o.id === data.id);
        if (order) {
            // Idempotence temporelle : vérifier updated_at si disponible
            if (data.updated_at && order.updated_at && new Date(data.updated_at) < new Date(order.updated_at)) {
                return;
            }

            order.preparation_status = data.to_status;
            if (data.accepted_at) order.accepted_at = data.accepted_at;
            if (data.ready_at) order.ready_at = data.ready_at;
            if (data.completed_at) order.completed_at = data.completed_at;
            if (data.cancelled_at) order.cancelled_at = data.cancelled_at;
            if (data.updated_at) order.updated_at = data.updated_at;

            renderBoard();
        } else if (['pending', 'accepted', 'preparing', 'ready'].includes(data.to_status)) {
            // Si la commande n'était pas affichée, la récupérer via REST
            try {
                const res = await fetch(`/api/v1/restaurants/${encodeURIComponent(state.restaurantId)}/orders/${encodeURIComponent(data.id)}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (res.ok) {
                    const json = await res.json();
                    if (json.data) {
                        state.orders.unshift(json.data);
                        renderBoard();
                    }
                }
            } catch (e) {}
        }
    }

    // CA-04 : Notification d'assistance en temps réel
    function handleRealtimeAssistance(data) {
        if (!data || !data.id) return;

        // Prévention des doublons
        if (state.handledAssistanceIds.has(data.id)) return;
        state.handledAssistanceIds.add(data.id);

        const tableText = data.restaurant_table_id ? `Table #${data.restaurant_table_id}` : 'Salle';
        const typeText = data.type === 'waiter' ? 'Serveur demandé' : 'Demande d\'assistance';

        window.showToast(`🔔 Appel : ${tableText} — ${typeText}`, 'warning', 8000);
        if (state.soundEnabled) playChime();
    }

    function startTimerTicker() {
        if (state.timerInterval) clearInterval(state.timerInterval);
        // Toutes les 30 secondes, recalculer les temps écoulés sur les cartes existantes
        state.timerInterval = setInterval(() => {
            renderBoard();
        }, 30000);
    }

    function updateLastSyncUi() {
        const syncEl = document.getElementById('kds-last-sync-time');
        if (syncEl && state.lastSyncTime) {
            syncEl.textContent = `Dernière synchro : ${state.lastSyncTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}`;
        }
    }

    function updateMetric(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function renderEmptyColumnState(message) {
        return `
            <div class="h-48 flex flex-col items-center justify-center text-center p-4 text-gray-500 border border-dashed border-gray-800 rounded-brand">
                <span class="text-2xl mb-1 select-none">🍳</span>
                <p class="text-xs font-semibold">${escapeHtml(message)}</p>
            </div>
        `;
    }

    function showLoading(show) {
        const overlay = document.getElementById('kds-loading-overlay');
        const board = document.getElementById('kds-kanban-board');
        if (overlay) overlay.classList.toggle('hidden', !show);
        if (board) board.classList.toggle('hidden', show);
    }

    function showError(message) {
        const banner = document.getElementById('kds-error-banner');
        const msgEl = document.getElementById('kds-error-message');
        if (banner) banner.classList.remove('hidden');
        if (msgEl) msgEl.textContent = message;
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
})();

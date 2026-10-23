import { createRealtimeClient } from './realtime.js';

(function () {
    'use strict';

    // État local de la caisse POS
    const state = {
        restaurantId: null,
        restaurantSlug: null,
        restaurant: null,
        restaurants: [],
        categories: [],
        products: [],
        tables: [], // Tables actives avec QR code: { id, label, capacity, qrToken }
        selectedTableQrToken: null,
        selectedTableLabel: null,
        activeCategory: 'all',
        searchTerm: '',
        activeTab: 'sale', // 'sale' | 'orders'
        serviceMode: 'takeaway', // 'takeaway' (Comptoir) | 'dine_in' (Sur place)
        selectedTableId: null,
        ticket: [], // Lignes du panier en cours
        currentCustomizingProduct: null,
        customizingQuantity: 1,
        activeOrderForPayment: null, // Commande en cours d'encaissement
        lastReceiptData: null, // Données pour l'impression du reçu
        ordersHistory: [], // Historique des commandes du jour
        ordersFilter: 'all', // 'all' | 'unpaid' | 'paid'
        isSubmitting: false,
        realtimeClient: null,
        realtimeSub: null,
        handledAssistanceIds: new Set(),
    };

    // Formatage monétaire
    function formatMoney(amountMinor, currency) {
        currency = currency || (state.restaurant ? state.restaurant.currency : 'XOF');
        const units = (amountMinor || 0) / 100;
        const formatted = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(units);
        return currency === 'XOF' ? `${formatted} FCFA` : `${formatted} ${currency}`;
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function () {
        initPos();
        bindEvents();
    });

    async function initPos() {
        showLoading(true);

        const urlParams = new URLSearchParams(window.location.search);
        state.restaurantId = urlParams.get('restaurant_id') || null;
        state.restaurantSlug = urlParams.get('restaurant') || 'restaurant-demo';

        // 1. Vérifier la session staff et récupérer les restaurants
        const authOk = await resolveUserRestaurants();
        if (!authOk) {
            showLoading(false);
            window.openModal('pos-login-modal');
            return;
        }

        // 2. Charger le catalogue (catégories, produits, paramètres)
        await loadCatalog();

        // 3. Charger les tables actives et leurs QR codes
        await loadActiveTables();

        // 4. Charger les commandes du jour pour le badge impayées
        await fetchOrdersHistory();

        // 5. Initialiser les notifications temps réel (Assistance & Commandes)
        initPosRealtime();

        showLoading(false);
    }

    function bindEvents() {
        // Bascule d'onglets principaux (Nouvelle vente vs Commandes du jour)
        const tabSaleBtn = document.getElementById('btn-pos-tab-sale');
        const tabOrdersBtn = document.getElementById('btn-pos-tab-orders');

        if (tabSaleBtn && tabOrdersBtn) {
            tabSaleBtn.addEventListener('click', () => switchTab('sale'));
            tabOrdersBtn.addEventListener('click', () => switchTab('orders'));
        }

        // Recherche produits en temps réel
        const searchInput = document.getElementById('pos-search-input');
        const searchClear = document.getElementById('pos-search-clear');
        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                state.searchTerm = e.target.value.trim().toLowerCase();
                if (searchClear) searchClear.classList.toggle('hidden', state.searchTerm === '');
                renderProductsGrid();
            });
        }
        if (searchClear) {
            searchClear.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                state.searchTerm = '';
                searchClear.classList.add('hidden');
                renderProductsGrid();
            });
        }

        // Modes de service (À emporter vs Sur place)
        const modeTakeawayBtn = document.getElementById('btn-pos-mode-takeaway');
        const modeDineInBtn = document.getElementById('btn-pos-mode-dine-in');
        if (modeTakeawayBtn && modeDineInBtn) {
            modeTakeawayBtn.addEventListener('click', () => setServiceMode('takeaway'));
            modeDineInBtn.addEventListener('click', () => setServiceMode('dine_in'));
        }

        // Sélecteur de table
        const tableSelect = document.getElementById('pos-table-select');
        if (tableSelect) {
            tableSelect.addEventListener('change', function () {
                state.selectedTableQrToken = this.value || null;
                const found = state.tables.find(t => t.qrToken === this.value);
                state.selectedTableLabel = found ? found.label : null;
            });
        }

        // Bouton vider le panier
        const clearCartBtn = document.getElementById('btn-pos-clear-cart');
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', function () {
                if (state.ticket.length === 0) return;
                if (confirm('Voulez-vous vraiment annuler ce ticket de caisse ?')) {
                    state.ticket = [];
                    state.activeOrderForPayment = null;
                    updateCartUi();
                }
            });
        }

        // Bouton Encaisser (Ouvre la modale de paiement)
        const checkoutBtn = document.getElementById('btn-pos-checkout');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', handleInitiateCheckout);
        }

        // Bouton Envoyer en cuisine uniquement (sans encaissement préalable)
        const sendKitchenBtn = document.getElementById('btn-pos-send-kitchen');
        if (sendKitchenBtn) {
            sendKitchenBtn.addEventListener('click', handleSendToKitchenOnly);
        }

        // Bouton Rafraîchir
        const refreshBtn = document.getElementById('btn-pos-refresh');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', async function () {
                const icon = document.getElementById('pos-refresh-icon');
                if (icon) icon.classList.add('animate-spin');
                await loadCatalog();
                await loadActiveTables();
                await fetchOrdersHistory();
                if (icon) icon.classList.remove('animate-spin');
                window.showToast('Données caisse actualisées', 'info', 2000);
            });
        }

        // Plein écran
        const fullscreenBtn = document.getElementById('btn-pos-fullscreen');
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', function () {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(() => {});
                } else {
                    document.exitFullscreen().catch(() => {});
                }
            });
        }

        // Boutons de filtres d'historique des commandes
        document.querySelectorAll('.pos-orders-filter').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.pos-orders-filter').forEach(b => {
                    b.classList.remove('active', 'bg-brand-orange', 'text-white');
                    b.classList.add('text-gray-600', 'dark:text-gray-400');
                });
                btn.classList.add('active', 'bg-brand-orange', 'text-white');
                btn.classList.remove('text-gray-600', 'dark:text-gray-400');
                state.ordersFilter = btn.getAttribute('data-filter') || 'all';
                renderOrdersTable();
            });
        });

        // Actualiser l'historique des ventes
        const ordersRefreshBtn = document.getElementById('btn-pos-orders-refresh');
        if (ordersRefreshBtn) {
            ordersRefreshBtn.addEventListener('click', async function () {
                ordersRefreshBtn.disabled = true;
                await fetchOrdersHistory();
                ordersRefreshBtn.disabled = false;
            });
        }

        // Formulaire de paiement
        const checkoutForm = document.getElementById('pos-checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', handleProcessPayment);
        }

        // Changement de moyen de paiement dans la modale
        document.querySelectorAll('input[name="pos_payment_method"]').forEach(radio => {
            radio.addEventListener('change', handlePaymentMethodChange);
        });

        // Calculatrice de monnaie en espèces
        const tenderedInput = document.getElementById('pos-cash-tendered-input');
        if (tenderedInput) {
            tenderedInput.addEventListener('input', updateCashChangeCalculation);
        }

        // Raccourcis de coupures en FCFA
        document.querySelectorAll('.btn-pos-bill').forEach(btn => {
            btn.addEventListener('click', function () {
                const amountInput = document.getElementById('pos-amount-to-charge-input');
                const dueUnits = parseFloat(amountInput?.value || 0);
                if (btn.dataset.billExact === 'true') {
                    if (tenderedInput) tenderedInput.value = dueUnits.toString();
                } else {
                    const billVal = parseInt(btn.dataset.billVal, 10) || 0;
                    if (tenderedInput) tenderedInput.value = billVal.toString();
                }
                updateCashChangeCalculation();
            });
        });

        // Fonction robuste d'impression thermique du reçu (Format 80mm)
        function printThermalReceipt() {
            const receiptEl = document.getElementById('pos-printable-receipt');
            if (!receiptEl) {
                window.print();
                return;
            }

            let printIframe = document.getElementById('pos-print-iframe');
            if (printIframe) {
                printIframe.remove();
            }

            printIframe = document.createElement('iframe');
            printIframe.id = 'pos-print-iframe';
            printIframe.style.position = 'fixed';
            printIframe.style.right = '0';
            printIframe.style.bottom = '0';
            printIframe.style.width = '0';
            printIframe.style.height = '0';
            printIframe.style.border = '0';
            printIframe.style.visibility = 'hidden';
            document.body.appendChild(printIframe);

            try {
                const doc = printIframe.contentWindow.document;
                doc.open();
                doc.write(`
                    <!DOCTYPE html>
                    <html lang="fr">
                    <head>
                        <meta charset="utf-8">
                        <title>Ticket de Caisse</title>
                        <style>
                            @page {
                                size: 80mm auto;
                                margin: 0;
                            }
                            body {
                                margin: 0;
                                padding: 4mm;
                                width: 72mm;
                                font-family: 'Courier New', Courier, monospace;
                                font-size: 11px;
                                line-height: 1.35;
                                color: #000;
                                background: #fff;
                            }
                            * { box-sizing: border-box; }
                            .text-center { text-align: center; }
                            .font-bold { font-weight: bold; }
                            .font-black { font-weight: 900; }
                            .uppercase { text-transform: uppercase; }
                            .tracking-wider { letter-spacing: 0.05em; }
                            .italic { font-style: italic; }
                            .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                            .text-sm { font-size: 13px; }
                            .text-xs { font-size: 11px; }
                            .text-\\[11px\\] { font-size: 11px; }
                            .text-\\[10px\\] { font-size: 10px; }
                            .flex { display: flex; }
                            .items-center { align-items: center; }
                            .items-start { align-items: flex-start; }
                            .justify-between { justify-content: space-between; }
                            .space-y-0\\.5 > * + * { margin-top: 2px; }
                            .space-y-1 > * + * { margin-top: 3px; }
                            .space-y-3 > * + * { margin-top: 10px; }
                            .border-b { border-bottom: 1px solid #444; }
                            .border-t { border-top: 1px solid #444; }
                            .border-dashed { border-style: dashed; }
                            .py-0\\.5 { padding-top: 2px; padding-bottom: 2px; }
                            .py-1 { padding-top: 3px; padding-bottom: 3px; }
                            .pb-1 { padding-bottom: 3px; }
                            .pb-2 { padding-bottom: 6px; }
                            .pt-1 { padding-top: 3px; }
                            .pt-2 { padding-top: 6px; }
                            .hidden { display: none !important; }
                            .shrink-0 { flex-shrink: 0; }
                        </style>
                    </head>
                    <body>
                        ${receiptEl.innerHTML}
                    </body>
                    </html>
                `);
                doc.close();

                setTimeout(function () {
                    try {
                        printIframe.contentWindow.focus();
                        printIframe.contentWindow.print();
                    } catch (e) {
                        window.print();
                    }
                }, 150);
            } catch (err) {
                window.print();
            }
        }

        window.printThermalReceipt = printThermalReceipt;

        // Bouton d'impression du reçu
        const printReceiptBtn = document.getElementById('btn-pos-print-receipt');
        if (printReceiptBtn) {
            printReceiptBtn.addEventListener('click', function (e) {
                e.preventDefault();
                printThermalReceipt();
            });
        }

        // Modale de connexion caissier
        const loginForm = document.getElementById('pos-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', handleLoginSubmit);
        }

        const posQuickOwnerBtn = document.getElementById('btn-pos-quick-login-owner');
        if (posQuickOwnerBtn) {
            posQuickOwnerBtn.addEventListener('click', function () {
                const emailInput = document.getElementById('pos-auth-email');
                const passInput = document.getElementById('pos-auth-password');
                if (emailInput) emailInput.value = 'owner@qreatery.test';
                if (passInput) passInput.value = 'Password123!';
                if (loginForm) {
                    loginForm.dispatchEvent(new Event('submit', { cancelable: true }));
                }
            });
        }

        // Sélecteur de quantité dans la modale d'options
        const optMinusBtn = document.getElementById('btn-pos-opt-qty-minus');
        const optPlusBtn = document.getElementById('btn-pos-opt-qty-plus');
        if (optMinusBtn && optPlusBtn) {
            optMinusBtn.addEventListener('click', function () {
                if (state.customizingQuantity > 1) {
                    state.customizingQuantity--;
                    updateOptionModalTotal();
                }
            });
            optPlusBtn.addEventListener('click', function () {
                if (state.customizingQuantity < 99) {
                    state.customizingQuantity++;
                    updateOptionModalTotal();
                }
            });
        }

        // Confirmation d'ajout depuis modale d'options
        const addCustomConfirmBtn = document.getElementById('btn-pos-add-to-cart-confirm');
        if (addCustomConfirmBtn) {
            addCustomConfirmBtn.addEventListener('click', handleConfirmCustomizedAdd);
        }
    }

    // Basculement entre Vente et Historique
    function switchTab(tabName) {
        state.activeTab = tabName;
        const tabSaleBtn = document.getElementById('btn-pos-tab-sale');
        const tabOrdersBtn = document.getElementById('btn-pos-tab-orders');
        const saleView = document.getElementById('pos-view-sale');
        const ordersView = document.getElementById('pos-view-orders');

        if (tabName === 'sale') {
            tabSaleBtn?.classList.add('active', 'bg-brand-orange', 'text-white');
            tabSaleBtn?.classList.remove('text-gray-400');
            tabOrdersBtn?.classList.remove('active', 'bg-brand-orange', 'text-white');
            tabOrdersBtn?.classList.add('text-gray-400');

            saleView?.classList.remove('hidden');
            ordersView?.classList.add('hidden');
        } else {
            tabOrdersBtn?.classList.add('active', 'bg-brand-orange', 'text-white');
            tabOrdersBtn?.classList.remove('text-gray-400');
            tabSaleBtn?.classList.remove('active', 'bg-brand-orange', 'text-white');
            tabSaleBtn?.classList.add('text-gray-400');

            ordersView?.classList.remove('hidden');
            saleView?.classList.add('hidden');
            fetchOrdersHistory();
        }
    }

    // Résolution des restaurants autorisés de l'opérateur
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
                window.showToast('Aucun restaurant actif n\'est associé à ce compte.', 'error', 5000);
                return false;
            }

            // Choisir le restaurant
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

            // Remplir le sélecteur si plusieurs établissements
            const selectContainer = document.getElementById('pos-restaurant-selector-container');
            const selectEl = document.getElementById('pos-restaurant-select');
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
        } catch (e) {
            console.warn('Erreur vérification session staff:', e);
            return false;
        }
    }

    // Chargement du catalogue via GET /api/v1/public/restaurants/{slug}/menu
    async function loadCatalog() {
        try {
            const res = await fetch(`/api/v1/public/restaurants/${encodeURIComponent(state.restaurantSlug)}/menu`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) {
                window.showToast('Impossible de charger le catalogue de produits.', 'error', 4000);
                return;
            }

            const json = await res.json();
            const data = json.data;

            state.restaurant = data.restaurant;
            state.categories = data.categories || [];

            // Aplatir les produits
            const prods = [];
            state.categories.forEach(cat => {
                (cat.products || []).forEach(p => {
                    p.category_id = cat.id;
                    p.category_name = cat.name;
                    prods.push(p);
                });
            });
            state.products = prods;

            // Rendu des filtres et de la grille
            renderCategoryBar();
            renderProductsGrid();
        } catch (e) {
            window.showToast('Erreur réseau lors de la récupération des produits.', 'error', 4000);
        }
    }

    // Chargement des QR codes et tables actives via GET /api/v1/restaurants/{id}/qr-codes
    async function loadActiveTables() {
        state.tables = [];
        state.selectedTableQrToken = null;
        state.selectedTableLabel = null;

        if (!state.restaurantId) return;

        try {
            const res = await fetch(`/api/v1/restaurants/${encodeURIComponent(state.restaurantId)}/qr-codes`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (!res.ok) {
                renderTableSelector();
                return;
            }

            const json = await res.json();
            const qrList = json.data || [];

            // Filtrer strictement les QR codes actifs associés à une table active
            const activeTableQrs = qrList.filter(q =>
                q.type === 'table' &&
                q.status === 'active' &&
                q.token &&
                q.table &&
                q.table.is_active
            );

            state.tables = activeTableQrs.map(q => ({
                id: q.table.id,
                label: q.table.label,
                capacity: q.table.capacity,
                qrToken: q.token,
            }));

            // Tri par libellé de table
            state.tables.sort((a, b) => (a.label || '').localeCompare(b.label || '', undefined, { numeric: true }));

            renderTableSelector();
        } catch (e) {
            console.warn('Erreur chargement des tables actives:', e);
            renderTableSelector();
        }
    }

    // Rendu du sélecteur de table
    function renderTableSelector() {
        const select = document.getElementById('pos-table-select');
        const warning = document.getElementById('pos-table-warning');
        if (!select) return;

        select.innerHTML = '<option value="">Sélectionner une table...</option>';

        if (state.tables.length === 0) {
            if (warning && state.serviceMode === 'dine_in') {
                warning.textContent = 'Aucun QR Code de table actif configuré pour cet établissement. Les commandes sur place exigent un QR Code de table actif. Veuillez utiliser le mode À emporter ou configurer les tables.';
                warning.classList.remove('hidden');
            }
            return;
        }

        if (warning) warning.classList.add('hidden');

        state.tables.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.qrToken;
            opt.textContent = `Table ${escapeHtml(t.label)}${t.capacity ? ` (${t.capacity} pers.)` : ''}`;
            select.appendChild(opt);
        });

        // Présélectionner si une seule table
        if (state.tables.length === 1) {
            select.value = state.tables[0].qrToken;
            state.selectedTableQrToken = state.tables[0].qrToken;
            state.selectedTableLabel = state.tables[0].label;
        }
    }

    // Rendu de la barre des catégories
    function renderCategoryBar() {
        const bar = document.getElementById('pos-category-bar');
        if (!bar) return;

        bar.innerHTML = `
            <button
                type="button"
                class="pos-category-pill ${state.activeCategory === 'all' ? 'active bg-brand-orange text-white shadow-2xs' : 'bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border text-gray-700 dark:text-gray-300 hover:bg-gray-100'} shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all focus-ring"
                data-category-id="all"
            >
                Tout le menu (${state.products.length})
            </button>
        `;

        state.categories.forEach(cat => {
            const count = (cat.products || []).length;
            if (count === 0) return;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `pos-category-pill ${state.activeCategory === String(cat.id) ? 'active bg-brand-orange text-white shadow-xs font-bold' : 'bg-white dark:bg-[#111827] border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'} shrink-0 px-4 py-2 rounded-full text-xs font-semibold transition-all focus-ring`;
            btn.setAttribute('data-category-id', cat.id);
            btn.innerHTML = `${escapeHtml(cat.name)} <span class="opacity-75 text-[10px]">(${count})</span>`;
            btn.addEventListener('click', () => {
                state.activeCategory = String(cat.id);
                renderCategoryBar();
                renderProductsGrid();
            });
            bar.appendChild(btn);
        });

        const allBtn = bar.querySelector('[data-category-id="all"]');
        if (allBtn) {
            allBtn.addEventListener('click', () => {
                state.activeCategory = 'all';
                renderCategoryBar();
                renderProductsGrid();
            });
        }
    }

    // Rendu de la grille des produits
    function renderProductsGrid() {
        const grid = document.getElementById('pos-products-grid');
        const emptyState = document.getElementById('pos-empty-products');
        if (!grid) return;

        grid.innerHTML = '';

        const filtered = state.products.filter(p => {
            if (state.activeCategory !== 'all' && String(p.category_id) !== state.activeCategory) {
                return false;
            }
            if (state.searchTerm) {
                const nameMatch = (p.name || '').toLowerCase().includes(state.searchTerm);
                const descMatch = (p.description || '').toLowerCase().includes(state.searchTerm);
                if (!nameMatch && !descMatch) return false;
            }
            return true;
        });

        if (filtered.length === 0) {
            grid.classList.add('hidden');
            emptyState?.classList.remove('hidden');
            return;
        }

        grid.classList.remove('hidden');
        emptyState?.classList.add('hidden');

        filtered.forEach(product => {
            const card = createProductPosCard(product);
            grid.appendChild(card);
        });
    }

    // Création d'une carte produit pour la caisse
    function createProductPosCard(product) {
        const card = document.createElement('article');
        const hasOptions = (product.variants && product.variants.length > 0) || (product.option_groups && product.option_groups.length > 0);
        const isAvail = product.is_available;

        card.className = `p-3 rounded-brand-xl bg-white dark:bg-[#111827] border border-gray-200 dark:border-gray-800 shadow-2xs flex flex-col justify-between transition-all select-none ${isAvail ? 'hover:border-brand-orange hover:shadow-md hover:-translate-y-0.5 active:scale-[0.98] cursor-pointer' : 'opacity-50 grayscale cursor-not-allowed'}`;

        const imageHtml = product.image_url
            ? `<img src="${product.image_url}" alt="${escapeHtml(product.name)}" class="w-full h-full object-cover">`
            : `<div class="w-full h-full bg-gradient-to-br from-amber-500/10 to-orange-500/20 dark:from-gray-800 dark:to-gray-900 flex items-center justify-center font-black text-xl text-brand-orange">
                ${escapeHtml((product.name || 'P').charAt(0).toUpperCase())}
               </div>`;

        card.innerHTML = `
            <div class="h-28 sm:h-32 rounded-brand-lg overflow-hidden bg-gray-100 dark:bg-[#0B0F19] mb-2.5 relative group">
                ${imageHtml}
                ${hasOptions ? '<span class="absolute top-1.5 right-1.5 px-2 py-0.5 rounded-full bg-brand-orange text-white text-[10px] font-black uppercase tracking-wider shadow-xs">Options</span>' : ''}
                ${!isAvail ? '<span class="absolute inset-0 bg-black/75 backdrop-blur-xs text-white text-xs font-black flex items-center justify-center uppercase tracking-wider">Épuisé</span>' : ''}
            </div>
            <div>
                <h4 class="text-xs font-bold text-gray-900 dark:text-white leading-snug line-clamp-2">
                    ${escapeHtml(product.name)}
                </h4>
                <div class="flex items-center justify-between gap-1 pt-2.5 mt-1 border-t border-gray-100 dark:border-gray-800/80">
                    <span class="text-sm font-black text-brand-orange font-mono">
                        ${formatMoney(product.price_minor)}
                    </span>
                    <span class="w-8 h-8 rounded-full bg-brand-orange/15 hover:bg-brand-orange hover:text-white text-brand-orange font-black text-sm flex items-center justify-center transition-colors shadow-2xs">
                        +
                    </span>
                </div>
            </div>
        `;

        if (isAvail) {
            card.addEventListener('click', () => handleProductClick(product));
        }

        return card;
    }

    // Clic sur un produit : ajout direct ou modale d'options
    function handleProductClick(product) {
        const hasVariants = product.variants && product.variants.length > 0;
        const hasOptionGroups = product.option_groups && product.option_groups.length > 0;

        if (hasVariants || hasOptionGroups) {
            openProductOptionModal(product);
        } else {
            // Ajout direct 1x au ticket
            addToTicket({
                productId: product.id,
                productName: product.name,
                variantId: null,
                variantName: null,
                unitPriceMinor: product.price_minor,
                quantity: 1,
                options: [],
                instructions: '',
                lineTotalMinor: product.price_minor,
            });
            window.showToast(`"${product.name}" ajouté`, 'success', 1500);
        }
    }

    // Ouverture de la modale de variantes & options
    function openProductOptionModal(product) {
        state.currentCustomizingProduct = product;
        state.customizingQuantity = 1;

        document.getElementById('pos-opt-product-title').textContent = product.name;
        document.getElementById('pos-opt-product-desc').textContent = product.description || '';
        document.getElementById('pos-opt-base-price').textContent = formatMoney(product.price_minor);
        document.getElementById('pos-opt-instructions').value = '';
        document.getElementById('pos-options-validation-error')?.classList.add('hidden');

        // Variantes
        const variantsContainer = document.getElementById('pos-opt-variants-container');
        const variantsList = document.getElementById('pos-opt-variants-list');
        variantsList.innerHTML = '';

        if (product.variants && product.variants.length > 0) {
            variantsContainer.classList.remove('hidden');
            product.variants.forEach((v, idx) => {
                const delta = v.price_delta_minor || 0;
                const deltaText = delta !== 0 ? ` (${delta > 0 ? '+' : ''}${formatMoney(delta)})` : '';
                const isChecked = idx === 0;

                const label = document.createElement('label');
                label.className = 'flex items-center justify-between p-2 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-white dark:bg-brand-dark cursor-pointer hover:border-brand-orange';
                label.innerHTML = `
                    <div class="flex items-center gap-2">
                        <input type="radio" name="pos_variant" value="${v.id}" data-delta="${delta}" ${isChecked ? 'checked' : ''} class="text-brand-orange focus:ring-brand-orange">
                        <span class="font-bold text-brand-text dark:text-white">${escapeHtml(v.name)}</span>
                    </div>
                    <span class="font-semibold text-gray-500">${deltaText}</span>
                `;
                label.querySelector('input').addEventListener('change', updateOptionModalTotal);
                variantsList.appendChild(label);
            });
        } else {
            variantsContainer.classList.add('hidden');
        }

        // Groupes d'options
        const groupsContainer = document.getElementById('pos-opt-groups-container');
        groupsContainer.innerHTML = '';

        if (product.option_groups && product.option_groups.length > 0) {
            product.option_groups.forEach(group => {
                const groupEl = document.createElement('div');
                groupEl.className = 'space-y-1.5';
                groupEl.setAttribute('data-group-id', group.id);
                groupEl.setAttribute('data-min', group.minimum_selections || 0);
                groupEl.setAttribute('data-max', group.maximum_selections || 0);

                const min = group.minimum_selections || 0;
                const max = group.maximum_selections || 0;
                const badgeText = min > 0 ? `Min ${min} requis` : 'Facultatif';

                groupEl.innerHTML = `
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-brand-text dark:text-gray-300 uppercase">${escapeHtml(group.name)}</span>
                        <span class="text-[10px] font-semibold ${min > 0 ? 'text-brand-orange' : 'text-gray-400'}">${badgeText}</span>
                    </div>
                    <div class="space-y-1 text-xs" data-pos-options-list></div>
                `;

                const optList = groupEl.querySelector('[data-pos-options-list]');

                (group.options || []).forEach(opt => {
                    const delta = opt.price_delta_minor || 0;
                    const deltaText = delta > 0 ? ` (+${formatMoney(delta)})` : '';
                    const isSingle = max === 1;

                    const label = document.createElement('label');
                    label.className = 'flex items-center justify-between p-2 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-white dark:bg-brand-dark cursor-pointer hover:border-brand-orange';
                    label.innerHTML = `
                        <div class="flex items-center gap-2">
                            <input
                                type="${isSingle ? 'radio' : 'checkbox'}"
                                name="pos_group_${group.id}${isSingle ? '' : '[]'}"
                                value="${opt.id}"
                                data-delta="${delta}"
                                class="text-brand-orange focus:ring-brand-orange rounded-sm"
                            >
                            <span class="text-brand-text dark:text-white font-medium">${escapeHtml(opt.name)}</span>
                        </div>
                        <span class="font-semibold text-gray-500">${deltaText}</span>
                    `;

                    const input = label.querySelector('input');
                    input.addEventListener('change', function () {
                        if (max > 1) {
                            const checked = groupEl.querySelectorAll('input:checked');
                            if (checked.length > max) {
                                input.checked = false;
                                window.showToast(`Max ${max} options pour ce groupe`, 'warning', 2500);
                            }
                        }
                        updateOptionModalTotal();
                    });

                    optList.appendChild(label);
                });

                groupsContainer.appendChild(groupEl);
            });
        }

        updateOptionModalTotal();
        window.openModal('pos-options-modal');
    }

    // Calcul du total affiché dans la modale d'options
    function updateOptionModalTotal() {
        const product = state.currentCustomizingProduct;
        if (!product) return;

        let unitMinor = product.price_minor || 0;

        // Delta variante
        const selVar = document.querySelector('input[name="pos_variant"]:checked');
        if (selVar) {
            unitMinor += parseInt(selVar.getAttribute('data-delta'), 10) || 0;
        }

        // Delta options
        const selOpts = document.querySelectorAll('#pos-opt-groups-container input:checked');
        selOpts.forEach(o => {
            unitMinor += parseInt(o.getAttribute('data-delta'), 10) || 0;
        });

        const qtyDisplay = document.getElementById('pos-opt-qty-display');
        if (qtyDisplay) qtyDisplay.textContent = state.customizingQuantity;

        const totalMinor = unitMinor * state.customizingQuantity;
        const totalDisplay = document.getElementById('pos-opt-line-total-display');
        if (totalDisplay) totalDisplay.textContent = formatMoney(totalMinor);
    }

    // Validation et confirmation de l'ajout personnalisé
    function handleConfirmCustomizedAdd() {
        const product = state.currentCustomizingProduct;
        if (!product) return;

        const errBox = document.getElementById('pos-options-validation-error');
        if (errBox) errBox.classList.add('hidden');

        // Contrôle min requises
        const groups = document.querySelectorAll('#pos-opt-groups-container [data-group-id]');
        for (let g of groups) {
            const min = parseInt(g.getAttribute('data-min'), 10) || 0;
            const checkedCount = g.querySelectorAll('input:checked').length;
            if (checkedCount < min) {
                const groupName = g.querySelector('span').textContent.trim();
                if (errBox) {
                    errBox.textContent = `Veuillez sélectionner au moins ${min} choix pour "${groupName}".`;
                    errBox.classList.remove('hidden');
                }
                return;
            }
        }

        // Variante
        let variantId = null;
        let variantName = null;
        let unitMinor = product.price_minor;

        const selVar = document.querySelector('input[name="pos_variant"]:checked');
        if (selVar) {
            variantId = parseInt(selVar.value, 10);
            const vObj = (product.variants || []).find(v => v.id === variantId);
            if (vObj) {
                variantName = vObj.name;
                unitMinor += (vObj.price_delta_minor || 0);
            }
        }

        // Options
        const options = [];
        document.querySelectorAll('#pos-opt-groups-container input:checked').forEach(inp => {
            const optId = parseInt(inp.value, 10);
            let foundOpt = null;
            (product.option_groups || []).forEach(g => {
                const o = (g.options || []).find(x => x.id === optId);
                if (o) foundOpt = o;
            });
            if (foundOpt) {
                unitMinor += (foundOpt.price_delta_minor || 0);
                options.push({
                    id: foundOpt.id,
                    name: foundOpt.name,
                    priceDeltaMinor: foundOpt.price_delta_minor || 0,
                });
            }
        });

        const instructions = (document.getElementById('pos-opt-instructions')?.value || '').trim();

        addToTicket({
            productId: product.id,
            productName: product.name,
            variantId: variantId,
            variantName: variantName,
            unitPriceMinor: unitMinor,
            quantity: state.customizingQuantity,
            options: options,
            instructions: instructions,
            lineTotalMinor: unitMinor * state.customizingQuantity,
        });

        window.closeModal('pos-options-modal');
        window.showToast(`"${product.name}" ajouté au ticket`, 'success', 2000);
    }

    // Ajout d'une ligne dans le ticket (panier)
    function addToTicket(item) {
        // Regrouper si article identique
        const existingIdx = state.ticket.findIndex(t => {
            if (t.productId !== item.productId) return false;
            if (t.variantId !== item.variantId) return false;
            if (t.instructions !== item.instructions) return false;
            if (t.options.length !== item.options.length) return false;
            const curIds = t.options.map(o => o.id).sort().join(',');
            const newIds = item.options.map(o => o.id).sort().join(',');
            return curIds === newIds;
        });

        if (existingIdx >= 0) {
            state.ticket[existingIdx].quantity += item.quantity;
            state.ticket[existingIdx].lineTotalMinor = state.ticket[existingIdx].unitPriceMinor * state.ticket[existingIdx].quantity;
        } else {
            item.cartId = 'pos_item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
            state.ticket.push(item);
        }

        updateCartUi();
    }

    // Mise à jour de l'affichage du ticket
    function updateCartUi() {
        const container = document.getElementById('pos-ticket-items');
        const emptyState = document.getElementById('pos-ticket-empty');
        const countBadge = document.getElementById('pos-ticket-count-badge');
        const clearBtn = document.getElementById('btn-pos-clear-cart');
        const checkoutBtn = document.getElementById('btn-pos-checkout');
        const sendKitchenBtn = document.getElementById('btn-pos-send-kitchen');

        const subtotalEl = document.getElementById('pos-summary-subtotal');
        const totalEl = document.getElementById('pos-summary-total');
        const checkoutTotalDisplay = document.getElementById('pos-btn-checkout-total');

        const totalItemsCount = state.ticket.reduce((sum, item) => sum + item.quantity, 0);
        const subtotalMinor = state.ticket.reduce((sum, item) => sum + item.lineTotalMinor, 0);

        // Taxes & Frais du restaurant
        const taxRate = parseFloat(state.restaurant?.settings?.tax_rate || 0);
        const taxMinor = Math.round((subtotalMinor * taxRate) / 100);
        const serviceFeeMinor = parseInt(state.restaurant?.settings?.service_fee_minor || 0, 10);
        const totalMinor = subtotalMinor + taxMinor + serviceFeeMinor;

        if (countBadge) {
            countBadge.textContent = `${totalItemsCount} article${totalItemsCount > 1 ? 's' : ''}`;
        }
        if (subtotalEl) subtotalEl.textContent = formatMoney(subtotalMinor);
        if (totalEl) totalEl.textContent = formatMoney(totalMinor);
        if (checkoutTotalDisplay) checkoutTotalDisplay.textContent = formatMoney(totalMinor);

        const hasItems = state.ticket.length > 0;
        if (clearBtn) clearBtn.disabled = !hasItems;
        if (checkoutBtn) checkoutBtn.disabled = !hasItems;
        if (sendKitchenBtn) sendKitchenBtn.disabled = !hasItems;

        if (!hasItems) {
            emptyState?.classList.remove('hidden');
            if (container) container.innerHTML = '';
            container?.appendChild(emptyState);
            return;
        }

        emptyState?.classList.add('hidden');
        if (!container) return;

        container.innerHTML = '';

        state.ticket.forEach(item => {
            const itemEl = document.createElement('div');
            itemEl.className = 'py-2.5 flex flex-col gap-1.5 text-xs';

            const optsText = (item.options || []).map(o => escapeHtml(o.name)).join(', ');

            itemEl.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <span class="font-bold text-gray-900 dark:text-white text-[13px] leading-tight block">
                            ${escapeHtml(item.productName)}
                        </span>
                        ${item.variantName ? `<span class="text-[11px] font-semibold text-brand-orange block mt-0.5">• ${escapeHtml(item.variantName)}</span>` : ''}
                        ${optsText ? `<span class="text-[11px] text-gray-500 dark:text-gray-400 block leading-tight mt-0.5">+ ${optsText}</span>` : ''}
                        ${item.instructions ? `<div class="text-[11px] text-amber-600 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-800/60 font-medium italic mt-1">Note: ${escapeHtml(item.instructions)}</div>` : ''}
                    </div>
                    <span class="font-black text-gray-900 dark:text-white font-mono shrink-0 text-xs">
                        ${formatMoney(item.lineTotalMinor)}
                    </span>
                </div>

                <div class="flex items-center justify-between pt-1.5">
                    <div class="flex items-center gap-1">
                        <button type="button" class="btn-pos-line-minus w-8 h-8 rounded-brand bg-gray-100 hover:bg-gray-200 dark:bg-[#111827] dark:hover:bg-gray-800 active:scale-90 border border-gray-300 dark:border-gray-700 text-brand-text dark:text-white font-black text-sm flex items-center justify-center transition-all shadow-2xs touch-target" aria-label="Diminuer la quantité">
                            -
                        </button>
                        <span class="w-8 text-center font-black text-brand-text dark:text-white font-mono text-sm">
                            ${item.quantity}
                        </span>
                        <button type="button" class="btn-pos-line-plus w-8 h-8 rounded-brand bg-gray-100 hover:bg-gray-200 dark:bg-[#111827] dark:hover:bg-gray-800 active:scale-90 border border-gray-300 dark:border-gray-700 text-brand-text dark:text-white font-black text-sm flex items-center justify-center transition-all shadow-2xs touch-target" aria-label="Augmenter la quantité">
                            +
                        </button>
                    </div>

                    <button type="button" class="btn-pos-line-remove text-[11px] text-rose-500 hover:text-rose-600 font-bold px-2 py-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors">
                        Supprimer
                    </button>
                </div>
            `;

            itemEl.querySelector('.btn-pos-line-minus').addEventListener('click', () => {
                if (item.quantity > 1) {
                    item.quantity--;
                    item.lineTotalMinor = item.unitPriceMinor * item.quantity;
                } else {
                    state.ticket = state.ticket.filter(t => t.cartId !== item.cartId);
                }
                updateCartUi();
            });

            itemEl.querySelector('.btn-pos-line-plus').addEventListener('click', () => {
                if (item.quantity < 99) {
                    item.quantity++;
                    item.lineTotalMinor = item.unitPriceMinor * item.quantity;
                    updateCartUi();
                }
            });

            itemEl.querySelector('.btn-pos-line-remove').addEventListener('click', () => {
                state.ticket = state.ticket.filter(t => t.cartId !== item.cartId);
                updateCartUi();
            });

            container.appendChild(itemEl);
        });
    }

    // Bascule de mode de service (Comptoir vs Sur place)
    function setServiceMode(mode) {
        state.serviceMode = mode;
        const btnTakeaway = document.getElementById('btn-pos-mode-takeaway');
        const btnDineIn = document.getElementById('btn-pos-mode-dine-in');
        const tableSelector = document.getElementById('pos-table-selector-container');
        const tableWarning = document.getElementById('pos-table-warning');

        if (mode === 'takeaway') {
            btnTakeaway?.classList.add('bg-brand-orange', 'text-white', 'shadow-2xs');
            btnTakeaway?.classList.remove('text-gray-600', 'dark:text-gray-400');
            btnDineIn?.classList.remove('bg-brand-orange', 'text-white', 'shadow-2xs');
            btnDineIn?.classList.add('text-gray-600', 'dark:text-gray-400');
            tableSelector?.classList.add('hidden');
            tableSelector?.classList.remove('flex');
            tableWarning?.classList.add('hidden');
        } else {
            btnDineIn?.classList.add('bg-brand-orange', 'text-white', 'shadow-2xs');
            btnDineIn?.classList.remove('text-gray-600', 'dark:text-gray-400');
            btnTakeaway?.classList.remove('bg-brand-orange', 'text-white', 'shadow-2xs');
            btnTakeaway?.classList.add('text-gray-600', 'dark:text-gray-400');
            tableSelector?.classList.remove('hidden');
            tableSelector?.classList.add('flex');

            if (state.tables.length === 0) {
                if (tableWarning) {
                    tableWarning.textContent = 'Aucun QR Code de table actif configuré pour cet établissement. Les commandes sur place exigent un QR Code de table actif. Veuillez utiliser le mode À emporter ou configurer les QR Codes de table.';
                    tableWarning.classList.remove('hidden');
                }
            } else {
                tableWarning?.classList.add('hidden');
            }
        }
    }

    // Déclenchement de l'encaissement
    async function handleInitiateCheckout() {
        if (state.ticket.length === 0) return;

        // Validation mode sur place
        if (state.serviceMode === 'dine_in') {
            if (state.tables.length === 0) {
                window.showToast('Impossible de commander sur place : aucun QR Code de table actif disponible. Utilisez le mode À emporter.', 'error', 4500);
                return;
            }
            if (!state.selectedTableQrToken) {
                window.showToast('Veuillez sélectionner une table active avant d\'encaisser la commande sur place.', 'warning', 4000);
                const tableSelect = document.getElementById('pos-table-select');
                if (tableSelect) tableSelect.focus();
                return;
            }
        }

        const checkoutBtn = document.getElementById('btn-pos-checkout');
        if (checkoutBtn) {
            checkoutBtn.disabled = true;
            checkoutBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Création commande...
            `;
        }

        // Créer d'abord la commande sur le backend si ce n'est pas déjà fait
        const createdOrder = await createBackendOrder();

        if (checkoutBtn) {
            checkoutBtn.disabled = false;
            updateCartUi();
        }

        if (!createdOrder) return;

        state.activeOrderForPayment = createdOrder;
        openCheckoutModal(createdOrder);
    }

    // Création de commande vers le backend
    async function createBackendOrder() {
        const payload = {
            mode: state.serviceMode,
            customer_name: state.serviceMode === 'dine_in'
                ? `Client Table ${state.selectedTableLabel || ''}`.trim()
                : 'Client Caisse / Comptoir',
            items: state.ticket.map(item => ({
                product_id: item.productId,
                variant_id: item.variantId || undefined,
                quantity: item.quantity,
                option_ids: (item.options || []).map(o => o.id),
                instructions: item.instructions || undefined,
            }))
        };

        if (state.serviceMode === 'dine_in' && state.selectedTableQrToken) {
            payload.qr_token = state.selectedTableQrToken;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch(`/api/v1/public/restaurants/${encodeURIComponent(state.restaurantSlug)}/orders`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (!res.ok) {
                let errorMsg = data.message || 'Impossible de créer la commande.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join(' ');
                }
                window.showToast(errorMsg, 'error', 4000);
                return null;
            }

            return data.data;
        } catch (e) {
            window.showToast('Erreur réseau lors de la validation de la commande.', 'error', 4000);
            return null;
        }
    }

    // Envoyer en cuisine uniquement sans encaisser
    async function handleSendToKitchenOnly() {
        if (state.ticket.length === 0) return;

        // Validation mode sur place
        if (state.serviceMode === 'dine_in') {
            if (state.tables.length === 0) {
                window.showToast('Impossible d\'envoyer sur place : aucun QR Code de table actif disponible. Utilisez le mode À emporter.', 'error', 4500);
                return;
            }
            if (!state.selectedTableQrToken) {
                window.showToast('Veuillez sélectionner une table active avant d\'envoyer la commande en cuisine.', 'warning', 4000);
                const tableSelect = document.getElementById('pos-table-select');
                if (tableSelect) tableSelect.focus();
                return;
            }
        }

        const btn = document.getElementById('btn-pos-send-kitchen');
        if (btn) btn.disabled = true;

        const order = await createBackendOrder();

        if (btn) btn.disabled = false;

        if (order) {
            window.showToast(`Commande #${order.number} envoyée en cuisine !`, 'success', 3500);
            state.ticket = [];
            state.activeOrderForPayment = null;
            updateCartUi();
            fetchOrdersHistory();
        }
    }

    // Ouverture de la modale d'encaissement
    function openCheckoutModal(order) {
        state.activeOrderForPayment = order;

        document.getElementById('pos-pay-order-number').textContent = `Commande #${order.number || order.id}`;
        document.getElementById('pos-checkout-error')?.classList.add('hidden');

        // Calcul du reste à payer
        const totalMinor = order.total_minor || 0;
        const dueMinor = totalMinor; // ou déduire si déjà partiellement payé
        const dueUnits = dueMinor / 100;

        document.getElementById('pos-pay-total-display').textContent = formatMoney(dueMinor, order.currency);

        const chargeInput = document.getElementById('pos-amount-to-charge-input');
        if (chargeInput) {
            chargeInput.value = dueUnits.toString();
            chargeInput.max = dueUnits.toString();
        }

        // Réinitialiser les champs espèces
        const tenderedInput = document.getElementById('pos-cash-tendered-input');
        if (tenderedInput) tenderedInput.value = dueUnits.toString();
        updateCashChangeCalculation();

        // Réinitialiser le mode sur espèces par défaut
        const cashRadio = document.querySelector('input[name="pos_payment_method"][value="cash"]');
        if (cashRadio) {
            cashRadio.checked = true;
            handlePaymentMethodChange();
        }

        window.openModal('pos-checkout-modal');
    }

    // Gestion du changement de moyen de paiement
    function handlePaymentMethodChange() {
        const selected = document.querySelector('input[name="pos_payment_method"]:checked')?.value || 'cash';
        const cashSection = document.getElementById('pos-cash-calculator-section');
        const refSection = document.getElementById('pos-external-ref-section');

        document.querySelectorAll('.pos-method-card').forEach(card => {
            const radio = card.querySelector('input');
            if (radio && radio.checked) {
                card.classList.add('border-brand-orange', 'bg-brand-orange/10');
                card.classList.remove('border-gray-200', 'dark:border-brand-dark-border', 'bg-white', 'dark:bg-brand-dark-card');
            } else {
                card.classList.remove('border-brand-orange', 'bg-brand-orange/10');
                card.classList.add('border-gray-200', 'dark:border-brand-dark-border', 'bg-white', 'dark:bg-brand-dark-card');
            }
        });

        if (selected === 'cash') {
            cashSection?.classList.remove('hidden');
            refSection?.classList.add('hidden');
        } else {
            cashSection?.classList.add('hidden');
            refSection?.classList.remove('hidden');
        }
    }

    // Calcul en direct de la monnaie à rendre
    function updateCashChangeCalculation() {
        const amountInput = document.getElementById('pos-amount-to-charge-input');
        const tenderedInput = document.getElementById('pos-cash-tendered-input');
        const changeDisplay = document.getElementById('pos-cash-change-display');

        const dueUnits = parseFloat(amountInput?.value || 0);
        const tenderedUnits = parseFloat(tenderedInput?.value || 0);

        const changeUnits = Math.max(0, tenderedUnits - dueUnits);
        if (changeDisplay) {
            changeDisplay.textContent = formatMoney(changeUnits * 100);
            if (tenderedUnits < dueUnits) {
                changeDisplay.className = 'font-mono font-black text-base text-rose-500';
                changeDisplay.textContent = 'Montant insuffisant';
            } else {
                changeDisplay.className = 'font-mono font-black text-base text-emerald-600 dark:text-emerald-400';
            }
        }
    }

    // Soumission du paiement vers POST /api/v1/restaurants/{id}/orders/{orderId}/payments
    async function handleProcessPayment(e) {
        e.preventDefault();
        const order = state.activeOrderForPayment;
        if (!order || !state.restaurantId) return;

        const errorBox = document.getElementById('pos-checkout-error');
        const submitBtn = document.getElementById('btn-pos-submit-payment');
        if (errorBox) errorBox.classList.add('hidden');

        const method = document.querySelector('input[name="pos_payment_method"]:checked')?.value || 'cash';
        const chargeUnits = parseFloat(document.getElementById('pos-amount-to-charge-input')?.value || 0);
        const amountMinor = Math.round(chargeUnits * 100);

        if (amountMinor <= 0) {
            if (errorBox) {
                errorBox.textContent = 'Le montant à encaisser doit être supérieur à zéro.';
                errorBox.classList.remove('hidden');
            }
            return;
        }

        // Vérification espèces : montant reçu suffisant
        let tenderedMinor = amountMinor;
        if (method === 'cash') {
            const tenderedUnits = parseFloat(document.getElementById('pos-cash-tendered-input')?.value || 0);
            tenderedMinor = Math.round(tenderedUnits * 100);
            if (tenderedMinor < amountMinor) {
                if (errorBox) {
                    errorBox.textContent = 'Le montant reçu en espèces est inférieur au montant à payer.';
                    errorBox.classList.remove('hidden');
                }
                return;
            }
        }

        const externalRef = (document.getElementById('pos-external-ref-input')?.value || '').trim();

        // Clé d'idempotence unique
        const idempotencyKey = `pos_pay_${order.id}_${Date.now()}_${Math.random().toString(36).substr(2, 7)}`;

        const payload = {
            amount_minor: amountMinor,
            method: method,
            external_reference: externalRef || undefined,
            idempotency_key: idempotencyKey,
        };

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Enregistrement...
            `;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch(`/api/v1/restaurants/${encodeURIComponent(state.restaurantId)}/orders/${encodeURIComponent(order.id)}/payments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            const json = await res.json();

            if (!res.ok) {
                let errorMsg = json.message || 'Échec du traitement du paiement.';
                if (json.errors) {
                    errorMsg = Object.values(json.errors).flat().join(' ');
                }
                if (errorBox) {
                    errorBox.textContent = errorMsg;
                    errorBox.classList.remove('hidden');
                }
                return;
            }

            const paymentData = json.data;
            window.showToast(`Paiement de ${formatMoney(amountMinor)} enregistré avec succès !`, 'success', 3000);

            // Vider le ticket
            state.ticket = [];
            updateCartUi();

            window.closeModal('pos-checkout-modal');

            // Préparer les données pour le reçu thermique
            const changeMinor = Math.max(0, tenderedMinor - amountMinor);
            state.lastReceiptData = {
                order: order,
                payment: paymentData,
                tenderedMinor: tenderedMinor,
                changeMinor: changeMinor,
                method: method,
            };

            openReceiptModal(state.lastReceiptData);

            // Actualiser l'historique des ventes
            fetchOrdersHistory();

        } catch (err) {
            if (errorBox) {
                errorBox.textContent = 'Erreur réseau lors de l\'encaissement.';
                errorBox.classList.remove('hidden');
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Valider l\'encaissement';
            }
        }
    }

    // Affichage du reçu de vente thermique
    function openReceiptModal(data) {
        const order = data.order;
        if (!order) return;

        document.getElementById('pos-receipt-restaurant-name').textContent = state.restaurant?.name || 'APP RESTAURANT';
        document.getElementById('pos-receipt-date').textContent = new Date().toLocaleString([], { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric' });
        document.getElementById('pos-receipt-order-num').textContent = `#${order.number || order.id}`;
        const tableLabel = order.table?.label || state.selectedTableLabel || '';
        document.getElementById('pos-receipt-mode').textContent = order.mode === 'takeaway'
            ? 'À emporter'
            : `Sur place${tableLabel ? ` (Table ${escapeHtml(tableLabel)})` : ''}`;

        // Articles
        const itemsList = document.getElementById('pos-receipt-items-list');
        if (itemsList) {
            const items = order.items || state.ticket || [];
            itemsList.innerHTML = items.map(item => `
                <div class="flex items-start justify-between py-0.5">
                    <span class="truncate pr-2">${item.quantity}x ${escapeHtml(item.product_name || item.productName)}</span>
                    <span class="font-bold shrink-0">${formatMoney(item.line_total_minor || item.lineTotalMinor)}</span>
                </div>
            `).join('');
        }

        document.getElementById('pos-receipt-subtotal').textContent = formatMoney(order.subtotal_minor || order.total_minor);
        document.getElementById('pos-receipt-total').textContent = formatMoney(order.total_minor);

        const methodMap = {
            'cash': 'Espèces',
            'card': 'Carte Bancaire',
            'mobile_money': 'Mobile Money',
            'other': 'Autre',
        };
        document.getElementById('pos-receipt-method').textContent = methodMap[data.method] || data.method;
        document.getElementById('pos-receipt-tendered').textContent = formatMoney(data.tenderedMinor || order.total_minor);
        document.getElementById('pos-receipt-change').textContent = formatMoney(data.changeMinor || 0);

        window.openModal('pos-receipt-modal');
    }

    // Chargement de l'historique des commandes via GET /api/v1/restaurants/{id}/orders
    async function fetchOrdersHistory() {
        if (!state.restaurantId) return;

        try {
            const res = await fetch(`/api/v1/restaurants/${encodeURIComponent(state.restaurantId)}/orders`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (!res.ok) return;

            const json = await res.json();
            state.ordersHistory = json.data || [];

            // Décompte impayées
            const unpaidCount = state.ordersHistory.filter(o => o.payment_status === 'unpaid' || o.payment_status === 'partially_paid').length;
            const badge = document.getElementById('pos-badge-unpaid-count');
            if (badge) {
                badge.textContent = unpaidCount;
                badge.classList.toggle('hidden', unpaidCount === 0);
            }

            renderOrdersTable();
        } catch (e) {
            console.warn('Erreur chargement historique POS:', e);
        }
    }

    // Rendu du tableau des ventes du jour
    function renderOrdersTable() {
        const tbody = document.getElementById('pos-orders-table-body');
        const emptyState = document.getElementById('pos-orders-empty');
        if (!tbody) return;

        tbody.innerHTML = '';

        const allCount = state.ordersHistory.length;
        const unpaidCount = state.ordersHistory.filter(o => o.payment_status === 'unpaid' || o.payment_status === 'partially_paid').length;
        const paidCount = state.ordersHistory.filter(o => o.payment_status === 'paid').length;

        document.getElementById('pos-filter-count-all').textContent = allCount;
        document.getElementById('pos-filter-count-unpaid').textContent = unpaidCount;
        document.getElementById('pos-filter-count-paid').textContent = paidCount;

        const filtered = state.ordersHistory.filter(o => {
            if (state.ordersFilter === 'unpaid') return o.payment_status === 'unpaid' || o.payment_status === 'partially_paid';
            if (state.ordersFilter === 'paid') return o.payment_status === 'paid';
            return true;
        });

        if (filtered.length === 0) {
            emptyState?.classList.remove('hidden');
            return;
        }

        emptyState?.classList.add('hidden');

        filtered.forEach(order => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 dark:hover:bg-brand-dark transition-colors';

            const createdTime = order.created_at ? new Date(order.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '--:--';

            let payBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-300">Impayé</span>';
            if (order.payment_status === 'paid') {
                payBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">Payé</span>';
            } else if (order.payment_status === 'partially_paid') {
                payBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300">Partiel</span>';
            }

            const isUnpaid = order.payment_status !== 'paid';

            tr.innerHTML = `
                <td class="p-3 font-mono font-bold text-brand-text dark:text-white">
                    #${escapeHtml(order.number || order.id)}
                </td>
                <td class="p-3 text-gray-500">${createdTime}</td>
                <td class="p-3">
                    ${order.mode === 'takeaway' ? 'À emporter' : 'Sur place'}
                </td>
                <td class="p-3 font-mono font-bold text-brand-text dark:text-white">
                    ${formatMoney(order.total_minor, order.currency)}
                </td>
                <td class="p-3">${payBadge}</td>
                <td class="p-3">
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 dark:bg-brand-dark text-gray-700 dark:text-gray-300">
                        ${escapeHtml(order.preparation_status)}
                    </span>
                </td>
                <td class="p-3 text-right space-x-1">
                    ${isUnpaid ? `
                        <button type="button" class="btn-pos-pay-row px-2.5 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs focus-ring">
                            Encaisser
                        </button>
                    ` : `
                        <button type="button" class="btn-pos-receipt-row px-2.5 py-1 rounded bg-gray-100 dark:bg-brand-dark hover:bg-gray-200 text-brand-text dark:text-white font-semibold text-xs border border-gray-300 dark:border-brand-dark-border">
                            Reçu
                        </button>
                    `}
                </td>
            `;

            const payBtn = tr.querySelector('.btn-pos-pay-row');
            if (payBtn) {
                payBtn.addEventListener('click', () => openCheckoutModal(order));
            }

            const receiptBtn = tr.querySelector('.btn-pos-receipt-row');
            if (receiptBtn) {
                receiptBtn.addEventListener('click', () => {
                    openReceiptModal({
                        order: order,
                        tenderedMinor: order.total_minor,
                        changeMinor: 0,
                        method: 'cash',
                    });
                });
            }

            tbody.appendChild(tr);
        });
    }

    // Gestion de la connexion caissier
    async function handleLoginSubmit(e) {
        e.preventDefault();
        const email = document.getElementById('pos-auth-email')?.value;
        const password = document.getElementById('pos-auth-password')?.value;
        const errorBox = document.getElementById('pos-login-error');
        const submitBtn = document.getElementById('btn-pos-login-submit');

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

            window.closeModal('pos-login-modal');
            window.showToast('Connexion caisse réussie', 'success', 2000);
            await initPos();
        } catch (e) {
            if (errorBox) {
                errorBox.textContent = 'Erreur réseau lors de la connexion.';
                errorBox.classList.remove('hidden');
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Connexion Caisse';
            }
        }
    }

    // CA-04 & CA-02 : Temps réel POS
    function initPosRealtime() {
        if (!state.restaurantId) return;

        if (state.realtimeClient) {
            state.realtimeClient.disconnect();
            state.realtimeClient = null;
        }

        state.realtimeClient = createRealtimeClient();

        // Souscription au canal privé du restaurant
        const channelName = `private-restaurants.${state.restaurantId}`;
        state.realtimeSub = state.realtimeClient.subscribe(channelName, {
            'assistance.requested': handlePosRealtimeAssistance,
            'orders.status.updated': handlePosRealtimeOrderStatusUpdated,
            'orders.created': handlePosRealtimeOrderCreated,
        });
    }

    // CA-04 : Notification d'assistance en caisse POS
    function handlePosRealtimeAssistance(data) {
        if (!data || !data.id) return;
        if (state.handledAssistanceIds.has(data.id)) return;
        state.handledAssistanceIds.add(data.id);

        const tableText = data.restaurant_table_id ? `Table #${data.restaurant_table_id}` : 'Salle';
        const typeText = data.type === 'waiter' ? 'Serveur demandé' : 'Assistance requise';

        window.showToast(`Appel : ${tableText} — ${typeText}`, 'warning', 8000);
    }

    // CA-02 : Mise à jour du statut des commandes dans l'historique POS
    function handlePosRealtimeOrderStatusUpdated(data) {
        if (!data || !data.id || !data.to_status) return;

        const order = state.ordersHistory.find(o => o.id === data.id);
        if (order) {
            order.preparation_status = data.to_status;
            renderOrdersHistory();
        }
    }

    function handlePosRealtimeOrderCreated(data) {
        if (!data || !data.id) return;
        fetchOrdersHistory();
        window.showToast(`Nouvelle commande #${data.number || data.id} enregistrée !`, 'info', 4000);
    }

    function showLoading(show) {
        const loading = document.getElementById('pos-loading-state');
        const grid = document.getElementById('pos-products-grid');
        if (loading) loading.classList.toggle('hidden', !show);
        if (grid && show) grid.classList.add('hidden');
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

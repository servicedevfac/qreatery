import { createRealtimeClient } from './realtime.js';

(function () {
    'use strict';

    // Sécurisation globale des gestionnaires de modales
    window.openModal = window.openModal || function(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeModal = window.closeModal || function(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    // État local de l'application client
    const state = {
        restaurantSlug: null,
        restaurant: null,
        categories: [],
        activeCategory: 'all',
        searchTerm: '',
        qrToken: null,
        table: null,
        orderMode: 'dine_in',
        cart: [],
        currentCustomizingProduct: null,
        activeTrackingToken: null,
        trackingOrder: null,
        trackingInterval: null,
        realtimeClient: null,
        realtimeSub: null,
        isDegraded: false,
    };

    // Formatage monétaire (XOF / FCFA ou devise restaurant)
    function formatMoney(amountMinor, currency) {
        currency = currency || (state.restaurant ? state.restaurant.currency : 'XOF');
        const units = (amountMinor || 0) / 100;
        const formatted = new Intl.NumberFormat('fr-FR', {
            maximumFractionDigits: 0
        }).format(units);
        return currency === 'XOF' ? `${formatted} FCFA` : `${formatted} ${currency}`;
    }

    // Persistance du panier dans le stockage local
    function saveCart() {
        if (!state.restaurantSlug) return;
        try {
            localStorage.setItem(`app_restaurant_cart_${state.restaurantSlug}`, JSON.stringify(state.cart));
            localStorage.setItem(`app_restaurant_mode_${state.restaurantSlug}`, state.orderMode);
        } catch (e) {
            console.warn('Impossible de sauvegarder le panier dans localStorage:', e);
        }
    }

    function loadSavedCart() {
        if (!state.restaurantSlug) return;
        try {
            const savedCart = localStorage.getItem(`app_restaurant_cart_${state.restaurantSlug}`);
            if (savedCart) {
                state.cart = JSON.parse(savedCart) || [];
            }
            const savedMode = localStorage.getItem(`app_restaurant_mode_${state.restaurantSlug}`);
            if (savedMode && ['dine_in', 'takeaway'].includes(savedMode)) {
                state.orderMode = savedMode;
            }
        } catch (e) {
            state.cart = [];
        }
    }

    // Initialisation au chargement du DOM
    document.addEventListener('DOMContentLoaded', function () {
        initApp();
    });

    async function initApp() {
        const urlParams = new URLSearchParams(window.location.search);
        state.qrToken = urlParams.get('qr') || null;
        state.activeTrackingToken = urlParams.get('tracking') || null;
        const slugFromUrl = urlParams.get('restaurant') || null;

        bindEvents();

        // Cas 1 : Suivi de commande direct par token
        if (state.activeTrackingToken) {
            showLoading(true);
            await loadOrderTracking(state.activeTrackingToken);
            return;
        }

        // Cas 2 : Scan QR Code de table
        if (state.qrToken) {
            showLoading(true);
            const qrSuccess = await resolveQrCode(state.qrToken);
            if (qrSuccess) {
                setOrderMode('dine_in');
                await loadMenu(state.restaurantSlug);
            }
            return;
        }

        // Cas 3 : Accès par slug restaurant ou restaurant par défaut (mode à emporter par défaut)
        state.restaurantSlug = slugFromUrl || 'restaurant-demo';
        setOrderMode('takeaway');
        showLoading(true);
        await loadMenu(state.restaurantSlug);
    }

    // Écouteurs d'événements principaux
    function bindEvents() {
        // Recherche
        const searchInput = document.getElementById('client-menu-search');
        const clearBtn = document.getElementById('client-search-clear');
        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                state.searchTerm = e.target.value.trim().toLowerCase();
                if (clearBtn) clearBtn.classList.toggle('hidden', state.searchTerm === '');
                renderProducts();
            });
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                state.searchTerm = '';
                clearBtn.classList.add('hidden');
                renderProducts();
            });
        }

        // Changement de mode de commande (Sur place vs À emporter)
        const btnDineIn = document.getElementById('btn-mode-dine-in');
        const btnTakeaway = document.getElementById('btn-mode-takeaway');
        if (btnDineIn && btnTakeaway) {
            btnDineIn.addEventListener('click', () => setOrderMode('dine_in'));
            btnTakeaway.addEventListener('click', () => setOrderMode('takeaway'));
        }

        // Ouverture / Fermeture tiroir panier
        const openCartBtn = document.getElementById('btn-open-cart-drawer');
        if (openCartBtn) {
            openCartBtn.addEventListener('click', () => window.openModal('cart-drawer-modal'));
        }

        // Vider le panier
        const clearCartBtn = document.getElementById('btn-clear-cart');
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', function () {
                if (confirm('Voulez-vous vraiment vider votre panier ?')) {
                    state.cart = [];
                    saveCart();
                    updateCartUi();
                }
            });
        }

        // Validation finale de la commande
        const confirmOrderBtn = document.getElementById('btn-confirm-order');
        if (confirmOrderBtn) {
            confirmOrderBtn.addEventListener('click', submitOrder);
        }

        // Soumission de demande d'assistance (EF-Client-04)
        const submitAssistanceBtn = document.getElementById('btn-submit-assistance');
        if (submitAssistanceBtn) {
            submitAssistanceBtn.addEventListener('click', submitAssistanceRequest);
        }

        // Bouton réessai en cas d'erreur
        const retryBtn = document.getElementById('btn-client-retry');
        if (retryBtn) {
            retryBtn.addEventListener('click', () => initApp());
        }

        // Bouton retour au menu depuis la vue suivi
        const backToMenuBtn = document.getElementById('btn-track-back-to-menu');
        if (backToMenuBtn) {
            backToMenuBtn.addEventListener('click', function () {
                document.getElementById('client-tracking-view')?.classList.add('hidden');
                document.getElementById('client-menu-container')?.classList.remove('hidden');
                document.getElementById('client-category-bar')?.classList.remove('hidden');
            });
        }

        // Bouton voir la commande active dans le header
        const viewActiveOrderBtn = document.getElementById('btn-view-active-order');
        if (viewActiveOrderBtn) {
            viewActiveOrderBtn.addEventListener('click', function () {
                document.getElementById('client-menu-container')?.classList.add('hidden');
                document.getElementById('client-category-bar')?.classList.add('hidden');
                document.getElementById('client-tracking-view')?.classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // Ajout personnalisé depuis la modale d'options
        const addCustomizedBtn = document.getElementById('btn-add-customized-to-cart');
        if (addCustomizedBtn) {
            addCustomizedBtn.addEventListener('click', handleAddCustomizedToCart);
        }
    }

    // Résolution du QR code public (table et restaurant)
    async function resolveQrCode(token) {
        try {
            const response = await fetch(`/api/v1/public/qr-codes/${encodeURIComponent(token)}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) {
                showError('QR Code invalide ou révoqué', 'Ce code de table n\'est plus actif. Veuillez vous adresser au personnel.');
                return false;
            }
            const data = await response.json();
            const qrData = data.data;

            state.restaurantSlug = qrData.restaurant?.slug;
            state.table = qrData.table;
            state.orderMode = (qrData.type === 'table' || qrData.type === 'dine_in') ? 'dine_in' : 'takeaway';

            // Affichage du badge de table dans le header
            const tableBadge = document.getElementById('client-table-badge');
            if (tableBadge && state.table) {
                tableBadge.textContent = `Table ${state.table.label || state.table.id}`;
                tableBadge.classList.remove('hidden');
            }

            return true;
        } catch (error) {
            showError('Erreur réseau', 'Impossible de valider le QR code. Vérifiez votre connexion internet.');
            return false;
        }
    }

    // Chargement du menu public via l'API backend
    async function loadMenu(slug) {
        showLoading(true);
        try {
            const response = await fetch(`/api/v1/public/restaurants/${encodeURIComponent(slug)}/menu`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                if (response.status === 404) {
                    showError('Établissement introuvable', 'Le menu de ce restaurant n\'est pas accessible actuellement.');
                } else {
                    showError('Erreur de chargement', 'Une erreur est survenue lors de la récupération de la carte.');
                }
                return;
            }

            const json = await response.json();
            const menuData = json.data;

            state.restaurant = menuData.restaurant;
            state.categories = menuData.categories || [];

            // Mise à jour de l'en-tête du restaurant
            updateRestaurantHeader();

            // Chargement du panier sauvegardé pour ce restaurant
            loadSavedCart();
            updateCartUi();

            // Rendu des catégories et des produits
            renderCategoriesBar();
            renderProducts();

            showLoading(false);
        } catch (error) {
            showError('Erreur de connexion', 'Vérifiez votre connexion réseau et réessayez.');
        }
    }

    // Mise à jour de l'en-tête
    function updateRestaurantHeader() {
        if (!state.restaurant) return;
        const nameEl = document.getElementById('client-restaurant-name');
        const initialEl = document.getElementById('client-restaurant-initial');
        const logoEl = document.getElementById('client-restaurant-logo');

        if (nameEl) nameEl.textContent = state.restaurant.name;
        if (initialEl) initialEl.textContent = (state.restaurant.name || 'R').charAt(0).toUpperCase();

        if (state.restaurant.logo_url && logoEl) {
            logoEl.innerHTML = `<img src="${state.restaurant.logo_url}" alt="${state.restaurant.name}" class="w-full h-full object-cover">`;
        }

        const modeBadge = document.getElementById('client-mode-badge');
        if (modeBadge) {
            modeBadge.textContent = state.orderMode === 'dine_in'
                ? (state.table ? `Sur place • Table ${state.table.label || state.table.id}` : 'Sur place')
                : 'Commande à emporter';
        }
    }

    // Rendu des pills de catégories
    function renderCategoriesBar() {
        const bar = document.getElementById('client-category-bar');
        if (!bar) return;

        // Conserver le bouton "Tout le menu"
        bar.innerHTML = `
            <button
                type="button"
                class="category-pill ${state.activeCategory === 'all' ? 'active bg-brand-orange text-white shadow-2xs' : 'bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border text-gray-700 dark:text-gray-300 hover:bg-gray-50'} shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all focus-ring"
                data-category-id="all"
            >
                Tout le menu
            </button>
        `;

        state.categories.forEach(cat => {
            const count = (cat.products || []).length;
            if (count === 0) return;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `category-pill ${state.activeCategory === String(cat.id) ? 'active bg-brand-orange text-white shadow-2xs' : 'bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border text-gray-700 dark:text-gray-300 hover:bg-gray-50'} shrink-0 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all focus-ring`;
            btn.setAttribute('data-category-id', cat.id);
            btn.innerHTML = `${escapeHtml(cat.name)} <span class="opacity-75 text-[10px]">(${count})</span>`;
            btn.addEventListener('click', () => {
                state.activeCategory = String(cat.id);
                renderCategoriesBar();
                renderProducts();
            });
            bar.appendChild(btn);
        });

        // Clic sur "Tout le menu"
        const allBtn = bar.querySelector('[data-category-id="all"]');
        if (allBtn) {
            allBtn.addEventListener('click', () => {
                state.activeCategory = 'all';
                renderCategoriesBar();
                renderProducts();
            });
        }
    }

    // Rendu des produits par catégorie avec support de la recherche
    function renderProducts() {
        const container = document.getElementById('client-menu-container');
        if (!container) return;

        container.innerHTML = '';
        let totalRenderedProducts = 0;

        const filteredCategories = state.categories.filter(cat => {
            if (state.activeCategory !== 'all' && String(cat.id) !== state.activeCategory) {
                return false;
            }
            return true;
        });

        filteredCategories.forEach(cat => {
            const products = (cat.products || []).filter(p => {
                if (!state.searchTerm) return true;
                const matchName = (p.name || '').toLowerCase().includes(state.searchTerm);
                const matchDesc = (p.description || '').toLowerCase().includes(state.searchTerm);
                return matchName || matchDesc;
            });

            if (products.length === 0) return;

            totalRenderedProducts += products.length;

            const section = document.createElement('section');
            section.className = 'space-y-3';
            section.innerHTML = `
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-brand-dark-border pb-1.5">
                    <h2 class="text-sm font-black text-brand-text dark:text-white uppercase tracking-wider">
                        ${escapeHtml(cat.name)}
                    </h2>
                    <span class="text-xs text-gray-400 font-semibold">${products.length} plat${products.length > 1 ? 's' : ''}</span>
                </div>
                <div class="grid grid-cols-1 gap-3" data-products-grid></div>
            `;

            const grid = section.querySelector('[data-products-grid]');

            products.forEach(product => {
                const card = createProductCardElement(product);
                grid.appendChild(card);
            });

            container.appendChild(section);
        });

        // Cas aucun produit trouvé
        if (totalRenderedProducts === 0) {
            container.innerHTML = `
                <div class="text-center py-12 px-4 bg-white dark:bg-brand-dark-card rounded-brand-lg border border-gray-200 dark:border-brand-dark-border">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-brand-orange/10 text-brand-orange flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-brand-text dark:text-white">Aucun plat correspondant</h3>
                    <p class="text-xs text-gray-500 mt-1">Essayez un autre mot-clé ou sélectionnez une autre catégorie.</p>
                </div>
            `;
        }
    }

    // Création du DOM pour une carte produit
    function createProductCardElement(product) {
        const card = document.createElement('article');
        card.className = `p-3.5 rounded-brand-lg bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border shadow-xs flex gap-3.5 transition-all hover:border-brand-orange/40 ${!product.is_available ? 'opacity-60 grayscale' : ''}`;

        const formattedPrice = formatMoney(product.price_minor);
        const hasOptionsOrVariants = (product.variants && product.variants.length > 0) || (product.option_groups && product.option_groups.length > 0);

        const imageHtml = product.image_url
            ? `<img src="${product.image_url}" alt="${escapeHtml(product.name)}" class="w-full h-full object-cover" loading="lazy">`
            : `<div class="w-full h-full bg-brand-light dark:bg-brand-dark flex items-center justify-center text-2xl font-bold text-brand-orange select-none">
                 ${escapeHtml(product.name.charAt(0).toUpperCase())}
               </div>`;

        card.innerHTML = `
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-brand overflow-hidden shrink-0 bg-gray-100 dark:bg-brand-dark relative">
                ${imageHtml}
                ${!product.is_available ? '<span class="absolute inset-0 bg-black/60 text-white text-[10px] font-bold flex items-center justify-center text-center p-1 uppercase">Épuisé</span>' : ''}
            </div>
            <div class="flex-1 min-w-0 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-sm font-bold text-brand-text dark:text-white leading-tight">
                            ${escapeHtml(product.name)}
                        </h3>
                    </div>
                    ${product.description ? `<p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-1 leading-snug">${escapeHtml(product.description)}</p>` : ''}
                </div>

                <div class="flex items-center justify-between gap-2 pt-2 mt-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black text-brand-orange">${formattedPrice}</span>
                        ${product.preparation_minutes ? `<span class="text-[10px] text-gray-400 font-medium hidden sm:inline-flex items-center gap-0.5"><svg class="w-3 h-3 inline text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>${product.preparation_minutes}m</span>` : ''}
                    </div>

                    ${product.is_available ? `
                        <button
                            type="button"
                            class="btn-add-product touch-target px-3 py-1.5 rounded-brand bg-brand-orange hover:bg-brand-orange-dark active:bg-brand-orange-dark text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1 focus-ring"
                            data-product-id="${product.id}"
                            aria-label="Ajouter ${escapeHtml(product.name)}"
                        >
                            <span>${hasOptionsOrVariants ? 'Choisir' : '+'}</span>
                        </button>
                    ` : `
                        <span class="text-[11px] font-semibold text-gray-400 bg-gray-100 dark:bg-brand-dark px-2 py-1 rounded-brand">
                            Indisponible
                        </span>
                    `}
                </div>
            </div>
        `;

        const addBtn = card.querySelector('.btn-add-product');
        if (addBtn) {
            addBtn.addEventListener('click', () => handleProductSelect(product));
        }

        return card;
    }

    // Gestion du clic d'ajout produit : modale personnalisation ou ajout direct
    function handleProductSelect(product) {
        const hasVariants = product.variants && product.variants.length > 0;
        const hasOptionGroups = product.option_groups && product.option_groups.length > 0;

        if (hasVariants || hasOptionGroups) {
            openOptionsModal(product);
        } else {
            // Ajout direct avec quantité 1
            addToCart({
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
            window.showToast(`"${product.name}" ajouté au panier`, 'success', 2500);
        }
    }

    // Ouverture et configuration de la modale de variantes & options
    function openOptionsModal(product) {
        state.currentCustomizingProduct = product;

        document.getElementById('opt-product-id').value = product.id;
        document.getElementById('options-modal-title').textContent = product.name;
        document.getElementById('options-modal-base-price').textContent = `À partir de ${formatMoney(product.price_minor)}`;
        document.getElementById('options-modal-description').textContent = product.description || '';
        document.getElementById('opt-instructions').value = '';

        // Réinitialiser le sélecteur de quantité à 1
        const qtyInput = document.getElementById('opt-quantity-picker');
        if (qtyInput) qtyInput.value = '1';

        // Section Variantes
        const variantsContainer = document.getElementById('options-variants-container');
        const variantsList = document.getElementById('options-variants-list');
        variantsList.innerHTML = '';

        if (product.variants && product.variants.length > 0) {
            variantsContainer.classList.remove('hidden');
            product.variants.forEach((v, idx) => {
                const delta = v.price_delta_minor || 0;
                const deltaText = delta !== 0 ? ` (${delta > 0 ? '+' : ''}${formatMoney(delta)})` : '';
                const isChecked = v.is_default || idx === 0;

                const label = document.createElement('label');
                label.className = 'flex items-center justify-between p-2.5 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark cursor-pointer hover:border-brand-orange transition-colors text-xs';
                label.innerHTML = `
                    <div class="flex items-center gap-2.5">
                        <input type="radio" name="opt_variant" value="${v.id}" data-delta="${delta}" ${isChecked ? 'checked' : ''} class="w-4 h-4 text-brand-orange focus:ring-brand-orange">
                        <span class="font-bold text-brand-text dark:text-white">${escapeHtml(v.name)}</span>
                    </div>
                    <span class="font-semibold text-gray-500">${deltaText}</span>
                `;
                label.querySelector('input').addEventListener('change', updateModalTotal);
                variantsList.appendChild(label);
            });
        } else {
            variantsContainer.classList.add('hidden');
        }

        // Section Groupes d'options
        const groupsContainer = document.getElementById('options-groups-container');
        groupsContainer.innerHTML = '';

        if (product.option_groups && product.option_groups.length > 0) {
            product.option_groups.forEach(group => {
                const groupEl = document.createElement('div');
                groupEl.className = 'space-y-2';
                groupEl.setAttribute('data-group-id', group.id);
                groupEl.setAttribute('data-min', group.minimum_selections || 0);
                groupEl.setAttribute('data-max', group.maximum_selections || 0);

                const min = group.minimum_selections || 0;
                const max = group.maximum_selections || 0;
                let badgeRule = 'Facultatif';
                if (min > 0 && max > 0 && min === max) {
                    badgeRule = `Exactement ${min} obligatoire${min > 1 ? 's' : ''}`;
                } else if (min > 0) {
                    badgeRule = `Min ${min} obligatoire${min > 1 ? 's' : ''}`;
                } else if (max > 0) {
                    badgeRule = `Jusqu'à ${max} au choix`;
                }

                groupEl.innerHTML = `
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-brand-text dark:text-white uppercase tracking-wider">
                            ${escapeHtml(group.name)}
                        </label>
                        <span class="text-[11px] font-semibold ${min > 0 ? 'text-brand-orange' : 'text-gray-400'}">${badgeRule}</span>
                    </div>
                    <div class="space-y-1.5" data-options-list></div>
                `;

                const optList = groupEl.querySelector('[data-options-list]');

                (group.options || []).forEach(opt => {
                    const delta = opt.price_delta_minor || 0;
                    const deltaText = delta > 0 ? ` (+${formatMoney(delta)})` : '';
                    const isSingleChoice = max === 1;

                    const label = document.createElement('label');
                    label.className = 'flex items-center justify-between p-2 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark cursor-pointer hover:border-brand-orange transition-colors text-xs';
                    label.innerHTML = `
                        <div class="flex items-center gap-2.5">
                            <input
                                type="${isSingleChoice ? 'radio' : 'checkbox'}"
                                name="opt_group_${group.id}${isSingleChoice ? '' : '[]'}"
                                value="${opt.id}"
                                data-delta="${delta}"
                                class="w-4 h-4 text-brand-orange focus:ring-brand-orange rounded-sm"
                            >
                            <span class="text-brand-text dark:text-white font-medium">${escapeHtml(opt.name)}</span>
                        </div>
                        <span class="font-semibold text-gray-500">${deltaText}</span>
                    `;

                    const input = label.querySelector('input');
                    input.addEventListener('change', function () {
                        enforceGroupLimits(groupEl, input, max);
                        updateModalTotal();
                    });

                    optList.appendChild(label);
                });

                groupsContainer.appendChild(groupEl);
            });
        }

        // Cacher message d'erreur éventuel
        const errorBox = document.getElementById('options-validation-error');
        if (errorBox) errorBox.classList.add('hidden');

        updateModalTotal();
        window.openModal('options-modal');
    }

    // Application stricte des limites min/max par groupe d'options
    function enforceGroupLimits(groupEl, changedInput, max) {
        if (max <= 1) return;
        const checked = groupEl.querySelectorAll('input:checked');
        if (checked.length > max) {
            changedInput.checked = false;
            window.showToast(`Vous pouvez sélectionner au maximum ${max} options pour ce groupe.`, 'warning', 3000);
        }
    }

    // Calcul en temps réel du total affiché dans la modale d'options
    function updateModalTotal() {
        if (!state.currentCustomizingProduct) return;
        let baseMinor = state.currentCustomizingProduct.price_minor || 0;

        // Delta variante
        const selectedVariant = document.querySelector('input[name="opt_variant"]:checked');
        if (selectedVariant) {
            baseMinor += parseInt(selectedVariant.getAttribute('data-delta'), 10) || 0;
        }

        // Delta options
        const checkedOptions = document.querySelectorAll('#options-groups-container input:checked');
        checkedOptions.forEach(opt => {
            baseMinor += parseInt(opt.getAttribute('data-delta'), 10) || 0;
        });

        const qty = parseInt(document.getElementById('opt-quantity-picker')?.value, 10) || 1;
        const total = baseMinor * qty;

        const totalEl = document.getElementById('options-modal-total-display');
        if (totalEl) totalEl.textContent = formatMoney(total);
    }

    // Traitement de validation et ajout au panier depuis la modale
    function handleAddCustomizedToCart() {
        const product = state.currentCustomizingProduct;
        if (!product) return;

        const errorBox = document.getElementById('options-validation-error');
        if (errorBox) errorBox.classList.add('hidden');

        // Validation des règles min requises pour chaque groupe
        const groupElements = document.querySelectorAll('#options-groups-container [data-group-id]');
        for (let groupEl of groupElements) {
            const min = parseInt(groupEl.getAttribute('data-min'), 10) || 0;
            const checkedCount = groupEl.querySelectorAll('input:checked').length;
            if (checkedCount < min) {
                const groupName = groupEl.querySelector('label').textContent.trim();
                if (errorBox) {
                    errorBox.textContent = `Veuillez sélectionner au moins ${min} choix pour "${groupName}".`;
                    errorBox.classList.remove('hidden');
                }
                return;
            }
        }

        // Extraction de la variante choisie
        let variantId = null;
        let variantName = null;
        let unitPriceMinor = product.price_minor;

        const selectedVariant = document.querySelector('input[name="opt_variant"]:checked');
        if (selectedVariant) {
            variantId = parseInt(selectedVariant.value, 10);
            const variantObj = (product.variants || []).find(v => v.id === variantId);
            if (variantObj) {
                variantName = variantObj.name;
                unitPriceMinor += (variantObj.price_delta_minor || 0);
            }
        }

        // Extraction des options choisies
        const options = [];
        const checkedOptions = document.querySelectorAll('#options-groups-container input:checked');
        checkedOptions.forEach(optInput => {
            const optId = parseInt(optInput.value, 10);
            let foundOpt = null;
            (product.option_groups || []).forEach(g => {
                const o = (g.options || []).find(x => x.id === optId);
                if (o) foundOpt = o;
            });

            if (foundOpt) {
                unitPriceMinor += (foundOpt.price_delta_minor || 0);
                options.push({
                    id: foundOpt.id,
                    name: foundOpt.name,
                    priceDeltaMinor: foundOpt.price_delta_minor || 0,
                });
            }
        });

        const qty = parseInt(document.getElementById('opt-quantity-picker')?.value, 10) || 1;
        const instructions = (document.getElementById('opt-instructions')?.value || '').trim();

        addToCart({
            productId: product.id,
            productName: product.name,
            variantId: variantId,
            variantName: variantName,
            unitPriceMinor: unitPriceMinor,
            quantity: qty,
            options: options,
            instructions: instructions,
            lineTotalMinor: unitPriceMinor * qty,
        });

        window.closeModal('options-modal');
        window.showToast(`"${product.name}" ajouté à votre commande`, 'success', 2500);
    }

    // Ajout d'une ligne d'article dans le panier
    function addToCart(item) {
        // Recherche si une ligne identique existe déjà (même produit, variante, options et instructions)
        const existingIdx = state.cart.findIndex(c => {
            if (c.productId !== item.productId) return false;
            if (c.variantId !== item.variantId) return false;
            if (c.instructions !== item.instructions) return false;
            if (c.options.length !== item.options.length) return false;
            const currentIds = c.options.map(o => o.id).sort().join(',');
            const newIds = item.options.map(o => o.id).sort().join(',');
            return currentIds === newIds;
        });

        if (existingIdx >= 0) {
            state.cart[existingIdx].quantity += item.quantity;
            state.cart[existingIdx].lineTotalMinor = state.cart[existingIdx].unitPriceMinor * state.cart[existingIdx].quantity;
        } else {
            item.cartId = 'cart_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
            state.cart.push(item);
        }

        saveCart();
        updateCartUi();
    }

    // Mise à jour complète de l'interface du panier
    function updateCartUi() {
        const totalCount = state.cart.reduce((sum, item) => sum + item.quantity, 0);
        const totalAmountMinor = state.cart.reduce((sum, item) => sum + item.lineTotalMinor, 0);

        // Barre flottante
        const floatingBar = document.getElementById('client-floating-cart-bar');
        const countBadge = document.getElementById('floating-cart-count');
        const totalDisplay = document.getElementById('floating-cart-total');

        if (floatingBar) {
            if (totalCount > 0) {
                floatingBar.classList.remove('hidden');
                if (countBadge) countBadge.textContent = totalCount;
                if (totalDisplay) totalDisplay.textContent = formatMoney(totalAmountMinor);
            } else {
                floatingBar.classList.add('hidden');
            }
        }

        // Tiroir / Modal panier
        const drawerCount = document.getElementById('cart-drawer-items-count');
        const emptyState = document.getElementById('cart-empty-state');
        const itemsList = document.getElementById('cart-items-list');
        const subtotalEl = document.getElementById('cart-summary-subtotal');
        const totalEl = document.getElementById('cart-summary-total');
        const confirmBtn = document.getElementById('btn-confirm-order');

        if (drawerCount) drawerCount.textContent = `${totalCount} article${totalCount > 1 ? 's' : ''}`;
        if (subtotalEl) subtotalEl.textContent = formatMoney(totalAmountMinor);
        if (totalEl) totalEl.textContent = formatMoney(totalAmountMinor);

        if (confirmBtn) {
            confirmBtn.disabled = totalCount === 0;
        }

        if (totalCount === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
            if (itemsList) itemsList.innerHTML = '';
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');
        if (!itemsList) return;

        itemsList.innerHTML = '';

        state.cart.forEach(item => {
            const itemEl = document.createElement('div');
            itemEl.className = 'p-3 rounded-brand bg-gray-50 dark:bg-brand-dark border border-gray-200 dark:border-brand-dark-border space-y-2';

            const optionsText = (item.options || []).map(o => escapeHtml(o.name)).join(', ');

            itemEl.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h4 class="text-xs font-bold text-brand-text dark:text-white leading-tight">
                            ${escapeHtml(item.productName)}
                        </h4>
                        ${item.variantName ? `<span class="text-[11px] font-semibold text-brand-orange block">${escapeHtml(item.variantName)}</span>` : ''}
                        ${optionsText ? `<span class="text-[11px] text-gray-500 block leading-tight">+ ${optionsText}</span>` : ''}
                        ${item.instructions ? `<span class="text-[10px] text-amber-600 dark:text-amber-400 italic block mt-0.5">Note: ${escapeHtml(item.instructions)}</span>` : ''}
                    </div>
                    <span class="text-xs font-black text-brand-text dark:text-white shrink-0">
                        ${formatMoney(item.lineTotalMinor)}
                    </span>
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-brand-dark-border/50">
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn-qty-minus touch-target w-7 h-7 rounded-brand bg-white dark:bg-brand-dark-card border border-gray-300 dark:border-brand-dark-border text-brand-text dark:text-white flex items-center justify-center font-bold text-xs hover:bg-gray-100 focus-ring">
                            -
                        </button>
                        <span class="text-xs font-bold text-brand-text dark:text-white w-5 text-center">${item.quantity}</span>
                        <button type="button" class="btn-qty-plus touch-target w-7 h-7 rounded-brand bg-white dark:bg-brand-dark-card border border-gray-300 dark:border-brand-dark-border text-brand-text dark:text-white flex items-center justify-center font-bold text-xs hover:bg-gray-100 focus-ring">
                            +
                        </button>
                    </div>

                    <button type="button" class="btn-cart-remove text-[11px] text-rose-600 dark:text-rose-400 font-semibold hover:underline">
                        Supprimer
                    </button>
                </div>
            `;

            // Boutons quantité et suppression
            itemEl.querySelector('.btn-qty-minus').addEventListener('click', () => {
                if (item.quantity > 1) {
                    item.quantity--;
                    item.lineTotalMinor = item.unitPriceMinor * item.quantity;
                } else {
                    state.cart = state.cart.filter(c => c.cartId !== item.cartId);
                }
                saveCart();
                updateCartUi();
            });

            itemEl.querySelector('.btn-qty-plus').addEventListener('click', () => {
                if (item.quantity < 99) {
                    item.quantity++;
                    item.lineTotalMinor = item.unitPriceMinor * item.quantity;
                    saveCart();
                    updateCartUi();
                }
            });

            itemEl.querySelector('.btn-cart-remove').addEventListener('click', () => {
                state.cart = state.cart.filter(c => c.cartId !== item.cartId);
                saveCart();
                updateCartUi();
            });

            itemsList.appendChild(itemEl);
        });
    }

    // Basculement de mode de commande
    function setOrderMode(mode) {
        state.orderMode = mode;
        const btnDineIn = document.getElementById('btn-mode-dine-in');
        const btnTakeaway = document.getElementById('btn-mode-takeaway');
        const noteEl = document.getElementById('cart-mode-note');

        if (mode === 'dine_in') {
            btnDineIn?.classList.add('bg-brand-orange', 'text-white', 'shadow-xs');
            btnDineIn?.classList.remove('text-gray-600', 'dark:text-gray-400');
            btnTakeaway?.classList.remove('bg-brand-orange', 'text-white', 'shadow-xs');
            btnTakeaway?.classList.add('text-gray-600', 'dark:text-gray-400');
            if (noteEl) {
                if (state.table) {
                    noteEl.innerHTML = `Commande servie directement à votre <strong>Table ${escapeHtml(state.table.label || state.table.id)}</strong>.`;
                } else if (state.qrToken) {
                    noteEl.innerHTML = 'Commande servie sur place.';
                } else {
                    noteEl.innerHTML = `<span class="text-amber-600 dark:text-amber-400 font-medium">Aucun QR code de table scanné. Pour commander à table, scannez le QR code de votre table, ou choisissez <strong>« À emporter »</strong> ci-dessus.</span>`;
                }
            }
        } else {
            btnTakeaway?.classList.add('bg-brand-orange', 'text-white', 'shadow-xs');
            btnTakeaway?.classList.remove('text-gray-600', 'dark:text-gray-400');
            btnDineIn?.classList.remove('bg-brand-orange', 'text-white', 'shadow-xs');
            btnDineIn?.classList.add('text-gray-600', 'dark:text-gray-400');
            if (noteEl) {
                noteEl.innerHTML = 'Commande préparée pour être emportée au comptoir.';
            }
        }

        updateRestaurantHeader();
        saveCart();
    }

    // Soumission de la commande client vers le backend
    async function submitOrder() {
        const confirmBtn = document.getElementById('btn-confirm-order');
        const errorBox = document.getElementById('cart-submit-error');
        if (errorBox) errorBox.classList.add('hidden');

        if (state.cart.length === 0) {
            if (errorBox) {
                errorBox.textContent = 'Votre panier est vide. Veuillez ajouter au moins un plat avant de valider.';
                errorBox.classList.remove('hidden');
            }
            return;
        }

        // Garde-fou commande sur place sans QR code de table (EF-Client-02 / Backend 422)
        if (state.orderMode === 'dine_in' && !state.qrToken) {
            if (errorBox) {
                errorBox.innerHTML = `
                    <div class="space-y-1">
                        <p class="font-bold">QR Code de table requis pour la commande sur place</p>
                        <p class="text-[11px]">Pour que la cuisine sache à quelle table livrer, vous devez scanner le QR Code de votre table.</p>
                        <p class="text-[11px]">Pour commander en direct sans être à table, sélectionnez le mode <strong>« À emporter »</strong> ci-dessus puis cliquez à nouveau sur <strong>« Valider ma commande »</strong>.</p>
                    </div>
                `;
                errorBox.classList.remove('hidden');
            }
            return;
        }

        // Préparation du payload conforme à StorePublicOrderRequest
        const payload = {
            mode: state.orderMode,
            qr_token: state.qrToken || undefined,
            customer_name: (document.getElementById('cart-customer-name')?.value || '').trim() || undefined,
            customer_phone: (document.getElementById('cart-customer-phone')?.value || '').trim() || undefined,
            notes: (document.getElementById('cart-order-notes')?.value || '').trim() || undefined,
            items: state.cart.map(item => ({
                product_id: item.productId,
                variant_id: item.variantId || undefined,
                quantity: item.quantity,
                option_ids: (item.options || []).map(o => o.id),
                instructions: item.instructions || undefined,
            }))
        };

        try {
            if (confirmBtn) {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Envoi en cuisine...
                `;
            }

            const response = await fetch(`/api/v1/public/restaurants/${encodeURIComponent(state.restaurantSlug)}/orders`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken ? window.csrfToken() : '',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                let errorMessage = 'Une erreur est survenue lors de la validation.';
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join('<br>');
                } else if (data.message) {
                    errorMessage = data.message;
                }
                if (errorBox) {
                    errorBox.innerHTML = errorMessage;
                    errorBox.classList.remove('hidden');
                }
                return;
            }

            // Commande validée avec succès (201 Created)
            const createdOrder = data.data;

            // Vider le panier
            state.cart = [];
            saveCart();
            updateCartUi();

            // Fermer le panier modal
            window.closeModal('cart-drawer-modal');

            // Afficher le toast
            window.showToast(`Commande #${createdOrder.number} enregistrée !`, 'success', 4000);

            // Basculer vers la vue de suivi
            state.activeTrackingToken = createdOrder.tracking_token;
            try {
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('tracking', createdOrder.tracking_token);
                window.history.pushState({}, '', newUrl);
            } catch (e) {}

            renderTrackingOrder(createdOrder);
            initTrackingRealtime(createdOrder.tracking_token);

        } catch (error) {
            if (errorBox) {
                errorBox.textContent = 'Erreur réseau lors de l\'envoi de la commande. Veuillez vérifier votre connexion.';
                errorBox.classList.remove('hidden');
            }
        } finally {
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Valider ma commande';
            }
        }
    }

    // Chargement du statut d'une commande via GET api/v1/public/orders/{token}
    async function loadOrderTracking(token) {
        try {
            const response = await fetch(`/api/v1/public/orders/${encodeURIComponent(token)}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                showError('Commande introuvable', 'Ce lien de suivi de commande est introuvable ou a expiré.');
                return;
            }

            const json = await response.json();
            const order = json.data;

            renderTrackingOrder(order);
            initTrackingRealtime(token);
            showLoading(false);
        } catch (error) {
            showError('Erreur de suivi', 'Impossible de récupérer le statut de votre commande.');
        }
    }

    // Rendu de l'écran de suivi
    function renderTrackingOrder(order) {
        state.trackingOrder = order;

        // Masquer le menu et afficher le suivi
        document.getElementById('client-menu-container')?.classList.add('hidden');
        document.getElementById('client-category-bar')?.classList.add('hidden');
        document.getElementById('client-floating-cart-bar')?.classList.add('hidden');

        const trackingView = document.getElementById('client-tracking-view');
        if (!trackingView) return;
        trackingView.classList.remove('hidden');

        // Afficher bouton de retour au suivi dans le header
        const viewActiveOrderBtn = document.getElementById('btn-view-active-order');
        if (viewActiveOrderBtn) viewActiveOrderBtn.classList.remove('hidden');

        // Métadonnées commande
        document.getElementById('track-order-number').textContent = `#${order.number || order.id}`;
        document.getElementById('track-order-mode').textContent = order.mode === 'dine_in' ? 'Sur place' : 'À emporter';
        document.getElementById('track-order-table').textContent = order.table ? `Table ${order.table.label || order.table.id}` : 'Sans table';
        document.getElementById('track-order-time').textContent = order.created_at ? new Date(order.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
        document.getElementById('track-order-total').textContent = formatMoney(order.total_minor, order.currency);

        // Badge de paiement
        const paymentContainer = document.getElementById('track-payment-badge-container');
        if (paymentContainer) {
            let paymentBadgeStyle = 'bg-rose-50 text-rose-700 border-rose-300';
            let paymentLabel = 'Impayé';
            if (order.payment_status === 'paid') {
                paymentBadgeStyle = 'bg-emerald-50 text-emerald-800 border-emerald-300';
                paymentLabel = 'Payé';
            } else if (order.payment_status === 'partially_paid') {
                paymentBadgeStyle = 'bg-amber-50 text-amber-800 border-amber-300';
                paymentLabel = 'Partiellement payé';
            }
            paymentContainer.innerHTML = `<span class="px-2.5 py-1 text-xs font-bold rounded-full border ${paymentBadgeStyle}">${paymentLabel}</span>`;
        }

        // Stepper de préparation
        const status = order.preparation_status || 'pending';
        updateStepper(status);

        // Liste des articles commandés
        const itemsList = document.getElementById('track-items-list');
        if (itemsList) {
            itemsList.innerHTML = (order.items || []).map(item => {
                const optionsText = (item.options || []).map(o => o.option_name || o.name).join(', ');
                return `
                    <div class="flex items-center justify-between py-1 border-b border-gray-100 dark:border-brand-dark-border/40">
                        <div class="min-w-0 pr-2">
                            <span class="font-bold text-brand-text dark:text-white">${item.quantity}x ${escapeHtml(item.product_name)}</span>
                            ${item.variant_name ? `<span class="text-[10px] text-brand-orange block">${escapeHtml(item.variant_name)}</span>` : ''}
                            ${optionsText ? `<span class="text-[10px] text-gray-500 block leading-tight">+ ${optionsText}</span>` : ''}
                        </div>
                        <span class="font-semibold text-gray-700 dark:text-gray-300 shrink-0">${formatMoney(item.line_total_minor, order.currency)}</span>
                    </div>
                `;
            }).join('');
        }
    }

    // Mise à jour visuelle du stepper selon le statut
    function updateStepper(status) {
        const steps = ['pending', 'accepted', 'preparing', 'ready'];
        let currentIdx = steps.indexOf(status);

        if (status === 'served' || status === 'completed') {
            currentIdx = steps.length;
        }

        const rejectedBox = document.getElementById('track-rejected-box');

        if (status === 'rejected' || status === 'cancelled') {
            if (rejectedBox) rejectedBox.classList.remove('hidden');
            steps.forEach(s => {
                const el = document.getElementById(`step-${s}`);
                if (el) el.classList.add('opacity-30');
            });
            return;
        }

        if (rejectedBox) rejectedBox.classList.add('hidden');

        steps.forEach((s, idx) => {
            const stepEl = document.getElementById(`step-${s}`);
            if (!stepEl) return;

            const iconEl = stepEl.querySelector('.rounded-full');

            if (idx < currentIdx) {
                // Étape passée
                stepEl.classList.remove('opacity-40');
                if (iconEl) {
                    iconEl.className = 'w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0 z-10 shadow-sm';
                    iconEl.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
                }
            } else if (idx === currentIdx) {
                // Étape en cours
                stepEl.classList.remove('opacity-40');
                if (iconEl) {
                    iconEl.className = 'w-8 h-8 rounded-full bg-brand-orange text-white flex items-center justify-center font-bold text-xs shrink-0 z-10 shadow-sm animate-pulse';
                    iconEl.textContent = idx + 1;
                }
            } else {
                // Étape future
                stepEl.classList.add('opacity-40');
                if (iconEl) {
                    iconEl.className = 'w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center font-bold text-xs shrink-0 z-10';
                    iconEl.textContent = idx + 1;
                }
            }
        });
    }

    // ARB-02 / REC-13 & CA-03 : Suivi client en temps réel avec résilience
    function initTrackingRealtime(token) {
        if (!token) return;

        if (state.realtimeClient) {
            state.realtimeClient.disconnect();
            state.realtimeClient = null;
        }

        // Polling de secours actif dès le chargement (REC-13)
        startTrackingPolling(token, 4000);

        // Connexion WebSocket temps réel sur le canal public
        state.realtimeClient = createRealtimeClient({
            onDegraded(isDegraded) {
                state.isDegraded = isDegraded;
                if (isDegraded) {
                    startTrackingPolling(token, 4000);
                }
            },
            onReconnect() {
                // Reconnexion confirmée : resynchronisation complète (REC-13)
                resyncTrackingOrder(token);
            }
        });

        // Souscription au canal public orders.{trackingToken}
        const channelName = `orders.${token}`;
        state.realtimeSub = state.realtimeClient.subscribe(channelName, {
            'orders.status.updated': handleTrackingStatusUpdated,
        });
    }

    // CA-03 : Mise à jour du stepper client
    function handleTrackingStatusUpdated(data) {
        if (!data || !data.to_status) return;

        // Idempotence temporelle : vérifier updated_at si fourni
        if (state.trackingOrder && data.updated_at && state.trackingOrder.updated_at) {
            if (new Date(data.updated_at) < new Date(state.trackingOrder.updated_at)) {
                return;
            }
        }

        if (state.trackingOrder) {
            state.trackingOrder.preparation_status = data.to_status;
            if (data.accepted_at) state.trackingOrder.accepted_at = data.accepted_at;
            if (data.ready_at) state.trackingOrder.ready_at = data.ready_at;
            if (data.completed_at) state.trackingOrder.completed_at = data.completed_at;
            if (data.cancelled_at) state.trackingOrder.cancelled_at = data.cancelled_at;
            if (data.updated_at) state.trackingOrder.updated_at = data.updated_at;
        }

        updateStepper(data.to_status);

        if (data.to_status === 'ready') {
            window.showToast('Votre commande est prête au comptoir !', 'success', 8000);
        } else if (data.to_status === 'accepted') {
            window.showToast('Votre commande a été acceptée par la cuisine !', 'info', 4000);
        } else if (data.to_status === 'preparing') {
            window.showToast('Votre commande est en cours de préparation !', 'info', 4000);
        } else if (data.to_status === 'served' || data.to_status === 'completed') {
            window.showToast('Votre commande a été servie. Bon appétit !', 'success', 6000);
            stopTrackingPolling();
        } else if (data.to_status === 'rejected' || data.to_status === 'cancelled') {
            window.showToast('Votre commande n\'a pas pu être préparée.', 'error', 8000);
            stopTrackingPolling();
        }
    }

    // Polling de secours automatique (REC-13)
    function startTrackingPolling(token, intervalMs = 4000) {
        stopTrackingPolling();
        state.trackingInterval = setInterval(async function () {
            await resyncTrackingOrder(token);
        }, intervalMs);
    }

    function stopTrackingPolling() {
        if (state.trackingInterval) {
            clearInterval(state.trackingInterval);
            state.trackingInterval = null;
        }
    }

    async function resyncTrackingOrder(token) {
        try {
            const res = await fetch(`/api/v1/public/orders/${encodeURIComponent(token)}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                const json = await res.json();
                const order = json.data;
                const prevStatus = state.trackingOrder?.preparation_status;
                state.trackingOrder = order;
                renderTrackingOrder(order);

                // Si le statut a évolué via le polling, informer le client avec un toast
                if (prevStatus && prevStatus !== order.preparation_status) {
                    if (order.preparation_status === 'accepted') {
                        window.showToast('Votre commande a été acceptée par la cuisine !', 'info', 4000);
                    } else if (order.preparation_status === 'preparing') {
                        window.showToast('Votre commande est en cours de préparation !', 'info', 4000);
                    } else if (order.preparation_status === 'ready') {
                        window.showToast('Votre commande est prête au comptoir !', 'success', 8000);
                    } else if (['served', 'completed'].includes(order.preparation_status)) {
                        window.showToast('Votre commande a été servie. Bon appétit !', 'success', 6000);
                    }
                }

                // Si commande terminée ou annulée, arrêter le polling
                if (['completed', 'served', 'rejected', 'cancelled'].includes(order.preparation_status)) {
                    stopTrackingPolling();
                }
            }
        } catch (e) {
            console.warn('Erreur lors du rafraîchissement de suivi:', e);
        }
    }

    // Soumission d'une demande d'assistance (EF-Client-04)
    async function submitAssistanceRequest() {
        const typeInput = document.querySelector('input[name="assistance_type"]:checked');
        const type = typeInput ? typeInput.value : 'waiter';
        const message = (document.getElementById('assistance-message')?.value || '').trim();
        const errorBox = document.getElementById('assistance-error-box');
        const submitBtn = document.getElementById('btn-submit-assistance');

        if (errorBox) errorBox.classList.add('hidden');

        // Il faut au moins un qr_token ou tracking_token
        if (!state.qrToken && !state.activeTrackingToken) {
            if (errorBox) {
                errorBox.textContent = 'Impossible d\'envoyer la demande sans numéro de table ou commande active. Veuillez scanner le QR Code.';
                errorBox.classList.remove('hidden');
            }
            return;
        }

        const payload = {
            type: type,
            message: message || undefined,
            qr_token: state.qrToken || undefined,
            tracking_token: state.activeTrackingToken || undefined,
        };

        try {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Envoi en cours...';
            }

            const response = await fetch(`/api/v1/public/restaurants/${encodeURIComponent(state.restaurantSlug)}/assistance-requests`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken ? window.csrfToken() : '',
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                const data = await response.json();
                if (errorBox) {
                    errorBox.textContent = data.message || 'Une erreur est survenue lors de l\'envoi de la demande.';
                    errorBox.classList.remove('hidden');
                }
                return;
            }

            window.closeModal('assistance-modal');
            window.showToast('Votre demande a bien été transmise à l\'équipe en salle.', 'success', 4000);
            document.getElementById('assistance-message').value = '';
        } catch (error) {
            if (errorBox) {
                errorBox.textContent = 'Erreur réseau. Veuillez réessayer.';
                errorBox.classList.remove('hidden');
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Envoyer la demande';
            }
        }
    }

    // Gestion de l'affichage des états de chargement et d'erreur
    function showLoading(show) {
        const skeleton = document.getElementById('client-loading-state');
        const container = document.getElementById('client-menu-container');
        const errorState = document.getElementById('client-error-state');
        if (skeleton) skeleton.classList.toggle('hidden', !show);
        if (container && show) container.innerHTML = '';
        if (errorState && show) errorState.classList.add('hidden');
    }

    function showError(title, message) {
        showLoading(false);
        const errorState = document.getElementById('client-error-state');
        const titleEl = document.getElementById('client-error-title');
        const msgEl = document.getElementById('client-error-message');

        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;
        if (errorState) errorState.classList.remove('hidden');
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

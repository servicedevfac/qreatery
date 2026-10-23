/**
 * Qreatery — Espace Gestion Interne Propriétaire de Restaurant
 * JavaScript natif complet pour interactions, modales, CRUD RESTful et gestion opérationnelle en direct.
 */

document.addEventListener('DOMContentLoaded', function () {
    var ctx = window.QreateryContext || {};
    var restaurantId = ctx.restaurantId || 1;
    var restaurantSlug = ctx.restaurantSlug || 'restaurant-demo';
    var csrfToken = ctx.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    /* ==========================================================
     * 0. SYSTÈME DE MODALES ET NOTIFICATIONS TOAST
     * ========================================================== */
    function openModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    document.querySelectorAll('.btn-close-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modalId = this.getAttribute('data-modal');
            if (modalId) closeModal(modalId);
        });
    });

    function showToast(message, type) {
        type = type || 'success';
        if (window.showToast) {
            window.showToast(message, type);
            return;
        }

        var toast = document.createElement('div');
        toast.className = 'fixed bottom-5 right-5 z-50 px-4 py-3 rounded-brand shadow-xl text-xs font-bold transition-all transform duration-300 flex items-center gap-2 ' +
            (type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white');
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(function () { toast.remove(); }, 300);
        }, 3500);
    }

    function requireAuth(actionCallback) {
        if (!ctx.isAuthenticated) {
            openModal('modal-owner-auth');
            return false;
        }
        if (actionCallback) actionCallback();
        return true;
    }

    /* ==========================================================
     * 1. AUTHENTIFICATION & SESSIONS PROPRIÉTAIRE
     * ========================================================== */
    var btnOpenAuth = document.getElementById('btn-open-owner-auth');
    var btnCloseAuth = document.getElementById('btn-close-owner-auth');
    var btnHeaderQuick = document.getElementById('btn-header-quick-login');
    var btnBannerQuick = document.getElementById('btn-banner-quick-login');
    var btnQuickLogin = document.getElementById('btn-quick-login-owner');
    var authErrorBox = document.getElementById('owner-auth-error');
    var loginForm = document.getElementById('owner-login-form');
    var btnLogout = document.getElementById('btn-owner-logout');

    if (btnOpenAuth) btnOpenAuth.addEventListener('click', function () { openModal('modal-owner-auth'); });
    if (btnCloseAuth) btnCloseAuth.addEventListener('click', function () { closeModal('modal-owner-auth'); });
    if (btnHeaderQuick) btnHeaderQuick.addEventListener('click', function () { performLogin('owner@qreatery.test', 'Password123!'); });
    if (btnBannerQuick) btnBannerQuick.addEventListener('click', function () { performLogin('owner@qreatery.test', 'Password123!'); });
    if (btnQuickLogin) btnQuickLogin.addEventListener('click', function () { performLogin('owner@qreatery.test', 'Password123!'); });

    async function performLogin(email, password) {
        if (authErrorBox) authErrorBox.classList.add('hidden');
        try {
            var res = await fetch('/api/v1/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin',
                body: JSON.stringify({ email: email, password: password })
            });

            if (!res.ok) {
                var errData = await res.json().catch(function () { return {}; });
                if (authErrorBox) {
                    authErrorBox.textContent = errData.message || 'Identifiants incorrects.';
                    authErrorBox.classList.remove('hidden');
                }
                return;
            }

            closeModal('modal-owner-auth');
            showToast('Connexion réussie ! Chargement de votre espace...', 'success');
            setTimeout(function () {
                window.location.reload();
            }, 600);
        } catch (err) {
            console.error('Erreur login:', err);
            if (authErrorBox) {
                authErrorBox.textContent = 'Erreur réseau lors de la tentative de connexion.';
                authErrorBox.classList.remove('hidden');
            }
        }
    }

    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var email = document.getElementById('owner-email')?.value;
            var password = document.getElementById('owner-password')?.value;
            performLogin(email, password);
        });
    }

    if (btnLogout) {
        btnLogout.addEventListener('click', async function () {
            try {
                await fetch('/api/v1/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                });
            } catch (e) {}
            window.location.reload();
        });
    }

    /* ==========================================================
     * 2. SÉLECTEUR D'ÉTABLISSEMENT ACTIF & REFRESH
     * ========================================================== */
    var restoSwitcher = document.getElementById('restaurant-switcher');
    if (restoSwitcher) {
        restoSwitcher.addEventListener('change', function () {
            var selectedSlug = this.value;
            var currentSection = ctx.activeSection || 'dashboard';
            window.location.href = '?view=restaurant&restaurant=' + encodeURIComponent(selectedSlug) + '&section=' + encodeURIComponent(currentSection);
        });
    }

    var refreshBtn = document.getElementById('btn-refresh-restaurant');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            window.location.reload();
        });
    }

    /* ==========================================================
     * 3. ONGLETS DE LA CARTE / MENU (Plats, Catégories, Options)
     * ========================================================== */
    var menuTabBtns = document.querySelectorAll('.menu-tab-btn');
    var menuTabContents = document.querySelectorAll('.menu-tab-content');

    menuTabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetTab = this.getAttribute('data-tab');

            menuTabBtns.forEach(function (b) {
                b.classList.remove('bg-white', 'dark:bg-brand-dark-card', 'text-brand-orange', 'shadow-xs');
                b.classList.add('text-gray-600', 'dark:text-gray-400');
            });
            this.classList.add('bg-white', 'dark:bg-brand-dark-card', 'text-brand-orange', 'shadow-xs');
            this.classList.remove('text-gray-600', 'dark:text-gray-400');

            menuTabContents.forEach(function (content) {
                if (content.id === targetTab) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });
        });
    });

    /* ==========================================================
     * 4. GESTION DES PRODUITS / PLATS DU MENU
     * ========================================================== */
    var searchInput = document.getElementById('filter-product-search');
    var catBtns = document.querySelectorAll('.filter-cat-btn');
    var productCards = document.querySelectorAll('.product-card');

    var currentCatFilter = '';
    var currentSearchText = '';

    function filterProducts() {
        productCards.forEach(function (card) {
            var name = card.getAttribute('data-name') || '';
            var catId = card.getAttribute('data-category-id') || '';

            var matchSearch = !currentSearchText || name.includes(currentSearchText);
            var matchCat = !currentCatFilter || catId === currentCatFilter;

            if (matchSearch && matchCat) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentSearchText = this.value.trim().toLowerCase();
            filterProducts();
        });
    }

    catBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            catBtns.forEach(function (b) {
                b.classList.remove('bg-brand-orange', 'text-white', 'font-bold');
                b.classList.add('bg-gray-100', 'dark:bg-brand-dark', 'text-gray-600', 'dark:text-gray-400', 'font-semibold');
            });
            this.classList.add('bg-brand-orange', 'text-white', 'font-bold');
            this.classList.remove('bg-gray-100', 'dark:bg-brand-dark', 'text-gray-600', 'dark:text-gray-400', 'font-semibold');

            currentCatFilter = this.getAttribute('data-category') || '';
            filterProducts();
        });
    });

    // Bascule rapide de disponibilité
    document.querySelectorAll('.toggle-product-availability').forEach(function (checkbox) {
        checkbox.addEventListener('change', async function () {
            var productId = this.getAttribute('data-id');
            var isAvailable = this.checked;
            var label = this.closest('label').querySelector('.availability-label');

            if (label) {
                label.textContent = isAvailable ? 'En stock' : 'Rupture';
                if (isAvailable) {
                    label.classList.add('text-emerald-600', 'dark:text-emerald-400');
                    label.classList.remove('text-gray-400');
                } else {
                    label.classList.remove('text-emerald-600', 'dark:text-emerald-400');
                    label.classList.add('text-gray-400');
                }
            }

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/products/' + productId, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ is_available: isAvailable })
                });

                if (res.status === 401) {
                    openModal('modal-owner-auth');
                    return;
                }
                if (!res.ok) {
                    showToast('Erreur lors du changement de disponibilité', 'error');
                } else {
                    showToast(isAvailable ? 'Plat remis en stock' : 'Plat marqué en rupture', 'success');
                }
            } catch (err) {
                console.error('Erreur disponibilité:', err);
            }
        });
    });

    // Ajout d'un nouveau plat
    var btnOpenCreateProduct = document.getElementById('btn-open-create-product');
    var formCreateProduct = document.getElementById('form-create-product');
    var createProductError = document.getElementById('create-product-error');

    if (btnOpenCreateProduct) {
        btnOpenCreateProduct.addEventListener('click', function () {
            requireAuth(function () {
                openModal('modal-create-product');
            });
        });
    }

    if (formCreateProduct) {
        formCreateProduct.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (createProductError) createProductError.classList.add('hidden');

            var name = document.getElementById('prod-name')?.value.trim();
            var categoryId = document.getElementById('prod-category')?.value || null;
            var price = parseFloat(document.getElementById('prod-price')?.value) || 0;
            var prepTime = parseInt(document.getElementById('prod-prep-time')?.value) || null;
            var description = document.getElementById('prod-desc')?.value.trim() || null;
            var isAvailable = document.getElementById('prod-available')?.checked ?? true;

            var payload = {
                name: name,
                category_id: categoryId ? parseInt(categoryId) : null,
                price_minor: Math.round(price * 100),
                preparation_minutes: prepTime,
                description: description,
                is_available: isAvailable,
                status: 'active'
            };

            var submitBtn = document.getElementById('btn-submit-create-product');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Enregistrement...';
            }

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });

                if (res.status === 401) {
                    closeModal('modal-create-product');
                    openModal('modal-owner-auth');
                    return;
                }

                if (!res.ok) {
                    var data = await res.json().catch(function () { return {}; });
                    var errorMsg = data.message || 'Impossible de créer le plat.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    if (createProductError) {
                        createProductError.innerHTML = errorMsg;
                        createProductError.classList.remove('hidden');
                    }
                    return;
                }

                closeModal('modal-create-product');
                showToast('Nouveau plat "' + name + '" ajouté avec succès !', 'success');
                setTimeout(function () { window.location.reload(); }, 800);
            } catch (err) {
                console.error('Erreur création produit:', err);
                if (createProductError) {
                    createProductError.textContent = 'Erreur réseau lors de la création.';
                    createProductError.classList.remove('hidden');
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enregistrer le plat';
                }
            }
        });
    }

    // Modification d'un plat
    var formEditProduct = document.getElementById('form-edit-product');
    var editProductError = document.getElementById('edit-product-error');

    document.querySelectorAll('.btn-edit-product').forEach(function (btn) {
        btn.addEventListener('click', function () {
            requireAuth(function () {
                var id = btn.getAttribute('data-id');
                var name = btn.getAttribute('data-name');
                var catId = btn.getAttribute('data-category-id') || '';
                var price = btn.getAttribute('data-price') || '0';
                var prep = btn.getAttribute('data-prep') || '';
                var desc = btn.getAttribute('data-desc') || '';
                var available = btn.getAttribute('data-available') === '1';

                document.getElementById('edit-prod-id').value = id;
                document.getElementById('edit-prod-name').value = name;
                document.getElementById('edit-prod-category').value = catId;
                document.getElementById('edit-prod-price').value = price;
                document.getElementById('edit-prod-prep-time').value = prep;
                document.getElementById('edit-prod-desc').value = desc;
                document.getElementById('edit-prod-available').checked = available;

                openModal('modal-edit-product');
            });
        });
    });

    if (formEditProduct) {
        formEditProduct.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (editProductError) editProductError.classList.add('hidden');

            var id = document.getElementById('edit-prod-id')?.value;
            var name = document.getElementById('edit-prod-name')?.value.trim();
            var categoryId = document.getElementById('edit-prod-category')?.value || null;
            var price = parseFloat(document.getElementById('edit-prod-price')?.value) || 0;
            var prepTime = parseInt(document.getElementById('edit-prod-prep-time')?.value) || null;
            var description = document.getElementById('edit-prod-desc')?.value.trim() || null;
            var isAvailable = document.getElementById('edit-prod-available')?.checked ?? true;

            var payload = {
                name: name,
                category_id: categoryId ? parseInt(categoryId) : null,
                price_minor: Math.round(price * 100),
                preparation_minutes: prepTime,
                description: description,
                is_available: isAvailable
            };

            var submitBtn = document.getElementById('btn-submit-edit-product');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Mise à jour...';
            }

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/products/' + id, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });

                if (res.status === 401) {
                    closeModal('modal-edit-product');
                    openModal('modal-owner-auth');
                    return;
                }

                if (!res.ok) {
                    var data = await res.json().catch(function () { return {}; });
                    var errorMsg = data.message || 'Impossible de mettre à jour le plat.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    if (editProductError) {
                        editProductError.innerHTML = errorMsg;
                        editProductError.classList.remove('hidden');
                    }
                    return;
                }

                closeModal('modal-edit-product');
                showToast('Plat "' + name + '" mis à jour avec succès !', 'success');
                setTimeout(function () { window.location.reload(); }, 800);
            } catch (err) {
                console.error('Erreur mise à jour produit:', err);
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Mettre à jour';
                }
            }
        });
    }

    // Suppression d'un plat
    document.querySelectorAll('.btn-delete-product').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var name = this.getAttribute('data-name') || 'ce plat';
            var card = this.closest('.product-card');

            requireAuth(async function () {
                if (!confirm('Voulez-vous vraiment supprimer le plat "' + name + '" du menu ?')) {
                    return;
                }

                try {
                    var res = await fetch('/api/v1/restaurants/' + restaurantId + '/products/' + id, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin'
                    });

                    if (res.status === 401) {
                        openModal('modal-owner-auth');
                        return;
                    }

                    if (res.ok) {
                        showToast('Plat "' + name + '" supprimé.', 'success');
                        if (card) {
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.95)';
                            card.style.transition = 'all 0.3s ease';
                            setTimeout(function () { card.remove(); }, 300);
                        }
                    } else {
                        showToast('Impossible de supprimer ce plat.', 'error');
                    }
                } catch (err) {
                    console.error('Erreur suppression produit:', err);
                }
            });
        });
    });

    /* ==========================================================
     * 5. GESTION DES CATÉGORIES
     * ========================================================== */
    var btnOpenCreateCategory = document.getElementById('btn-open-create-category');
    var formCreateCategory = document.getElementById('form-create-category');
    var createCategoryError = document.getElementById('create-category-error');

    if (btnOpenCreateCategory) {
        btnOpenCreateCategory.addEventListener('click', function () {
            requireAuth(function () {
                openModal('modal-create-category');
            });
        });
    }

    if (formCreateCategory) {
        formCreateCategory.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (createCategoryError) createCategoryError.classList.add('hidden');

            var name = document.getElementById('cat-name')?.value.trim();
            var description = document.getElementById('cat-desc')?.value.trim() || null;

            var submitBtn = document.getElementById('btn-submit-create-category');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Création...';
            }

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/categories', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ name: name, description: description, is_active: true })
                });

                if (res.status === 401) {
                    closeModal('modal-create-category');
                    openModal('modal-owner-auth');
                    return;
                }

                if (!res.ok) {
                    var data = await res.json().catch(function () { return {}; });
                    var errorMsg = data.message || 'Impossible de créer la catégorie.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    if (createCategoryError) {
                        createCategoryError.innerHTML = errorMsg;
                        createCategoryError.classList.remove('hidden');
                    }
                    return;
                }

                closeModal('modal-create-category');
                showToast('Catégorie "' + name + '" créée !', 'success');
                setTimeout(function () { window.location.reload(); }, 800);
            } catch (err) {
                console.error('Erreur création catégorie:', err);
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Créer la catégorie';
                }
            }
        });
    }

    // Suppression catégorie
    document.querySelectorAll('.btn-delete-category').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var name = this.getAttribute('data-name') || 'cette catégorie';
            var row = document.querySelector('[data-category-row="' + id + '"]');

            requireAuth(async function () {
                if (!confirm('Voulez-vous vraiment supprimer la catégorie "' + name + '" ?')) {
                    return;
                }

                try {
                    var res = await fetch('/api/v1/restaurants/' + restaurantId + '/categories/' + id, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin'
                    });

                    if (res.status === 401) {
                        openModal('modal-owner-auth');
                        return;
                    }

                    if (res.ok) {
                        showToast('Catégorie "' + name + '" supprimée.', 'success');
                        if (row) {
                            row.style.opacity = '0';
                            setTimeout(function () { row.remove(); }, 300);
                        }
                    } else {
                        showToast('Impossible de supprimer cette catégorie.', 'error');
                    }
                } catch (err) {
                    console.error('Erreur suppression catégorie:', err);
                }
            });
        });
    });

    /* ==========================================================
     * 6. GESTION DES TABLES & PLAN DE SALLE
     * ========================================================== */
    var btnOpenCreateTable = document.getElementById('btn-open-create-table');
    var formCreateTable = document.getElementById('form-create-table');
    var createTableError = document.getElementById('create-table-error');

    if (btnOpenCreateTable) {
        btnOpenCreateTable.addEventListener('click', function () {
            requireAuth(function () {
                openModal('modal-create-table');
            });
        });
    }

    if (formCreateTable) {
        formCreateTable.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (createTableError) createTableError.classList.add('hidden');

            var label = document.getElementById('table-label')?.value.trim();
            var capacity = parseInt(document.getElementById('table-capacity')?.value) || 4;
            var isActive = document.getElementById('table-is-active')?.checked ?? true;

            var submitBtn = document.getElementById('btn-submit-create-table');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Création...';
            }

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/tables', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ label: label, capacity: capacity, is_active: isActive })
                });

                if (res.status === 401) {
                    closeModal('modal-create-table');
                    openModal('modal-owner-auth');
                    return;
                }

                if (!res.ok) {
                    var data = await res.json().catch(function () { return {}; });
                    var errorMsg = data.message || 'Impossible de créer la table.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    if (createTableError) {
                        createTableError.innerHTML = errorMsg;
                        createTableError.classList.remove('hidden');
                    }
                    return;
                }

                closeModal('modal-create-table');
                showToast('Table "' + label + '" créée avec succès !', 'success');
                setTimeout(function () { window.location.reload(); }, 800);
            } catch (err) {
                console.error('Erreur création table:', err);
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Créer la table';
                }
            }
        });
    }

    // Libérer une table occupée (Fermer la session)
    document.querySelectorAll('.btn-close-table').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var label = this.getAttribute('data-label') || 'cette table';
            var card = this.closest('.table-card');

            requireAuth(async function () {
                if (!confirm('Voulez-vous libérer la ' + label + ' ? Les commandes actives de cette table seront clôturées.')) {
                    return;
                }

                try {
                    var res = await fetch('/api/v1/restaurants/' + restaurantId + '/tables/' + id + '/close', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin'
                    });

                    if (res.status === 401) {
                        openModal('modal-owner-auth');
                        return;
                    }

                    if (res.ok) {
                        showToast(label + ' libérée avec succès !', 'success');
                        setTimeout(function () { window.location.reload(); }, 800);
                    } else {
                        showToast('Impossible de libérer la table.', 'error');
                    }
                } catch (err) {
                    console.error('Erreur libération table:', err);
                }
            });
        });
    });

    // Supprimer une table
    document.querySelectorAll('.btn-delete-table').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var label = this.getAttribute('data-label') || 'cette table';
            var card = this.closest('.table-card');

            requireAuth(async function () {
                if (!confirm('Voulez-vous supprimer définitivement la ' + label + ' ?')) {
                    return;
                }

                try {
                    var res = await fetch('/api/v1/restaurants/' + restaurantId + '/tables/' + id, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin'
                    });

                    if (res.status === 401) {
                        openModal('modal-owner-auth');
                        return;
                    }

                    if (res.ok) {
                        showToast(label + ' supprimée.', 'success');
                        if (card) {
                            card.style.opacity = '0';
                            setTimeout(function () { card.remove(); }, 300);
                        }
                    } else {
                        showToast('Impossible de supprimer cette table.', 'error');
                    }
                } catch (err) {
                    console.error('Erreur suppression table:', err);
                }
            });
        });
    });

    /* ==========================================================
     * 7. FILTRES ET GESTION DES COMMANDES
     * ========================================================== */
    var orderSearchInput = document.getElementById('order-search-input');
    var filterModeBtns = document.querySelectorAll('.filter-mode-btn');
    var filterStatusBtns = document.querySelectorAll('.filter-status-btn');
    var orderRows = document.querySelectorAll('.order-row');

    var currentOrderMode = '';
    var currentOrderStatus = '';
    var currentOrderSearch = '';

    function filterOrders() {
        orderRows.forEach(function (row) {
            var num = row.getAttribute('data-number') || '';
            var customer = row.getAttribute('data-customer') || '';
            var mode = row.getAttribute('data-mode') || '';
            var status = row.getAttribute('data-status') || '';

            var matchSearch = !currentOrderSearch || num.includes(currentOrderSearch) || customer.includes(currentOrderSearch);
            var matchMode = !currentOrderMode || mode === currentOrderMode;
            var matchStatus = !currentOrderStatus || status === currentOrderStatus;

            if (matchSearch && matchMode && matchStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (orderSearchInput) {
        orderSearchInput.addEventListener('input', function () {
            currentOrderSearch = this.value.trim().toLowerCase();
            filterOrders();
        });
    }

    filterModeBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filterModeBtns.forEach(function (b) {
                b.classList.remove('bg-brand-orange', 'text-white', 'font-bold');
                b.classList.add('bg-gray-100', 'dark:bg-brand-dark', 'text-gray-600', 'dark:text-gray-400', 'font-semibold');
            });
            this.classList.add('bg-brand-orange', 'text-white', 'font-bold');
            this.classList.remove('bg-gray-100', 'dark:bg-brand-dark', 'text-gray-600', 'dark:text-gray-400', 'font-semibold');

            currentOrderMode = this.getAttribute('data-mode') || '';
            filterOrders();
        });
    });

    filterStatusBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filterStatusBtns.forEach(function (b) {
                b.classList.remove('bg-brand-orange', 'text-white', 'font-bold');
                b.classList.add('font-semibold');
            });
            this.classList.add('bg-brand-orange', 'text-white', 'font-bold');

            currentOrderStatus = this.getAttribute('data-status') || '';
            filterOrders();
        });
    });

    // Modale Détail & Transition de statut commande
    var modalOrder = document.getElementById('modal-order-detail');
    var modalOrderContent = document.getElementById('modal-order-content');
    var modalOrderNumber = document.getElementById('modal-order-number');
    var modalOrderDate = document.getElementById('modal-order-date');
    var btnCloseModal = document.getElementById('btn-close-order-modal');
    var btnCloseModalFooter = document.getElementById('btn-close-order-modal-footer');

    function closeOrderModal() {
        if (modalOrder) modalOrder.classList.add('hidden');
    }

    if (btnCloseModal) btnCloseModal.addEventListener('click', closeOrderModal);
    if (btnCloseModalFooter) btnCloseModalFooter.addEventListener('click', closeOrderModal);

    document.querySelectorAll('.btn-view-order').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            var orderId = this.getAttribute('data-id');
            if (!modalOrder || !modalOrderContent) return;

            modalOrder.classList.remove('hidden');
            modalOrderContent.innerHTML = '<div class="py-8 text-center text-gray-400 animate-pulse">Chargement des détails de la commande...</div>';

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/orders/' + orderId, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (!res.ok) {
                    modalOrderContent.innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded">Impossible de charger la commande.</div>';
                    return;
                }

                var data = await res.json();
                var order = data.data;

                if (modalOrderNumber) modalOrderNumber.textContent = 'Commande #' + order.number;
                if (modalOrderDate) {
                    modalOrderDate.textContent = (order.mode === 'dine_in' ? 'Sur place (Table ' + (order.table?.label || 'N/A') + ')' : 'À emporter') +
                        ' • ' + (order.customer_name || 'Client sans nom');
                }

                var itemsHtml = '';
                if (order.items && order.items.length) {
                    itemsHtml = order.items.map(function (item) {
                        var optionsText = '';
                        if (item.options && item.options.length) {
                            optionsText = '<div class="text-[11px] text-gray-400 mt-0.5">' + item.options.map(function (o) { return o.name; }).join(', ') + '</div>';
                        }
                        return '<div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-brand-dark-border">' +
                            '<div><span class="font-bold">' + item.quantity + 'x</span> ' + item.product_name + optionsText + '</div>' +
                            '<div class="font-bold">' + ((item.line_total_minor || 0) / 100).toLocaleString('fr-FR') + ' ' + (ctx.currency || 'XOF') + '</div>' +
                            '</div>';
                    }).join('');
                }

                // Génération des boutons d'actions selon le statut
                var statusActionsHtml = '<div class="pt-3 border-t border-gray-200 dark:border-brand-dark-border flex flex-wrap items-center gap-2">';
                var prepStatus = order.preparation_status;

                if (prepStatus === 'pending') {
                    statusActionsHtml += '<button type="button" class="btn-change-order-status px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-brand text-xs shadow-xs" data-id="' + order.id + '" data-status="accepted">Accepter la commande</button>';
                    statusActionsHtml += '<button type="button" class="btn-change-order-status px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-brand text-xs shadow-xs" data-id="' + order.id + '" data-status="cancelled">Refuser la commande</button>';
                } else if (prepStatus === 'accepted') {
                    statusActionsHtml += '<button type="button" class="btn-change-order-status px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-brand text-xs shadow-xs" data-id="' + order.id + '" data-status="preparing">Passer en préparation (Cuisine)</button>';
                } else if (prepStatus === 'preparing') {
                    statusActionsHtml += '<button type="button" class="btn-change-order-status px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-brand text-xs shadow-xs" data-id="' + order.id + '" data-status="ready">Marquer comme prête</button>';
                } else if (prepStatus === 'ready') {
                    statusActionsHtml += '<button type="button" class="btn-change-order-status px-3 py-1.5 bg-gray-800 hover:bg-black text-white font-bold rounded-brand text-xs shadow-xs" data-id="' + order.id + '" data-status="served">Marquer comme servie</button>';
                }

                statusActionsHtml += '<a href="/api/v1/restaurants/' + restaurantId + '/orders/' + order.id + '/kitchen-ticket" target="_blank" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark text-gray-700 dark:text-gray-200 font-bold rounded-brand text-xs border border-gray-200 dark:border-brand-dark-border transition-colors">Ticket Cuisine</a>';
                statusActionsHtml += '</div>';

                modalOrderContent.innerHTML =
                    '<div class="space-y-4">' +
                    '<div class="bg-gray-50 dark:bg-brand-dark p-3.5 rounded-brand space-y-2">' +
                    '<div class="flex items-center justify-between text-xs text-gray-500"><span>Statut actuel :</span><span class="font-bold uppercase text-brand-orange">' + (order.preparation_status || 'N/A') + '</span></div>' +
                    '<div class="flex items-center justify-between text-xs text-gray-500"><span>Paiement :</span><span class="font-bold ' + (order.payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600') + '">' + (order.payment_status === 'paid' ? 'Payé' : 'En attente') + '</span></div>' +
                    (order.notes ? '<div class="text-xs text-gray-500 bg-white dark:bg-brand-dark-card p-2 rounded border border-gray-200 dark:border-brand-dark-border"><strong>Remarque client :</strong> ' + order.notes + '</div>' : '') +
                    '</div>' +
                    '<div class="bg-gray-50 dark:bg-brand-dark p-3 rounded-brand">' +
                    '<div class="flex items-center justify-between mb-2 font-bold text-xs uppercase tracking-wider text-gray-500"><span>Articles commandés</span><span>Sous-total</span></div>' +
                    itemsHtml +
                    '<div class="flex items-center justify-between pt-3 font-bold text-sm"><span>Total</span><span class="text-brand-orange text-base">' + ((order.total_minor || 0) / 100).toLocaleString('fr-FR') + ' ' + (ctx.currency || 'XOF') + '</span></div>' +
                    '</div>' +
                    statusActionsHtml +
                    '</div>';

                // Gestionnaire d'action de statut
                modalOrderContent.querySelectorAll('.btn-change-order-status').forEach(function (statusBtn) {
                    statusBtn.addEventListener('click', async function () {
                        var oId = this.getAttribute('data-id');
                        var nextStatus = this.getAttribute('data-status');

                        requireAuth(async function () {
                            try {
                                var sRes = await fetch('/api/v1/restaurants/' + restaurantId + '/orders/' + oId + '/status', {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({ status: nextStatus })
                                });

                                if (sRes.status === 401) {
                                    closeOrderModal();
                                    openModal('modal-owner-auth');
                                    return;
                                }

                                if (sRes.ok) {
                                    showToast('Statut de la commande mis à jour : ' + nextStatus, 'success');
                                    closeOrderModal();
                                    setTimeout(function () { window.location.reload(); }, 600);
                                } else {
                                    showToast('Échec de la transition de statut', 'error');
                                }
                            } catch (err) {
                                console.error('Erreur transition:', err);
                            }
                        });
                    });
                });

            } catch (err) {
                console.error('Erreur chargement commande:', err);
                modalOrderContent.innerHTML = '<div class="p-4 bg-red-50 text-red-700 rounded">Erreur lors de la communication serveur.</div>';
            }
        });
    });

    /* ==========================================================
     * 8. ACQUITTEMENT DES APPELS D'ASSISTANCE
     * ========================================================== */
    document.querySelectorAll('.btn-acknowledge-assistance').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            var id = this.getAttribute('data-id');
            var container = this.closest('.bg-amber-500\\/10');

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/assistance-requests/' + id, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: 'acknowledged' })
                });

                if (res.ok && container) {
                    showToast('Appel acquitté', 'success');
                    container.style.opacity = '0';
                    container.style.transition = 'opacity 0.3s ease';
                    setTimeout(function () { container.remove(); }, 300);
                }
            } catch (err) {
                console.error('Erreur acquittement:', err);
            }
        });
    });

    /* ==========================================================
     * 9. CONFIGURATION OPÉRATIONNELLE & ÉQUIPE
     * ========================================================== */
    var formSettings = document.getElementById('form-restaurant-settings');
    var settingsAlert = document.getElementById('settings-save-alert');

    if (formSettings) {
        formSettings.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (settingsAlert) settingsAlert.classList.add('hidden');

            var taxRate = parseFloat(document.getElementById('setting-tax-rate')?.value) || 0;
            var serviceFee = parseFloat(document.getElementById('setting-service-fee')?.value) || 0;
            var prepTime = parseInt(document.getElementById('setting-prep-time')?.value) || 20;

            var payload = {
                tax_rate: taxRate,
                service_fee_minor: Math.round(serviceFee * 100),
                estimated_preparation_minutes: prepTime
            };

            var saveBtn = document.getElementById('btn-save-settings');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = 'Enregistrement...';
            }

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/configuration', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });

                if (res.status === 401) {
                    openModal('modal-owner-auth');
                    return;
                }

                if (res.ok) {
                    showToast('Configuration enregistrée avec succès !', 'success');
                    if (settingsAlert) {
                        settingsAlert.className = 'p-2.5 rounded text-xs bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200';
                        settingsAlert.textContent = 'Paramètres enregistrés et appliqués en direct sur les prochaines commandes.';
                        settingsAlert.classList.remove('hidden');
                    }
                } else {
                    showToast('Erreur lors de l\'enregistrement des paramètres', 'error');
                }
            } catch (err) {
                console.error('Erreur configuration:', err);
            } finally {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Enregistrer la configuration';
                }
            }
        });
    }

    // Ajout de membre d'équipe
    var btnOpenCreateMember = document.getElementById('btn-open-create-member');
    var formCreateMember = document.getElementById('form-create-member');
    var createMemberError = document.getElementById('create-member-error');

    if (btnOpenCreateMember) {
        btnOpenCreateMember.addEventListener('click', function () {
            requireAuth(function () {
                openModal('modal-create-member');
            });
        });
    }

    if (formCreateMember) {
        formCreateMember.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (createMemberError) createMemberError.classList.add('hidden');

            var name = document.getElementById('member-name')?.value.trim();
            var email = document.getElementById('member-email')?.value.trim();
            var role = document.getElementById('member-role')?.value;

            var submitBtn = document.getElementById('btn-submit-create-member');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Ajout en cours...';
            }

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/members', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ name: name, email: email, role: role })
                });

                if (res.status === 401) {
                    closeModal('modal-create-member');
                    openModal('modal-owner-auth');
                    return;
                }

                if (!res.ok) {
                    var data = await res.json().catch(function () { return {}; });
                    var errorMsg = data.message || 'Impossible d\'ajouter ce membre.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    if (createMemberError) {
                        createMemberError.innerHTML = errorMsg;
                        createMemberError.classList.remove('hidden');
                    }
                    return;
                }

                closeModal('modal-create-member');
                showToast('Membre "' + name + '" ajouté avec succès !', 'success');
                setTimeout(function () { window.location.reload(); }, 800);
            } catch (err) {
                console.error('Erreur membre:', err);
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Ajouter le membre';
                }
            }
        });
    }

    // Suppression de membre
    document.querySelectorAll('.btn-delete-member').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var name = this.getAttribute('data-name') || 'ce membre';
            var row = document.querySelector('[data-member-row="' + id + '"]');

            requireAuth(async function () {
                if (!confirm('Voulez-vous vraiment révoquer les accès de "' + name + '" ?')) {
                    return;
                }

                try {
                    var res = await fetch('/api/v1/restaurants/' + restaurantId + '/members/' + id, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin'
                    });

                    if (res.status === 401) {
                        openModal('modal-owner-auth');
                        return;
                    }

                    if (res.ok) {
                        showToast('Accès de "' + name + '" révoqué.', 'success');
                        if (row) {
                            row.style.opacity = '0';
                            setTimeout(function () { row.remove(); }, 300);
                        }
                    } else {
                        showToast('Impossible de révoquer ce membre.', 'error');
                    }
                } catch (err) {
                    console.error('Erreur révocation membre:', err);
                }
            });
        });
    });

    /* ==========================================================
     * 11. GESTION DES TABLES ET CONFIGURATION DES QR CODES
     * ========================================================== */
    // 11.1 Ouverture modale ajout de table
    var btnOpenCreateTable = document.getElementById('btn-open-create-table');
    if (btnOpenCreateTable) {
        btnOpenCreateTable.addEventListener('click', function () {
            var errBox = document.getElementById('create-table-error');
            if (errBox) errBox.classList.add('hidden');
            var form = document.getElementById('form-create-table');
            if (form) form.reset();
            openModal('modal-create-table');
        });
    }

    // 11.2 Soumission création table
    var formCreateTable = document.getElementById('form-create-table');
    if (formCreateTable) {
        formCreateTable.addEventListener('submit', async function (e) {
            e.preventDefault();
            var errBox = document.getElementById('create-table-error');
            var submitBtn = document.getElementById('btn-submit-create-table');
            if (errBox) errBox.classList.add('hidden');

            var label = document.getElementById('table-label')?.value?.trim();
            var capacity = parseInt(document.getElementById('table-capacity')?.value, 10) || 4;
            var isActive = document.getElementById('table-is-active')?.checked ?? true;

            if (!label) return;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Création...';
            }

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/tables', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        label: label,
                        capacity: capacity,
                        is_active: isActive
                    })
                });

                if (res.status === 401) {
                    closeModal('modal-create-table');
                    openModal('modal-owner-auth');
                    return;
                }

                if (!res.ok) {
                    var errData = await res.json().catch(function () { return {}; });
                    if (errBox) {
                        errBox.textContent = errData.message || 'Impossible de créer la table.';
                        errBox.classList.remove('hidden');
                    }
                    return;
                }

                closeModal('modal-create-table');
                showToast('Table "' + label + '" et son QR Code créés avec succès !', 'success');
                setTimeout(function () { window.location.reload(); }, 600);
            } catch (err) {
                console.error('Erreur création table:', err);
                if (errBox) {
                    errBox.textContent = 'Erreur réseau lors de la création.';
                    errBox.classList.remove('hidden');
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Créer la table';
                }
            }
        });
    }

    // 11.3 Suppression de table
    document.querySelectorAll('.btn-delete-table').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            var tableId = this.getAttribute('data-id');
            var label = this.getAttribute('data-label') || ('#' + tableId);

            if (!confirm('Êtes-vous sûr de vouloir supprimer la ' + label + ' ? Son QR code sera également révoqué.')) {
                return;
            }

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/tables/' + tableId, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                });

                if (res.status === 401) {
                    openModal('modal-owner-auth');
                    return;
                }

                if (res.ok) {
                    showToast('Table ' + label + ' supprimée avec succès.', 'success');
                    var card = this.closest('.table-card');
                    if (card) {
                        card.style.opacity = '0';
                        setTimeout(function () { card.remove(); }, 300);
                    }
                } else {
                    showToast('Impossible de supprimer cette table.', 'error');
                }
            } catch (err) {
                console.error('Erreur suppression table:', err);
                showToast('Erreur réseau lors de la suppression.', 'error');
            }
        });
    });

    // 11.4 Libération / Clôture de table occupée
    document.querySelectorAll('.btn-close-table').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            var tableId = this.getAttribute('data-id');
            var label = this.getAttribute('data-label') || ('#' + tableId);

            if (!confirm('Clôturer les commandes actives et libérer la ' + label + ' ?')) {
                return;
            }

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/tables/' + tableId + '/close', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                });

                if (res.status === 401) {
                    openModal('modal-owner-auth');
                    return;
                }

                if (res.ok) {
                    showToast('Table ' + label + ' libérée !', 'success');
                    setTimeout(function () { window.location.reload(); }, 500);
                } else {
                    showToast('Impossible de libérer cette table.', 'error');
                }
            } catch (err) {
                console.error('Erreur clôture table:', err);
            }
        });
    });

    // 11.5 Modale de configuration générale des QR Codes
    var btnOpenQrConfig = document.getElementById('btn-open-qr-config');
    var triggerQrConfigBtns = document.querySelectorAll('.btn-trigger-qr-config');
    [btnOpenQrConfig, ...triggerQrConfigBtns].forEach(function (btn) {
        if (btn) {
            btn.addEventListener('click', function () {
                var errBox = document.getElementById('qr-config-error');
                if (errBox) errBox.classList.add('hidden');
                openModal('modal-qr-config');
            });
        }
    });

    var formQrConfig = document.getElementById('form-qr-config');
    if (formQrConfig) {
        formQrConfig.addEventListener('submit', async function (e) {
            e.preventDefault();
            var errBox = document.getElementById('qr-config-error');
            var submitBtn = document.getElementById('btn-submit-qr-config');
            if (errBox) errBox.classList.add('hidden');

            var allowTableOrdering = document.querySelector('input[name="allow_table_ordering"]:checked')?.value === '1';
            var allowWaiterCall = document.getElementById('cfg-allow-waiter-call')?.checked ?? true;
            var qrColor = document.querySelector('input[name="qr_color"]:checked')?.value || '#F97316';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Enregistrement...';
            }

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/configuration', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        qr_options: {
                            allow_table_ordering: allowTableOrdering,
                            allow_waiter_call: allowWaiterCall,
                            qr_color: qrColor,
                            display_logo: true
                        }
                    })
                });

                if (res.status === 401) {
                    closeModal('modal-qr-config');
                    openModal('modal-owner-auth');
                    return;
                }

                if (!res.ok) {
                    var errData = await res.json().catch(function () { return {}; });
                    if (errBox) {
                        errBox.textContent = errData.message || 'Erreur lors de l\'enregistrement des paramètres.';
                        errBox.classList.remove('hidden');
                    }
                    return;
                }

                closeModal('modal-qr-config');
                showToast('Configuration des QR Codes mise à jour avec succès !', 'success');
                setTimeout(function () { window.location.reload(); }, 600);
            } catch (err) {
                console.error('Erreur config QR:', err);
                if (errBox) {
                    errBox.textContent = 'Erreur réseau.';
                    errBox.classList.remove('hidden');
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enregistrer les paramètres';
                }
            }
        });
    }

    // 11.6 Régénération globale de tous les QR codes
    var btnRegenAll = document.getElementById('btn-regenerate-all-qr');
    if (btnRegenAll) {
        btnRegenAll.addEventListener('click', async function () {
            if (!confirm('Attention : cette action va révoquer TOUS les QR codes de tables existants et en générer de nouveaux. Les chevalets déjà imprimés ne fonctionneront plus. Voulez-vous continuer ?')) {
                return;
            }

            btnRegenAll.disabled = true;
            btnRegenAll.textContent = 'Régénération en cours...';

            try {
                var tableCards = document.querySelectorAll('.table-card');
                var promises = [];
                tableCards.forEach(function (card) {
                    var tableId = card.getAttribute('data-id');
                    promises.push(
                        fetch('/api/v1/restaurants/' + restaurantId + '/qr-codes', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                type: 'table',
                                restaurant_table_id: tableId
                            })
                        })
                    );
                });

                await Promise.all(promises);
                closeModal('modal-qr-config');
                showToast('Tous les QR Codes de table ont été régénérés avec succès !', 'success');
                setTimeout(function () { window.location.reload(); }, 700);
            } catch (err) {
                console.error('Erreur régénération globale QR:', err);
                showToast('Erreur lors de la régénération.', 'error');
            } finally {
                btnRegenAll.disabled = false;
                btnRegenAll.textContent = 'Régénérer tout';
            }
        });
    }

    // 11.7 Modale individuelle : Gérer le QR Code d'une table
    var activeQrDetailState = {
        tableId: null,
        qrId: null,
        qrToken: null
    };

    document.querySelectorAll('.btn-config-table-qr').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tableId = this.getAttribute('data-table-id');
            var tableLabel = this.getAttribute('data-table-label') || ('Table #' + tableId);
            var qrId = this.getAttribute('data-qr-id');
            var qrToken = this.getAttribute('data-qr-token') || '';

            activeQrDetailState = {
                tableId: tableId,
                qrId: qrId,
                qrToken: qrToken
            };

            var titleEl = document.getElementById('modal-table-qr-title');
            if (titleEl) titleEl.textContent = tableLabel;

            var tokenEl = document.getElementById('modal-qr-token-display');
            if (tokenEl) tokenEl.textContent = qrToken || 'Aucun';

            var fullUrl = window.location.origin + '/?qr=' + encodeURIComponent(qrToken);
            var urlInput = document.getElementById('modal-qr-url-input');
            if (urlInput) urlInput.value = fullUrl;

            var testLink = document.getElementById('modal-qr-test-link');
            if (testLink) testLink.href = '/?qr=' + encodeURIComponent(qrToken);

            var svgLink = document.getElementById('modal-qr-download-svg');
            if (svgLink) {
                svgLink.href = '/api/v1/restaurants/' + restaurantId + '/qr-codes/' + qrId + '/download?format=svg';
            }

            var pdfLink = document.getElementById('modal-qr-download-pdf');
            if (pdfLink) {
                pdfLink.href = '/api/v1/restaurants/' + restaurantId + '/qr-codes/' + qrId + '/download?format=pdf';
            }

            var previewImg = document.getElementById('modal-qr-preview-img');
            if (previewImg && qrId) {
                previewImg.src = '/api/v1/restaurants/' + restaurantId + '/qr-codes/' + qrId + '/download?format=svg&inline=1';
            }

            openModal('modal-table-qr-detail');
        });
    });

    // 11.8 Copier l'URL du QR Code
    var btnCopyUrl = document.getElementById('btn-copy-qr-url');
    if (btnCopyUrl) {
        btnCopyUrl.addEventListener('click', function () {
            var urlInput = document.getElementById('modal-qr-url-input');
            if (urlInput && urlInput.value) {
                navigator.clipboard.writeText(urlInput.value).then(function () {
                    showToast('Lien du QR Code copié dans le presse-papier !', 'success');
                }).catch(function () {
                    urlInput.select();
                    document.execCommand('copy');
                    showToast('Lien copié !', 'success');
                });
            }
        });
    }

    // 11.9 Régénérer le QR Code individuel d'une table
    var btnRegenSingle = document.getElementById('btn-regenerate-single-qr');
    if (btnRegenSingle) {
        btnRegenSingle.addEventListener('click', async function () {
            if (!activeQrDetailState.qrId) return;

            if (!confirm('Régénérer le QR Code de cette table ? L\'ancien chevalet imprimé sera immédiatement révoqué.')) {
                return;
            }

            btnRegenSingle.disabled = true;
            btnRegenSingle.textContent = 'Régénération...';

            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/qr-codes/' + activeQrDetailState.qrId + '/regeneration', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                });

                if (res.status === 401) {
                    closeModal('modal-table-qr-detail');
                    openModal('modal-owner-auth');
                    return;
                }

                if (res.ok) {
                    showToast('Nouveau QR Code généré avec succès !', 'success');
                    closeModal('modal-table-qr-detail');
                    setTimeout(function () { window.location.reload(); }, 600);
                } else {
                    showToast('Impossible de régénérer ce QR Code.', 'error');
                }
            } catch (err) {
                console.error('Erreur régénération QR table:', err);
                showToast('Erreur réseau.', 'error');
            } finally {
                btnRegenSingle.disabled = false;
                btnRegenSingle.textContent = 'Régénérer ce jeton QR';
            }
        });
    }

    // 11.10 Générer un QR pour une table qui n'en a pas
    document.querySelectorAll('.btn-create-table-qr').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            var tableId = this.getAttribute('data-table-id');
            try {
                var res = await fetch('/api/v1/restaurants/' + restaurantId + '/qr-codes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        type: 'table',
                        restaurant_table_id: tableId
                    })
                });

                if (res.status === 401) {
                    openModal('modal-owner-auth');
                    return;
                }

                if (res.ok) {
                    showToast('QR Code créé !', 'success');
                    setTimeout(function () { window.location.reload(); }, 500);
                } else {
                    var errData = await res.json().catch(function () { return {}; });
                    showToast(errData.message || 'Erreur lors de la génération.', 'error');
                }
            } catch (e) {
                console.error(e);
                showToast('Erreur réseau lors de la génération.', 'error');
            }
        });
    });

    /* ==========================================================
     * 10. BASCULE DU THÈME SOMBRE / CLAIR
     * ========================================================== */
    var themeToggle = document.getElementById('admin-theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            var html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('qreatery-theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('qreatery-theme', 'dark');
            }
        });
    }
});

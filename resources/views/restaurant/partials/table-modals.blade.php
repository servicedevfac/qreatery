<!-- Modale 1 : Ajout de Table -->
<div id="modal-create-table" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg max-w-md w-full shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-brand-dark-border">
            <h3 class="text-base font-bold text-brand-text dark:text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Ajouter une nouvelle table</span>
            </h3>
            <button type="button" class="btn-close-modal text-gray-400 hover:text-gray-600 dark:hover:text-white p-1" data-modal="modal-create-table">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="create-table-error" class="hidden p-3 rounded-brand bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-medium"></div>

        <form id="form-create-table" class="space-y-4 text-xs">
            <div>
                <label for="table-label" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Nom / Numéro de la table <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="table-label"
                    required
                    placeholder="Ex: Table T07, Terrasse 03, Salon VIP..."
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                />
            </div>

            <div>
                <label for="table-capacity" class="font-bold text-gray-700 dark:text-gray-300 block mb-1">
                    Capacité (nombre de couverts) <span class="text-red-500">*</span>
                </label>
                <input
                    type="number"
                    id="table-capacity"
                    required
                    min="1"
                    max="100"
                    value="4"
                    class="w-full p-2.5 rounded-brand border border-gray-300 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark text-brand-text dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-orange"
                />
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input
                    type="checkbox"
                    id="table-is-active"
                    checked
                    class="rounded text-brand-orange focus:ring-brand-orange h-4 w-4"
                />
                <label for="table-is-active" class="text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                    Table active et disponible pour le service
                </label>
            </div>

            <p class="text-[11px] text-gray-400 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-amber-500 inline shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Un QR Code sécurisé unique sera automatiquement généré pour cette table.</span>
            </p>

            <div class="pt-4 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end gap-2">
                <button
                    type="button"
                    class="btn-close-modal px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold rounded-brand transition-colors"
                    data-modal="modal-create-table"
                >
                    Annuler
                </button>
                <button
                    type="submit"
                    id="btn-submit-create-table"
                    class="px-4 py-2 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold rounded-brand transition-colors shadow-sm"
                >
                    Créer la table
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modale 2 : Paramètres Généraux des QR Codes des Tables -->
<div id="modal-qr-config" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg max-w-lg w-full shadow-2xl p-6 space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-brand-dark-border">
            <div>
                <h3 class="text-base font-bold text-brand-text dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Configuration des QR Codes de Table</span>
                </h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Personnalisez le comportement et l'apparence des QR codes pour vos clients</p>
            </div>
            <button type="button" class="btn-close-modal text-gray-400 hover:text-gray-600 dark:hover:text-white p-1" data-modal="modal-qr-config">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="qr-config-error" class="hidden p-3 rounded-brand bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-medium"></div>

        <form id="form-qr-config" class="space-y-4 text-xs">
            <!-- Mode de commande à table -->
            <div class="p-3.5 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark space-y-2">
                <label class="font-bold text-gray-800 dark:text-gray-200 block">
                    Mode d'interaction client à table :
                </label>
                <div class="space-y-2 pt-1">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input
                            type="radio"
                            name="allow_table_ordering"
                            value="1"
                            {{ ($qrConfig['allow_table_ordering'] ?? true) ? 'checked' : '' }}
                            class="mt-0.5 text-brand-orange focus:ring-brand-orange"
                        />
                        <div>
                            <span class="font-bold text-gray-700 dark:text-gray-300">Commande &amp; Règlement en ligne directs</span>
                            <p class="text-[11px] text-gray-500">Le client compose son panier, valide sa commande à table et effectue le paiement depuis son téléphone.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input
                            type="radio"
                            name="allow_table_ordering"
                            value="0"
                            {{ !($qrConfig['allow_table_ordering'] ?? true) ? 'checked' : '' }}
                            class="mt-0.5 text-brand-orange focus:ring-brand-orange"
                        />
                        <div>
                            <span class="font-bold text-gray-700 dark:text-gray-300">Menu digital uniquement (Consultation sans commande directe)</span>
                            <p class="text-[11px] text-gray-500">Le client consulte les plats et prix sur son mobile, la commande est prise par un serveur ou au comptoir.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Bouton d'assistance et appel serveur -->
            <div class="flex items-center justify-between p-3.5 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark">
                <div>
                    <label for="cfg-allow-waiter-call" class="font-bold text-gray-800 dark:text-gray-200 block cursor-pointer">
                        Bouton « Appeler le serveur » / Demander l'addition
                    </label>
                    <p class="text-[11px] text-gray-500">Affiche une sonnette d'appel flottante sur le smartphone du client assis à table.</p>
                </div>
                <input
                    type="checkbox"
                    id="cfg-allow-waiter-call"
                    name="allow_waiter_call"
                    value="1"
                    {{ ($qrConfig['allow_waiter_call'] ?? true) ? 'checked' : '' }}
                    class="rounded text-brand-orange focus:ring-brand-orange h-4 w-4 shrink-0"
                />
            </div>

            <!-- Couleur et style visuel du QR code -->
            <div class="p-3.5 rounded-brand border border-gray-200 dark:border-brand-dark-border bg-gray-50 dark:bg-brand-dark space-y-2">
                <label class="font-bold text-gray-800 dark:text-gray-200 block">
                    Couleur d'accentuation des QR Codes :
                </label>
                <div class="flex items-center gap-4 pt-1">
                    @php
                        $colors = [
                            '#F97316' => 'Orange Qreatery',
                            '#111827' => 'Noir Minimaliste',
                            '#059669' => 'Vert Émeraude',
                            '#2563EB' => 'Bleu Roi',
                        ];
                        $currentColor = $qrConfig['qr_color'] ?? '#F97316';
                    @endphp
                    @foreach($colors as $hex => $name)
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input
                                type="radio"
                                name="qr_color"
                                value="{{ $hex }}"
                                {{ $currentColor === $hex ? 'checked' : '' }}
                                class="text-brand-orange focus:ring-brand-orange"
                            />
                            <span class="w-4 h-4 rounded-full border border-gray-300" style="background-color: {{ $hex }}"></span>
                            <span class="text-[11px] font-medium text-gray-600 dark:text-gray-400">{{ $name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Actions globales sécurisées -->
            <div class="pt-2">
                <div class="flex items-center justify-between p-3 rounded-brand border border-amber-200 dark:border-amber-900/40 bg-amber-50 dark:bg-amber-950/20">
                    <div class="pr-3">
                        <span class="font-bold text-amber-800 dark:text-amber-300 block text-xs">Sécurité : Renouvellement massif</span>
                        <span class="text-[10px] text-amber-700/80 dark:text-amber-400 block mt-0.5">Régénère les jetons de toutes les tables (les anciens QR imprimés ne fonctionneront plus).</span>
                    </div>
                    <button
                        type="button"
                        id="btn-regenerate-all-qr"
                        class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded transition-colors shrink-0 shadow-xs flex items-center gap-1.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Régénérer tout</span>
                    </button>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-brand-dark-border flex items-center justify-end gap-2">
                <button
                    type="button"
                    class="btn-close-modal px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-brand-dark dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold rounded-brand transition-colors"
                    data-modal="modal-qr-config"
                >
                    Annuler
                </button>
                <button
                    type="submit"
                    id="btn-submit-qr-config"
                    class="px-4 py-2 bg-brand-orange hover:bg-brand-orange-dark text-white font-bold rounded-brand transition-colors shadow-sm"
                >
                    Enregistrer les paramètres
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modale 3 : Gestion & Visualisation Individuelle du QR Code d'une Table -->
<div id="modal-table-qr-detail" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-brand-dark-card border border-gray-200 dark:border-brand-dark-border rounded-brand-lg max-w-md w-full shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-brand-dark-border">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-brand-orange">Configuration Table</span>
                <h3 id="modal-table-qr-title" class="text-base font-bold text-brand-text dark:text-white">
                    Table #...
                </h3>
            </div>
            <button type="button" class="btn-close-modal text-gray-400 hover:text-gray-600 dark:hover:text-white p-1" data-modal="modal-table-qr-detail">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Zone d'aperçu visuel du QR Code -->
        <div class="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-brand-dark rounded-brand border border-gray-200 dark:border-brand-dark-border space-y-2">
            <div id="modal-qr-preview-container" class="w-48 h-48 bg-white p-3 rounded-brand shadow-sm flex items-center justify-center border border-gray-200">
                <img id="modal-qr-preview-img" src="" alt="QR Code Table" class="w-full h-full object-contain" />
            </div>
            <span class="text-[11px] text-gray-500 font-medium text-center">
                Scannez avec un appareil photo pour tester
            </span>
        </div>

        <!-- Détails techniques et URL -->
        <div class="space-y-2 text-xs">
            <div>
                <label class="font-bold text-gray-600 dark:text-gray-400 block mb-1">
                    Lien direct vers le menu client :
                </label>
                <div class="flex items-center gap-1.5">
                    <input
                        type="text"
                        id="modal-qr-url-input"
                        readonly
                        class="w-full p-2 bg-gray-100 dark:bg-brand-dark rounded-brand border border-gray-200 dark:border-brand-dark-border font-mono text-[11px] text-gray-700 dark:text-gray-300"
                    />
                    <button
                        type="button"
                        id="btn-copy-qr-url"
                        class="px-2.5 py-2 bg-white dark:bg-brand-dark-card hover:bg-gray-100 dark:hover:bg-brand-dark border border-gray-200 dark:border-brand-dark-border text-gray-700 dark:text-gray-200 rounded-brand font-bold text-xs shrink-0 transition-colors flex items-center gap-1"
                        title="Copier le lien"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>Copier</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between p-2 rounded bg-gray-50 dark:bg-brand-dark border border-gray-100 dark:border-brand-dark-border text-[11px]">
                <span class="font-bold text-gray-600 dark:text-gray-400">Jeton actif (Token) :</span>
                <span id="modal-qr-token-display" class="font-mono text-gray-500 truncate max-w-[200px]">...</span>
            </div>
        </div>

        <!-- Actions de téléchargement et régénération -->
        <div class="space-y-2 pt-2 border-t border-gray-200 dark:border-brand-dark-border">
            <div class="grid grid-cols-2 gap-2">
                <a
                    id="modal-qr-download-svg"
                    href="#"
                    class="px-3 py-2 bg-white dark:bg-brand-dark-card hover:border-brand-orange border border-gray-200 dark:border-brand-dark-border text-brand-text dark:text-white rounded-brand text-xs font-bold text-center transition-colors flex items-center justify-center gap-1.5 shadow-xs"
                >
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Télécharger SVG</span>
                </a>
                <a
                    id="modal-qr-download-pdf"
                    href="#"
                    target="_blank"
                    class="px-3 py-2 bg-white dark:bg-brand-dark-card hover:border-brand-orange border border-gray-200 dark:border-brand-dark-border text-brand-text dark:text-white rounded-brand text-xs font-bold text-center transition-colors flex items-center justify-center gap-1.5 shadow-xs"
                >
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Chevalet A5 (PDF)</span>
                </a>
            </div>

            <div class="flex items-center justify-between pt-2">
                <button
                    type="button"
                    id="btn-regenerate-single-qr"
                    class="text-xs text-amber-600 hover:text-amber-700 dark:text-amber-400 font-bold flex items-center gap-1 hover:underline"
                    title="Invalide l'ancien QR code et en génère un nouveau"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Régénérer ce jeton QR</span>
                </button>

                <a
                    id="modal-qr-test-link"
                    href="#"
                    target="_blank"
                    class="px-3.5 py-1.5 bg-brand-orange hover:bg-brand-orange-dark text-white rounded-brand text-xs font-bold transition-colors shadow-sm"
                >
                    Tester Table ↗
                </a>
            </div>
        </div>
    </div>
</div>

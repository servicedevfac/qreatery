/**
 * APP RESTAURANT — Client WebSocket Temps Réel & Résilience REC-13
 * Protocole Pusher v7 natif compatible Laravel Reverb sans dépendance externe
 */

export function createRealtimeClient(options = {}) {
    const metaKey = document.querySelector('meta[name="reverb-key"]')?.getAttribute('content');
    const metaHost = document.querySelector('meta[name="reverb-host"]')?.getAttribute('content');
    const metaPort = document.querySelector('meta[name="reverb-port"]')?.getAttribute('content');
    const metaScheme = document.querySelector('meta[name="reverb-scheme"]')?.getAttribute('content');

    let host = options.host || metaHost;
    if (!host || host === '127.0.0.1' || host === 'localhost') {
        host = window.location.hostname || '127.0.0.1';
    }
    const port = options.port || metaPort || '8080';
    const scheme = options.scheme || metaScheme || (window.location.protocol === 'https:' ? 'https' : 'http');
    const wsProtocol = (scheme === 'https' || window.location.protocol === 'https:') ? 'wss:' : 'ws:';
    const authEndpoint = options.authEndpoint || '/broadcasting/auth';

    const onStateChange = typeof options.onStateChange === 'function' ? options.onStateChange : () => {};
    const onDegraded = typeof options.onDegraded === 'function' ? options.onDegraded : () => {};
    const onReconnect = typeof options.onReconnect === 'function' ? options.onReconnect : () => {};

    let socket = null;
    let socketId = null;
    let isConnected = false;
    let isExplicitlyClosed = false;
    let reconnectAttempts = 0;
    let reconnectTimeout = null;
    let pingInterval = null;
    let degradedTimer = null;
    let isDegradedState = false;
    let hasConnectedOnce = false;

    // Registre des canaux: channelName -> Map<eventName, Set<Function>>
    const channels = new Map();

    function getWsUrl() {
        return `${wsProtocol}//${host}:${port}/app/${encodeURIComponent(appKey)}?protocol=7&client=js&version=8.4.0-reverb&flash=false`;
    }

    function connect() {
        if (isExplicitlyClosed) return;
        if (socket && (socket.readyState === WebSocket.CONNECTING || socket.readyState === WebSocket.OPEN)) {
            return;
        }

        try {
            onStateChange('connecting');
            socket = new WebSocket(getWsUrl());

            socket.onopen = function () {
                reconnectAttempts = 0;
            };

            socket.onmessage = function (event) {
                handleMessage(event.data);
            };

            socket.onerror = function () {
                handleDisconnect('error');
            };

            socket.onclose = function () {
                handleDisconnect('close');
            };
        } catch (e) {
            handleDisconnect('exception');
        }
    }

    function handleDisconnect(reason) {
        if (isConnected) {
            isConnected = false;
            onStateChange('disconnected');
        }

        socketId = null;
        clearInterval(pingInterval);

        // Déclenchement du mode dégradé après 3 secondes de coupure confirmée (REC-13)
        if (!degradedTimer && !isDegradedState) {
            degradedTimer = setTimeout(() => {
                isDegradedState = true;
                onDegraded(true);
            }, 3000);
        }

        // Reconnexion avec backoff borné entre 1s et 10s
        if (!isExplicitlyClosed && !reconnectTimeout) {
            const delay = Math.min(1000 * Math.pow(1.4, reconnectAttempts), 10000);
            reconnectAttempts++;
            reconnectTimeout = setTimeout(() => {
                reconnectTimeout = null;
                connect();
            }, delay);
        }
    }

    function handleMessage(raw) {
        try {
            const msg = JSON.parse(raw);
            const evt = msg.event;

            if (evt === 'pusher:connection_established') {
                const connData = typeof msg.data === 'string' ? JSON.parse(msg.data) : msg.data;
                socketId = connData.socket_id;
                isConnected = true;

                // Annuler le passage en mode dégradé s'il était en attente
                if (degradedTimer) {
                    clearTimeout(degradedTimer);
                    degradedTimer = null;
                }

                const wasDegraded = isDegradedState;
                isDegradedState = false;

                // Signalisation du rétablissement
                onDegraded(false);
                onStateChange('connected');

                // Si nous étions déjà connectés auparavant (ou dégradés), signaler la reconnexion pour resynchronisation REST
                if (hasConnectedOnce || wasDegraded) {
                    onReconnect();
                }
                hasConnectedOnce = true;

                // Démarrer le ping périodique toutes les 25s pour garder la connexion vivante
                clearInterval(pingInterval);
                pingInterval = setInterval(() => {
                    if (socket && socket.readyState === WebSocket.OPEN) {
                        socket.send(JSON.stringify({ event: 'pusher:ping', data: {} }));
                    }
                }, 25000);

                // Ré-abonner tous les canaux enregistrés
                resubscribeAll();
                return;
            }

            if (evt === 'pusher:ping') {
                if (socket && socket.readyState === WebSocket.OPEN) {
                    socket.send(JSON.stringify({ event: 'pusher:pong', data: {} }));
                }
                return;
            }

            if (evt === 'pusher_internal:subscription_succeeded') {
                return;
            }

            // Événement applicatif reçu
            const channelName = msg.channel;
            if (channelName && channels.has(channelName)) {
                let payload = msg.data;
                if (typeof payload === 'string') {
                    try {
                        payload = JSON.parse(payload);
                    } catch (e) {
                        // Conserver comme string
                    }
                }

                const channelListeners = channels.get(channelName);
                // Notifier les écouteurs pour ce nom exact ou avec point
                notifyListeners(channelListeners, evt, payload);
            }
        } catch (e) {
            console.warn('[Realtime] Erreur traitement message:', e);
        }
    }

    function notifyListeners(channelListeners, eventName, payload) {
        if (!channelListeners) return;

        // Match exact, e.g. "orders.created"
        if (channelListeners.has(eventName)) {
            channelListeners.get(eventName).forEach(fn => {
                try { fn(payload); } catch (err) { console.error(err); }
            });
        }

        // Match avec préfixe Laravel éventuel ".orders.created"
        const dotName = '.' + eventName;
        if (channelListeners.has(dotName)) {
            channelListeners.get(dotName).forEach(fn => {
                try { fn(payload); } catch (err) { console.error(err); }
            });
        }
    }

    async function authorizeAndSubscribe(channelName) {
        if (!socket || socket.readyState !== WebSocket.OPEN || !socketId) {
            return;
        }

        const isPrivate = channelName.startsWith('private-') || channelName.startsWith('presence-');

        if (!isPrivate) {
            socket.send(JSON.stringify({
                event: 'pusher:subscribe',
                data: { channel: channelName }
            }));
            return;
        }

        // Canal privé : appel d'authentification sur /broadcasting/auth
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch(authEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    channel_name: channelName,
                    socket_id: socketId
                })
            });

            if (!res.ok) {
                console.warn(`[Realtime] Échec authentification canal ${channelName} (HTTP ${res.status})`);
                return;
            }

            const data = await res.json();
            if (data && data.auth && socket && socket.readyState === WebSocket.OPEN) {
                socket.send(JSON.stringify({
                    event: 'pusher:subscribe',
                    data: {
                        channel: channelName,
                        auth: data.auth,
                        channel_data: data.channel_data || undefined
                    }
                }));
            }
        } catch (e) {
            console.warn(`[Realtime] Erreur réseau auth canal ${channelName}:`, e);
        }
    }

    function resubscribeAll() {
        channels.forEach((_, channelName) => {
            authorizeAndSubscribe(channelName);
        });
    }

    function subscribe(channelName, eventsMap = {}) {
        if (!channels.has(channelName)) {
            channels.set(channelName, new Map());
        }

        const channelListeners = channels.get(channelName);
        Object.entries(eventsMap).forEach(([evtName, handler]) => {
            if (typeof handler === 'function') {
                if (!channelListeners.has(evtName)) {
                    channelListeners.set(evtName, new Set());
                }
                channelListeners.get(evtName).add(handler);
            }
        });

        // Si déjà connecté, souscrire immédiatement
        if (isConnected && socketId) {
            authorizeAndSubscribe(channelName);
        }

        return {
            unsubscribe: () => unsubscribe(channelName)
        };
    }

    function unsubscribe(channelName) {
        if (channels.has(channelName)) {
            channels.delete(channelName);
            if (socket && socket.readyState === WebSocket.OPEN) {
                socket.send(JSON.stringify({
                    event: 'pusher:unsubscribe',
                    data: { channel: channelName }
                }));
            }
        }
    }

    function disconnect() {
        isExplicitlyClosed = true;
        clearInterval(pingInterval);
        if (reconnectTimeout) clearTimeout(reconnectTimeout);
        if (degradedTimer) clearTimeout(degradedTimer);
        if (socket) {
            try { socket.close(); } catch (e) {}
            socket = null;
        }
        isConnected = false;
        socketId = null;
    }

    // Démarrage initial
    connect();

    return {
        subscribe,
        unsubscribe,
        disconnect,
        isConnected: () => isConnected,
        isDegraded: () => isDegradedState
    };
}

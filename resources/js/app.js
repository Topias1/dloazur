import Alpine from 'alpinejs';

// PWA SW registration — D-56 'prompt' (pas autoUpdate, protège la saisie en cours)
import { registerSW } from 'virtual:pwa-register';

const updateSW = registerSW({
    onNeedRefresh() {
        // Alpine store pour bannière update (Plan 02-05 affichera le toast)
        if (window.Alpine) {
            Alpine.store('pwaUpdate', { available: true, apply: () => updateSW(true) });
        }
    },
    onOfflineReady() {
        console.log('[SW] App ready for offline use');
    },
    onRegisteredSW(swUrl, registration) {
        console.log('[SW] registered at', swUrl, registration?.scope);
    },
});

// Alpine store global : compteur d'items en attente de synchro (badge topbar + bottom-nav)
Alpine.store('offlineQueue', {
    pendingCount: 0,
    errorCount: 0,
    async refresh() {
        const { countPendingAll } = await import('./offline-queue.js');
        const counts = await countPendingAll();
        this.pendingCount = counts.pending;
        this.errorCount = counts.errors;
    },
});

// PWA update store (initialisé null tant que onNeedRefresh n'a pas été déclenché)
Alpine.store('pwaUpdate', { available: false, apply: () => {} });

window.Alpine = Alpine;
Alpine.start();

// Refresh initial du badge au boot (lit IDB si présent — sinon stay à 0)
document.addEventListener('alpine:initialized', () => {
    Alpine.store('offlineQueue').refresh().catch(() => {});
});

// Persistance storage (D-57) — durabilité IDB sur iOS
if (navigator.storage?.persist) {
    navigator.storage.persisted().then((persisted) => {
        if (!persisted) navigator.storage.persist();
    });
}

// Bundle Livewire + its own Alpine through Vite so there is a SINGLE Alpine
// instance (kills the "multiple instances of Alpine running" warning) and so
// Livewire/Alpine ship inside the precached PWA build instead of the
// /livewire/livewire.js route (unavailable offline). Pairs with
// inject_assets=false + @livewireScriptConfig in the layout.
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Passage form + sync drawer (Plan 02-05)
import { passageForm } from './passage-form.js';
import { syncDrawerStore } from './sync-drawer.js';

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

// Sync drawer store (Plan 02-05) — partagé entre topbar badge et composant drawer
Alpine.store('syncDrawer', syncDrawerStore());

// Alpine.data factories (Plan 02-05)
Alpine.data('passageForm', passageForm);

window.Alpine = Alpine;
// Livewire.start() boots its bundled Alpine — do NOT call Alpine.start() too.
Livewire.start();

// Initialiser le store syncDrawer une fois Alpine démarré
document.addEventListener('alpine:initialized', () => {
    Alpine.store('syncDrawer').init().catch(() => {});
});

// Event relay : le sync-drawer dispatch 'passage-form:flush' → si passageForm n'est pas
// monté sur la page courante, le store global rafraîchit juste le badge.
window.addEventListener('passage-form:flush', async () => {
    if (window.Alpine?.store('offlineQueue')) {
        window.Alpine.store('offlineQueue').refresh().catch(() => {});
    }
});

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

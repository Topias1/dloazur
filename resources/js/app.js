// EasyMDE — blog admin Markdown editor (Plan 06-04, CONTENT-01). CSS + lib must
// load before Alpine so window.EasyMDE is available inside the x-init scope of
// post-editor.js (the postEditor factory news up `new EasyMDE(...)`).
import 'easymde/dist/easymde.min.css';
import EasyMDE from 'easymde';
window.EasyMDE = EasyMDE;

// Passage form + sync drawer (Plan 02-05)
import { passageForm } from './passage-form.js';
import { syncDrawerStore } from './sync-drawer.js';

// Blog admin Markdown editor factory — CONTENT-01 (Plan 06-04)
import { postEditor } from './post-editor.js';

// Carnet local-only — DIAG-07 (Plan 05-06)
import { carnetStore, carnetResumeStrip } from './diagnostic-carnet.js';

// PWA SW registration — D-56 'prompt' (pas autoUpdate, protège la saisie en cours)
import { registerSW } from 'virtual:pwa-register';

const updateSW = registerSW({
    onNeedRefresh() {
        // Alpine store pour bannière update (Plan 02-05 affichera le toast)
        if (window.Alpine) {
            window.Alpine.store('pwaUpdate', { available: true, apply: () => updateSW(true) });
        }
    },
    onOfflineReady() {
        console.log('[SW] App ready for offline use');
    },
    onRegisteredSW(swUrl, registration) {
        console.log('[SW] registered at', swUrl, registration?.scope);
    },
});

// Livewire 3 owns the single Alpine instance — do NOT import alpinejs or call
// Alpine.start() here (a 2nd instance triggers "multiple instances of Alpine"
// and breaks $wire). Register stores + data factories on Livewire's Alpine via
// the alpine:init hook, which fires before Livewire starts Alpine.
document.addEventListener('alpine:init', () => {
    // Compteur d'items en attente de synchro (badge topbar + bottom-nav)
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

    // Alpine.data factory (Plan 06-04) — EasyMDE Markdown editor for blog admin
    Alpine.data('postEditor', postEditor);

    // Alpine.data factories (Plan 05-06) — carnet local-only DIAG-07
    Alpine.data('carnetStore', carnetStore);
    Alpine.data('carnetResumeStrip', carnetResumeStrip);
});

// Initialiser le store syncDrawer une fois Alpine démarré
document.addEventListener('alpine:initialized', () => {
    window.Alpine.store('syncDrawer').init().catch(() => {});
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
    window.Alpine.store('offlineQueue').refresh().catch(() => {});
});

// Persistance storage (D-57) — durabilité IDB sur iOS
if (navigator.storage?.persist) {
    navigator.storage.persisted().then((persisted) => {
        if (!persisted) navigator.storage.persist();
    });
}

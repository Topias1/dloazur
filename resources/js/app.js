// EasyMDE — blog admin Markdown editor (Plan 06-04, CONTENT-01). CSS + lib must
// load before Alpine so window.EasyMDE is available inside the x-init scope of
// post-editor.js (the postEditor factory news up `new EasyMDE(...)`).
import 'easymde/dist/easymde.min.css';
import EasyMDE from 'easymde';
window.EasyMDE = EasyMDE;

// Passage form + sync drawer (Plan 02-05)
import { passageForm } from './passage-form.js';
import { syncDrawerStore } from './sync-drawer.js';

// Shared upload pipeline — flush() available from any admin page (P0 SC-1)
import { flushPipeline, recoverOrphans } from './upload-pipeline.js';

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

// Alpine ownership (D-… CF-02).
//
// On pages that render a Livewire component, Livewire injects and starts the
// single shared Alpine instance — we must NOT start a 2nd one (that triggers
// "multiple instances of Alpine" and breaks $wire). We register our stores +
// data factories below via the `alpine:init` hook, which fires whichever Alpine
// ends up starting.
//
// BUT the offline passage form, the dashboard and other admin screens are 100%
// Alpine with NO Livewire component (CF-02 — Livewire needs the network, the
// field-entry screen must not). On those pure-Blade pages Livewire never loads,
// so its Alpine never starts: every x-data/@click/x-model is inert and the
// "Enregistrer le passage" button does nothing. We therefore bundle Alpine
// through Vite (so it is precached by the service worker and works offline) and
// start it ourselves — but only when Livewire has not already provided one.
document.addEventListener('alpine:init', () => {
    // Compteur d'items en attente de synchro (badge topbar + bottom-nav)
    Alpine.store('offlineQueue', {
        pendingCount: 0,
        errorCount: 0,
        syncSuccess: false,  // true briefly after queue empties — drives success confirmation
        async refresh() {
            const { countPendingAll } = await import('./offline-queue.js');
            const counts = await countPendingAll();
            this.pendingCount = counts.pending;
            this.errorCount = counts.errors;
        },
        /**
         * Full flush: recover uploading orphans → upload pending → retry produits.
         * Works from any admin page whether or not passageForm is mounted (P0 SC-1).
         * Shows syncSuccess briefly when the queue reaches zero.
         */
        async flush() {
            await flushPipeline();
            await this.refresh();
            if (this.pendingCount === 0 && this.errorCount === 0) {
                this.syncSuccess = true;
                setTimeout(() => { this.syncSuccess = false; }, 3500);
            }
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

// Event relay : le sync-drawer dispatch 'passage-form:flush' → le store global
// exécute le flush complet (upload + recovery) que passageForm soit monté ou non (P0 SC-1).
window.addEventListener('passage-form:flush', async () => {
    if (window.Alpine?.store('offlineQueue')) {
        window.Alpine.store('offlineQueue').flush().catch(() => {});
    }
});

// Boot: refresh badge + recover any uploading orphans left by a killed tab/SW.
// recoverOrphans() is one-shot (re-queues to pending) — safe to call every load.
document.addEventListener('alpine:initialized', () => {
    window.Alpine.store('offlineQueue').refresh().catch(() => {});
    recoverOrphans().catch(() => {});
});

// Fallback Alpine bootstrap for pure-Blade pages (no Livewire component).
//
// Livewire starts its bundled Alpine on DOMContentLoaded. We defer this check
// one macrotask past that so that, on Livewire-backed pages, `window.Alpine` is
// already set and we bail out — guaranteeing a single shared instance. Only when
// Livewire is genuinely absent (offline passage form, dashboard, …) do we lazily
// import Alpine and start it. The dynamic import keeps Alpine in its own Vite
// chunk so it is still emitted to /build (precached by the SW → offline-safe).
function bootstrapStandaloneAlpine() {
    if (window.Alpine) return; // Livewire already owns Alpine on this page
    import('alpinejs').then(({ default: Alpine }) => {
        if (window.Alpine) return; // Livewire won the race — don't double-start
        window.Alpine = Alpine;
        Alpine.start();
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => setTimeout(bootstrapStandaloneAlpine, 0));
} else {
    setTimeout(bootstrapStandaloneAlpine, 0);
}

// Persistance storage (D-57) — durabilité IDB sur iOS
if (navigator.storage?.persist) {
    navigator.storage.persisted().then((persisted) => {
        if (!persisted) navigator.storage.persist();
    });
}

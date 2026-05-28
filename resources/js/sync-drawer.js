import { openOfflineDB, getPassagesByStatus, markStatus } from './offline-queue.js';

/**
 * Alpine.store('syncDrawer') factory — panneau glissant liste des passages en queue.
 *
 * UI-SPEC §"Sync drawer" :
 * - Mobile : translate-y(100%) → 0 (250ms ease-out)
 * - Desktop : translate-x(100%) → 0
 * - Liste des passages non-synced avec statut + retry
 * - Bouton "Synchroniser maintenant" → flush global
 * - role="dialog" aria-modal="true" + focus trap
 *
 * Usage dans app.js :
 *   Alpine.store('syncDrawer', syncDrawerStore());
 *
 * Usage dans les composants :
 *   $store.syncDrawer.open = true
 *   $store.syncDrawer.toggle()
 */
export function syncDrawerStore() {
    return {
        open:    false,
        items:   [],  // [{ id, client_uuid, status, attempts, visited_at, actionsCount }]
        loading: false,

        async init() {
            // Écouter l'event sync-badge @click → ouvrir le drawer
            window.addEventListener('sync-drawer:open', async () => {
                if (!this.open) {
                    this.open = true;
                    await this.refresh();
                }
            });
        },

        async toggle() {
            this.open = !this.open;
            if (this.open) await this.refresh();
        },

        async refresh() {
            this.loading = true;
            try {
                const db  = await openOfflineDB();
                const all = await db.getAll('passages');
                this.items = all
                    .filter((it) => it.status !== 'synced')
                    .map((it) => {
                        let p = {};
                        try { p = JSON.parse(it.payload_json); } catch {}
                        return {
                            id:           it.id,
                            client_uuid:  it.client_uuid,
                            status:       it.status,
                            attempts:     it.attempts || 0,
                            visited_at:   p.visited_at ?? null,
                            actionsCount: (p.actions || []).length,
                        };
                    });
            } finally {
                this.loading = false;
            }
        },

        /**
         * Réessayer un item en erreur : le repasse en 'pending' puis dispatch flush.
         */
        async retry(id) {
            await markStatus('passages', id, 'pending');
            await this.refresh();
            window.dispatchEvent(new CustomEvent('passage-form:flush'));
        },

        /**
         * Synchroniser maintenant : tous les items 'error' → 'pending' puis flush.
         */
        async flushAll() {
            const db  = await openOfflineDB();
            const all = await db.getAll('passages');
            for (const it of all) {
                if (it.status === 'error') {
                    await markStatus('passages', it.id, 'pending');
                }
            }
            await this.refresh();
            window.dispatchEvent(new CustomEvent('passage-form:flush'));
        },

        statusLabel(status) {
            return {
                pending:   'En attente',
                uploading: 'En cours',
                error:     'Erreur',
                draft:     'Brouillon',
            }[status] ?? status;
        },

        statusClass(status) {
            return {
                pending:   'bg-warn/15 text-[oklch(0.5_0.11_72)]',
                uploading: 'bg-azure-50 text-azure-700',
                error:     'bg-danger/10 text-danger',
                draft:     'bg-sand-100 text-ink-500',
            }[status] ?? '';
        },
    };
}

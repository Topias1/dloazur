import {
    getPassagesByStatus,
    markStatus,
    openOfflineDB,
} from './offline-queue.js';

/**
 * Backoff delays shared by the store flush and the passageForm component.
 * Defined once — no duplicated literals.
 * @type {number[]}
 */
export const UPLOAD_DELAYS = [2000, 8000, 30000];

/**
 * Orphan-recovery: re-queues any IDB passage stuck in 'uploading' back to
 * 'pending' so it is retried on next flush.
 *
 * Triggered at boot (alpine:initialized) and at the start of every flushAll()
 * so a tab killed mid-upload never creates a zombie.
 *
 * @returns {Promise<number>} number of orphans re-queued
 */
export async function recoverOrphans() {
    const uploading = await getPassagesByStatus('uploading');
    for (const item of uploading) {
        await markStatus('passages', item.id, 'pending', item.attempts ?? 0);
    }
    return uploading.length;
}

/**
 * Returns the CSRF + JSON headers required for fetch calls to /api/passages.
 * @param {boolean} isJson - true adds Content-Type: application/json
 * @returns {Record<string, string>}
 */
export function buildHeaders(isJson) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const h = {
        'X-CSRF-TOKEN':     csrf,
        'Accept':           'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
    if (isJson) h['Content-Type'] = 'application/json';
    return h;
}

/**
 * Extracts a user-readable error message from a 422 Laravel validation response.
 * Falls back to a generic FR message if parsing fails.
 * @param {Response} res
 * @returns {Promise<string>}
 */
export async function validationMessage(res) {
    const RANGE_LABELS = {
        ph_avant:     'pH',
        chlore_libre: 'Chlore libre',
        chlore_total: 'Chlore total',
        tac:          'TAC',
        sel_g_l:      'Sel',
        th:           'TH',
    };
    try {
        const body = await res.json();
        if (body?.errors && typeof body.errors === 'object') {
            const labels = Object.keys(body.errors).map((f) => RANGE_LABELS[f] ?? f);
            if (labels.length) {
                return `Valeur hors limites : ${labels.join(', ')}. Corrige la saisie puis réenregistre.`;
            }
        }
        if (body?.message) return body.message;
    } catch (_e) { /* corps non-JSON */ }
    return 'Enregistrement refusé par le serveur. Vérifie les valeurs saisies puis réessaie.';
}

/**
 * Synchronise la consommation chimie vers POST /api/passages/produits.
 * Soft-fail : un échec pose produits_pending=true pour retry au prochain flush.
 *
 * @param {Object} item  - IDB passage record (needs .id, .client_uuid, .payload_json)
 * @returns {Promise<void>}
 */
export async function syncProduits(item) {
    let payload = {};
    try { payload = JSON.parse(item.payload_json); } catch { /* JSON malformé */ }
    const produits = payload.produits ?? [];
    if (!produits.length) return;

    try {
        const res = await fetch('/api/passages/produits', {
            method:      'POST',
            headers:     buildHeaders(true),
            credentials: 'same-origin',
            body:        JSON.stringify({
                passage_client_uuid: item.client_uuid,
                produits,
            }),
        });

        if (res.ok) {
            const db = await openOfflineDB();
            const fresh = await db.get('passages', item.id);
            if (fresh) {
                fresh.produits_pending = false;
                await db.put('passages', fresh);
            }
        } else {
            throw new Error('HTTP ' + res.status);
        }
    } catch (e) {
        console.warn('[upload-pipeline] produits sync deferred', item.client_uuid, e);
        try {
            const db = await openOfflineDB();
            const fresh = await db.get('passages', item.id);
            if (fresh) {
                fresh.produits_pending = true;
                await db.put('passages', fresh);
            }
        } catch (dbErr) {
            console.error('[upload-pipeline] IDB write failed for produits_pending', dbErr);
        }
    }
}

/**
 * Uploads a single pending passage to POST /api/passages with exponential
 * backoff (2 s → 8 s → 30 s).
 *
 * Returns an object that lets the caller react to specific outcomes:
 *   { ok: true }                          — uploaded successfully
 *   { conflict: true, msg: string }       — 409 already closed (D-40)
 *   { permanentError: true, msg: string } — 4xx client rejection (422 etc.)
 *   { transient: true }                   — all retries exhausted; left as pending
 *
 * @param {Object} item - IDB passage record
 * @returns {Promise<{ok?:boolean, conflict?:boolean, permanentError?:boolean, transient?:boolean, msg?:string}>}
 */
export async function uploadPassage(item) {
    for (let attempt = 0; attempt < UPLOAD_DELAYS.length; attempt++) {
        try {
            await markStatus('passages', item.id, 'uploading');
            const res = await fetch('/api/passages', {
                method:      'POST',
                headers:     buildHeaders(true),
                credentials: 'same-origin',
                body:        item.payload_json,
            });

            if (res.status === 409) {
                await markStatus('passages', item.id, 'synced');
                return {
                    conflict: true,
                    msg:      "Ce passage a déjà été clos. Tes modifications n'ont pas été enregistrées.",
                };
            }

            if (res.ok) {
                await markStatus('passages', item.id, 'synced');
                await syncProduits(item);
                return { ok: true };
            }

            // 4xx permanent refusal
            if (res.status >= 400 && res.status < 500) {
                await markStatus('passages', item.id, 'error', (item.attempts || 0) + 1);
                const msg = await validationMessage(res);
                return { permanentError: true, msg };
            }

            // 5xx / unexpected — retry
            throw new Error('Server error ' + res.status);

        } catch (e) {
            console.warn('[upload-pipeline] upload attempt', attempt + 1, 'failed', e);
            if (attempt < UPLOAD_DELAYS.length - 1) {
                await new Promise((r) => setTimeout(r, UPLOAD_DELAYS[attempt]));
            } else {
                // Transient exhaustion → back to pending (not dead error)
                await markStatus('passages', item.id, 'pending', (item.attempts || 0) + 1);
                return { transient: true };
            }
        }
    }
    return { transient: true };
}

/**
 * Full flush pipeline:
 *   1. Recover uploading orphans → pending
 *   2. Upload all pending passages (backoff)
 *   3. Retry synced passages with produits_pending=true
 *
 * Returns a summary useful for showing sync-success feedback.
 *
 * @returns {Promise<{flushed:number, conflicts:number, errors:number, orphans:number}>}
 */
export async function flushPipeline() {
    const orphans = await recoverOrphans();
    const pending = await getPassagesByStatus('pending');

    let flushed = 0, conflicts = 0, errors = 0;

    for (const item of pending) {
        const result = await uploadPassage(item);
        if (result.ok)            flushed++;
        if (result.conflict)      { conflicts++; flushed++; }
        if (result.permanentError) errors++;
        // transient stays in pending — counted neither flushed nor error
    }

    // Retry produits deferred
    const db = await openOfflineDB();
    const all = await db.getAll('passages');
    for (const p of all) {
        if (p.status === 'synced' && p.produits_pending === true) {
            await syncProduits(p);
        }
    }

    return { flushed, conflicts, errors, orphans };
}

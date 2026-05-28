import { openDB } from 'idb';

/**
 * IndexedDB queue manager pour les passages + photos offline.
 * Schema D-59 — DB: 'dloazur-offline-v1' v1, stores: passages + photos.
 */
const DB_NAME = 'dloazur-offline-v1';
const DB_VERSION = 1;

/** @returns {Promise<import('idb').IDBPDatabase>} */
export async function openOfflineDB() {
    return openDB(DB_NAME, DB_VERSION, {
        upgrade(db) {
            if (!db.objectStoreNames.contains('passages')) {
                const passStore = db.createObjectStore('passages', {
                    keyPath: 'id',
                    autoIncrement: true,
                });
                passStore.createIndex('by-status', 'status');
                passStore.createIndex('by-created', 'created_at');
            }
            if (!db.objectStoreNames.contains('photos')) {
                const photoStore = db.createObjectStore('photos', {
                    keyPath: 'id',
                    autoIncrement: true,
                });
                photoStore.createIndex('by-passage', 'passage_client_uuid');
                photoStore.createIndex('by-status', 'status');
            }
        },
    });
}

/**
 * Sauvegarde / met à jour un passage en IDB (idempotent sur client_uuid via index séparé).
 * @param {Object} record - { client_uuid, payload_json, status, attempts, created_at, last_attempt_at }
 * @returns {Promise<IDBValidKey>}
 */
export async function upsertPassage(record) {
    const db = await openOfflineDB();
    return db.put('passages', record);
}

/**
 * Sauvegarde une photo (blob inclus).
 * @param {Object} record - { client_uuid, passage_client_uuid, blob, status, attempts, captured_at }
 * @returns {Promise<IDBValidKey>}
 */
export async function savePhoto(record) {
    const db = await openOfflineDB();
    return db.put('photos', record);
}

/**
 * Récupère tous les passages avec status === filter (default 'pending').
 * @param {string} status
 * @returns {Promise<Array>}
 */
export async function getPassagesByStatus(status = 'pending') {
    const db = await openOfflineDB();
    return db.getAllFromIndex('passages', 'by-status', status);
}

/**
 * Récupère toutes les photos d'un passage (par client_uuid du passage).
 * @param {string} passageClientUuid
 * @returns {Promise<Array>}
 */
export async function getPhotosByPassage(passageClientUuid) {
    const db = await openOfflineDB();
    return db.getAllFromIndex('photos', 'by-passage', passageClientUuid);
}

/**
 * Met à jour le status (pending/uploading/synced/error) d'un item.
 * @param {'passages'|'photos'} storeName
 * @param {number} id
 * @param {string} status
 * @param {number|null} attempts
 * @returns {Promise}
 */
export async function markStatus(storeName, id, status, attempts = null) {
    const db = await openOfflineDB();
    const item = await db.get(storeName, id);
    if (!item) return null;
    item.status = status;
    if (attempts !== null) item.attempts = attempts;
    item.last_attempt_at = new Date().toISOString();
    return db.put(storeName, item);
}

/**
 * Compte les items non synchronisés (pending + uploading + error) pour le badge.
 * @returns {Promise<{pending: number, errors: number, total: number}>}
 */
export async function countPendingAll() {
    const db = await openOfflineDB();
    const passages = await db.getAllFromIndex('passages', 'by-status', 'pending');
    const errors = await db.getAllFromIndex('passages', 'by-status', 'error');
    const uploading = await db.getAllFromIndex('passages', 'by-status', 'uploading');
    return {
        pending: passages.length + uploading.length,
        errors: errors.length,
        total: passages.length + uploading.length + errors.length,
    };
}

/**
 * Purge les passages et photos avec status === 'synced'.
 * Appelé après une synchro réussie pour libérer de la place.
 * @returns {Promise<void>}
 */
export async function clearSynced() {
    const db = await openOfflineDB();
    const syncedPassages = await db.getAllFromIndex('passages', 'by-status', 'synced');
    const tx = db.transaction(['passages', 'photos'], 'readwrite');
    for (const p of syncedPassages) {
        const syncedPhotos = await tx.objectStore('photos').index('by-passage').getAll(p.client_uuid);
        for (const ph of syncedPhotos) {
            if (ph.status === 'synced') {
                await tx.objectStore('photos').delete(ph.id);
            }
        }
        await tx.objectStore('passages').delete(p.id);
    }
    await tx.done;
}

/**
 * Carnet local — DIAG-07 (Plan 05-06)
 *
 * Store on-device pour l'historique des diagnostics terminés.
 * Technologie : localStorage (shape compacte, 0 dépendance — idb non requis).
 * Isolation : 0 serveur, 0 synchro, 0 compte.
 * Règle DIAG-02 : ce module stocke UNIQUEMENT le résumé/texte calculé du diagnostic ;
 *                 AUCUNE formule arithmétique, AUCUN coefficient de dose.
 * Sécurité T-05-20 : le contenu est rendu via x-text / double-brace Alpine (jamais innerHTML).
 *
 * API exposée sur window.diagnosticCarnet et enregistrée comme Alpine.data('carnetStore') :
 *   save(entry)    — persiste une entrée ; écrase si même id
 *   all()          — retourne toutes les entrées en ordre anti-chronologique
 *   get(id)        — retourne une entrée par id (ou null)
 *   clear()        — supprime tout le carnet
 *   hasEntries()   — true si au moins une entrée
 *   latestEntry()  — dernière entrée (pour le strip "Reprendre" sur la landing)
 *   markRetested(id, success) — marque un re-test : success=true|false
 *
 * Format d'une entrée :
 * {
 *   id:           string,   — identifiant unique (timestamp ISO ou server id stringifié)
 *   date:         string,   — date lisible (ISO 8601)
 *   symptome:     string,   — libellé symptôme ou mode (ex: "Analyse chimique")
 *   diagnostic:   string,   — texte du résultat/diagnostic
 *   confidence:   string,   — 'eleve' | 'moyen' | 'indicatif'
 *   mesuresCles:  string,   — résumé textuel des mesures clés (pH, chlore, TAC)
 *   resultText:   string,   — texte complet du résultat (pour le resume)
 *   serverId:     number|null — id server pour le lien PDF (null si anonyme non persisté)
 *   retested:     boolean,  — re-test effectué
 *   retestOk:     boolean|null — null = pas re-testé, true = ok, false = echec
 * }
 *
 * NOTE : les doses/recommandations calculées NE SONT PAS stockées ici.
 * Seuls le résumé textuel (resultText) et les mesures clés lisibles sont conservés.
 */

const CARNET_KEY = 'dloazur_diagnostic_carnet_v1';

/**
 * Lit le carnet depuis localStorage.
 * Retourne un tableau d'entrées (peut être vide).
 */
function readCarnet() {
    try {
        const raw = localStorage.getItem(CARNET_KEY);
        if (!raw) return [];
        const data = JSON.parse(raw);
        return Array.isArray(data) ? data : [];
    } catch {
        return [];
    }
}

/**
 * Écrit le carnet dans localStorage.
 */
function writeCarnet(entries) {
    try {
        localStorage.setItem(CARNET_KEY, JSON.stringify(entries));
    } catch {
        // Quota localStorage dépassé — silencieux, le carnet est un feature de confort
    }
}

/**
 * Sauvegarde une entrée de diagnostic dans le carnet.
 * Écrase l'entrée si un enregistrement avec le même `id` existe déjà.
 *
 * @param {Object} entry — entrée conforme au format ci-dessus
 */
function save(entry) {
    if (!entry || !entry.id) return;
    const entries = readCarnet();
    const idx = entries.findIndex((e) => e.id === entry.id);
    const normalized = {
        id:          String(entry.id),
        date:        entry.date || new Date().toISOString(),
        symptome:    String(entry.symptome || ''),
        diagnostic:  String(entry.diagnostic || ''),
        confidence:  String(entry.confidence || 'indicatif'),
        mesuresCles: String(entry.mesuresCles || ''),
        resultText:  String(entry.resultText || ''),
        serverId:    entry.serverId ?? null,
        retested:    Boolean(entry.retested),
        retestOk:    entry.retestOk ?? null,
    };
    if (idx >= 0) {
        entries[idx] = normalized;
    } else {
        entries.unshift(normalized); // anti-chronologique
    }
    writeCarnet(entries);
}

/**
 * Retourne toutes les entrées en ordre anti-chronologique.
 */
function all() {
    return readCarnet();
}

/**
 * Retourne une entrée par son id, ou null si introuvable.
 */
function get(id) {
    return readCarnet().find((e) => e.id === String(id)) ?? null;
}

/**
 * Efface tout le carnet du device.
 */
function clear() {
    localStorage.removeItem(CARNET_KEY);
}

/**
 * True si le carnet contient au moins une entrée.
 */
function hasEntries() {
    return readCarnet().length > 0;
}

/**
 * Retourne la dernière entrée (la plus récente), ou null.
 */
function latestEntry() {
    const entries = readCarnet();
    return entries.length > 0 ? entries[0] : null;
}

/**
 * Marque le re-test d'une entrée.
 * @param {string} id
 * @param {boolean} success
 */
function markRetested(id, success) {
    const entries = readCarnet();
    const idx = entries.findIndex((e) => e.id === String(id));
    if (idx >= 0) {
        entries[idx].retested = true;
        entries[idx].retestOk = Boolean(success);
        writeCarnet(entries);
    }
}

// Exposé sur window pour utilisation depuis Alpine (x-data="carnetStore()")
const diagnosticCarnet = { save, all, get, clear, hasEntries, latestEntry, markRetested };
window.diagnosticCarnet = diagnosticCarnet;

/**
 * Factory Alpine.data pour le composant carnet.
 * Utilisé dans le wizard : x-data="carnetStore()"
 * et sur la landing : x-data="carnetResumeStrip()"
 */
export function carnetStore() {
    return {
        entries: [],
        showClearConfirm: false,

        init() {
            this.entries = diagnosticCarnet.all();
        },

        refresh() {
            this.entries = diagnosticCarnet.all();
        },

        hasEntries() {
            return this.entries.length > 0;
        },

        formatDate(isoDate) {
            try {
                return new Date(isoDate).toLocaleDateString('fr-FR', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                });
            } catch {
                return isoDate;
            }
        },

        confidenceLabel(level) {
            const labels = {
                eleve:     'Confiance élevée',
                moyen:     'Confiance moyenne',
                indicatif: 'Indicatif',
            };
            return labels[level] || 'Indicatif';
        },

        clearCarnet() {
            diagnosticCarnet.clear();
            this.entries = [];
            this.showClearConfirm = false;
        },
    };
}

/**
 * Factory Alpine.data pour le strip "Reprendre mon dernier diagnostic" (landing S1).
 */
export function carnetResumeStrip() {
    return {
        latest: null,

        init() {
            this.latest = diagnosticCarnet.latestEntry();
        },

        hasLatest() {
            return this.latest !== null;
        },
    };
}

export default diagnosticCarnet;

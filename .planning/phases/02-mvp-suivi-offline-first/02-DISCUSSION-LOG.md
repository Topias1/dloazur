# Phase 2: MVP Suivi Offline-First - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-05-28
**Phase:** 2-MVP Suivi Offline-First
**Areas discussed:** Stratégie de synchro & conflits, Pipeline photos offline, Magic link client (AUTH-02..04), Architecture PWA
**Mode:** advisor (USER-PROFILE.md present) + calibration tier `standard` (2-4 options)

---

## Zone 1 — Stratégie de synchro & résolution de conflits (PASS-03)

Recherche : agent advisor avec contexte solo-opérateur + ~10 passages/sem + idempotence `client_uuid` existante.

| Option | Description | Selected |
|--------|-------------|----------|
| A — Server-wins conditionnel | `INSERT ON CONFLICT (client_uuid) DO UPDATE WHERE status='draft'`. Endpoint unique POST /api/passages, 409 si déjà clos. | ✓ |
| B — Last-write-wins via `updated_at` | UPSERT inconditionnel basé sur le timestamp. Risque clock skew iPhone. | |
| C — POST/PUT séparés + UI réconciliation | 409 + payload serveur retourné, Alpine affiche un diff. | |
| D — CRDT merge field-by-field | (présenté en recherche, écarté avant proposition utilisateur — sur-engineering) | |

**User's choice:** A — Server-wins conditionnel (Recommandé)
**Notes:** Justification : 1 seul opérateur, conflit ultra-rare. Atomicité Postgres + un seul endpoint = simplicité maintenance solo. Le `client_uuid` est généré côté Alpine via `crypto.randomUUID()` au démarrage de la saisie, stocké en IDB, envoyé tel quel. Code retour 409 géré par toast Alpine.

---

## Zone 2 — Pipeline photos offline (PASS-02, PASS-04)

Recherche : 4 sous-décisions. Les sous-décisions (b)(c)(d) avaient des recos nettes (Background Sync fallback Alpine, JPEG q=0.80 max 2048px, passage-d'abord puis photos individuelles). Seule la sous-décision (a) HEIC iPhone méritait un choix explicite.

| Option | Description | Selected |
|--------|-------------|----------|
| A — Server-side Imagick | Upload HEIC brut (3-5MB/photo), conversion serveur en queue worker. ~15-25MB/passage sur 3G Martinique. | |
| B — WASM heic2any client + Canvas compress | Lazy-import ~2.7MB uniquement si HEIC détecté. Resize+JPEG q=0.80 → ~400KB/photo. Optimise le réseau Martinique. | ✓ |
| C — Désactiver HEIC côté iPhone | Demander à Pierre de configurer "Le plus compatible". (Écarté avant proposition — friction non-technophile.) | |

**User's choice:** B — WASM client + Canvas compress (Recommandé)
**Notes:** Choix justifié par le réseau hasardeux Martinique (contrainte n°1 PROJECT.md). Bundle PWA +2.7MB lazy-loaded uniquement si HEIC détecté via magic bytes. Sur Android JPEG natif : WASM jamais chargé. Question utilisateur de suivi : "ça va marcher sur Android ?" → réponse : oui, lazy import via `import('heic2any')` conditionnel, BackgroundSync API Android Chrome bénéficie même mieux.

**Sous-décisions automatiques (recos sans choix explicite) :**
- (b) Background Sync : fallback Alpine `online` + `visibilitychange` (+ ajout `BackgroundSyncPlugin` Workbox suite à correction zone 4)
- (c) Compression : Canvas JPEG q=0.80 resize 2048px + correction EXIF orientation via `exifr`
- (d) Ordre upload : passage → puis photos individuelles avec `idempotency_key` UUID par photo, backoff 2s→8s→30s

---

## Zone 3 — Magic link client (AUTH-02, AUTH-03, AUTH-04)

Recherche : 6 sous-décisions regroupées en 3 profils cohérents (Léger / Équilibré / Strict).

| Option | Description | Selected |
|--------|-------------|----------|
| A — Léger UX | 72h, multi-use sans limite, GET direct consomme, session permanente 1 an, rate limit IP seul. Risque SafeLinks Outlook. | |
| B — Équilibré (Recommandé) | 48h, numMaxVisits=3, page /auth/confirm HTML statique + bouton POST, session glissante 30j, rate limit IP+email+cap 3/24h. | ✓ |
| C — Strict OTP | 15min single-use + code OTP 6 chiffres. Friction max pour portail consultation. | |

**User's choice:** B — Équilibré (Recommandé)
**Notes:** Décision pilotée par le risque réel SafeLinks (Microsoft 365 Business Defender for Office 365) qui ferait un GET au moment du clic via proxy Microsoft → consommerait un token single-use. Confirmé via doc Microsoft Learn (time-of-click rewriting + proxy GET). Conciergeries B2B Martinique probablement sur M365. Page `/auth/confirm` (HTML statique → POST) protège proprement. Pattern OWASP/FusionAuth.

---

## Zone 4 — Architecture PWA (PASS-03, PASS-06)

Recherche initiale : profil "Standalone iOS-first" (5 sous-décisions groupées).

**Interruption critique de l'utilisateur** : "Comment tu sait que Pierre est sur iPhone, je n'en sais rien moi même, et je crois que non d'ailleurs. Mais je veux que ça puisse fonctionner sur iOS et Android indifféremment, qu'il soit récent ou ancien."

→ Correction d'une assomption erronée : ROADMAP-02 success criterion #2 et STATE blocker disaient "iPhone / iOS Safari" — l'utilisateur ne sait pas et croit que Pierre n'est PAS sur iPhone. Le PROJECT.md, lui, dit seulement "Smartphone uniquement (mobile-first)".

**Impact** :
- Memory utilisateur sauvegardée : `pierre-device-platform.md` (Pierre device inconnu, pas iPhone par défaut, cross-platform requis)
- ROADMAP-02 SC#2 à amender avant verify-phase (D-37)
- Le profil PWA est révisé pour activer `BackgroundSyncPlugin` (utile Android Chrome, no-op iOS) + `storage.persist()` inconditionnel au boot

| Option | Description | Selected |
|--------|-------------|----------|
| Profil "Standalone iOS-first" (initial) | generateSW + prompt + storage.persist standalone + 4 caches + 2 stores IDB | (rejeté — iOS-only) |
| Profil "Cross-platform mobile-first" (révisé) | + BackgroundSyncPlugin actif + storage.persist inconditionnel | ✓ |
| Idem avec injectManifest | SW custom, +complexité debug iOS | |

**User's choice:** Adopter le profil révisé (Recommandé)
**Notes:** Profil cross-platform retenu. `generateSW` couvre iOS+Android. `BackgroundSyncPlugin` actif → Android Chrome bénéficie, iOS no-op silencieusement. Update strategy `prompt` protège la saisie en cours. `storage.persist()` appelé inconditionnellement au boot Alpine (iOS auto-grant si standalone, Android prompt possible). 2 stores IDB séparés (`passages` + `photos`) requis par PASS-04 (résilience photo par photo).

---

## Claude's Discretion

Délégation explicite ou implicite :
- Recherche clients UX simple (`ILIKE` sur ~10 clients) — D-61
- Validation soft des mesures (warnings, pas de blocage) — D-63
- Auto-pick piscine unique vs sélecteur si multi — D-64
- Structure routes API et architecture Alpine (factories vs Alpine.data) — research/plan-phase tranche
- Naming IDB stores cohérent convention Laravel — researcher décide

---

## Deferred Ideas

- Push notifications → Phase 4 (NOTIF-*) — pourrait justifier injectManifest plus tard
- Recherche full-text Postgres clients → si > 100 clients (volume actuel ~10)
- Filtres avancés portail client (date range, recherche notes)
- WhatsApp pour compte-rendu/rappel → Phase 4
- Multi-piscines par client en UI → v2 (CLI-04)
- Détection doublons pré-submit côté Alpine (le `client_uuid` côté serveur suffit pour MVP)
- Mode "draft auto-save" après chaque champ (à clarifier au researcher selon mockup passage.html)
- Téléchargement ZIP historique côté portail → v2

---

## Méta — Corrections de source pendant la discussion

1. **2026-05-28** : Assomption "Pierre sur iPhone" invalidée par l'utilisateur. Source de l'erreur : ROADMAP.md Phase 2 SC#2 ("sur iPhone") + STATE blocker Phase 2 ("iOS Safari + Martinique"). Mémoire user `pierre-device-platform.md` créée. Amendement ROADMAP requis avant verify-phase (D-37). Aucun impact sur les décisions D-38..D-54 (synchro + photos + magic link). Impact uniquement sur le profil PWA (D-55..D-60 reformulés en "cross-platform").

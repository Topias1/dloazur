# Phase 2: MVP Suivi Offline-First — Research

**Researched:** 2026-05-28
**Domain:** PWA offline-first (vite-plugin-pwa, Workbox, IndexedDB), Laravel API (Fortify multi-guard, Sanctum SPA auth, magic link), Alpine.js offline form, Livewire 3 CRUD, photo pipeline (HEIC → JPEG), Cloudflare R2 media
**Confidence:** HIGH

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

- **CF-01:** Stack Laravel 13 + Livewire 3 + Alpine 3 + Tailwind 4 + Postgres 17
- **CF-02:** Pas Livewire pour la saisie passage offline — Alpine 3 + IndexedDB (`idb` ^8) + Service Worker
- **CF-03:** Storage photos Cloudflare R2 (S3-compatible, zero-egress, 10 GB gratuits) — disk `r2` configuré Phase 1
- **CF-04:** Magic link via `cesargb/laravel-magiclink` ^2.27
- **CF-05:** Médias via `spatie/laravel-medialibrary` ^11.22
- **CF-06:** PWA via `vite-plugin-pwa` ^1.3 + `idb` ^8
- **CF-07:** Schéma déjà migré Phase 1 — `passages.client_uuid` UUID unique, `photos_meta.disk` default `'r2'`; **TODO Phase 2 : `ALTER TABLE photos_meta ADD COLUMN client_uuid UUID UNIQUE`**
- **CF-08:** Design system verrouillé via skill `impeccable` (PRODUCT.md, DESIGN.md, mockups/v1/*)
- **D-36:** Cible iOS Safari ET Android Chrome, récents (~5 ans) et anciens (≥ iOS 14 / Android 5). Device de Pierre inconnu.
- **D-37:** ROADMAP-02 SC#2 doit remplacer `"sur iPhone"` par `"sur smartphone"` en commit dédié.
- **D-38:** Server-wins UPSERT conditionnel — `POST /api/passages` avec `ON CONFLICT (client_uuid) DO UPDATE … WHERE passages.status = 'draft'`
- **D-39:** `client_uuid` généré côté client Alpine via `crypto.randomUUID()`
- **D-40:** `409 Conflict` quand passage déjà clos — toast + purge IDB
- **D-41:** Pas de CRDT, pas de merge field-by-field
- **D-42:** Photos : `client_uuid` par photo, UPSERT sur `photos_meta.client_uuid`
- **D-43:** Conversion HEIC côté client via `heic2any` ^0.0.4 (lazy-import)
- **D-44:** Compression Canvas `image/jpeg 0.80` max 2048px, correction EXIF via `exifr` AVANT `drawImage`; pas de WebP (Safari iOS 17)
- **D-45:** Upload séquentiel — passage d'abord, puis photos une par une
- **D-46:** Backoff Alpine 3 retries `2s → 8s → 30s`, puis `status='error'`
- **D-47:** BackgroundSyncPlugin Workbox pour Android + fallback `online`/`visibilitychange` Alpine pour iOS Safari
- **D-48:** `upload_max_filesize = 10MB`, `post_max_size` aligné
- **D-49:** Magic link — lifetime 48h (2880 min), numMaxVisits 3
- **D-50:** Page intermédiaire GET `/auth/confirm?token=…` statique → POST consomme le token (protection SafeLinks M365)
- **D-51:** Session client glissante 30 jours post-confirmation
- **D-52:** Rate limit magic link — IP 5/h + email 3/24h, message anti-énumération générique
- **D-53:** Pas de CAPTCHA, pas d'OTP
- **D-54:** `MagicLink::create($action, lifetime: 2880, numMaxVisits: 3)` avec callback LoginAction
- **D-55:** Mode `generateSW` (pas `injectManifest`) pour Phase 2
- **D-56:** `registerType: 'prompt'` + toast Alpine pour update PWA
- **D-57:** `navigator.storage.persist()` appelé au boot Alpine (root layout admin)
- **D-58:** Cache strategies — NavigationFirst 5s + offline.html / CacheFirst pour assets Vite / NetworkOnly + BackgroundSync pour `/api/*` / NetworkFirst `/admin/*` sans fallback offline
- **D-59:** IndexedDB — DB `dloazur-offline-v1`, stores `passages` et `photos`, schéma typé `DBSchema`
- **D-60:** `buildBase: '/build/'` dans `vite.config.js` + header `Service-Worker-Allowed: /`
- **D-61:** Recherche clients — ILIKE sur `name + email + phone + address`, pagination 25/page, tri `updated_at DESC`
- **D-62:** Historique passages — paginé 25/page, filtres `client_id` + date range, tri `visited_at DESC`
- **D-63:** Validation mesures soft — warnings toast Alpine, pas de blocage submit
- **D-64:** 1 client = 1 piscine en UI v1, auto-pick si unique
- **D-65:** Portail client — transposition 1:1 `mockups/v1/portail.html`, lazy-load photos

### Claude's Discretion

- Architecture exacte des composants Alpine (factories vs `Alpine.data()` global)
- Structure des routes API (`routes/api.php` avec préfixe `/api/v1/` ou pas)
- Stratégie de test offline : Pest 4 Playwright disponible
- UI du badge "N passages en attente" — référence `mockups/v1/dashboard.html`
- Naming fichiers IDB / Alpine stores

### Deferred Ideas (OUT OF SCOPE)

- Push notifications → Phase 4 (injectManifest deviendra nécessaire)
- Recherche full-text Postgres clients → si > 100 clients
- Filtres avancés portail client
- WhatsApp compte-rendu / rappel → Phase 4
- Multi-piscines par client en UI → v2
- Mode "draft auto-save" entre chaque champ → optimisation
- Détection doublons pré-submit hash côté client → D-38 UPSERT couvre le cas
</user_constraints>

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| AUTH-02 | Client se connecte via magic link (sans mot de passe) | `cesargb/laravel-magiclink` v2.27 — guard `clients` isolé, LoginAction, D-49/D-54 |
| AUTH-03 | Session persiste entre visites, déconnexion possible | Session glissante 30j (D-51), `Auth::guard('clients')->logout()` |
| AUTH-04 | Magic link passe par page de confirmation intermédiaire | GET statique + POST consomme le token (D-50), protection SafeLinks M365 |
| CLI-01 | Pro crée et modifie une fiche client | Livewire 3 `ClientForm` component, Eloquent Client model présent |
| CLI-02 | Pro enregistre la piscine d'un client | Livewire 3 `PiscineForm`, relation existante `Client::piscines()` |
| CLI-03 | Pro recherche et filtre ses clients | Livewire 3 `ClientIndex` avec `wire:model.live.debounce`, ILIKE Postgres |
| PASS-01 | Pro saisit un passage (mesures pH/Cl/TAC/sel, actions, notes) | Alpine 3 `x-data` + IDB store `passages`, `crypto.randomUUID()`, steppers mockup 1:1 |
| PASS-02 | Pro ajoute photos à un passage | `<input capture="environment">`, HEIC→JPEG (heic2any), Canvas resize, blob → IDB store `photos` |
| PASS-03 | Saisie offline + sync idempotente sans doublon | `vite-plugin-pwa` generateSW + BackgroundSync (Android) + `online` event fallback (iOS), UPSERT `ON CONFLICT` |
| PASS-04 | Photos file résiliente (retry par photo, compression) | Backoff D-46, upload séquentiel D-45, `photos_meta.client_uuid` UNIQUE |
| PASS-05 | Historique passages pro avec filtres client/date | Livewire 3 `PassageIndex`, `WithPagination`, filtres `client_id` + date range |
| PASS-06 | PWA indique N passages en attente de synchronisation | Badge Alpine lu depuis IDB `passages.status != 'synced'` + `aria-live="polite"` |
| PORT-01 | Client voit historique de ses passages en lecture seule | Livewire portail gated par guard `clients`, timeline `mockups/v1/portail.html` 1:1 |
| PORT-02 | Client voit mesures, photos et notes pour chaque passage | Medialibrary `temporaryUrl()` depuis R2, lightbox Alpine, grille mesures |
</phase_requirements>

---

## Summary

Phase 2 est le cœur de valeur du projet : l'opérateur (Pierre ADAM, solo) enregistre un passage d'entretien piscine sur le terrain **sans réseau** depuis son smartphone, les données et photos se synchronisent **idempotamment** au retour réseau sans créer de doublon, et le client consulte son historique depuis un lien sécurisé sans mot de passe. La stack est entièrement verrouillée depuis Phase 1 ; cette recherche documente les détails d'implémentation, les patterns exacts et les pièges à éviter pour que le planificateur n'ait pas à faire de choix techiques.

L'architecture est en deux courants indépendants : (1) le **courant offline-first** (Alpine 3 → IndexedDB `idb` v8 → Service Worker Workbox → `POST /api/passages`) qui ne touche jamais Livewire, et (2) le **courant back-office Livewire** (Clients/Piscines CRUD + liste Passages + Portail client) qui exige une connexion. Ces deux courants cohabitent dans la même application Laravel sans se marcher dessus. L'API REST `/api/passages` est protégée par le guard `web` (session cookie pro) — pas Sanctum tokens, pas d'ajout de package, le PWA est same-domain.

La complexité principale est dans le **pipeline photos mobile** : iOS envoie des HEIC, Android des JPEG. Le HEIC doit être détecté par magic bytes (pas MIME type — iOS Safari ment), converti via `heic2any` (WASM lazy-loaded), puis compressé via Canvas API. L'ensemble doit tenir dans IndexedDB (blob stocké localement) et être uploadé photo par photo avec retry idempotent. Côté base de données, une migration Phase 2 ajoute `photos_meta.client_uuid UUID UNIQUE` — colonne manquante dans le schéma Phase 1.

**Recommandation principale :** Ne pas ajouter Sanctum pour l'API. Utiliser le cookie de session `web` existant pour `routes/api.php` (le PWA est same-domain). Ajouter `routes/portail.php` pour le guard `clients` séparé. Deux guards, zéro nouveau package d'auth.

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Saisie passage offline | Browser (Alpine + IDB) | — | Livewire exige le réseau — Alpine seul peut écrire hors-ligne |
| Sync passage → serveur | Browser (SW BackgroundSync + Alpine online event) | API Laravel | SW gère Android, Alpine gère iOS |
| UPSERT idempotent passages | API/Backend (`POST /api/passages`) | Database (Postgres UPSERT) | Logique de conflit serveur-side uniquement |
| Compression photos HEIC→JPEG | Browser (Canvas API + heic2any WASM) | — | Réduire de 15-25 MB à ~300 KB avant tout transit réseau |
| Upload photos file | Browser (Alpine backoff + SW) | API/Backend | File côté client, réception idempotente côté serveur |
| Stockage photos | CDN/Storage (Cloudflare R2) | Database (photos_meta meta) | S3-compatible zero-egress |
| Auth pro (email+pwd) | Frontend Server (Fortify guard `web`) | — | Déjà opérationnel Phase 1 |
| Auth client magic link | API/Backend (magiclink + guard `clients`) | Frontend Server | Guard séparé, session isolée |
| CRUD clients/piscines | Frontend Server (Livewire 3) | Database | Livewire reactive CRUD, connexion requise |
| Liste historique passages | Frontend Server (Livewire 3) | Database | CRUD lecture, filtres Eloquent |
| Portail client lecture | Frontend Server (Livewire 3 + guard clients) | CDN/Storage (R2 URLs signées) | Client en ligne quand il consulte |
| Badge sync IDB | Browser (Alpine $store) | — | Lecture IDB locale, pas de round-trip serveur |

---

## Standard Stack

### Core (tous verrouillés Phase 1 / CONTEXT.md)

| Package | Version vérifié | Purpose |
|---------|----------------|---------|
| `laravel/framework` | 13.x (composer.json) | Backend framework |
| `livewire/livewire` | 3.x (composer.lock) | CRUD Clients/Piscines/Passages pro + Portail client |
| `alpinejs` | 3.15.12 (package.json) | Saisie passage offline, IDB orchestration, badge sync |
| `tailwindcss` | 4.x (package.json) | Utility CSS, tokens @theme |
| `vite-plugin-pwa` | 1.3.0 (npm registry, 2026-05-05) | Service Worker generateSW + Web App Manifest |
| `idb` | 8.0.3 (npm registry, vérifié) | Wrapper IndexedDB typé — stores `passages` + `photos` |
| `heic2any` | 0.0.4 (npm registry, vérifié) | Conversion HEIC→JPEG WASM côté client (lazy-import) |
| `exifr` | 7.1.3 (npm registry, vérifié) | Lecture EXIF orientation avant drawImage Canvas |
| `cesargb/laravel-magiclink` | 2.27.1 (Packagist, 2026-04-20) | Magic link clients, guard `clients`, lifetime+visits |
| `spatie/laravel-medialibrary` | 11.23.0 (Packagist, 2026-05-28) | Collections photos + R2 disk + URL temporaires |
| `spatie/laravel-pdf` | 2.11.0 (composer.json — déjà installé) | PDF Phase 3 — présent mais non utilisé Phase 2 |
| `laravel/fortify` | 1.37 (composer.json) | Auth pro email+pwd (opérationnel Phase 1) |

### Packages à installer en Phase 2

```bash
# PHP (Packagist)
composer require cesargb/laravel-magiclink:^2.27
composer require spatie/laravel-medialibrary:^11.22
composer require league/flysystem-aws-s3-v3:"^3.0" --with-all-dependencies

# npm
npm install vite-plugin-pwa idb heic2any exifr
```

### Alternatives considérées

| Standard retenu | Alternative | Raison du rejet |
|----------------|-------------|-----------------|
| `generateSW` (Workbox auto) | `injectManifest` | Overkill pour Phase 2 ; injectManifest réservé Phase 4 push notif (D-55) |
| `cesargb/laravel-magiclink` | `maize-tech/laravel-magic-login` | Moins de communauté (462 vs 28 stars), cas similaire |
| `idb` v8 | IndexedDB brut | API callback enfer, idb ajoute DBSchema typé + cursors async + tx.done Promise |
| Cookie session `web` pour API | Sanctum tokens | Same-domain PWA = cookies envoyés automatiquement ; Sanctum tokens = package inutile |
| `exifr` | `piexifjs` | exifr est 4× plus léger, supporte HEIC EXIF, API async |

---

## Package Legitimacy Audit

> Protocole exécuté. slopcheck disponible (`/opt/homebrew/bin/slopcheck`). Les packages PHP (Packagist) ont retourné [SLOP] uniquement parce que slopcheck cherche sur npm par défaut — faux positifs confirmés par vérification Packagist directe.

| Package | Registre | Age | Downloads | Source Repo | slopcheck | Disposition |
|---------|----------|-----|-----------|-------------|-----------|-------------|
| `vite-plugin-pwa` | npm | ~3 ans | >4M/mois | github.com/vite-pwa/vite-plugin-pwa | [OK] | Approuvé |
| `idb` | npm | ~8 ans | >10M/sem | github.com/jakearchibald/idb | [OK] | Approuvé |
| `heic2any` | npm | ~4 ans | ~100K/sem | github.com/alexcorvi/heic2any | [OK] | Approuvé |
| `exifr` | npm | ~5 ans | ~500K/sem | github.com/MikeKovarik/exifr | [OK] | Approuvé |
| `cesargb/laravel-magiclink` | Packagist | ~5 ans | ~70K/mois | github.com/cesargb/laravel-magiclink | [SLOP faux positif — npm only] [VERIFIED: Packagist 2026-04-20] | Approuvé |
| `spatie/laravel-medialibrary` | Packagist | ~9 ans | ~2M/mois | github.com/spatie/laravel-medialibrary | [SLOP faux positif — npm only] [VERIFIED: Packagist 2026-05-28] | Approuvé |
| `league/flysystem-aws-s3-v3` | Packagist | ~6 ans | bundled Laravel | github.com/thephpleague/flysystem | N/A (dependency) | Approuvé |

**Packages retirés pour verdict [SLOP] :** aucun
**Packages signalés [SUS] :** aucun

*Note : slopcheck vérifie uniquement npm. Tous les packages Packagist ont été vérifiés manuellement sur packagist.org avec dates et téléchargements confirmés.*

---

## Architecture Patterns

### System Architecture Diagram

```
Smartphone Pierre (offline)
  │
  ├─ Alpine form (x-data)
  │     ├─ crypto.randomUUID() → client_uuid
  │     ├─ canvas.toBlob() → JPEG compressed
  │     └─ idb.put(store: 'passages' / 'photos')
  │
  ├─ window.online / visibilitychange → flush IDB queue
  │
  └─ Workbox SW (generateSW)
        ├─ Precache: assets Vite hashés (CacheFirst)
        ├─ Precache: offline.html (additionalManifestEntries)
        ├─ Navigation: NetworkFirst 5s → offline.html
        └─ /api/*: NetworkOnly + BackgroundSyncPlugin (Android Chrome)

                   ↕ (réseau rétabli)

Laravel Cloud (EU Frankfurt)
  │
  ├─ POST /api/passages (middleware: web, auth)
  │     └─ DB::statement("INSERT INTO passages … ON CONFLICT (client_uuid)
  │          DO UPDATE SET … WHERE passages.status = 'draft'")
  │          → 200 (upserted) | 409 (already closed)
  │
  ├─ POST /api/passages/{uuid}/photos (middleware: web, auth)
  │     └─ PhotoMeta upsert ON CONFLICT (client_uuid)
  │          → Medialibrary → R2 (Cloudflare)
  │
  ├─ /admin/* (Livewire 3, guard: web)
  │     ├─ ClientIndex, ClientForm, PiscineForm (CLI-01..03)
  │     └─ PassageIndex (PASS-05)
  │
  └─ /portail/* (Livewire 3, guard: clients)
        └─ PassagePortail (PORT-01, PORT-02)

Cloudflare R2 (Paris)
  └─ photos stockées, temporaryUrl() pour affichage portail
```

### Structure de fichiers recommandée

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── PassageController.php      # POST /api/passages UPSERT
│   │   │   └── PassagePhotoController.php # POST /api/passages/{uuid}/photos
│   │   ├── Portail/
│   │   │   └── MagicLinkController.php    # GET /portail/link + confirmation
│   │   └── Admin/
│   │       └── ClientController.php       # si pas Livewire full
│   └── Middleware/
│       └── RedirectIfClientAuthenticated.php
├── Livewire/
│   ├── ClientIndex.php                    # CLI-03 recherche + pagination
│   ├── ClientForm.php                     # CLI-01 create/edit
│   ├── PiscineForm.php                    # CLI-02
│   ├── PassageIndex.php                   # PASS-05
│   └── Portail/
│       └── PassageTimeline.php            # PORT-01, PORT-02
├── Models/
│   └── (existants Phase 1 — ajouter HasMedia sur Passage)

resources/
├── css/app.css                            # tokens existants Phase 1
├── js/
│   ├── app.js                             # Alpine.data() + SW registration
│   ├── passage-form.js                    # Alpine component saisie passage
│   ├── offline-queue.js                   # IDB queue manager
│   ├── photo-pipeline.js                  # HEIC detect + Canvas compress
│   └── sw.js                              # (si injectManifest — Phase 4 only)
└── views/
    ├── admin/
    │   ├── clients/
    │   │   ├── index.blade.php
    │   │   └── show.blade.php
    │   └── passages/
    │       ├── index.blade.php
    │       └── create.blade.php           # Vue saisie offline (Alpine only)
    ├── portail/
    │   ├── confirm.blade.php              # GET /auth/confirm (statique)
    │   └── passages.blade.php             # Portail client (Livewire)
    └── offline.blade.php                  # Précachée par Workbox

routes/
├── api.php                                # POST /api/passages, /api/passages/{uuid}/photos
├── portail.php                            # /portail/*, /auth/confirm (guard: clients)
└── admin.php                              # existant + nouveaux controllers

database/migrations/
└── 2026_05_28_000011_add_client_uuid_to_photos_meta.php  # D-42
```

### Pattern 1 : IndexedDB store avec `idb` v8 `DBSchema`

**Ce qu'on fait :** Schéma fortement typé avec `DBSchema` TypeScript, deux stores séparés.

```javascript
// Source : jakearchibald/idb README + CONTEXT.md D-59
import { openDB } from 'idb';

// Définir le schéma
/** @type {import('idb').DBSchema} */
const schema = {
  passages: {
    keyPath: 'id',
    autoIncrement: true,
    value: {
      id: Number,
      client_uuid: String,
      payload_json: String,
      status: String,       // 'pending' | 'uploading' | 'synced' | 'error'
      attempts: Number,
      created_at: String,
      last_attempt_at: String,
    },
    indexes: { 'by-status': 'status', 'by-created': 'created_at' },
  },
  photos: {
    keyPath: 'id',
    autoIncrement: true,
    value: {
      id: Number,
      client_uuid: String,
      passage_client_uuid: String,
      blob: Blob,
      status: String,       // 'pending' | 'uploading' | 'synced' | 'error'
      attempts: Number,
      captured_at: String,
    },
    indexes: { 'by-passage': 'passage_client_uuid', 'by-status': 'status' },
  },
};

async function openOfflineDB() {
  return openDB('dloazur-offline-v1', 1, {
    upgrade(db) {
      const passStore = db.createObjectStore('passages', {
        keyPath: 'id', autoIncrement: true
      });
      passStore.createIndex('by-status', 'status');
      passStore.createIndex('by-created', 'created_at');

      const photoStore = db.createObjectStore('photos', {
        keyPath: 'id', autoIncrement: true
      });
      photoStore.createIndex('by-passage', 'passage_client_uuid');
      photoStore.createIndex('by-status', 'status');
    },
  });
}
```

### Pattern 2 : Composant Alpine `passage-form`

**Ce qu'on fait :** `Alpine.data('passageForm', () => ({ ... }))` registré dans `app.js`, initialisé dans la vue Blade via `x-data="passageForm"`.

```javascript
// Source : CONTEXT.md D-39, D-46, D-47 + alpine.dev/essentials/state
Alpine.data('passageForm', () => ({
  clientUuid: '',
  ph: null, chloreLibre: null, tac: null, sel: null,
  actions: [],
  notes: '',
  notesPrivees: '',
  photos: [],        // { clientUuid, blob, previewUrl, status }
  online: navigator.onLine,
  warnings: [],      // messages toast soft validation

  async init() {
    this.clientUuid = crypto.randomUUID();
    // Persister immédiatement en IDB (D-39)
    await this._saveToIDB();

    // Écouter les events réseau
    window.addEventListener('online',  () => { this.online = true;  this._flushQueue(); });
    window.addEventListener('offline', () => { this.online = false; });
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'visible' && navigator.onLine) {
        this._flushQueue();
      }
    });

    // storage.persist() (D-57)
    if (navigator.storage?.persisted) {
      const persisted = await navigator.storage.persisted();
      if (!persisted) navigator.storage.persist?.();
    }
  },

  async submit() {
    await this._saveToIDB('pending');
    if (navigator.onLine) this._flushQueue();
  },

  async _flushQueue() { /* voir Pattern 3 */ },
  async _saveToIDB(status = 'draft') { /* put en IDB */ },
}));
```

### Pattern 3 : Queue flush avec backoff exponentiel

```javascript
// Source : CONTEXT.md D-46 + letsbuildsolutions.com offline-first guide
async _flushQueue() {
  const db = await openOfflineDB();
  const pending = await db.getAllFromIndex('passages', 'by-status', 'pending');

  for (const item of pending) {
    let delay = 2000;
    for (let attempt = 0; attempt < 3; attempt++) {
      try {
        await db.put('passages', { ...item, status: 'uploading' });
        const res = await fetch('/api/passages', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
          body: item.payload_json,
        });

        if (res.status === 409) {
          // Passage déjà clos (D-40)
          await db.put('passages', { ...item, status: 'synced' });
          this._showConflictToast();
          break;
        }
        if (res.ok) {
          await db.put('passages', { ...item, status: 'synced' });
          await this._uploadPhotos(item.client_uuid);
          break;
        }
        throw new Error('Server error ' + res.status);

      } catch (e) {
        if (attempt < 2) {
          await new Promise(r => setTimeout(r, delay));
          delay *= 4;  // 2s → 8s → 32s (approx D-46 "2s → 8s → 30s")
        } else {
          await db.put('passages', { ...item, status: 'error', attempts: (item.attempts || 0) + 1 });
        }
      }
    }
  }
  this._refreshBadge();
},
```

### Pattern 4 : Pipeline photo HEIC → JPEG

```javascript
// Source : CONTEXT.md D-43, D-44 + github.com/alexcorvi/heic2any + github.com/MikeKovarik/exifr
async processPhoto(file) {
  let processedBlob = file;

  // Détection HEIC par magic bytes (iOS Safari ment sur le MIME type)
  const buf = await file.slice(0, 12).arrayBuffer();
  const bytes = new Uint8Array(buf);
  const isHeic = bytes[4] === 0x66 && bytes[5] === 0x74 && bytes[6] === 0x79 && bytes[7] === 0x70;

  if (isHeic) {
    const heic2any = (await import('heic2any')).default;
    processedBlob = await heic2any({ blob: file, toType: 'image/jpeg', quality: 0.85 });
  }

  // Correction EXIF orientation AVANT drawImage
  const exifr = (await import('exifr')).default;
  const orientation = await exifr.orientation(processedBlob).catch(() => 1);

  // Compression Canvas max 2048px (D-44)
  const imageBitmap = await createImageBitmap(processedBlob);
  const canvas = document.createElement('canvas');
  const maxEdge = 2048;
  const scale = Math.min(1, maxEdge / Math.max(imageBitmap.width, imageBitmap.height));
  canvas.width  = Math.round(imageBitmap.width  * scale);
  canvas.height = Math.round(imageBitmap.height * scale);

  const ctx = canvas.getContext('2d');
  // Appliquer la rotation EXIF si nécessaire
  if (orientation && orientation > 1) {
    // rotation matrix selon orientation 1-8
    ctx.save();
    // ... (logique rotation standard EXIF)
    ctx.restore();
  }
  ctx.drawImage(imageBitmap, 0, 0, canvas.width, canvas.height);

  return new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.80));
},
```

### Pattern 5 : UPSERT Postgres conditionnel (Laravel)

```php
// Source : postgresql.org/docs/current/sql-insert + CONTEXT.md D-38
// routes/api.php → App\Http\Controllers\Api\PassageController

public function store(Request $request): JsonResponse
{
    $data = $request->validate([
        'client_uuid'  => ['required', 'uuid'],
        'piscine_id'   => ['nullable', 'integer'],
        'client_id'    => ['nullable', 'integer'],
        'visited_at'   => ['nullable', 'date'],
        'ph_avant'     => ['nullable', 'numeric'],
        'chlore_libre' => ['nullable', 'numeric'],
        'tac'          => ['nullable', 'numeric'],
        'sel_g_l'      => ['nullable', 'numeric'],
        'actions'      => ['nullable', 'array'],
        'notes'        => ['nullable', 'string'],
    ]);

    $affected = DB::affectingStatement("
        INSERT INTO passages (client_uuid, piscine_id, client_id, visited_at, status,
                              ph_avant, chlore_libre, tac, sel_g_l, actions, notes,
                              synced_at, created_at, updated_at)
        VALUES (:client_uuid, :piscine_id, :client_id, :visited_at, 'draft',
                :ph_avant, :chlore_libre, :tac, :sel_g_l, :actions, :notes,
                NOW(), NOW(), NOW())
        ON CONFLICT (client_uuid) DO UPDATE SET
            ph_avant     = EXCLUDED.ph_avant,
            chlore_libre = EXCLUDED.chlore_libre,
            tac          = EXCLUDED.tac,
            sel_g_l      = EXCLUDED.sel_g_l,
            actions      = EXCLUDED.actions,
            notes        = EXCLUDED.notes,
            synced_at    = NOW(),
            updated_at   = NOW()
        WHERE passages.status = 'draft'
    ", [...$data, 'actions' => json_encode($data['actions'] ?? [])]);

    if ($affected === 0) {
        return response()->json([
            'error'        => 'already_closed',
            'server_state' => Passage::where('client_uuid', $data['client_uuid'])->first(),
        ], 409);
    }

    return response()->json(['ok' => true], 200);
}
```

### Pattern 6 : Magic link — deux guards Laravel

```php
// config/auth.php (CONTEXT.md D-54 + CLAUDE.md §Auth)
'guards' => [
    'web'     => ['driver' => 'session', 'provider' => 'users'],
    'clients' => ['driver' => 'session', 'provider' => 'clients'],
],
'providers' => [
    'users'   => ['driver' => 'eloquent', 'model' => App\Models\User::class],
    'clients' => ['driver' => 'eloquent', 'model' => App\Models\Client::class],
],

// routes/portail.php
// GET /auth/magic — formulaire demande de lien
// POST /auth/magic — crée et envoie le lien
// GET /auth/confirm?token=... — page statique (D-50, protection SafeLinks)
// POST /auth/confirm — POST consomme le token
// /portail/* — protégé par guard 'clients'

Route::middleware('guest:clients')->group(function () {
    Route::view('/auth/magic', 'portail.magic-link-request');
    Route::post('/auth/magic', [MagicLinkController::class, 'send'])
         ->middleware('throttle:magic-link');
    Route::get('/auth/confirm', [MagicLinkController::class, 'confirmView']);
    Route::post('/auth/confirm', [MagicLinkController::class, 'confirm']);
});

Route::middleware('auth:clients')->prefix('portail')->name('portail.')->group(function () {
    Route::get('/passages', PassageTimeline::class)->name('passages');
    Route::post('/logout', [MagicLinkController::class, 'logout'])->name('logout');
});

// Création magic link (controller)
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;

$action = new LoginAction($client, guard: 'clients', remember: false);
$magicLink = MagicLink::create($action, lifetime: 2880, numMaxVisits: 3);
// $magicLink->url = "https://dloazur.com/auth/confirm?token=..."
```

### Pattern 7 : vite.config.js avec vite-plugin-pwa

```javascript
// Source : vite-pwa-org.netlify.app/frameworks/laravel + CONTEXT.md D-55..D-60
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';
import { bunny } from 'laravel-vite-plugin/fonts';

export default defineConfig({
  build: {
    outDir: 'public/build',
  },
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
      fonts: [
        bunny('Fredoka', { weights: [600, 700] }),
        bunny('Inter', { weights: [400, 600] }),
      ],
    }),
    tailwindcss(),
    VitePWA({
      registerType: 'prompt',           // D-56: pas d'autoUpdate (protège saisie en cours)
      buildBase: '/build/',              // D-60: Laravel met son build ici
      includeAssets: ['icons/*.png', 'offline.html'],
      manifest: {
        name: 'Dlo Azur · Métier',
        short_name: 'Dlo Azur',
        description: 'Saisie de passages offline-first — Dlo Azur Piscines',
        theme_color: '#0080ff',
        background_color: '#fdfcf9',
        display: 'standalone',
        orientation: 'portrait',
        start_url: '/admin/passages/create',
        icons: [
          { src: '/icons/pwa-192x192.png', sizes: '192x192', type: 'image/png' },
          { src: '/icons/pwa-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'any maskable' },
        ],
      },
      workbox: {
        // Précacher offline.html (additionalManifestEntries)
        additionalManifestEntries: [
          { url: '/offline', revision: null },
        ],
        navigateFallback: '/offline',
        navigateFallbackDenylist: [/^\/admin\//, /^\/portail\//],
        runtimeCaching: [
          // Assets Vite hashés — CacheFirst 1 an
          {
            urlPattern: /\/build\/assets\//,
            handler: 'CacheFirst',
            options: {
              cacheName: 'vite-assets',
              expiration: { maxAgeSeconds: 365 * 24 * 60 * 60 },
            },
          },
          // API passages — NetworkOnly + BackgroundSync (Android only — no-op iOS Safari)
          {
            urlPattern: /\/api\/passages/,
            handler: 'NetworkOnly',
            method: 'POST',
            options: {
              backgroundSync: {
                name: 'passages-queue',
                options: { maxRetentionTime: 24 * 60 },
              },
            },
          },
        ],
      },
    }),
  ],
});
```

### Pattern 8 : Livewire 3 CRUD avec recherche et pagination

```php
// Source : laravel-livewire.com/docs + medium.com/@harrisrafto Livewire v3 pagination
// app/Livewire/ClientIndex.php
class ClientIndex extends Component
{
    use WithPagination;

    public string $search = '';

    // Réinitialiser la page quand la recherche change (D-61)
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $clients = Client::query()
            ->when($this->search, fn ($q) =>
                $q->where(DB::raw("name || ' ' || COALESCE(email,'') || ' ' || COALESCE(phone,'') || ' ' || COALESCE(address,'')"),
                    'ILIKE', '%' . $this->search . '%')
            )
            ->orderBy('updated_at', 'desc')
            ->paginate(25);

        return view('livewire.client-index', compact('clients'));
    }
}
```

```html
<!-- Vue Blade (wire:model.live pour debounce) -->
<input wire:model.live.debounce.300ms="search" type="search" placeholder="Rechercher..." />
```

### Anti-Patterns à éviter

- **Livewire dans la saisie passage** : Livewire envoie des requêtes serveur pour chaque interaction — en offline, toute l'UI se gèle. Alpine seul pour le formulaire passage.
- **WebP pour les photos** : Safari iOS 17 a des régressions WebP (D-44). Rester en JPEG.
- **`skipWaiting()` auto dans le SW** : couperait la saisie en cours si Pierre est au milieu d'un passage et qu'une mise à jour est disponible (D-56 `registerType: 'prompt'`).
- **Un seul UPSERT SQL brut sans transaction** : L'ORM Eloquent `updateOrCreate()` fait deux queries non atomiques. Utiliser `DB::affectingStatement()` avec le SQL natif `ON CONFLICT … WHERE status = 'draft'`.
- **Détecter HEIC par extension `.heic`** : iOS Safari renomme parfois les fichiers. Toujours lire les magic bytes (offset 4-11 du ftyp box).
- **Appeler `navigator.storage.persist()` à chaque render** : Vérifier `navigator.storage.persisted()` d'abord (D-57).
- **La page `/auth/confirm` qui consomme le token sur GET** : SafeLinks Microsoft 365 fait un GET pré-scan avant la redirection utilisateur — le token serait consommé sans que le client se connecte (D-50).
- **Stocker le CSRF token dans IDB pour les requêtes SW** : Le SW ne peut pas lire le DOM — passer le CSRF token dans le payload JSON ou via meta tag lu par Alpine avant mise en queue.
- **`DB::raw("field ILIKE '%search%'")` sans binding** : Risque d'injection SQL. Toujours utiliser les bindings Eloquent.

---

## Don't Hand-Roll

| Problème | Ne pas construire | Utiliser | Raison |
|----------|------------------|----------|--------|
| IndexedDB async API | Wrapper custom | `idb` v8 | IDB raw = callbacks imbriqués + 200 lignes de boilerplate pour les transactions |
| HEIC → JPEG conversion | Canvas brut depuis HEIC | `heic2any` | Le format HEIC n'est pas décodable par `createImageBitmap` en Safari |
| Correction rotation EXIF | Re-lire les bytes à la main | `exifr` | 8 cas de rotation + flippage, ~50 lignes de matrix math |
| Background Sync SW | `fetch` dans SW custom | `BackgroundSyncPlugin` Workbox (via generateSW) | Gère la queue IDB interne, le retry automatique, le SW re-start |
| Magic link signing | Crypto custom HMAC | `cesargb/laravel-magiclink` | HMAC-signed JSON, rotation des secrets, guard isolation |
| Upload media + R2 | `Storage::put()` direct | `spatie/laravel-medialibrary` | Collections nommées, conversions images, URL temporaires signées, association Eloquent |
| Pagination Livewire | Pagination manuelle | `WithPagination` trait | Gère l'état de page dans l'URL, reset sur recherche, links() Blade |

---

## Runtime State Inventory

> Phase 2 n'est pas un renommage/refactoring — section normalement omise pour les phases greenfield. Cependant, les décisions Phase 1 ont créé du state runtime pertinent.

| Catégorie | Items trouvés | Action requise |
|-----------|--------------|----------------|
| Stored data | Schéma `photos_meta` sans `client_uuid` (Phase 1) | Migration `add_client_uuid_to_photos_meta` à créer |
| Live service config | Disk `r2` configuré dans `config/filesystems.php` Phase 1 | Réutiliser tel quel — `useDisk('r2')` dans registerMediaCollections |
| OS-registered state | Aucun service worker enregistré (Phase 1 n'a pas installé vite-plugin-pwa) | Aucune désinstallation requise |
| Secrets/env vars | `AWS_*` vars pour R2 dans `.env` Phase 1 | Aucun changement, même disk |
| Build artifacts | `public/build/` généré sans SW en Phase 1 | Le premier build vite-plugin-pwa générера `sw.js` dans `public/build/` |

**Nothing found in category :** `pierre-device-platform` memory existe mais ne constitue pas un state applicatif à migrer.

---

## Common Pitfalls

### Pitfall 1 : Background Sync API silencieux sur iOS Safari

**Ce qui se passe :** Le `BackgroundSyncPlugin` Workbox ne lève aucune erreur sur iOS Safari — il est simplement no-op. Les passages en queue ne se synchronisent jamais si on ne teste qu'Android Chrome.

**Pourquoi :** Background Sync API n'est pas supportée sur iOS (confirmé magicbell.com, 2026-03-update). Sur iOS, le SW ne peut pas être réveillé en tâche de fond.

**Comment éviter :** Double fallback obligatoire (D-47) :
```javascript
window.addEventListener('online', () => this._flushQueue());
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible' && navigator.onLine) {
    this._flushQueue();
  }
});
```
Ce fallback fonctionne sur iOS car il s'exécute quand l'utilisateur revient dans l'app.

**Signes d'alerte :** Testeur sur Android = tout marche. Testeur sur iPhone = passages restent "en attente" indéfiniment.

---

### Pitfall 2 : Scope Service Worker bloqué par `public/build/`

**Ce qui se passe :** Sans `Service-Worker-Allowed: /` header, le SW ne peut contrôler que `/build/**` — le formulaire passage sur `/admin/passages/create` n'est pas dans sa portée.

**Pourquoi :** Les navigateurs restreignent le scope SW au répertoire où `sw.js` est servi. Si `sw.js` est dans `/build/sw.js`, son scope par défaut est `/build/`.

**Comment éviter :** En Phase 2, ajouter ce header via un middleware Laravel ou la config Laravel Cloud :
```php
// app/Http/Middleware/ServiceWorkerHeaders.php
if ($request->is('build/sw.js')) {
    return $next($request)->withHeaders([
        'Service-Worker-Allowed' => '/',
        'Cache-Control' => 'no-cache',
    ]);
}
```
Et dans `vite.config.js` : `buildBase: '/build/'` (D-60).

**Signes d'alerte :** DevTools > Application > Service Workers montre un scope `/build/` au lieu de `/`.

---

### Pitfall 3 : ITP Safari iOS — éviction IDB sans Home Screen

**Ce qui se passe :** Sur iOS Safari (pas standalone), les données IndexedDB d'une origin non visitée depuis 7 jours sont effacées par ITP (Intelligent Tracking Prevention).

**Pourquoi :** Politique WebKit storage — les origins en "best-effort mode" (non-persistent) peuvent être évincées. [VERIFIED: webkit.org/blog/14403]

**Comment éviter :** (1) `navigator.storage.persist()` au boot Alpine (D-57) — sur une PWA installée en Home Screen, WebKit accorde silencieusement. (2) Instruire Pierre d'installer la PWA. (3) Pour les clients du portail : pas de IDB côté portail, donc pas d'impact.

**Quota différence :** Installé Home Screen = 60% disk / Non installé = 15% disk. [VERIFIED: webkit.org/blog/14403]

---

### Pitfall 4 : SafeLinks Microsoft 365 consomme le token magic link sur GET

**Ce qui se passe :** M365 Business (cible conciergeries B2B de la vitrine) pré-scanne les liens email via un proxy Microsoft qui fait un GET du lien AVANT de rediriger l'utilisateur. Si le GET consomme le token, le client arrive sur une page "lien expiré".

**Pourquoi :** Microsoft Defender for Office 365 SafeLinks — proxy de sécurité qui re-écrit et pré-visite les URLs dans les emails.

**Comment éviter :** Page intermédiaire obligatoire (D-50) : GET `/auth/confirm?token=…` retourne une page HTML statique avec un bouton `<form method="POST">`. Seul le POST consomme le token. [CITED: learn.microsoft.com/defender-office-365/safe-links]

**Signes d'alerte :** Clients B2B se plaignent que le lien est "déjà utilisé" dès l'ouverture de l'email.

---

### Pitfall 5 : CSRF token manquant dans les fetch Alpine hors-ligne

**Ce qui se passe :** Les requêtes POST vers `/api/passages` échouent avec 419 (CSRF token mismatch) ou passent en mode no-cors sans le header `X-CSRF-TOKEN`.

**Pourquoi :** Laravel vérifie le CSRF token sur toutes les routes `web` middleware par défaut.

**Comment éviter :** Deux options :
1. Routes `/api/passages` dans `bootstrap/app.php` exemptées de CSRF via `withMiddleware` `validateCsrfTokens()` avec exclusion `/api/*`, ET protection par `auth` middleware (session cookie suffit).
2. Inclure le token dans le payload JSON (`'_token' => csrf_token()`) — plus simple mais moins propre.

Recommandé : exclure `/api/*` du CSRF token check (comme le fait Laravel par défaut dans `routes/api.php`) et s'appuyer sur la session cookie pour l'auth.

---

### Pitfall 6 : `Passage` n'a pas `HasMedia` — medialibrary ne sait pas gérer les photos

**Ce qui se passe :** `spatie/laravel-medialibrary` exige que le modèle implémente `HasMedia` et utilise le trait `InteractsWithMedia`. Sans ça, `$passage->addMediaFromRequest()` n'existe pas.

**Comment éviter :** Ajouter au modèle Passage :
```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Passage extends Model implements HasMedia {
    use InteractsWithMedia;

    public function registerMediaCollections(): void {
        $this->addMediaCollection('photos')->useDisk('r2');
    }
}
```

**Note :** La table `photos_meta` existante est DISTINCTE de la table `media` de medialibrary. Les deux coexistent — `photos_meta` stocke les métadonnées custom (client_uuid, disk, captured_at), `media` gère les conversions et URLs. Decision : soit utiliser medialibrary pour l'upload R2 uniquement, soit piloter directement `Storage::disk('r2')` et remplir `photos_meta` manuellement. L'approche manuelle évite la double-table.

---

### Pitfall 7 : `DB::affectingStatement` retourne 0 même en cas d'upsert "DO NOTHING"

**Ce qui se passe :** PostgreSQL compte 1 row affected pour un INSERT et 1 pour un UPDATE, mais 0 quand la condition `WHERE status = 'draft'` n'est pas satisfaite. C'est le comportement attendu pour détecter un passage déjà clos.

**Vérification :** `DB::affectingStatement()` retourne le nombre de rows affectées. Si 0 → répondre 409. Si 1 → OK. Ne pas utiliser `DB::statement()` (retourne bool, pas count).

---

### Pitfall 8 : Photo HEIC — `file.type` ment sur iOS Safari

**Ce qui se passe :** iOS Safari rapporte `file.type = 'image/heic'` ou `'image/heif'` ou même `'image/jpeg'` pour la même photo HEIC selon la version iOS et le contexte (picker caméra vs bibliothèque).

**Comment éviter :** Toujours lire les magic bytes (4 premiers octets du ftyp box à offset 4) :
```javascript
const buf = await file.slice(0, 12).arrayBuffer();
const bytes = new Uint8Array(buf);
// ftyp box : bytes 4-7 = 'ftyp', bytes 8-11 = major brand
const brand = String.fromCharCode(bytes[8], bytes[9], bytes[10], bytes[11]);
const isHeic = ['heic', 'heis', 'hevc', 'mif1', 'msf1'].includes(brand);
```

---

### Pitfall 9 : `vite-plugin-pwa` uniquement en production build

**Ce qui se passe :** Par défaut, vite-plugin-pwa ne génère pas de SW en mode `dev` (yarn dev / npm run dev). Les tests offline échouent en local car il n'y a pas de SW enregistré.

**Comment éviter :** Pour tester le SW localement : `npm run build && php artisan serve`. Documenter dans CLAUDE.md ou README. En CI, c'est automatique (build avant tests).

---

## Code Examples

### Vérification CSRF exempt pour /api/passages

```php
// Source : laravel.com/docs/13.x/csrf + bootstrap/app.php existant
->withMiddleware(function (Middleware $middleware): void {
    $middleware->validateCsrfTokens(except: [
        '/api/*',
    ]);
})
```

### Rate limiter magic link

```php
// Source : CONTEXT.md D-52 + OWASP Forgot Password Cheatsheet
RateLimiter::for('magic-link', function (Request $request) {
    return [
        Limit::perHour(5)->by($request->ip()),
        Limit::perDay(3)->by($request->input('email', '')),
    ];
});
```

### Migration photos_meta.client_uuid (D-42)

```php
// database/migrations/2026_05_28_000011_add_client_uuid_to_photos_meta.php
return new class extends Migration {
    public function up(): void {
        Schema::table('photos_meta', function (Blueprint $table) {
            $table->uuid('client_uuid')->nullable()->unique()->after('passage_id');
        });
    }
    public function down(): void {
        Schema::table('photos_meta', function (Blueprint $table) {
            $table->dropColumn('client_uuid');
        });
    }
};
```

### Medialibrary — collection photos sur R2

```php
// Source : spatie.be/docs/laravel-medialibrary/v11
// Alternative simple sans medialibrary (recommandée — évite double-table) :
public function storePhoto(Request $request, string $passageUuid): JsonResponse
{
    $request->validate([
        'photo'      => ['required', 'file', 'mimes:jpeg,jpg', 'max:10240'],
        'client_uuid' => ['required', 'uuid'],
        'captured_at' => ['nullable', 'date'],
    ]);

    $passage = Passage::where('client_uuid', $passageUuid)->firstOrFail();
    $file    = $request->file('photo');
    $path    = Storage::disk('r2')->putFile("passages/{$passageUuid}/photos", $file);

    // Upsert idempotent sur photos_meta (D-42)
    PhotoMeta::updateOrCreate(
        ['client_uuid' => $request->input('client_uuid')],
        [
            'passage_id'  => $passage->id,
            'disk'        => 'r2',
            'path'        => $path,
            'mime_type'   => 'image/jpeg',
            'size_bytes'  => $file->getSize(),
            'captured_at' => $request->input('captured_at'),
        ]
    );

    return response()->json(['ok' => true]);
}
```

---

## State of the Art

| Ancienne approche | Approche actuelle (2026) | Impact |
|------------------|--------------------------|--------|
| Background Sync seul pour offline | BG Sync (Android) + `online` event fallback (iOS) | Couvre ~100% des cibles mobiles |
| `injectManifest` pour tout custom SW | `generateSW` pour le cas standard, `injectManifest` réservé push notif | Moins de boilerplate, plus maintenable |
| `localStorage` pour queue offline | IndexedDB (`idb`) pour les blobs et objets larges | localStorage = 5MB max, pas de blobs, synchrone |
| `type="file"` sans `capture` | `capture="environment"` pour forcer la caméra arrière | Évite que l'utilisateur choisisse une vieille photo de la bibliothèque |
| Token magic link single-use GET | GET statique + POST consomme (pattern OWASP + SafeLinks M365) | Compatible avec les scanners de sécurité email corporate |
| PHP 8.2 Laravel 11 | PHP 8.4 Laravel 13 (floor: composer.lock Symfony v8) | PHP 8.5.6 installé localement (compatible) |

**Déprécié / obsolète :**
- `rahaug/laravel-magic-link` : dernière release 2021, abandonné — `cesargb/laravel-magiclink` est le successeur de facto
- `edujugon/laradoo` : Laravel 5-8 uniquement — exclu (Phase 3 Odoo)
- WebP pour photos mobile : régressions Safari iOS 17 — rester JPEG (D-44)

---

## Assumptions Log

| # | Claim | Section | Risque si faux |
|---|-------|---------|----------------|
| A1 | `league/flysystem-aws-s3-v3` est déjà installé dans Phase 1 (R2 configuré) | Standard Stack | Si manquant → `composer require` bloque le déploiement Phase 2. Vérifier `composer show league/flysystem-aws-s3-v3` |
| A2 | Laravel Cloud supporte le header `Service-Worker-Allowed` via middleware PHP (pas de config infra custom requise) | Pitfall 2 | Si Laravel Cloud strip les custom headers → SW scope cassé, tout offline fail |
| A3 | `upload_max_filesize = 10MB` est configurable dans Laravel Cloud (D-48) | Standard Stack | Si plafonné à 2MB → les HEIC bruts non convertis échouent |
| A4 | `heic2any` WASM fonctionne dans un contexte Service Worker (si on en avait besoin) | Pitfall 1 | Pas de risque Phase 2 — heic2any est appelé dans Alpine (page context), pas dans le SW |
| A5 | `Client` model implémente `Authenticatable` (requis par guard `clients`) | Pattern 6 | Sans `Authenticatable`, le guard session ne peut pas logguer l'utilisateur |
| A6 | `exifr` v7.1.3 (dernière release août 2021) est stable pour JPEG + HEIC EXIF | Standard Stack | Dernière release ~5 ans — risque de bugs non patchés sur nouveaux formats iOS. Très faible car EXIF est un format stable |

---

## Open Questions

1. **`league/flysystem-aws-s3-v3` est-il déjà installé ?**
   - Ce qu'on sait : `spatie/laravel-medialibrary` le requiert. R2 était configuré en Phase 1.
   - Ce qui est flou : La config R2 a été faite mais le package peut ne pas être dans composer.lock si medialibrary n'était pas installé en Phase 1.
   - Recommandation : En Wave 0, vérifier `composer show league/flysystem-aws-s3-v3` et installer si absent.

2. **Photos : medialibrary ou `Storage::disk('r2')` directement ?**
   - Ce qu'on sait : `photos_meta` table existante stocke déjà les métadonnées. medialibrary crée sa propre table `media`.
   - Ce qui est flou : Double-table est-elle acceptable ? medialibrary apporte les URL temporaires signées.
   - Recommandation : Utiliser `Storage::disk('r2')` directement + remplir `photos_meta` manuellement. Plus simple, cohérent avec le schéma existant, pas de double-table. Les URL temporaires R2 sont accessibles via `Storage::disk('r2')->temporaryUrl($path, now()->addMinutes(60))`.

3. **`Client` model a besoin d'implémenter `Authenticatable`**
   - Ce qu'on sait : Le guard `clients` session driver requiert que le model implémente `Illuminate\Contracts\Auth\Authenticatable`.
   - Ce qui est flou : Le `Client.php` Phase 1 étend `Model`, pas `Authenticatable`. Nécessite modification.
   - Recommandation : Changer en `class Client extends Authenticatable` + ajouter `use Notifiable` pour les emails magic link.

---

## Environment Availability

| Dependency | Required By | Available | Version | Fallback |
|------------|------------|-----------|---------|----------|
| PHP | Backend | ✓ | 8.5.6 (compatible ≥ 8.3) | — |
| PostgreSQL | Database | ✓ | 17.10 (Homebrew) | — |
| Node.js | Vite build | ✓ | 26.0.0 | — |
| Composer | PHP deps | ✓ | 2.9.8 | — |
| `vite-plugin-pwa` | PWA/SW | ✗ (pas encore installé) | — | npm install — aucun fallback |
| `idb` | IndexedDB | ✗ (pas encore installé) | — | npm install — aucun fallback |
| `heic2any` | HEIC photos | ✗ (pas encore installé) | — | npm install — aucun fallback |
| `exifr` | EXIF rotation | ✗ (pas encore installé) | — | npm install — aucun fallback |
| `cesargb/laravel-magiclink` | Auth clients | ✗ (pas encore installé) | — | composer require — aucun fallback |
| `spatie/laravel-medialibrary` | Photos R2 | ✗ (pas encore installé) | — | composer require — aucun fallback OU `Storage::disk('r2')` direct (recommandé) |
| Cloudflare R2 bucket | Photos | ✓ (D-03 Phase 1 configué) | — | — |
| Brevo SMTP | Magic link emails | ✓ (D-15 Phase 1 configuré) | — | — |

**Dépendances manquantes bloquantes :**
- `vite-plugin-pwa`, `idb`, `heic2any`, `exifr` → `npm install` en Wave 0
- `cesargb/laravel-magiclink` → `composer require` en Wave 0

**Vérification Wave 0 recommandée :**
```bash
composer show league/flysystem-aws-s3-v3  # vérifier si déjà présent
php artisan config:show filesystems        # vérifier disk 'r2' configuré
```

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | Pest 4.7 + pestphp/pest-plugin-laravel 4.1 |
| Config file | `phpunit.xml` (existant Phase 1) |
| Quick run command | `./vendor/bin/pest tests/Feature --ci -p` |
| Full suite command | `./vendor/bin/pest --ci` |

### Phase Requirements → Test Map

| Req ID | Behavior | Type | Commande automatisée | Fichier existe ? |
|--------|----------|------|---------------------|-----------------|
| AUTH-02 | Client reçoit magic link, clique, est connecté | Feature | `pest tests/Feature/MagicLinkTest.php -x` | ❌ Wave 0 |
| AUTH-03 | Session client persiste 30j, logout fonctionne | Feature | `pest tests/Feature/ClientSessionTest.php -x` | ❌ Wave 0 |
| AUTH-04 | GET /auth/confirm ne consomme pas le token, POST le consomme | Feature | `pest tests/Feature/MagicLinkConfirmTest.php -x` | ❌ Wave 0 |
| CLI-01 | Create + update fiche client via Livewire | Feature | `pest tests/Feature/ClientCrudTest.php -x` | ❌ Wave 0 |
| CLI-02 | Create + update piscine d'un client | Feature | `pest tests/Feature/PiscineCrudTest.php -x` | ❌ Wave 0 |
| CLI-03 | Recherche ILIKE retourne les bons résultats | Feature | `pest tests/Feature/ClientSearchTest.php -x` | ❌ Wave 0 |
| PASS-01 | POST /api/passages crée un passage, retourne 200 | Feature | `pest tests/Feature/PassageApiTest.php::create -x` | ❌ Wave 0 |
| PASS-03 | POST /api/passages en double retourne 200 (upsert, pas 500) | Feature | `pest tests/Feature/PassageApiTest.php::upsert -x` | ❌ Wave 0 |
| PASS-03 | POST /api/passages sur passage clos retourne 409 | Feature | `pest tests/Feature/PassageApiTest.php::conflict -x` | ❌ Wave 0 |
| PASS-04 | POST /api/passages/{uuid}/photos idempotent (client_uuid unique) | Feature | `pest tests/Feature/PhotoUploadTest.php -x` | ❌ Wave 0 |
| PASS-05 | Liste passages Livewire paginée, filtre client_id | Feature | `pest tests/Feature/PassageIndexTest.php -x` | ❌ Wave 0 |
| PASS-06 | Badge IDB lit correctement le count pending (JS) | Manual | Vérification terrain — Alpine hors portée Pest | Manuel |
| PORT-01 | Portail accessible après magic link confirm | Feature | `pest tests/Feature/PortailAccessTest.php -x` | ❌ Wave 0 |
| PORT-02 | Portail non accessible sans auth clients | Feature | (inclus dans PortailAccessTest) | ❌ Wave 0 |
| D-52 | Rate limit magic link (IP + email) | Feature | `pest tests/Feature/MagicLinkRateLimitTest.php -x` | ❌ Wave 0 |

### Sampling Rate

- **Par commit de tâche :** `./vendor/bin/pest tests/Feature --ci -p` (~10s)
- **Par merge de wave :** `./vendor/bin/pest --ci` (suite complète)
- **Phase gate :** Suite complète verte avant `/gsd:verify-work`

### Wave 0 Gaps (tests à créer avant implémentation)

- [ ] `tests/Feature/MagicLinkTest.php` — couvre AUTH-02, AUTH-04, D-52
- [ ] `tests/Feature/ClientCrudTest.php` — couvre CLI-01, CLI-02, CLI-03
- [ ] `tests/Feature/PassageApiTest.php` — couvre PASS-01, PASS-03 (upsert + 409)
- [ ] `tests/Feature/PhotoUploadTest.php` — couvre PASS-04 (idempotence client_uuid)
- [ ] `tests/Feature/PassageIndexTest.php` — couvre PASS-05
- [ ] `tests/Feature/PortailAccessTest.php` — couvre PORT-01, PORT-02, AUTH-03
- [ ] `tests/Feature/MagicLinkRateLimitTest.php` — couvre D-52

---

## Security Domain

### Applicable ASVS Categories

| ASVS Category | Applies | Standard Control |
|---------------|---------|-----------------|
| V2 Authentication | Oui | Fortify (pro) + `cesargb/laravel-magiclink` (clients) — guards isolés |
| V3 Session Management | Oui | Session glissante 30j (clients), cookie `secure + httpOnly + SameSite=Lax`, `Auth::guard('clients')->logout()` |
| V4 Access Control | Oui | Middleware `auth:clients` sur toutes les routes `/portail/*`, `auth` sur `/admin/*` |
| V5 Input Validation | Oui | `$request->validate()` sur tous les endpoints API, validation mesures soft côté Alpine |
| V6 Cryptographie | Oui — magic links | `cesargb/laravel-magiclink` utilise HMAC-signed JSON (migration depuis PHP serialize faite en v2.x) |

### Threat Patterns pour cette stack

| Pattern | STRIDE | Mitigation standard |
|---------|--------|---------------------|
| CSRF sur POST /api/passages | Tampering | Exclure `/api/*` de `validateCsrfTokens`, auth par session cookie (même origine) |
| Énumération emails magic link | Information Disclosure | Message générique `"Si cet email correspond…"` + timing constant (D-52) |
| Token magic link consommé par SafeLinks | Elevation of Privilege | Page intermédiaire GET statique + POST consomme (D-50) |
| Upload de fichiers malveillants | Tampering | `mimes:jpeg` validation (rejette HEIC côté serveur si pipeline client échoue), `max:10240` |
| Injection SQL ILIKE | Tampering | Bindings Eloquent — NE PAS utiliser `DB::raw()` sans binding |
| Accès portail client entre clients | Elevation of Privilege | Filtrer `where('client_id', Auth::guard('clients')->id())` sur toutes les queries portail |
| IDB data exfiltration (XSS) | Information Disclosure | CSP strict sur les pages admin (hors scope Phase 2 mais à documenter) |
| IndexedDB persiste après logout pro | Information Disclosure | Sur logout, appeler `deleteDB('dloazur-offline-v1')` ou purger les passages synced |

**Règle critique portail :** Toute requête Eloquent dans les composants Livewire portail DOIT filtrer sur `client_id = Auth::guard('clients')->id()`. Ne jamais exposer des passages d'un autre client.

---

## Sources

### Primary (HIGH confidence)

- CONTEXT.md Phase 2 — toutes les décisions D-36..D-65, verrouillées
- CLAUDE.md projet — stack lock, packages recommandés, packages interdits
- `package.json` + `composer.json` existants — versions vérifiées en live
- npm registry — `vite-plugin-pwa@1.3.0` (2026-05-05), `idb@8.0.3`, `heic2any@0.0.4`, `exifr@7.1.3`
- Packagist — `cesargb/laravel-magiclink@2.27.1` (2026-04-20), `spatie/laravel-medialibrary@11.23.0` (2026-05-28)
- webkit.org/blog/14403 — storage.persist() iOS Safari, quotas installed vs non-installed
- spatie.be/docs/laravel-medialibrary/v11 — `useDisk()`, collections
- laravel.com/docs/13.x/filesystem — Cloudflare R2 S3 driver, temporaryUrl()

### Secondary (MEDIUM confidence)

- vite-pwa-org.netlify.app/frameworks/laravel — `buildBase`, `Service-Worker-Allowed` header requis
- vite-pwa-org.netlify.app/workbox/generate-sw — runtimeCaching, BackgroundSyncPlugin config
- developer.chrome.com/docs/workbox/modules/workbox-background-sync — BackgroundSyncPlugin, maxRetentionTime
- github.com/cesargb/laravel-magiclink — `LoginAction`, `guard`, `lifetime`, `numMaxVisits`
- cheatsheetseries.owasp.org/Forgot_Password_Cheat_Sheet — anti-énumération, rate limiting
- github.com/jakearchibald/idb README — `openDB`, `DBSchema`, tx.done, cursors
- pestphp.com/docs/browser-testing — Playwright integration Pest 4

### Tertiary (LOW confidence)

- magicbell.com/blog/pwa-ios-limitations — iOS 14+ limitations, Background Sync "None"
- medium.com/@harrisrafto Livewire v3 pagination — `WithPagination`, `wire:model.live.debounce`

---

## Metadata

**Confidence breakdown :**
- Standard Stack : HIGH — toutes les versions vérifiées sur registries officiels le 2026-05-28
- Architecture : HIGH — contraintes verrouillées CONTEXT.md, patterns vérifiés docs officielles
- Pitfalls : HIGH pour les pitfalls 1-5 (sources officielles + CONTEXT.md), MEDIUM pour 6-9 (patterns raisonnés)
- Tests : HIGH — Pest 4 infrastructure existante Phase 1, patterns établis

**Research date :** 2026-05-28
**Valid until :** 2026-06-28 (30 jours — stack stable, pas fast-moving)

# Architecture Research

**Domain:** Laravel monolith — vitrine + outil métier offline-first + portail client + facturation Odoo
**Researched:** 2026-05-27
**Confidence:** HIGH (stack + offline patterns), MEDIUM (Odoo, plan incertain)

---

## Standard Architecture

### System Overview

```
┌──────────────────────────────────────────────────────────────────────┐
│  BROWSER (smartphone opérateur)                                       │
│                                                                       │
│  ┌──────────────────────┐     ┌─────────────────────────────────┐    │
│  │  Livewire/Blade       │     │  PWA Island (Alpine vanilla)     │    │
│  │  (pages connectées)   │     │  /passages/create               │    │
│  │  portail client       │     │  SW + IndexedDB + sync queue    │    │
│  │  historique, clients  │     │  (aucune dépendance réseau)     │    │
│  └──────────┬───────────┘     └──────────────┬──────────────────┘    │
│             │ AJAX/HTTP                       │ fetch (quand online)  │
└─────────────┼───────────────────────────────-┼──────────────────────-┘
              │                                 │
┌─────────────┴─────────────────────────────────┴──────────────────────┐
│  LARAVEL 11 (Laravel Cloud, Francfort)                                │
│                                                                       │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────┐  │
│  │  Web Routes  │  │  API Routes  │  │  Queue       │  │ Scheduler│  │
│  │  Livewire    │  │  /api/v1/    │  │  Workers     │  │          │  │
│  │  Blade views │  │  (passages,  │  │  (Odoo jobs, │  │ (pull    │  │
│  │              │  │  photos sync)│  │  PDF, notifs)│  │  paiement│  │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘  └────┬─────┘  │
│         └─────────────────┴─────────────────┴───────────────┘        │
│                                    │                                  │
│  ┌────────────────────────────────────────────────────────────────┐  │
│  │  Application Layer (modules)                                    │  │
│  │  Vitrine | Auth | CRM | Passages | Facturation | Diagnostic    │  │
│  └────────────────────────────────┬────────────────────────────────┘  │
│                                    │                                  │
│  ┌─────────────────────────────────┴──────────────────────────────┐  │
│  │  PostgreSQL (managé Laravel Cloud)                              │  │
│  │  clients, piscines, passages, mesures, photos_meta, produits,  │  │
│  │  contrats, factures, signatures, diagnostics                    │  │
│  └─────────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────────┘
              │                                 │
     ┌────────┴─────────┐             ┌─────────┴──────────┐
     │  Scaleway S3      │             │  Odoo (XML-RPC     │
     │  (Paris)          │             │  si plan Custom,   │
     │  photos originales│             │  sinon CSV export) │
     └───────────────────┘             └────────────────────┘
```

---

## Composants Principaux et Frontières

### Component Responsibilities

| Composant | Responsabilité | Frontière d'entrée | Frontière de sortie |
|-----------|----------------|---------------------|---------------------|
| **PWA Island (Alpine + SW)** | Saisie passage offline, stockage local, upload photos résilient | Interaction opérateur | `POST /api/v1/passages` (sync) |
| **Service Worker** | Cache assets statiques, interception fetch, trigger sync | Browser lifecycle | IndexedDB writes, réseau |
| **IndexedDB** | File de sync locale, brouillons passages, queue photos | Alpine.js write | SW read → API push |
| **Web Routes / Livewire** | Toutes les pages connectées (vitrine, back-office, portail client) | HTTP request | Blade/Livewire HTML |
| **API Routes (`/api/v1/`)** | Réception des syncs PWA, upload photos | Token API (opérateur) | PostgreSQL + Scaleway S3 |
| **Module Passages** | Logique métier passage, mesures, validation, PDF CR | API controller | DB, S3, job PDF |
| **Module Facturation** | Factures, contrats, catalogue | Livewire + Job | DB, Odoo ACL |
| **Module Diagnostic** | Wizard arbre décision, calcul doses, Stripe | Livewire | DB, Stripe |
| **OdooService (ACL)** | Anti-corruption layer Odoo XML-RPC | Job queue | Odoo `/xmlrpc/2/object` |
| **Scaleway S3** | Stockage binaire photos (permanent) | Laravel Filesystem | URL signée |
| **Queue Workers** | PushInvoiceToOdoo, PullPaymentStatus, SendPdfJob, NotifyJob | Dispatch depuis controllers | Services externes |

---

## Architecture Offline-First (priorité absolue)

### Principe de cohabitation Livewire / PWA Island

Livewire est **incompatible avec le offline** : chaque interaction déclenche un POST AJAX vers le serveur. La solution est une **séparation stricte par route** :

- Toutes les routes sauf `/passages/create` (et `/passages/{id}/edit`) → Livewire normal.
- La route de saisie d'un passage → page Blade **shell** qui charge une **Alpine.js application vanilla** totalement autonome. Cette page ne contient aucun composant Livewire.

Ce pattern est un "island of interactivity offline" : le shell Blade sert l'HTML initial et les assets en cache, Alpine prend le relais pour toute la logique de saisie, le Service Worker intercepte les requêtes réseau.

### Flux de saisie d'un passage (offline-first)

```
Opérateur ouvre /passages/create
        ↓
[Shell Blade servi (ou du cache SW si offline)]
        ↓
Alpine.js initialise : lit IndexedDB → brouillon existant ?
        ↓                        ↓
   Reprend brouillon        Nouveau passage (UUID v4 généré côté client)
        ↓
Saisie (pH, chlore, TAC, sel, actions, notes)
        ↓
Photos capturées (camera API) → blob → stocké dans IndexedDB (objet binary)
        ↓
[Submit] → écrit passage complet dans IndexedDB `sync_queue`
        ↓
      Online ?
     /       \
   Oui        Non
    ↓           ↓
Trigger sync  Background Sync registered (Chromium)
immédiat       OU listener 'online' event (iOS/Firefox fallback)
    ↓
Service Worker `sync` handler (ou main thread pour iOS)
    ↓
POST /api/v1/passages (idempotent via client_uuid)
    ↓ succès (201 ou 200 si déjà existant)
Supprimer item de sync_queue dans IndexedDB
    ↓
Upload photos : pour chaque photo blob
  → POST /api/v1/passages/{uuid}/photos (multipart)
  → si échec → rester dans IndexedDB photo_queue, retry au prochain online
    ↓
Entrée confirmée → navigation vers historique
```

### Idempotence

Chaque passage reçoit un `client_uuid` (UUID v4) généré dans le navigateur **avant** la première tentative d'envoi. Le serveur Laravel fait :

```php
Passage::firstOrCreate(
    ['client_uuid' => $request->client_uuid],
    [...attributs...]
);
```

Si le réseau coupe après que le serveur a créé le passage mais avant que la réponse ne revienne, le retry suivant reçoit un 200 (ou 409 + id existant selon convention choisie). Pas de doublon.

### Gestion des conflits

Quasi-nulle : un seul opérateur, les passages sont des créations (pas de mises à jour concurrentes). La politique est **last-write-wins sur les champs mesures** si jamais un brouillon est envoyé deux fois, ce qui ne peut arriver qu'en cas de bug. Aucun CRDT ni OT nécessaire.

### Upload photos résilient

Les photos ne sont **pas incluses** dans le payload JSON du passage (elles sont trop lourdes pour une file JSON). Architecture séparée :

1. Le passage est créé/confirmé côté serveur en premier (sans photos).
2. Une `photo_queue` séparée dans IndexedDB contient `{ passage_client_uuid, photo_blob, photo_client_uuid, uploaded: false }`.
3. Un worker Alpine indépendant vide cette queue photo par photo (`POST /api/v1/passages/{uuid}/photos`).
4. Chaque photo a son propre `photo_client_uuid` pour idempotence.
5. Si une photo échoue, elle reste en queue. Les autres continuent.
6. Pas de chunking pour des photos smartphone (<5MB typiquement) — un seul POST par photo suffit. Si on atteignait des vidéos, on reviendrait sur cette décision.

### Background Sync vs online event (iOS)

Background Sync (Chromium uniquement) est utilisé comme déclencheur primaire. Pour iOS Safari (qui représente ~30-40% des smartphones en Martinique), le fallback est :

```javascript
window.addEventListener('online', () => drainSyncQueue());
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') drainSyncQueue();
});
```

Cette double écoute couvre le retour au premier plan de l'app Safari installée en PWA.

**Attention iOS :** IndexedDB sur iOS peut être évicté après 7 jours sans activité. Tant que l'opérateur utilise l'app quotidiennement, ce n'est pas un risque réel.

---

## Modèle de Données

### Entités principales

```
clients
  id, uuid, name, email, phone, address, magic_link_token, magic_link_expires_at
  created_at, updated_at

piscines
  id, uuid, client_id → clients
  volume_m3, type (béton/liner/coque/résine/autre), filtration (sable/cartouche/DE)
  equipements jsonb  -- chlorinateur, chauffage, volet, etc.
  notes
  created_at, updated_at

passages
  id, client_uuid (UNIQUE, index), uuid (alias=client_uuid à la réception)
  piscine_id → piscines
  client_id → clients (dénormalisé pour requêtes rapides)
  visited_at (datetime, rempli par l'opérateur)
  status (draft | synced | archived)
  -- Mesures eau
  ph_avant, ph_apres, chlore_libre, chlore_total, tac, th, sel_g_l
  -- Compte-rendu
  actions jsonb   -- [{ type, produit_id?, qte?, note }]
  notes text
  pdf_path
  signature_path
  synced_at
  created_at, updated_at

photos
  id, client_uuid (UNIQUE), passage_id → passages
  path (Scaleway S3 key)
  caption
  taken_at
  created_at

produits
  id, nom, description, unite (litre/kg/unité), prix_unitaire, tva_rate
  type (produit | service)
  actif boolean
  created_at, updated_at

contrats
  id, client_id → clients
  type (ponctuel | forfait_mensuel | forfait_annuel)
  description, prix, tva_rate
  date_debut, date_fin nullable
  nb_passages_inclus nullable
  statut (actif | suspendu | terminé)
  created_at, updated_at

factures
  id, uuid
  client_id → clients
  contrat_id nullable → contrats
  passage_id nullable → passages  -- pour facture ponctuelle post-passage
  lignes jsonb   -- [{ description, qte, prix_unitaire, tva_rate }]
  total_ht, tva, total_ttc
  statut (brouillon | envoyée | payée | en_retard | annulée)
  odoo_id nullable (int) -- id côté Odoo une fois pushé
  odoo_synced_at nullable
  odoo_sync_error nullable
  date_echeance
  created_at, updated_at

signatures
  id, passage_id → passages
  client_id → clients
  signed_at, ip_address, user_agent
  signature_path (S3 key SVG ou PNG)

diagnostics
  id, uuid
  session_token (pour diagnostics anonymes non authentifiés)
  client_id nullable → clients
  type_piscine, volume_m3, resultats jsonb  -- mesures saisies dans le wizard
  recommandations jsonb  -- calculées
  doses jsonb  -- calculées
  stripe_payment_intent_id nullable
  statut (gratuit | payant_en_attente | payant_complété)
  created_at, updated_at
```

### Index critiques

- `passages.client_uuid` UNIQUE — idempotence offline sync
- `photos.client_uuid` UNIQUE — idempotence upload
- `passages.client_id + visited_at DESC` — historique client
- `factures.odoo_id` — lookup lors du pull statut paiement
- `factures.statut` — dashboard facturation

---

## Intégration Odoo

### Couche anti-corruption (ACL)

L'application Laravel ne connaît **jamais** les types Odoo directement dans les controllers ou les models. Tout passe par `App\Services\Odoo\OdooClient` (wrapper XML-RPC) et `App\Services\Odoo\OdooInvoiceService`.

```
Controller/Job
     ↓
OdooInvoiceService::push(Facture $f): OdooInvoiceId
     ↓
OdooClient::call('account.move', 'create', [...])
     ↓
XML-RPC /xmlrpc/2/object
```

Le service traduit les types Laravel (`Facture`, `LigneFacture`) vers le modèle Odoo (`account.move`, `account.move.line`) et retransforme les réponses. Si Odoo change de version ou si on bascule en CSV, seul ce service change.

### Jobs asynchrones (Laravel Queue)

| Job | Déclencheur | Ce qu'il fait | Retry policy |
|-----|-------------|--------------|--------------|
| `PushInvoiceToOdooJob` | Facture marquée `envoyée` | Crée `account.move` dans Odoo, stocke `odoo_id` | 3 retries, backoff exp. |
| `PullPaymentStatusJob` | Scheduler (toutes les heures) | Lit `account.move` où `odoo_id` connu, met à jour `statut` | 3 retries |
| `GeneratePdfJob` | Passage synced | Génère PDF via Laravel + DomPDF, upload S3 | 2 retries |
| `SendNotificationJob` | Passage synced + PDF prêt | Email CR client, rappel J-1 (optionnel WhatsApp) | 3 retries |

### Pont CSV (fallback si plan non Custom)

Si le plan Odoo de l'opérateur ne donne pas accès à l'API externe (plan One App ou Standard), on active le mode CSV :

1. Une command artisan `odoo:export-invoices --since=...` génère un CSV normalisé Odoo dans `storage/odoo-exports/`.
2. L'opérateur télécharge ce CSV depuis le back-office et l'importe manuellement dans Odoo.
3. Le statut paiement ne peut pas être pulled automatiquement → l'opérateur met à jour manuellement depuis le back-office.

La feature flag `ODOO_MODE=api|csv` contrôle quel chemin est emprunté par `OdooInvoiceService`. Les jobs ne sont pas dispatchés en mode CSV.

### Authentification XML-RPC Odoo

Odoo 14+ : utiliser une **clé API** (Settings > Technical > API Keys) plutôt qu'un mot de passe. Deux appels :
1. `POST /xmlrpc/2/common` → `authenticate()` → retourne `uid`
2. `POST /xmlrpc/2/object` → `execute_kw(db, uid, api_key, model, method, args)`

Le `uid` peut être mis en cache (session, config cache) pour éviter un appel réseau supplémentaire par job.

---

## Structure de Modules Laravel

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Web/          # Livewire + Blade routes
│   │   └── Api/V1/       # Routes PWA offline sync
│   └── Middleware/
├── Livewire/             # Composants Livewire (pages connectées)
├── Models/               # Eloquent models (flat, pas de modules séparés)
├── Services/
│   ├── Odoo/
│   │   ├── OdooClient.php          # Wrapper XML-RPC brut
│   │   └── OdooInvoiceService.php  # ACL : traduction Laravel ↔ Odoo
│   ├── PassageService.php          # Logique métier passage
│   ├── FactureService.php          # Génération factures, calcul totaux
│   └── DiagnosticService.php       # Arbre de décision + formules doses
├── Jobs/
│   ├── PushInvoiceToOdooJob.php
│   ├── PullPaymentStatusJob.php
│   ├── GeneratePdfJob.php
│   └── SendNotificationJob.php
└── Actions/              # Single-action classes (optionnel, pour clarity)

resources/
├── views/
│   ├── layouts/
│   ├── livewire/
│   └── passage-create/   # Shell Blade pour la page offline
│       └── index.blade.php
├── js/
│   ├── passage-create/   # Alpine vanilla app (offline island)
│   │   ├── app.js        # Point d'entrée Alpine
│   │   ├── db.js         # Abstraction IndexedDB (idb ou vanilla)
│   │   ├── sync.js       # Logic drain sync_queue + photo_queue
│   │   └── camera.js     # Capture photo, résolution, blob
│   └── sw.js             # Service Worker

public/
└── manifest.webmanifest  # PWA manifest
```

### Rationale structure

- **`Services/Odoo/`** isolé : tout changement Odoo (version, mode CSV/API) localisé ici.
- **`resources/js/passage-create/`** totalement séparé du reste du JS Livewire : aucun import croisé, aucune dépendance Livewire.
- **`sw.js`** compilé dans `public/` via Vite (pas dans `resources/` directement, car doit être servi à la racine pour que le scope SW couvre toute l'app).

---

## Flux de Données

### Flux 1 — Sync passage offline

```
[Alpine submit]
    → IndexedDB sync_queue.add({ client_uuid, ...données })
    → navigator.serviceWorker.ready.sync.register('sync-passage')  [Chromium]
    OU window.online event  [iOS]
        ↓
[Service Worker / main thread]
    → IndexedDB sync_queue.getAll()
    → pour chaque item : fetch POST /api/v1/passages
        ↓ réponse 2xx
    → IndexedDB sync_queue.delete(item.id)
        ↓
[Upload photos]
    → IndexedDB photo_queue.getAll()
    → pour chaque photo : fetch POST /api/v1/passages/{uuid}/photos
        ↓ réponse 2xx
    → IndexedDB photo_queue.delete(photo.id)
```

### Flux 2 — Réception passage côté API

```
POST /api/v1/passages
    → PassageController@sync
    → Passage::firstOrCreate(['client_uuid' => ...], [...])
    → dispatch(GeneratePdfJob)
    → dispatch(SendNotificationJob) [si passage complet]
    → return 201 | 200
```

### Flux 3 — Push facture Odoo

```
FactureController@envoyer
    → $facture->statut = 'envoyée'
    → dispatch(PushInvoiceToOdooJob::class, $facture)
    → return redirect (pas d'attente synchrone)

[PushInvoiceToOdooJob::handle]
    → OdooInvoiceService::push($facture)
        → OdooClient::call('account.move', 'create', [...])
    → $facture->odoo_id = $result
    → $facture->odoo_synced_at = now()
    → $facture->save()
```

### Flux 4 — Pull statut paiement (scheduler)

```
[Scheduler toutes les heures]
    → PullPaymentStatusJob dispatch pour chaque facture avec odoo_id et statut != 'payée'
    → OdooClient::call('account.move', 'read', [[$odoo_id], ['payment_state']])
    → si payment_state == 'paid' → $facture->statut = 'payée'
```

---

## Ordre de Construction (dépendances)

### Phase V — Fondations (aucune dépendance)
1. Setup Laravel Cloud + PostgreSQL + Scaleway S3 + Vite
2. Authentification (Sanctum, email+mdp pro, magic link client)
3. Modèles Eloquent + migrations (clients, piscines, passages, photos)
4. Vitrine Blade/Livewire (SEO, pas de dépendance métier)

### Phase 0 — MVP (dépend de V)
5. CRUD clients + piscines (Livewire, connecté)
6. PWA Island offline-first : SW + IndexedDB + Alpine saisie passage
7. API endpoints `/api/v1/passages` + `/api/v1/passages/{uuid}/photos`
8. Sync upload photos (photo_queue)
9. Historique passages (Livewire, connecté)
10. Portail client lecture seule (magic link)
11. Génération PDF CR (DomPDF + S3)
12. Signature client

### Phase 1a — Facturation (dépend de 0 + modèles produits/contrats)
13. Modèles produits + contrats + factures
14. Interface facturation Livewire
15. POC Odoo : vérifier plan → choisir API ou CSV
16. `OdooClient` + `OdooInvoiceService` (ACL)
17. `PushInvoiceToOdooJob` + `PullPaymentStatusJob`
18. Pont CSV comme fallback activable

### Phase 1b — Notifications (dépend de 1a pour jobs infrastructure)
19. `SendNotificationJob` (email, Mailgun/SES)
20. Rappel J-1 passage (Scheduler)
21. Option WhatsApp (Twilio, séparable)

### Phase 2 — Diagnostic (dépend de V pour auth, indépendant du reste)
22. `DiagnosticService` (logique métier depuis l'ancienne maquette React)
23. Wizard Livewire multi-étapes
24. Calcul doses
25. Stripe (paiement résultat complet)

---

## Anti-Patterns

### Anti-Pattern 1 : Livewire dans la page de saisie offline

**Ce que les gens font :** Mettre un composant Livewire sur `/passages/create` parce que c'est "uniforme".
**Pourquoi c'est faux :** Livewire fait des POST AJAX synchrones vers le serveur. Sans réseau, le composant est mort. Toute la valeur offline-first disparaît.
**À la place :** Shell Blade + Alpine vanilla totalement autonome pour cette page uniquement.

### Anti-Pattern 2 : Inclure les photos dans le payload JSON du passage

**Ce que les gens font :** Base64-encoder les photos dans le JSON du passage pour "tout envoyer en une fois".
**Pourquoi c'est faux :** Un passage avec 5 photos de 3MB chacun = 15MB JSON. La sync échoue sur réseau mobile faible. Un seul timeout tue tout.
**À la place :** Séparer passage (JSON léger) et photos (queue dédiée, retry individuel par photo).

### Anti-Pattern 3 : Appeler Odoo de façon synchrone depuis un controller

**Ce que les gens font :** `OdooClient::call(...)` dans le controller qui génère la facture, utilisateur bloqué en attendant la réponse Odoo.
**Pourquoi c'est faux :** Odoo Online est lent (1 call/sec), des timeout réseau sont inévitables, l'UX est dégradée.
**À la place :** Toujours via Job (`PushInvoiceToOdooJob`), retour immédiat à l'utilisateur, notification quand synced.

### Anti-Pattern 4 : Service Worker avec scope global qui intercepte les requêtes Livewire

**Ce que les gens font :** Un `sw.js` avec `fetch` handler catch-all qui intercepte toutes les requêtes, y compris les POST Livewire.
**Pourquoi c'est faux :** Les POST Livewire contiennent l'état serveur, mettre en cache ou rejeter ces requêtes brise l'interface connectée.
**À la place :** Le fetch handler du SW ne met en cache que les assets statiques et les GET de l'app shell. Les POST sont passés through sauf sur la route passage create.

### Anti-Pattern 5 : Stocker l'UIDs Odoo comme clé primaire locale

**Ce que les gens font :** Faire dépendre `factures.id` ou des foreign keys de `odoo_id`.
**Pourquoi c'est faux :** Odoo peut ne pas être joignable au moment de la création, `odoo_id` peut être null. Toute l'app serait bloquée par la disponibilité d'Odoo.
**À la place :** `odoo_id` est une colonne nullable sur `factures`, populée de façon asynchrone par le Job.

---

## Considérations de Scalabilité

| Échelle | Ajustement architectural |
|---------|--------------------------|
| Actuel (~10 passages/semaine, ~10 clients) | Monolithe Laravel, queue synchrone (database driver), pas de Redis nécessaire |
| 10x (~100 passages/semaine) | Passer queue driver de `database` à `Redis` (Laravel Cloud le supporte), mise en cache portail client |
| 100x+ | Séparation lectures/écritures PostgreSQL, CDN pour photos, queue workers dédiés — non pertinent pour ce projet |

Pour ce projet, **la scalabilité n'est pas une contrainte**. L'architecture doit optimiser la simplicité de maintenance solo.

---

## Points d'Intégration

### Services externes

| Service | Pattern d'intégration | Gotchas |
|---------|-----------------------|---------|
| Odoo Online (XML-RPC) | `OdooClient` wrapper + Jobs asynchrones + feature flag CSV | API réservée plan Custom (vérifié). Rate limit ~1 call/sec. Utiliser API key v14+, pas mot de passe. |
| Scaleway Object Storage | Laravel Filesystem driver S3-compatible (`FILESYSTEM_DISK=s3`) | Région Paris (`fr-par`). Configurer CORS pour upload direct depuis browser si besoin futur. |
| Mailgun / SES | Laravel Mail + Mailables | Pré-configurer SPF/DKIM pour dloazurpiscines.com. |
| Stripe | Cashier ou SDK direct (Phase 2 seulement) | Webhook endpoint pour confirmer paiements diagnostic. |
| Twilio WhatsApp | Job + HTTP client (Phase 1b optionnel) | Sandbox Twilio pour dev. Sandbox ne fonctionne qu'avec numéros pré-approuvés. |

### Frontières internes

| Frontière | Communication | Notes |
|-----------|---------------|-------|
| PWA Island ↔ API Laravel | `fetch` REST JSON (POST) | Auth via Bearer token (Sanctum API token, stocké dans IndexedDB chiffré) |
| Livewire ↔ DB | Eloquent direct (via composant ou service) | Pas de passer par l'API interne — overhead inutile |
| Jobs ↔ OdooService | PHP direct (injection) | Pas d'interface HTTP interne |
| Scheduler ↔ Jobs | `dispatch()` depuis `Console/Kernel` | Retries configurés par Job class |
| Modules Livewire ↔ Modules | Via Events Livewire ou Alpine events | Éviter les dépendances directes entre composants de domaines différents |

---

## Sources

- MDN Background Synchronization API — https://developer.mozilla.org/en-US/docs/Web/API/Background_Synchronization_API
- PWA iOS Limitations 2026 — https://www.magicbell.com/blog/pwa-ios-limitations-safari-support-complete-guide
- Offline-First by Design (IndexedDB queue) — https://medium.com/@11.sahil.kmr/offline-first-by-design-pwa-indexed-db-and-a-reliable-queue-775605b3d76c
- Advanced PWA Guide (Background Sync patterns) — https://rishikc.com/articles/advanced-pwa-features-offline-push-background-sync/
- Odoo External API documentation — https://www.odoo.com/documentation/17.0/developer/reference/external_api.html
- Odoo API Integration Guide 2026 — https://www.getknit.dev/blog/odoo-api-integration-guide-in-depth
- Livewire 3 Alpine.js integration — https://livewire.laravel.com/docs/4.x/alpine
- Livewire + PWA forum discussion — https://forum.laravel-livewire.com/t/possible-to-create-pwa-with-livewire/1724
- huge-uploader (offline-aware chunked upload) — https://github.com/Buzut/huge-uploader

---
*Architecture research for: Dlo Azur Piscines — monolithe Laravel offline-first*
*Researched: 2026-05-27*

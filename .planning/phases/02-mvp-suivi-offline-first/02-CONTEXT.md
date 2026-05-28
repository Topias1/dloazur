# Phase 2: MVP Suivi Offline-First - Context

**Gathered:** 2026-05-28
**Status:** Ready for planning

<domain>
## Phase Boundary

Livrer le **cœur de valeur** du projet : l'opérateur saisit un passage sur le terrain **sans réseau** sur son smartphone, les données + photos se synchronisent à la reconnexion **sans doublon** (idempotence `client_uuid`), et le client consulte son historique de passages **en lecture seule** via magic link.

**In scope :**
- Auth client via magic link `cesargb/laravel-magiclink` ^2.27 (AUTH-02, AUTH-03, AUTH-04)
- CRUD pro clients + fiches piscines + recherche (CLI-01, CLI-02, CLI-03)
- Saisie passage offline-first via Alpine 3 + IndexedDB (`idb` ^8) + Service Worker (PASS-01, PASS-03)
- Photos passage avec compression client-side + file résiliente (PASS-02, PASS-04)
- Historique passages pro avec filtres client/date (PASS-05)
- Badge "N passages en attente" de synchronisation (PASS-06)
- Portail client lecture seule (PORT-01, PORT-02) avec historique mesures + photos
- PWA installable Home Screen + `storage.persist()` actif
- Pages admin transposées 1:1 depuis `mockups/v1/passage.html`, `dashboard.html`, `portail.html`

**Out of scope (déféré Phase 3+) :**
- Facturation, catalogue produits/services, contrats (Phase 3 — FACT-*)
- Génération PDF compte-rendu (Phase 3 — FACT-06)
- Signature électronique client (Phase 3 — FACT-07)
- Push notifications (Phase 4 — NOTIF-*)
- Diagnostic commercialisable (Phase 5 — DIAG-*)
- Multi-piscines par client en UI (v2 — CLI-04)
- Multi-opérateurs / rôles techniciens (Out of Scope définitif)

</domain>

<decisions>
## Implementation Decisions

### Stack & infra (carried forward de Phase 1, verrouillé)
- **CF-01 (carry):** Stack Laravel 13 + Livewire 3 + Alpine 3 + Tailwind 4 + Postgres 17 (D-01 Phase 1)
- **CF-02 (carry):** **Pas Livewire** pour la saisie passage offline (Livewire exige le réseau) → **Alpine 3 + IndexedDB (`idb` ^8) + Service Worker** (CLAUDE.md §PWA)
- **CF-03 (carry):** Storage photos **Cloudflare R2** (S3-compatible, zero-egress, 10GB gratuits) — disk `r2` configuré Phase 1 (D-02 amendé)
- **CF-04 (carry):** Magic link via `cesargb/laravel-magiclink` ^2.27 (verrouillé CLAUDE.md, supporte L13)
- **CF-05 (carry):** Médias via `spatie/laravel-medialibrary` ^11.22 (verrouillé CLAUDE.md)
- **CF-06 (carry):** PWA via `vite-plugin-pwa` ^1.3 + `idb` ^8 (verrouillé CLAUDE.md)
- **CF-07 (carry):** Schéma déjà migré Phase 1 : `passages.client_uuid` UUID unique (idempotence), `photos_meta.disk` default `'r2'`, `status` default `'draft'`, `signature_path` nullable (Phase 3)
- **CF-08 (carry):** Design system verrouillé via skill `impeccable` (PRODUCT.md, DESIGN.md, mockups/v1/*)

### Cross-platform device target (correction post-discussion)
- **D-36:** **Cible : iOS Safari ET Android Chrome, récents (~5 ans) et anciens** (≥iOS 14 / Android 5). Pas d'iPhone-only. Device de Pierre **inconnu** — confirmer par interview avant validation terrain. Voir `[[pierre-device-platform]]` mémoire user.
- **D-37:** **ROADMAP-02 success criterion #2 à amender** avant verify-phase : `"sur iPhone"` → `"sur smartphone"`. À traiter dans le plan-phase ou en commit dédié `docs(roadmap): platform-agnostic Phase 2 SC#2`.

### Stratégie de synchro & résolution de conflits (zone grise 1)
- **D-38 :** **Server-wins conditionnel via UPSERT contraint.** Endpoint unique **`POST /api/passages`** qui agit en UPSERT Postgres : `INSERT INTO passages (...) VALUES (...) ON CONFLICT (client_uuid) DO UPDATE SET ... WHERE passages.status = 'draft'`. Un passage `status != 'draft'` (clos, envoyé, facturé en Phase 3+) ne peut **plus** être modifié par un push mobile tardif.
- **D-39 :** **Génération du `client_uuid` côté client Alpine** via `crypto.randomUUID()` au démarrage de la saisie passage (avant tout input). Persisté immédiatement en IndexedDB store `passages`. Envoyé tel quel dans le body POST.
- **D-40 :** **Code retour `409 Conflict`** quand le UPSERT ne met rien à jour (passage déjà clos). Payload `{ error: 'already_closed', server_state: {...} }`. Côté Alpine : afficher toast "Ce passage a déjà été clos. Tes modifications n'ont pas été enregistrées." + supprimer l'item de la queue IDB.
- **D-41 :** **Pas de CRDT, pas de merge field-by-field.** Solo opérateur ~10 passages/sem → conflit ultra-rare, sur-engineering injustifié.
- **D-42 :** Idempotence des photos : chaque photo a son propre `client_uuid` (UUID v4 généré au moment de la capture côté Alpine, persisté avec le blob en IDB). Endpoint `POST /api/passages/{passage_uuid}/photos` UPSERT sur `photos_meta.client_uuid` (à ajouter au schéma via migration en Phase 2). **TODO migration : `ALTER TABLE photos_meta ADD COLUMN client_uuid UUID UNIQUE`**.

### Pipeline photos offline (zone grise 2)
- **D-43 :** **Conversion HEIC côté client via WASM `heic2any`** (npm `heic2any` ^0.0.4, lazy-imported uniquement si magic bytes HEIC détectés). Réduit le poids réseau Martinique de ~15-25MB/passage (HEIC brut) à ~2-4MB (JPEG compressé). Sur Android JPEG natif : WASM **jamais chargé** (lazy import via `import('heic2any')` conditionnel).
- **D-44 :** **Compression Canvas** : `canvas.toBlob('image/jpeg', 0.80)` resize max 2048px (largest edge). Correction EXIF orientation via `exifr` (~15KB) AVANT `drawImage`. Cible ~300-400KB/photo. **Pas de WebP** (broken Safari iOS 17).
- **D-45 :** **Ordre upload** : `POST /api/passages` (passage seul, sans photos) → puis **`POST /api/passages/{client_uuid}/photos`** une par une avec `idempotency_key=photo_client_uuid` (D-42). Granularité de retry par photo individuelle. **Pas de multipart bundle** (30-40MB par requête = timeout garanti sur réseau Martinique).
- **D-46 :** **Backoff côté Alpine** : 3 retries automatiques avec délais `2s → 8s → 30s` (exponentiel simple, pas de jitter). Après 3 échecs : marquer en IDB `status='error'`, exposer dans le badge "X erreurs de synchro" + bouton manuel "Réessayer".
- **D-47 :** **Background Sync API (Workbox)** : activée via `vite-plugin-pwa` `workbox.runtimeCaching` avec `BackgroundSyncPlugin` pour les POSTs `/api/passages/*`. Active sur Android Chrome (~85% support), no-op silencieux sur iOS Safari. **+ Fallback Alpine obligatoire** : `window.addEventListener('online')` + `document.addEventListener('visibilitychange')` déclenchent un flush de queue manuel — fonctionne partout.
- **D-48 :** **Laravel Cloud `upload_max_filesize` à 10MB** + `post_max_size` aligné (au cas où WASM échoue et qu'un HEIC brut remonte).

### Magic link client AUTH-02..04 (zone grise 3)
- **D-49 :** **Profil "Équilibré"** retenu : `lifetime = 48*60` minutes (48h), `numMaxVisits = 3`.
- **D-50 :** **Page intermédiaire AUTH-04 obligatoire** : GET `/auth/confirm?token=...` retourne une page HTML statique avec bouton `<form method="POST">Confirmer ma connexion</form>`. Seul le POST consomme le token. **Raison critique** : Microsoft 365 SafeLinks (Defender for Office 365, inclus dans M365 Business 6€/user) effectue un GET via proxy Microsoft au moment du clic → consommerait un token single-use AVANT que le client soit redirigé. Pattern recommandé OWASP/FusionAuth. Les conciergeries B2B (cible secondaire vitrine) sont probablement sur M365.
- **D-51 :** **Session client glissante 30 jours** post-confirmation (cookie renouvelé à chaque visite). Portail consultation ponctuelle, pas un service quotidien.
- **D-52 :** **Rate limit envoi magic link** : par IP (5/h) + par email (3/24h). Anti-énumération : message générique `"Si cet email correspond à un compte, un lien de connexion a été envoyé."` + `sleep(rand(1,3))` pour uniformiser le temps de réponse (OWASP Forgot Password Cheatsheet).
- **D-53 :** **Pas de CAPTCHA, pas d'OTP** — friction injustifiée pour des données pH/photos peu sensibles.
- **D-54 :** Configuration `cesargb/laravel-magiclink` : `MagicLink::create($action, lifetime: 2880, numMaxVisits: 3)` avec callback `$action` qui logue le `Client` model et redirige vers le portail `/portail/passages`.

### Architecture PWA cross-platform (zone grise 4)
- **D-55 :** **Mode `generateSW`** (Workbox auto-généré) — pas `injectManifest`. Toute la logique de synchro vit dans Alpine (`online`/`visibilitychange` events + `BackgroundSyncPlugin` Workbox). Le SW ne fait que précache + offline fallback. `injectManifest` reste une porte de sortie si on ajoute push notif (Phase 4) ou logique custom.
- **D-56 :** **Update strategy `registerType: 'prompt'`** + toast Alpine. `registerSW({ onNeedRefresh() { ... } })` via `virtual:pwa-register`. Protège la saisie en cours (pas de skipWaiting auto qui couperait un formulaire mid-passage). Toast simple : "Mise à jour disponible — Recharger ?".
- **D-57 :** **`navigator.storage.persist()` appelé inconditionnellement au boot Alpine** (root layout admin). iOS Safari accorde silencieusement si standalone Home Screen (WebKit Storage Policy 2023). Android Chrome peut afficher un prompt système (acceptable). Vérifier `navigator.storage.persisted()` avant pour éviter le double-call.
- **D-58 :** **Cache strategies Workbox** :
  - HTML navigation : `NetworkFirst` (5s timeout) + fallback `offline.html` précaché via `additionalManifestEntries`
  - Assets Vite hashés (JS/CSS) : `CacheFirst` + expiration 1 an
  - `/api/*` : `NetworkOnly` + `BackgroundSyncPlugin` (queue Workbox pour Android) — les passages restent dans IDB Alpine côté queue applicative
  - `/admin/*` routes : `NetworkFirst` sans fallback offline (acceptable, admin = online only sauf saisie passage)
- **D-59 :** **Schéma IndexedDB : 2 stores séparés** via `idb` v8 `DBSchema` typé :
  - Store `passages` : `{ id (autoIncr), client_uuid, payload_json, status, attempts, created_at, last_attempt_at }`, index `by-status` et `by-created`
  - Store `photos` : `{ id (autoIncr), client_uuid, passage_client_uuid, blob, status, attempts, captured_at }`, index `by-passage` et `by-status`
  - DB version 1, nom `dloazur-offline-v1`
- **D-60 :** Configuration Laravel obligatoire : `buildBase: '/build/'` dans `vite.config.js` + header `Service-Worker-Allowed: /` (middleware Laravel ou config Laravel Cloud). Contraintes documentées CLAUDE.md.

### Recherche & UI (Claude's Discretion)
- **D-61 :** **Recherche clients (CLI-03)** : recherche simple `ILIKE` sur `name + email + phone + address`. Pas de full-text Postgres (volume ~10 clients ne le justifie pas). Pagination 25/page si > 25 résultats. **Tri par défaut : `updated_at DESC`**. Filtres : aucun à v1 (volume trop faible).
- **D-62 :** **Historique passages pro (PASS-05)** : liste paginée 25/page, filtres `client_id` (select) + `date_range` (depuis/à). Tri par défaut `visited_at DESC`. Vue carte mobile + table desktop responsive. Transposition 1:1 depuis `mockups/v1/dashboard.html` pour les variantes.
- **D-63 :** **Validation mesures** : intervalles softs (warning toast Alpine si hors limites mais pas de blocage). Limites : pH `[5.0, 9.0]`, chlore libre `[0, 10] mg/L`, chlore total `[0, 15] mg/L`, TAC `[50, 300] mg/L`, sel `[0, 8] g/L`, TH `[0, 50] °f`. Pierre peut saisir des valeurs aberrantes (cas d'eau verte, piscine bricolée) — UI prévient mais soumet.
- **D-64 :** **Modèle client/piscine** : 1 client = 1 piscine en UI v1 (modèle déjà migré flexible — `piscines.client_id` FK). Si Pierre a un client multi-piscines (rare), sélecteur de piscine s'affiche dans le formulaire de saisie passage. Sinon auto-pick piscine unique.
- **D-65 :** **Portail client (PORT-01, PORT-02)** : transposition 1:1 de `mockups/v1/portail.html`. Liste passages chronologique inverse, détail = mesures + photos lightbox + notes. Lazy-load photos via `loading="lazy"` + intersection observer pour mesurer le scroll. Pas de filtres v1.

### Claude's Discretion (déférable aux planner/researcher)
- Architecture exacte des composants Alpine (factories vs `Alpine.data()` global) — researcher évaluera selon convention Laravel+Alpine.
- Structure des routes API (`routes/api.php` avec préfixe `/api/v1/` ou pas).
- Stratégie de test offline : Pest 4 supporte Playwright (CLAUDE.md) — recherche évaluera intégration tests E2E PWA.
- UI précise du badge "N passages en attente" (icône, position dans le shell admin) — référence `mockups/v1/dashboard.html`.
- Naming des fichiers IDB / Alpine stores (cohérence avec convention Laravel `snake_case`).

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Stratégie & cadrage produit
- `PRODUCT.md` — registre `product` par défaut, principes design, accessibilité WCAG AA, anti-references
- `.planning/PROJECT.md` — Core value (saisie offline = ne doit pas échouer), requirements actifs, Key Decisions, Out of Scope explicit
- `.planning/REQUIREMENTS.md` — v1 requirements AUTH-02..04, CLI-01..03, PASS-01..06, PORT-01..02 (Phase 2 scope) + traceability
- `.planning/ROADMAP.md` §"Phase 2: MVP Suivi Offline-First" — goal, success criteria, dependencies (Phase 1)
- `.planning/STATE.md` — current position, blockers Phase 2 (validation terrain réseau Martinique requise avant Phase 3)
- `.planning/phases/01-vitrine-fondations/01-CONTEXT.md` — décisions D-01..D-31 Phase 1 (stack, schéma, R2, Brevo, etc.) — TOUTES carried forward
- `docs/superpowers/specs/2026-05-27-dloazur-refonte-design.md` — note de cadrage v2 détaillée
- `docs/superpowers/specs/2026-05-27-dloazur-design-system.md` — design system + prompts Claude Design (écran saisie passage + accueil)

### Design system & maquettes (verrouillés)
- `DESIGN.md` — tokens OKLCH, typographie Fredoka + Inter, élévation, composants
- `.impeccable/design.json` — sidecar (rampes tonales, ombres, motion, breakpoints)
- `mockups/v1/passage.html` — **maquette saisie passage offline** à transposer 1:1 (Alpine + IDB, pas Livewire)
- `mockups/v1/dashboard.html` — **maquette shell admin** : sidebar avec Clients/Passages désormais actifs, badge "N en attente"
- `mockups/v1/portail.html` — **maquette portail client** lecture seule à transposer 1:1 (mesures + photos lightbox)
- `mockups/v1/auth.html` — référence visuelle login pro (et magic link confirm landing)
- `mockups/v1/theme.js`, `mockups/v1/app.css` — tokens design transposés dans `resources/css/app.css` @theme (Phase 1)
- `.claude/skills/impeccable/SKILL.md` — skill obligatoire avant tout travail UI

### Stack & sécurité
- `CLAUDE.md` — instructions projet : stack verrouillé, packages recommandés (Fortify, magiclink, medialibrary, vite-plugin-pwa, idb), packages interdits (Livewire pour offline, Background Sync seul, edujugon/laradoo, etc.)
- `CLAUDE.md` §"PWA / Offline" — contraintes Laravel + vite-plugin-pwa : `buildBase` + header `Service-Worker-Allowed`
- `CLAUDE.md` §"Auth" — `cesargb/laravel-magiclink` ^2.27 verrouillé pour magic link client
- `CLAUDE.md` §"Stockage médias" — `spatie/laravel-medialibrary` ^11.22 + Cloudflare R2 disk

### Recherche stack & architecture (Phase 1)
- `.planning/research/SUMMARY.md` — synthèse stack
- `.planning/research/STACK.md` — versions vérifiées
- `.planning/research/PITFALLS.md` — **Background Sync iOS Safari non supportée (à relire Phase 2)**, contraintes upload mobile

### Schéma DB (Phase 1, déjà migré)
- `database/migrations/2026_05_28_000001_create_clients_table.php` — table `clients` (uuid, name, email, phone, magic_link_token, magic_link_expires_at)
- `database/migrations/2026_05_28_000002_create_piscines_table.php` — table `piscines` (client_id FK, volume_m3, type, filtration, traitement, equipements JSON)
- `database/migrations/2026_05_28_000005_create_passages_table.php` — table `passages` (`client_uuid` UUID unique, `piscine_id`, `client_id`, mesures, actions JSON, notes, `pdf_path`, `signature_path`, `synced_at`, `status` default 'draft')
- `database/migrations/2026_05_28_000006_create_photos_meta_table.php` — table `photos_meta` (passage_id FK, disk default 'r2', path, mime, size, dimensions, captured_at) — **TODO Phase 2 : ajouter `client_uuid` UUID UNIQUE pour idempotence par photo (D-42)**

### Mémoire utilisateur (background)
- `/Users/amnesia/.claude/projects/-Users-amnesia-dev-dloazur/memory/pierre-device-platform.md` — **Pierre device inconnu, *pas* iPhone par défaut. PWA cross-platform iOS+Android requise (D-36, D-37)**
- `/Users/amnesia/.claude/projects/-Users-amnesia-dev-dloazur/memory/pierre-statut-fiscal.md` — auto-entrepreneur, franchise en base TVA (impact Phase 3 facturation, pas Phase 2)
- `/Users/amnesia/.claude/projects/-Users-amnesia-dev-dloazur/memory/brand-identity.md` — palette extraite (azure #0080ff, marine, turquoise, Fredoka)

### Documentation externe à consulter (researcher)
- vite-pwa-org.netlify.app/frameworks/laravel — intégration Laravel (`buildBase`, header SW)
- vite-pwa-org.netlify.app/guide/inject-manifest et /generate-sw — comparaison modes
- webkit.org/blog/14403/updates-to-storage-policy — `storage.persist()` iOS standalone
- developer.mozilla.org `BackgroundSyncPlugin` Workbox
- github.com/cesargb/laravel-magiclink — `lifetime`, `numMaxVisits`, `protectWithAccessCode`
- learn.microsoft.com SafeLinks proxy GET — justifie page `/auth/confirm` (D-50)
- cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html — anti-énumération (D-52)
- jakearchibald.com idb v8 `DBSchema` API — typage TypeScript des stores (D-59)
- github.com/alexcorvi/heic2any — décode HEIC WASM (D-43)

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets (Phase 1 livré)
- **Layouts admin** : `resources/views/admin/` + `resources/views/layouts/` (shell pré-câblé Phase 1, sidebar avec Clients/Passages **grisés** → à activer en Phase 2)
- **Routes** : `routes/admin.php` (admin), `routes/web.php`, `routes/vitrine.php`, `routes/blog.php` — pattern de partition établi, ajouter `routes/portail.php` pour le portail client + `routes/api.php` pour l'endpoint passages
- **Auth Fortify** : `app/Providers/FortifyServiceProvider.php` (pro auth opérationnelle — magic link client à brancher en parallèle via `cesargb/laravel-magiclink` qui ne dépend pas de Fortify)
- **Models existants** : `app/Models/Client.php`, `Piscine.php`, `Passage.php`, `PhotoMeta.php` (squelettes Eloquent prêts, factories `database/factories/*` également)
- **Composants Livewire existants** : `app/Livewire/ContactForm.php` + `app/Livewire/GoogleReviews.php` (pattern Livewire 3 établi pour les pages back-office)
- **Tests Pest existants** : `tests/Feature/AuthLoginTest.php`, `PierreSeederTest.php`, `AdminShellTest.php` — pattern Pest 4 établi

### Established Patterns
- **Stack Tailwind 4 @theme** : tokens dans `resources/css/app.css` (Phase 1 D-05) — réutiliser pour les variantes mobile de saisie
- **Brand colors verrouillés** : azure-500 (#0080ff), navy marine, turquoise lagon, sand — OKLCH partout
- **Touch targets ≥ 44px** : non négociable mobile (PRODUCT.md Accessibility), critique pour la saisie sur smartphone
- **Mockup-driven** : Phase 1 a établi le pattern "mockup → Blade 1:1, pas de redesign". À répliquer pour `passage.html`, `dashboard.html`, `portail.html`.
- **Migration pattern** : `database/migrations/2026_05_28_*` numérotation date-jour, snake_case, FK contraintes explicites — suivre pour `add_client_uuid_to_photos_meta`

### Integration Points
- **Sidebar admin** (`resources/views/components/admin/`) : items Clients/Passages actuellement grisés (D-18 Phase 1) — Phase 2 les active. Garder le pattern "grisé = mention 'bientôt'" pour Factures/Catalogue (Phase 3+).
- **`app/Livewire/`** : nouveaux composants pour CRUD clients/piscines (CLI-01..03) + liste passages pro (PASS-05). Pas Livewire pour la saisie passage (CF-02).
- **`resources/views/passage/`** : nouveau dossier pour saisie passage Alpine + IDB. Pas de Livewire ici.
- **`config/filesystems.php`** : disk `r2` configuré Phase 1 — réutiliser tel quel pour `photos_meta`.
- **`vite.config.js`** : Phase 1 a configuré Vite. Phase 2 ajoute `vite-plugin-pwa` config (`registerType: 'prompt'`, `workbox.runtimeCaching`, `manifest`).
- **`app/Models/Passage.php`** : ajouter mutator/accessor pour `client_uuid` (auto-generate côté API si null reçu).
- **Brevo mail** (D-15 Phase 1) : driver `MAIL_MAILER=brevo` déjà configuré — magic link emails passent par ce canal sans config supplémentaire.

</code_context>

<specifics>
## Specific Ideas

- **Mapping mockup → Blade obligatoire 1:1** : `mockups/v1/passage.html` (saisie offline), `mockups/v1/dashboard.html` (shell + liste passages + badge sync), `mockups/v1/portail.html` (portail client). Pas de redesign. Composants Alpine pour passage, Livewire/Blade pour le reste.
- **Badge "N passages en attente"** : dans la top bar du dashboard admin, à droite de la nav. Couleur warning si > 0, neutre si 0. Tap → ouvre un drawer listant les passages en queue avec statut individuel (`pending`/`error`/`uploading`).
- **Magic link UX client** : email avec lien → page `/auth/confirm?token=...` (HTML statique sobre, logo Dlo Azur, texte "Vous êtes sur le point d'accéder à votre espace Dlo Azur Piscines" + bouton vert "Confirmer ma connexion" + petit texte sécurité "Ce lien expire dans 48h"). POST consomme. Redirect vers `/portail/passages`.
- **Photos lightbox portail client** : navigation au swipe sur mobile + flèches sur desktop. Métadonnées affichées en overlay : date passage, position dans le set (1/5, 2/5...).
- **Validation mesures soft** : champs `<input type="number" inputmode="decimal" step="0.01">`. Warning toast si hors plage soft (D-63). Pas de bloquage de submit.
- **`offline.html`** : page minimale précachée affichée si l'utilisateur tape dans la barre d'adresse une route non cachée hors-ligne. Logo + message "Vous êtes hors-ligne — La saisie d'un passage reste disponible." + bouton "Retour à la saisie".

</specifics>

<deferred>
## Deferred Ideas

- **Push notifications** → Phase 4 (NOTIF-*). `injectManifest` mode SW pourrait devenir nécessaire à ce moment-là (D-55 retient `generateSW` pour Phase 2 simplicité).
- **Recherche full-text Postgres clients** → si Pierre dépasse 100 clients, ré-évaluer (D-61). Pour ~10 clients, ILIKE suffit.
- **Filtres avancés portail client** → date range + recherche dans notes (D-65). Volume trop faible v1.
- **WhatsApp pour envoi compte-rendu / rappel** → Phase 4 (NOTIF-03). Templates Business à demander avant.
- **Multi-piscines par client en UI** → v2 (CLI-04 déféré dans REQUIREMENTS.md). Modèle déjà flexible (D-64 auto-pick si 1 piscine).
- **Détection de doublons en amont (pré-submit)** → Hash de payload Alpine côté client pour éviter double-tap "Envoyer". Mais le `client_uuid` côté serveur (D-38) couvre le cas. Si l'UX est confusante on pourra ajouter en optimisation.
- **Mode "draft auto-save"** → sauvegarder en IDB après chaque champ (pas seulement au submit). Le mockup `passage.html` peut le suggérer, à clarifier au researcher.
- **Téléchargement ZIP de l'historique côté portail** → v2 / sur demande client. Hors scope v1.

</deferred>

---

*Phase: 2-MVP Suivi Offline-First*
*Context gathered: 2026-05-28*
*Source corrections: Pierre device = inconnu (pas iPhone) — D-36, D-37 amendent ROADMAP-02 SC#2*

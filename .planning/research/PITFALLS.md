# Pitfalls Research

**Domain:** Application Laravel offline-first (PWA) — suivi passages terrain + portail client + facturation/Odoo + diagnostic piscine. Martinique, opérateur seul.
**Researched:** 2026-05-27
**Confidence:** HIGH pour PWA/iOS et Odoo ; MEDIUM pour TVA DOM et RGPD ; MEDIUM pour responsabilité diagnostic

---

## Critical Pitfalls

### Pitfall 1 : Background Sync API absent sur iOS Safari — les photos ne partent pas sans l'app ouverte

**What goes wrong:**
L'opérateur saisit 5 passages offline dans la journée. En rentrant chez lui il ferme l'onglet Safari. Les photos et données restent coincées dans IndexedDB. La Background Sync API (SyncManager) n'existe pas sur iOS/Safari en 2026 — ni pour Safari browser ni pour PWA installée. La synchronisation ne se déclenche que si l'app est en premier plan.

**Why it happens:**
Les guides PWA "offline-first" montrent des exemples avec `registration.sync.register()` qui fonctionne sur Chrome Android. Les développeurs testent sur Chrome desktop ou Android et considèrent la feature terminée. L'opérateur, lui, est sur iPhone.

**How to avoid:**
- Détecter l'absence de Background Sync par feature detection (`'SyncManager' in window`) et basculer sur une stratégie de rattrapage manuelle.
- Stratégie de rattrapage iOS : écouter `visibilitychange` (app revient au premier plan) + `online` event + `focus` event pour déclencher la synchro de la queue IndexedDB.
- Afficher un badge/indicateur persistant dans l'UI tant qu'il y a des passages non synchronisés ("3 passages en attente de sync").
- Ne jamais dépendre silencieusement de Background Sync — traiter le cas iOS comme le cas nominal.

**Warning signs:**
- Tests effectués uniquement sur Chrome ou Android.
- Aucun test sur iPhone Safari avec l'app fermée entre deux sessions.
- Code qui appelle `registration.sync.register()` sans vérification de support.

**Phase to address:** Phase offline-first / saisie de passage (le premier sprint qui implémente la queue offline).

---

### Pitfall 2 : Éviction silencieuse du stockage IndexedDB iOS — données perdues sans avertissement

**What goes wrong:**
Safari (iOS) évince les données IndexedDB selon une politique "least-recently-used" si le système est sous pression de stockage, ou si l'origine n'a pas été interagie depuis "un certain temps" (cap de 7 jours confirmé pour les scripts-writable storage sur les sites non-installés comme Home Screen App). Un passage saisi le vendredi peut disparaître si l'opérateur n'a pas rouvert l'app avant le mercredi suivant.

**Why it happens:**
iOS traite les données web comme de la cache purgeable par défaut. Sans appel explicite à `StorageManager.persist()` (qui peut être accordé pour les PWA installées en Home Screen depuis iOS 17), tout peut être effacé.

**How to avoid:**
- Appeler `navigator.storage.persist()` dès le premier lancement de l'app et à chaque passage créé. Vérifier `navigator.storage.persisted()` et alerter l'utilisateur si refusé.
- Instruire l'opérateur d'installer la PWA en Home Screen (améliore fortement les chances d'obtenir `persist()` accordé automatiquement par WebKit).
- Implémenter un timestamp "last sync" visible dans l'UI : "Dernière synchro : il y a 2h — 0 passage en attente".
- Quota Safari iOS 17+ : 15% du disque par origin. Un iPhone à 64 Go peut théoriquement stocker ~9 Go, mais ce n'est pas garanti en pratique si l'appareil est quasi-plein.

**Warning signs:**
- Aucun appel à `StorageManager.persist()` dans le code.
- Aucun test "ne pas ouvrir l'app pendant 8 jours".
- L'opérateur utilise Safari browser plutôt que la PWA installée.

**Phase to address:** Phase offline-first / setup du Service Worker (dès le début de l'implémentation offline).

---

### Pitfall 3 : Doublons à la synchronisation — même passage créé deux fois côté serveur

**What goes wrong:**
L'opérateur crée un passage offline, rentre en zone réseau, l'app tente la synchro, le réseau coupe à mi-upload. L'app ne sait pas si le serveur a enregistré le passage ou non. Elle retente. Le serveur crée un deuxième passage identique. L'historique client montre deux interventions le même jour avec les mêmes mesures.

**Why it happens:**
L'opérateur est en Martinique sur un réseau mobile "hasardeux". Les timeouts partiels sont fréquents. Sans idempotence côté serveur, toute retry crée un doublon.

**How to avoid:**
- Générer un UUID v4 côté client au moment de la création de chaque passage (dans Alpine.js, avant même de stocker dans IndexedDB). Ce UUID devient la clé d'idempotence.
- Le endpoint POST `/passages` Laravel vérifie `unique(passage_uuid)` avant d'insérer. Si le UUID existe déjà → retourner 200 avec le passage existant (pas 409).
- La queue offline stocke `{ uuid, payload, synced: false }`. Après synchro réussie, marquer `synced: true`.
- Ne jamais réutiliser un UUID. Ne jamais retirer l'entrée IndexedDB avant confirmation serveur.

**Warning signs:**
- Endpoint POST `/passages` sans vérification d'idempotence.
- UUID généré côté serveur plutôt que client.
- Aucun test "couper le réseau pendant l'upload".

**Phase to address:** Phase offline-first / API de synchro (conception du endpoint dès le début).

---

### Pitfall 4 : Photos volumineuses saturant IndexedDB et le réseau Martinique

**What goes wrong:**
Un iPhone 15 Pro produit des photos de 8–15 Mo par défaut (HEIC/JPEG haute résolution). L'opérateur prend 3–4 photos par passage. Un seul passage = 30–60 Mo dans IndexedDB. Sur 5 passages offline = potentiellement 300 Mo à synchroniser sur un réseau 3G/4G capricieux. Les uploads échouent systématiquement. IndexedDB se remplit.

**Why it happens:**
Le projet impose des "photos systématiques" mais ne contraint pas leur taille. Les développeurs testent avec des photos de 200 Ko sur leur connexion fibre.

**How to avoid:**
- Compresser et redimensionner **avant** stockage dans IndexedDB, pas avant upload. Utiliser `canvas.toBlob()` avec quality 0.75 et max 1200px wide — cible < 300 Ko par photo.
- Stocker en IndexedDB sous forme de Blob compressé, pas en base64 (base64 gonfle de 33% la taille).
- Limiter à 4 photos par passage maximum (contrainte métier acceptée côté opérateur).
- Uploader les photos séparément des données texte : d'abord les données du passage (léger), puis les photos en arrière-plan. L'affichage côté client est possible immédiatement.
- Implémenter un retry progressif avec backoff exponentiel pour les uploads photo.

**Warning signs:**
- Aucune compression côté client visible dans le code.
- Upload des photos en base64 JSON dans le body de la requête.
- Tests sans simulation réseau lent (Network throttling : Slow 3G).

**Phase to address:** Phase offline-first / saisie passage (contrainte de compression dès la spec de l'interface de capture).

---

### Pitfall 5 : Odoo API externe indisponible — intégration bloquée en découvrant le plan tarifaire tard

**What goes wrong:**
L'intégration Odoo est développée via XML-RPC/JSON-RPC (l'API externe officielle). Au moment de la mise en production ou du test de connexion, on découvre que le plan Odoo de l'opérateur est Standard ou Free — l'API externe est **strictement réservée au plan Custom** (29,90 €/user/mois minimum, confirmé documentation officielle). L'intégration entière doit être réarchitecturée vers un pont CSV manuel.

**Why it happens:**
La documentation Odoo parle de son API comme d'une feature standard, sans toujours mettre en avant la restriction de plan. Les développeurs supposent que "Odoo Online" = accès API.

**How to avoid:**
- **POC d'abord, toujours** : avant d'écrire une ligne de code d'intégration, vérifier le plan Odoo de l'opérateur par un appel test XML-RPC (`xmlrpc.client` Python, 5 minutes de test).
- Prévoir les deux branches architecturales dès la spec :
  - Branche A (plan Custom) : intégration API bidirectionnelle.
  - Branche B (plan Standard/Free) : génération CSV Laravel → import manuel Odoo. Le format CSV d'import Odoo factures est documenté.
- Ne pas démarrer le développement de la branche A avant validation du POC.
- Budget réaliste : si l'opérateur est sur le plan Free (1 app), le plan Custom est 29,90 €/mois — à inclure dans l'analyse coût.

**Warning signs:**
- Début du développement d'intégration API sans test de connexion sur l'instance Odoo réelle.
- Aucune mention de "pont CSV" dans les specs de la phase facturation.
- Hypothèse implicite que l'API est disponible.

**Phase to address:** Phase facturation — **1er ticket, avant tout développement** : POC de connexion Odoo.

---

### Pitfall 6 : TVA DOM incorrecte sur les factures — taux continental appliqué par défaut

**What goes wrong:**
Le développeur configure Laravel avec le taux de TVA français standard (20%). Toutes les factures générées comportent 20% de TVA. En Martinique (DOM), le taux normal est **8,5%** et le taux réduit est **2,1%**. Les factures sont fiscalement incorrectes. L'opérateur découvre l'erreur lors de sa déclaration de TVA ou lors d'un contrôle fiscal.

**Why it happens:**
Les packages de facturation Laravel sont souvent configurés par défaut sur les taux métropolitains. La distinction DOM n'est pas documentée dans les packages génériques.

**How to avoid:**
- Coder explicitement les taux DOM dans la configuration : `TAX_RATE_NORMAL=8.5`, `TAX_RATE_REDUCED=2.1`.
- Ne jamais utiliser un package de facturation avec des taux "auto" non vérifiés.
- Mentionner sur chaque facture le taux appliqué et la base légale si nécessaire.
- Faire valider une facture test par un comptable local martiniquais avant mise en production.
- La TVA DOM s'applique aux opérations réalisées en Martinique par un assujetti martiniquais à destination de clients martiniquais — c'est le cas nominal du projet.

**Warning signs:**
- Taux TVA en dur à 20% dans le code ou la config.
- Aucun test avec un comptable ou expert-comptable local.

**Phase to address:** Phase facturation / génération PDF (config avant tout développement de facturation).

---

### Pitfall 7 : Numérotation de factures non-séquentielle — illégalité fiscale

**What goes wrong:**
Les factures sont numérotées avec un identifiant interne (ID base de données auto-incrémenté), avec des trous (factures annulées, brouillons supprimés), ou réinitialisées après une erreur. L'article 242 nonies A du CGI impose une séquence chronologique **continue et sans trou**. Des trous dans la numérotation sont un motif de rejet lors d'un contrôle fiscal.

**Why it happens:**
Utiliser l'ID PostgreSQL comme numéro de facture est tentant. Les factures brouillon annulées créent naturellement des trous si elles sont supprimées.

**How to avoid:**
- Numéro de facture = séquence dédiée, séparée de l'ID base de données. Format recommandé : `YYYYMM-NNNN` (ex. `202601-0001`).
- Les brouillons n'ont pas de numéro. Le numéro est attribué au moment du `Confirmer` (état `draft → posted`), jamais avant.
- Une facture confirmée ne se supprime jamais — elle s'annule avec un avoir (avoir = nouvelle facture en négatif avec son propre numéro séquentiel).
- Implémenter un lock transactionnel lors de l'attribution du numéro pour éviter les doublons en cas de race condition.

**Warning signs:**
- Colonne `invoice_number` remplie à la création du brouillon.
- Suppressions de factures dans la base de données.
- Séquence basée sur `id` de la table.

**Phase to address:** Phase facturation / modèle de données (avant migration initiale).

---

### Pitfall 8 : Magic link — token à longue durée de vie ou réutilisable

**What goes wrong:**
Le client reçoit un magic link valable 24h ou 7 jours. Le lien est intercepté dans un email d'entreprise scanné par un outil de sécurité (Outlook Safe Links, etc.) qui clique le lien pour vérification — consommant le token. Le client clique ensuite son lien et reçoit "lien invalide ou expiré". Ou inversement : un lien valable 7 jours intercepté donne un accès prolongé si le compte email est compromis.

**Why it happens:**
Les développeurs veulent éviter les frictions (le client doit pouvoir ouvrir son link le lendemain). Ils ne testent pas les scanners d'email professionnels.

**How to avoid:**
- Expiration : **15–30 minutes maximum** pour un accès authentifiant.
- Usage unique strict : invalider immédiatement après le premier clic valide (flag `used_at` en base, pas de soft delete).
- Pour le cas "scanner email" : utiliser une page intermédiaire qui demande une confirmation explicite avant de consommer le token (clic sur un bouton "Ouvrir mon espace"), pas une redirection automatique.
- Hasher le token en base (bcrypt ou SHA-256) — stocker uniquement le hash, jamais le token en clair.
- Notifier le client par email si une connexion via magic link est détectée depuis une IP inconnue.
- Rate limiting : max 3 demandes de magic link par heure par email.

**Warning signs:**
- Expiration > 30 minutes.
- Token stocké en clair dans la base de données.
- Pas de page de confirmation intermédiaire.
- Aucun test avec Outlook ou Gmail (qui prévisualisent les liens).

**Phase to address:** Phase auth client / portail client (dès l'implémentation du magic link).

---

### Pitfall 9 : Photos terrain soumises au RGPD — piscines privées = données personnelles

**What goes wrong:**
Les photos prises à chaque passage montrent la piscine, potentiellement l'extérieur de la maison, parfois des personnes. Ces photos constituent des données personnelles liées à un client identifié. Hébergées sur un bucket Scaleway Paris → conforme EU, mais sans politique de conservation définie ni procédure de suppression → violation du principe de minimisation des données (article 5 RGPD).

**Why it happens:**
On pense aux données structurées (nom, email) mais pas aux photos comme données personnelles. Le projet est small-scale → sentiment de disproportion des contraintes RGPD.

**How to avoid:**
- Définir en base une durée de conservation : recommandation — 3 ans après la dernière intervention (correspond à la prescription courte pour les contrats de services). Implémenter un job de purge automatique.
- Mentions légales et CGU à jour : indiquer explicitement la collecte de photos, leur finalité (preuve de passage, compte-rendu), leur durée de conservation.
- Scaleway Paris est conforme EU RGPD — maintenir cette contrainte d'hébergement, ne pas migrer vers AWS S3 us-east-1 par commodité.
- Accès limité : seul l'opérateur et le client propriétaire peuvent voir les photos. Pas de partage public par URL directe sans signature temporaire (pre-signed URLs avec expiration).
- Procédure de droit à l'oubli : script de suppression par client_id (photos + données passage).

**Warning signs:**
- Aucun TTL ou date de péremption sur les objets Scaleway.
- URLs de photos publiques non signées.
- Absence de mention de photos dans la politique de confidentialité.

**Phase to address:** Phase portail client / photos (dès l'implémentation du stockage photos), + mention dans l'onboarding légal.

---

### Pitfall 10 : Diagnostic piscine — responsabilité juridique sur les conseils de dosage

**What goes wrong:**
Le wizard diagnostic calcule "Ajoutez 1,2 kg de chlore choc" et l'utilisateur (grand public non professionnel) suit la recommandation de façon incorrecte (mauvaise dilution, ajout au mauvais moment). Accident. L'application est citée comme source de la recommandation. En France, donner des conseils de dosage chimique sans disclaimer clair expose à une responsabilité civile délictuelle.

**Why it happens:**
L'outil est perçu comme un "calculateur" neutre, pas comme un conseil professionnel. La distinction n'est pas claire juridiquement si l'application se présente comme un service de diagnostic.

**How to avoid:**
- Disclaimer obligatoire à l'entrée du wizard : "Ce diagnostic est un outil d'aide, non un conseil professionnel. Les manipulations chimiques doivent être effectuées par un professionnel ou sous sa supervision. Consultez un pisciniste agréé avant tout traitement."
- Ne pas calculer des doses "à l'unité près" pour le grand public sans contexte (qualifier les recommandations : "environ X kg, à faire confirmer par votre pisciniste").
- CGU / mentions légales : clause de limitation de responsabilité explicite sur les calculs de dosage.
- Si le diagnostic est commercialisé (Stripe), les CGV doivent inclure cette limitation.
- Pour la version B2B (pisciniste abonné), le disclaimer peut être allégé car l'utilisateur est un professionnel.
- Respecter la réglementation sur les concentrations maximales autorisées pour la vente au particulier (peroxyde d'hydrogène > 12%, acide sulfurique > 15% : vente aux particuliers restreinte).

**Warning signs:**
- Wizard sans disclaimer visible à l'entrée.
- Doses calculées sans fourchette ni avertissement.
- CGU absentes ou génériques sans clause sur les conseils chimiques.

**Phase to address:** Phase diagnostic (dès la spec du wizard, avant développement).

---

## Technical Debt Patterns

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| UUID serveur au lieu de client pour les passages | Simplicité, séquence propre | Doublons à la synchro offline, retry sans idempotence | Jamais pour une app offline-first |
| Numéro de facture = ID base de données | Zéro config | Non-conformité fiscale CGI | Jamais |
| Taux TVA hardcodé à 20% | Rapidité | Factures fiscalement incorrectes en DOM | Jamais |
| Photos stockées en base64 JSON | Pas de stockage objet à configurer | IndexedDB saturation, uploads lents, stockage 33% plus cher | Jamais pour photos terrain |
| Token magic link en clair en base | Simplicité | Compromission si dump base de données | Jamais |
| Pont CSV manuel Odoo sans POC initial | Développement API en avance | Relivraison complète si plan incompatible | Acceptable si POC confirme l'impossibilité API |
| Pas de `StorageManager.persist()` | Moins de complexité | Pertes de données silencieuses sur iOS | Jamais pour offline critique |

---

## Integration Gotchas

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| Odoo API | Supposer que l'accès API est inclus dans tout plan | POC de connexion XML-RPC sur l'instance réelle avant tout développement |
| Odoo API | Créer les contacts/produits à chaque facture | Maintenir une table de mapping `local_client_id → odoo_partner_id`, `local_service_id → odoo_product_id` |
| Odoo API | Pousser une facture directement en état `Posted` | Créer en `Draft`, valider manuellement dans Odoo (ou par `action_post()` avec le plan Custom) |
| Scaleway S3 | URLs publiques permanentes sur les photos clients | Pre-signed URLs avec expiration 1h, jamais d'URLs permanentes publiques |
| Magic link email | Lien qui consomme le token à l'ouverture (GET) | Page intermédiaire avec bouton de confirmation (POST pour consommer le token) |
| Alpine.js IndexedDB | Race condition si deux onglets ouverts simultanément | Vérouiller la queue de synchro avec un flag `syncing_in_progress` dans IndexedDB |

---

## Performance Traps

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| Photos non compressées dans IndexedDB | App lente, quota épuisé, uploads qui timeout | Compression canvas avant stockage, target < 300 Ko/photo | Dès le 3e passage offline avec photos |
| Chargement de tout l'historique des passages en une requête | Portail client lent avec clients de 2+ ans | Pagination côté serveur, lazy load par client | À partir de 50+ passages par client |
| Synchro séquentielle photo-par-photo | Upload prend 10 min sur réseau lent | Upload parallèle limité (max 2 simultanés) | Sur réseau < 1 Mbps |
| Requêtes N+1 sur l'historique (passages + mesures + photos) | Portail client lent | Eager loading avec `with(['mesures', 'photos'])` | Dès 10+ clients |

---

## Security Mistakes

| Mistake | Risk | Prevention |
|---------|------|------------|
| Token magic link en clair en base | Accès non autorisé si dump DB | Stocker SHA-256(token), comparer lors de la vérification |
| Pre-signed URLs Scaleway sans expiration | Photos clients accessibles indéfiniment par quiconque connaît l'URL | Expiration 1–4h, régénérer à chaque consultation |
| IDOR sur les passages : `/passages/{id}` sans vérification ownership | Client A accède aux passages du client B | Policy Laravel : `Gate::authorize('view', $passage)` — vérifier `$passage->client_id === auth()->id()` |
| Magic link cliquable depuis n'importe quelle IP | Lien email intercepté = accès direct | Invalider le token côté serveur immédiatement + page de confirmation intermédiaire |
| Formules de dosage exposées en clair dans le JS frontend | Clonage du wizard sans payer | Calculs côté serveur uniquement (API Laravel), jamais dans le JS Alpine |

---

## UX Pitfalls

| Pitfall | User Impact | Better Approach |
|---------|-------------|-----------------|
| Aucun indicateur d'état de synchro | L'opérateur ne sait pas si ses passages sont sauvegardés | Badge persistant : "N passages en attente" + dernière synchro réussie |
| Interface Livewire pour la saisie de passage | Formulaire inutilisable hors réseau | Alpine.js pur pour le formulaire de saisie, Livewire interdit sur cette page |
| Compression photo transparente sans feedback | L'opérateur pense que l'app est gelée pendant la compression | Indicateur de progression pendant la compression ("Optimisation de la photo...") |
| Magic link envoyé mais spam filtré | Le client ne reçoit jamais son lien | Email de test avec whitelisting SPF/DKIM, lien "renvoyer" visible |
| Formulaire de saisie qui vide les champs si l'app passe en arrière-plan | Perte du travail en cours | Sauvegarde automatique dans IndexedDB à chaque changement de champ (`input` event) |

---

## "Looks Done But Isn't" Checklist

- [ ] **Saisie offline** : fonctionne sur Chrome desktop → tester sur iPhone Safari avec mode avion activé PUIS app fermée puis rouverte.
- [ ] **Synchro** : tester le retry après timeout réseau à mi-upload (throttle to 0 puis restore).
- [ ] **Idempotence** : envoyer le même UUID de passage deux fois → vérifier qu'il n'y a qu'un seul enregistrement en base.
- [ ] **TVA** : vérifier qu'une facture générée affiche 8,5% et non 20%.
- [ ] **Numérotation factures** : créer un brouillon, l'annuler, créer une vraie facture → vérifier qu'il n'y a pas de trou dans la séquence.
- [ ] **Magic link** : cliquer deux fois le même lien → le second doit être invalide.
- [ ] **Magic link** : lien envoyé à une adresse Gmail — vérifier que Google Preview ne consomme pas le token.
- [ ] **Photos RGPD** : URL directe Scaleway → doit retourner 403 sans token signé.
- [ ] **Odoo** : tenter une connexion XML-RPC sur l'instance Odoo réelle avant tout développement d'intégration.
- [ ] **Diagnostic** : disclaimer visible AVANT le premier calcul, pas en bas de page.
- [ ] **`StorageManager.persist()`** : vérifier dans DevTools → Application → Storage que le mode est "Persistent" après installation PWA.

---

## Recovery Strategies

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| Doublons de passages post-synchro | MEDIUM | Script de déduplification par `passage_uuid` + interface admin de fusion |
| Odoo plan incompatible découvert tard | HIGH | Réarchitecturer vers pont CSV, documenter le format d'import Odoo, script d'export Laravel |
| Numérotation de factures trouée | HIGH | Émettre des avoirs pour les factures incorrectes, recommencer la séquence proprement, documenter auprès du comptable |
| Photos évincées iOS avant synchro | HIGH (données perdues) | Irrécouvrable — prévention obligatoire. Mitigation : `persist()` + indicateur de synchro |
| TVA erronée sur factures émises | HIGH | Rectificatif fiscal (avoir + nouvelle facture), notification clients |
| Token magic link compromis | MEDIUM | Invalider tous les tokens actifs du compte, notifier le client par email alternatif |

---

## Pitfall-to-Phase Mapping

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| Background Sync absent iOS | Phase offline-first — Service Worker setup | Test sur iPhone Safari mode avion + app fermée |
| Éviction IndexedDB iOS | Phase offline-first — Service Worker setup | `navigator.storage.persisted()` retourne `true` sur PWA installée |
| Doublons à la synchro | Phase offline-first — API endpoint | Test d'idempotence : POST même UUID deux fois → 1 seul enregistrement |
| Photos volumineuses | Phase offline-first — interface saisie passage | Photo uploadée < 300 Ko après compression |
| Odoo API plan restriction | Phase facturation — POC (1er ticket) | Connexion XML-RPC réussie OU décision pont CSV documentée |
| TVA DOM 8,5% | Phase facturation — modèle et config | Facture test = 8,5%, validée par comptable |
| Numérotation séquentielle | Phase facturation — modèle de données | Aucun trou après annulation de brouillon |
| Magic link sécurité | Phase auth client | Double-clic invalide + token hashé en base |
| Photos RGPD | Phase photos + portail client | Pre-signed URLs uniquement, TTL objet Scaleway |
| Diagnostic responsabilité | Phase diagnostic — spec wizard | Disclaimer visible au premier écran, CGU avec clause dosage |

---

## Sources

- WebKit Blog — Updates to Storage Policy (iOS 17 quota changes): https://webkit.org/blog/14403/updates-to-storage-policy/
- Smashing Magazine — Building An Offline-Friendly Image Upload System (avril 2025): https://www.smashingmagazine.com/2025/04/building-offline-friendly-image-upload-system/
- MDN — Offline and background operation / Background Sync API support: https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Guides/Offline_and_background_operation
- Apple Developer Forums — Background Sync PWA iOS support status: https://developer.apple.com/forums/thread/694805
- Odoo documentation — External API plan restriction (Custom only): https://www.odoo.com/forum/help-1/can-we-use-the-external-api-with-odoo-online-in-the-custom-plan-282627
- Odoo Official Pricing page: https://www.odoo.com/pricing
- Axonaut — TVA DOM-TOM Martinique taux 8,5% : https://axonaut.com/blog/facturation-tva-dom-tom/
- Legifrance — Article 242 nonies A annexe II CGI (numérotation factures): https://www.legifrance.gouv.fr/codes/id/LEGISCTA000006179228
- economie.gouv.fr — Mentions obligatoires facture: https://www.economie.gouv.fr/entreprises/gerer-son-entreprise-au-quotidien/gerer-sa-comptabilite-et-ses-demarches/mentions-obligatoires-dune-facture-tout-savoir
- Security Boulevard — Magic Links technical deep-dive (mai 2026): https://securityboulevard.com/2026/05/are-magic-links-secure-a-technical-deep-dive-into-email-based-authentication/
- CNIL — Durées de conservation des données: https://www.cnil.fr/fr/passer-laction/les-durees-de-conservation-des-donnees
- Legifrance — Réglementation produits chimiques piscine: https://www.legifrance.gouv.fr/jorf/id/JORFTEXT000043535364

---
*Pitfalls research for: Laravel offline-first PWA — suivi passages terrain + facturation Odoo + diagnostic piscine, Martinique*
*Researched: 2026-05-27*

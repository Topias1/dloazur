# Note de cadrage — Refonte Dlo Azur Piscines

**Version :** v2 (remplace v1 du 2026-05-27)
**Date :** 2026-05-27
**Statut :** design validé en séance — maquettes en cours via Claude Design — en attente (1) revue dev, (2) réponses Odoo du client avant Phase 1a

**Changements vs v1 :**
- §2 : ajout des décisions sur le maquettage (Claude Design) et la palette (eau/Caraïbes)
- §9 nouvelle : Maquettes & Design (référence au fichier `2026-05-27-dloazur-design-system.md`)
- §13 Prochaines étapes : intégration du travail de maquettage avant les writing-plans

---

## 1. Contexte

**Dlo Azur Piscines** (Martinique) — entretien/nettoyage de piscines & spas, **opérateur seul**. Pas de construction ni vente.

- **Existant :** site vitrine sur **Zyro** (builder Hostinger) — accueil, services, réalisations, blog, formulaire contact, réseaux. **Aucune fonctionnalité métier, pas d'espace client.**
- **Contact :** +596 696 94 00 54 · contact@dloazurpiscines.com
- **Demande initiale :** « refaire le site » + 2 lots métier (suivi des passages + facturation Odoo ; diagnostic piscine commercialisable).

**À propos de la maquette `diagnostic-dloazur.html` :** c'est une app **React compilée** (build single-file) qui prototypait déjà le diagnostic *et* un squelette d'espace client (`/api/me/visits`, `/api/clients`, auth cookie). **Le client n'en est pas satisfait → maquette jetée.** On en conserve uniquement **la logique métier du diagnostic** (arbre de décision, formules de doses, libellés de traitement) comme **spécification** pour réécrire propre (Phase 2).

---

## 2. Décisions verrouillées

| Décision | Choix | Raison |
|---|---|---|
| **Stack** | **Laravel 11 + Livewire + Alpine.js + Tailwind + PostgreSQL** | Fluence PHP du dev ; app CRUD/portail/facturation/SEO « batteries incluses » ; maintenance solo durable (on ne parie pas sur un assistant IA toujours dispo). |
| **Front** | Server-rendered (Livewire/Blade). **Pas de React, pas de SPA.** | Cohérent avec le stack ; React abandonné. |
| **Architecture** | **Monolithe** unique : vitrine + portail + admin + diagnostic | Un seul codebase, un seul déploiement. |
| **Multi-tenant** | **Non** (single-tenant) | Lot 2 ciblé A+B, pas de marque blanche (C) pour l'instant. |
| **Offline** | **Offline-first dès le MVP**, sur l'écran de saisie d'un passage uniquement | Réseau « hasardeux » en Martinique + photos systématiques → la saisie doit survivre sans réseau. |
| **Hébergement** | **Laravel Cloud, région EU (Francfort)** | Managé max, scale-to-zero, Postgres managé inclus, ~4-7 €/mois. |
| **Stockage photos** | **Scaleway Object Storage (Paris)** | Hébergeur européen, RGPD propre, coût négligeable. |
| **Maquettage** | **Claude Design** (claude.ai/design) | Conversationnel, applique un design system, handoff vers Claude Code. Pas de Figma. |
| **Palette visuelle** | **Eau / Caraïbes** — **bleu azur dominant** (`sky-500`, aligné au logo), turquoise en accent | Logo bleu azur + nom « Azur » ; détaillée dans `2026-05-27-dloazur-design-system.md`. |

---

## 3. Architecture

Monolithe Laravel découpé en modules. **Point clé :** Livewire fait un aller-retour serveur à chaque interaction → **inutilisable hors-ligne**. L'archi se dédouble donc proprement :

- **Online (Livewire / Blade)** — vitrine, dashboards pro, historique, espace client, facturation, diagnostic.
- **Offline-first (Alpine + JS + IndexedDB + Service Worker)** — **uniquement l'écran de saisie d'un passage** : persistance locale, file d'attente d'upload photos, synchronisation au retour réseau. Seule brique PWA « dure ».

| Module | Rôle | Accès |
|---|---|---|
| Vitrine | Pages marketing SEO local | Public |
| Auth & rôles | Pro (email + mot de passe) / Client (magic link) | — |
| Suivi passages | Saisie mobile offline-first + historique | Pro |
| Portail client | Lecture seule : passages, mesures, photos | Client |
| Facturation | Catalogue, contrats, factures, pont Odoo | Pro |
| Diagnostic | Wizard + calcul de doses, paiement Stripe | Public / freemium |

**Convention :** code & tables en anglais (idiomatique Laravel), **UI en français**.

---

## 4. Modèle de données

### Phase 0 (MVP suivi)
- **users** — `id, name, email, password (nullable), role enum(pro|client), client_id (FK→clients, nullable), timestamps`
  *Le pro = compte staff ; un client se connecte par magic link et pointe vers son enregistrement `clients`.*
- **clients** — `id, name, email, phone, address, notes, timestamps`
- **pools** — `id, client_id (FK), label, volume_m3, type enum(liner|coque|béton|autre), filtration enum(sable|verre|cartouche|poche), has_electrolyseur bool, equipment json (nullable), notes, timestamps`
- **visits** — `id, pool_id (FK), performed_at, ph, free_chlorine, tac, salt_ppm (nullable), water_temp (nullable), actions text, notes text, client_uuid (idempotence offline), created_by (FK→users), timestamps`
- **visit_photos** — `id, visit_id (FK), disk_path, original_name, uploaded_at` *(ou spatie/laravel-medialibrary)*

**Relations :** `Client 1—* Pool` · `Pool 1—* Visit` · `Visit 1—* VisitPhoto` · `User(client) *—1 Client`.
**Idempotence offline :** chaque passage reçoit un `client_uuid` généré côté téléphone → la re-synchro ne crée pas de doublon.

### Phase 1a (ajouts)
- **products** — catalogue facturable (`name, type service|produit, unit_price, vat_rate, odoo_product_id nullable`)
- **contracts** — `client_id, type enum(ponctuel|forfait_mensuel|forfait_saisonnier), price, start_at, end_at, status`
- **invoices** — `client_id, visit_id?, contract_id?, number, status enum(draft|sent|paid), total, pdf_path, odoo_invoice_id?, issued_at, paid_at`
- **invoice_lines** — `invoice_id, product_id, label, qty, unit_price, vat_rate`
- **signatures** — image de signature liée au passage (`visit_id, image_path, signed_at`)

### Phase 2 (ajouts)
- **diagnostics** — `user_id (nullable, anonyme permis), inputs json, decision_path json, recommendations json, created_at`
- **subscriptions** — via `laravel/cashier` (Stripe)
- Contenu du diagnostic (arbre + doses) = données de config/seed extraites de la maquette.

---

## 5. Roadmap

> Principe : **chaque phase est utilisable en production sans la suivante.**

### Phase V — Vitrine
Refonte de la vitrine dans le codebase Laravel, **remplace Zyro**. Pages : accueil, services, réalisations (galerie), blog, contact. SEO local Martinique, mobile-first, formulaire contact, liens réseaux/WhatsApp, CTA avis Google. Reprise du contenu/photos existants.

### Phase 0 — MVP Suivi (offline-first)
**On fait :** auth (pro mdp / client magic link) · clients + piscine (mono-piscine en UI) · **saisie d'un passage hors-ligne** (mesures pH/chlore/TAC/sel, actions, notes, **photos**) avec synchro au retour réseau · historique pro (filtres client/date) · espace client lecture seule.
**On ne fait pas :** facturation, Odoo, signature, planning, notifications métier (hors e-mail d'auth magic link), app native.

### Phase 1a — Facturation & Odoo
**POC Odoo en tout premier (2-3 j)** pour valider la viabilité selon sa version (voir §6). Puis : catalogue produits/services · contrats (ponctuel + forfait) · génération de facture à la clôture d'un passage → **Odoo (API)** *ou* **export CSV** selon le résultat du POC · récupération du statut de paiement · **compte-rendu PDF** auto · **signature client** sur le téléphone du pro.

### Phase 1b — Notifications *(réduit)*
Email compte-rendu après passage + rappel passage J-1, **option WhatsApp**.
*(Les rôles techniciens de la note v0.2 sont **supprimés** : le client travaille seul.)*

### Phase 2 — Diagnostic (commercialisation)
Réécriture propre du wizard « ma piscine est verte » : diagnostic enrichi, **calculs de doses selon le bassin**, plan d'action chiffré, suivi multi-mesures, **paiement Stripe**.
**Monétisation : pistes A + B** — vente directe particuliers (abonnement Stripe) **et** module premium en upsell sur ses clients d'entretien. **Piste C (marque blanche multi-tenant) écartée pour l'instant.**

---

## 6. Stratégie Odoo (critique pour 1a)

**Fait vérifié (doc officielle Odoo) :** *« Access to data via the external API is only available on Custom Odoo pricing plans. Access to the external API is not available on One App Free or Standard plans. »* L'API externe (XML-RPC) exige donc le plan **Custom (29,90 €/user/mois)** sur Odoo Online ; **Standard (19,90 €) et One App Free ne l'ont pas**. Odoo **Community auto-hébergé** a l'API gratuitement mais ajoute de l'ops (va contre « managé/simple »).

**Approche :** **POC en début de Phase 1a** pour trancher entre deux voies :
- **Voie A — API live (XML-RPC via package PHP)** si le client est sur **Custom** ou **Community/Enterprise** avec API : push facture (draft ou émise) + pull statut de paiement, temps réel.
- **Voie B — Pont sans API (CSV)** si le client reste sur Free/Standard : l'app génère les factures (source de vérité) et exporte en CSV → import manuel dans Odoo. Zéro surcoût Odoo.

**Source de vérité par défaut :** l'app (à confirmer avec lui — voir §11).

---

## 7. Hébergement & RGPD

- **Laravel Cloud** (GA) — région **EU / Francfort**. Plan Starter 0 €/mois + à l'usage, **scale-to-zero** (hibernation), **Postgres managé (Neon) inclus**. Coût réel attendu **~4-7 €/mois** vu le volume.
- **Nuance RGPD :** Laravel Cloud tourne sur **AWS** (maison-mère US) → post-Schrems II, transferts couverts par **SCCs** (fournies par AWS). Acceptable ici car données **peu sensibles** (noms, adresses, photos de piscines — pas de santé/finance). Si besoin d'un récit RGPD « zéro paperasse » plus tard : bascule vers hébergeur **européen** (Scaleway Paris / Hetzner DE) + Ploi.
- **Photos → Scaleway Object Storage (Paris)** : européen, RGPD propre, ~0,01 €/Go/mois.

---

## 8. Offline-first — approche technique

- **Périmètre minimal :** seul l'**écran de saisie d'un passage** est offline-first. Le reste (historique, dashboards) suppose le réseau.
- **Mécanique :** Service Worker (PWA installable sur smartphone) + **IndexedDB** pour stocker passage + photos en local + **file d'attente de synchro** rejouée au retour réseau.
- **Idempotence :** `client_uuid` par passage → pas de doublon à la re-synchro.
- **Conflits :** quasi inexistants (un seul opérateur, données en création seule) → pas de résolution de conflit complexe nécessaire.
- **Implémentation :** Alpine.js + JS vanilla pour cette brique (**pas Livewire**, qui exige le réseau).
- **Risque #1 à dérisquer tôt :** quotas IndexedDB et comportement du Service Worker sur **iOS Safari**. Tester sur le smartphone réel du client avant d'engager le module.

---

## 9. Maquettes & Design

**Outil retenu :** [Claude Design](https://claude.ai/design) (Anthropic Labs, lancé avril 2026). Conversationnel, applique un design system de manière cohérente, exporte vers PDF/PPTX/URL et fait un handoff propre vers Claude Code pour l'implémentation.

**Pourquoi pas Figma :** pas de bénéfice ici (dev solo, pas de designer dans l'équipe). Claude Design produit directement des maquettes implémentables en Tailwind, ce qui colle au stack.

**Design system :** détaillé dans `2026-05-27-dloazur-design-system.md` (palette eau/Caraïbes, typographie Plus Jakarta Sans + Inter, style sobre/aéré, photos réelles).

**Ordre d'attaque des maquettes :**

| Priorité | Écran | Justification |
|---|---|---|
| 1 | Saisie d'un passage (mobile) | Écran critique, valide l'ergo terrain |
| 2 | Accueil vitrine | Pose le ton public, en parallèle |
| 3 | Dashboard pro | Vue quotidienne du client |
| 4 | Espace client (lecture seule) | Justifie le travail Phase 0 |
| 5 | Auth (login pro + magic link) | Simple, à la fin |
| 6 | Reste vitrine (services, réalisations, blog, contact) | Itératif sur la base du #2 |

**Workflow :**
1. Coller le prompt initial (design system) dans Claude Design pour fixer la base visuelle.
2. Itérer en parallèle sur l'écran de saisie passage (#1) et l'accueil vitrine (#2).
3. Une fois ces deux références validées, dérouler le reste à la chaîne.
4. Quand le codebase Laravel sera initialisé (Phase V), pointer Claude Design sur le repo pour qu'il extraie automatiquement les tokens Tailwind et applique le design system aux nouveaux écrans.

---

## 10. Coûts indicatifs (récurrents)

| Poste | Coût |
|---|---|
| Hébergement Laravel Cloud EU | ~4-7 €/mois |
| Stockage photos Scaleway | quelques centimes/mois |
| Odoo — **si Voie A** (plan Custom, 1 user) | +29,90 €/mois |
| Odoo — **si Voie B** (CSV) | 0 € |
| Stripe (Phase 2) | ~1,5 % + 0,25 €/transaction (cartes EU) |
| Domaine | déjà possédé |

---

## 11. Questions restantes pour le client (bloquent Phase 1a)

- **Version exacte d'Odoo** : Community / Enterprise / Online (quel plan) / Odoo.sh ? *(détermine Voie A vs B)*
- **Prêt à fournir des credentials API techniques** (clé API) ?
- Factures **en draft** (validation manuelle) ou **émises directement** ?
- **Source de vérité** des contacts : l'app ou Odoo ?
- **Signature client** : systématique à chaque passage, ou ponctuelle ?

*(Déjà répondu : ~10 passages/semaine, ~dizaine de clients à l'année · travaille seul · facturation mix ponctuel/forfait 50-50 · smartphone uniquement · réseau hasardeux · photos systématiques · 1 piscine/client · Lot 2 cible A+B, pas C.)*

---

## 12. Hypothèses & risques

- **H1 :** Odoo probablement sur un plan **sans API** → prévoir la Voie B (CSV) comme défaut tant que le POC n'a pas tranché. **Ne pas découvrir au jour 3.**
- **H2 :** L'offline-first alourdit le MVP (Phase 0) — assumé, car un MVP online-only serait inutilisable sur le terrain.
- **H3 :** Contenu/photos de la vitrine récupérables depuis le site Zyro actuel (à confirmer, sinon shooting).
- **H4 :** RGPD — données peu sensibles, AWS+SCCs acceptable ; réévaluer si le périmètre de données change.
- **H5 :** IndexedDB + Service Worker sur iOS Safari — quotas et restrictions variables. POC à faire tôt sur le smartphone réel du client.
- **H6 :** Extraire l'arbre de décision et les formules de doses de la maquette React **avant de la jeter** — sinon spec perdue pour la Phase 2.

---

## 13. Prochaines étapes

1. **Revue de cette note v2** par le dev.
2. **Maquettes Claude Design** : produire les deux écrans de référence (saisie passage + accueil vitrine) en parallèle. Voir `2026-05-27-dloazur-design-system.md`.
3. **Extraction spec diagnostic** depuis `diagnostic-dloazur.html` (arbre de décision, formules) — à archiver avant que la maquette soit oubliée.
4. **Obtenir les réponses Odoo** du client (§11).
5. **Plan d'implémentation** (writing-plans) — commencer par **Phase V (vitrine)** puis **Phase 0 (MVP suivi)**.

---

*Sources vérifiées : [Odoo — External API (restriction plan Custom)](https://www.odoo.com/documentation/17.0/developer/reference/external_api.html) · [Odoo Pricing](https://www.odoo.com/page/pricing) · [Laravel Cloud Pricing](https://cloud.laravel.com/pricing) · [Laravel Cloud — régions EU & nuance RGPD/Schrems II](https://danubedata.ro/blog/laravel-cloud-alternatives-europe-2026) · [Best Laravel hosting 2026](https://benjamincrozat.com/best-laravel-hosting-providers) · [Claude Design — Anthropic Labs](https://www.anthropic.com/news/claude-design-anthropic-labs)*

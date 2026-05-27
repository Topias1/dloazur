# Research Summary — Dlo Azur Piscines

**Synthesized:** 2026-05-27 from STACK.md · FEATURES.md · ARCHITECTURE.md · PITFALLS.md
**Overall confidence:** HIGH (phases V, 0, 1b) · MEDIUM (1a — dépend POC Odoo ; 2 — dépend validation légale)

---

## Executive Summary

Dlo Azur est un outil métier de niche — pisciniste solo en Martinique — où la contrainte dominante est l'**offline-first sur la saisie terrain**. La résistance du réseau mobile local rend cette contrainte non-optionnelle : sans elle, l'outil est inutilisable. L'approche recommandée est un **monolithe Laravel** avec une séparation **stricte** entre les pages Livewire (connectées) et une **« PWA island » Alpine.js pure** pour la seule page de saisie de passage — les deux patterns sont incompatibles et ne doivent jamais se mélanger.

L'**intégration Odoo** est l'autre risque majeur. L'API externe est strictement réservée au plan **Custom** (29,90 €/user/mois) ; l'opérateur est probablement sur un plan sans accès. Toute la phase facturation doit être précédée d'un **POC XML-RPC** sur l'instance réelle (2-3 j max) avant d'écrire une ligne d'intégration. Prévoir les deux branches dès la spec via feature flag `ODOO_MODE=api|csv`.

Risques légaux/fiscaux précis et non-négociables : **TVA Martinique 8,5 %** (pas 20 %), **numérotation de factures séquentielle sans trou** (CGI art. 242 nonies A), **disclaimer obligatoire** avant le wizard de diagnostic chimique. Trois bloquants de phase, pas des finitions.

---

## Key Findings

### Stack (HIGH — versions vérifiées Packagist/npm)
- Laravel 11 + Livewire 3 + Alpine.js 3 + Tailwind 4 + PostgreSQL 16, sur Laravel Cloud EU (Francfort)
- `cesargb/laravel-magiclink` ^2.27 — seul package magic-link maintenu pour Laravel 11 (+ `laravel/fortify` pour l'auth pro)
- `spatie/laravel-medialibrary` ^11.22 + Scaleway S3 (Paris) — photos, collections Eloquent, URLs signées
- `obuchmann/odoo-jsonrpc` ^1.9 — successeur maintenu ; `laradoo` et `laravel-odoo-api` abandonnés
- `spatie/laravel-pdf` ^2.11 + DomPDF — PDF serverless (Browsershot incompatible Laravel Cloud)
- `laravel/cashier` ^16.5 — SCA/3DS `default_incomplete` conforme EU
- `vite-plugin-pwa` ^1.3 — nécessite `buildBase` custom + header `Service-Worker-Allowed`
- Tests : Pest 4 + GitHub Actions

### Features (HIGH)
- **Must have (V + 0)** : offline-first saisie passage, idempotence `client_uuid`, auth pro + magic link client, gestion clients/piscines, historique pro, portail client lecture seule, vitrine SEO
- **Should have (1a + 1b)** : catalogue + contrats + factures + Odoo, PDF + signature client, email + option WhatsApp
- **Différenciateurs** : signature client (absente des concurrents pool), localisation FR + TVA DOM, intégration Odoo (vs QuickBooks US), diagnostic « eau verte » avec doses contextualisées au volume du bassin, monétisation Stripe
- **Anti-features (motivées)** : pas de multi-techniciens, pas de routing/calendrier drag-and-drop, pas de scan bandelettes caméra, pas d'inventaire, pas de portail client en écriture, pas de multi-tenant, pas de chat in-app

### Architecture (HIGH)
- Shell Blade + Alpine vanilla sur `/passages/create` **uniquement** — aucun Livewire sur cette page (incompatible offline)
- Sync : Background Sync (Chromium) + **fallback `visibilitychange` + `online`** (iOS Safari) ; `photo_queue` IndexedDB séparée de la sync passage (retry par photo)
- Idempotence : UUID v4 généré navigateur avant stockage → `firstOrCreate(['client_uuid' => …])` serveur ; jamais supprimer l'entrée IndexedDB avant confirmation serveur
- Odoo : `app/Services/Odoo/` comme couche anti-corruption (ACL) — `OdooClient` + `OdooInvoiceService` ; bascule API/CSV localisée ici ; `invoices.odoo_id` nullable asynchrone ; séquence de numérotation factures séparée de l'`id` DB
- Ordre de construction : V (fondations + migrations complètes dès le départ) → 0 (MVP offline) → 1a (POC Odoo gate) → 1b → 2 (quasi-indépendante)

### Top pitfalls
1. **Background Sync absent iOS** → fallback `visibilitychange`/`online` + badge UI « N passages en attente » obligatoire
2. **Éviction IndexedDB iOS** → `navigator.storage.persist()` dès le 1er lancement + PWA installée en Home Screen (instruction onboarding)
3. **Doublons à la synchro** → UUID client-side + endpoint idempotent ; ne pas purger l'entrée locale avant confirmation
4. **Photos volumineuses** → compression `canvas.toBlob()` quality 0.75, max 1200px, cible < 300 Ko avant IndexedDB
5. **Odoo plan restriction** → POC avant tout dev, deux branches archi prévues dès la spec
6. **TVA DOM 8,5 %** + **numérotation CGI** → configurés avant la 1re facture (bloquants Phase 1a)
7. **Responsabilité diagnostic** → disclaimer dès le 1er écran ; calculs de doses **côté serveur uniquement** (jamais en JS exposé)

---

## Roadmap Implications

| Phase | Contenu | Risque | Note |
|---|---|---|---|
| **V — Vitrine + Fondations** | Infra (Laravel Cloud + S3 + auth) + vitrine (remplace Zyro) | Faible | Migrations complètes dès le départ (inclure `client_uuid`, `odoo_id`, `signature_path`) |
| **0 — MVP Terrain (offline-first)** | Saisie passage offline + photos + portail client magic link | **Le plus élevé** | À valider sur iPhone réel en Martinique avant de continuer |
| **1a — Facturation + Odoo** | **POC Odoo en 1er ticket**, catalogue, contrats, factures (TVA 8,5 %, numérotation), PDF, signature | Moyen | Toute l'archi dépend du POC ; basculer CSV si pas d'API |
| **1b — Notifications** | Email compte-rendu + rappel J-1, option WhatsApp | Faible | Réutilise les jobs de 1a ; ne pas bloquer sur l'approval WhatsApp Business |
| **2 — Diagnostic commercialisable** | Wizard + doses (serveur) + Stripe | Moyen | Standalone ; disclaimer légal dès l'écran 1 |

**Ordering rationale :** dépendances strictes infra → core value → facturation → notifications → diagnostic. POC Odoo = gate de 1a. Phase 2 indépendante sauf auth + fiche piscine (Phase 0).

---

## Research Flags (recherche-phase recommandée)
- **Phase 0** : validation terrain iOS Safari + réseau réel Martinique
- **Phase 1a** : spécificités révélées par le POC Odoo (version instance, format `account.move`)
- **Phase 2** : validation légale du disclaimer dosage chimique avant lancement commercial

Patterns standard (pas de recherche-phase) : Phase V (CRUD + SEO + déploiement), Phase 1b (Mailable + scheduler + Twilio).

---

## Gaps / à confirmer
- **Plan Odoo** de l'opérateur non confirmé → POC obligatoire avant Phase 1a
- **TVA 8,5 %** à valider par un comptable local avant les premières factures
- **PWA en Home Screen** requise pour `storage.persist()` → instruction d'onboarding obligatoire
- **Format CSV import Odoo** à documenter pendant le POC même si l'API est inaccessible
- **Templates WhatsApp Business** : approval 1-5 j → ne pas bloquer Phase 1b

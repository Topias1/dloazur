# Requirements: Dlo Azur Piscines

**Defined:** 2026-05-27
**Core Value:** L'opérateur enregistre chaque passage d'entretien sur le terrain de façon fiable (même sans réseau) et le client consulte l'historique de ses interventions.

## v1 Requirements

Périmètre du milestone initial. Chaque requirement est mappé à une phase (voir Traceability).

### Vitrine (SITE)

- [ ] **SITE-01**: Un visiteur voit une page d'accueil qui présente l'activité (hero, services, réalisations, confiance, CTA WhatsApp)
- [ ] **SITE-02**: Un visiteur consulte une page services détaillée
- [ ] **SITE-03**: Un visiteur parcourt une galerie de réalisations (photos)
- [ ] **SITE-04**: Un visiteur lit le blog (liste d'articles + article)
- [ ] **SITE-05**: Un visiteur envoie un message via un formulaire de contact
- [ ] **SITE-06**: Un visiteur contacte l'entreprise via WhatsApp en un tap
- [x] **SITE-07**: Les pages publiques sont optimisées SEO local Martinique (balises meta, sitemap, données structurées)

### Authentification (AUTH)

- [ ] **AUTH-01**: Le pro se connecte avec email + mot de passe
- [ ] **AUTH-02**: Le client se connecte via magic link (sans mot de passe)
- [ ] **AUTH-03**: La session persiste entre les visites et la déconnexion est possible
- [ ] **AUTH-04**: Le magic link passe par une page de confirmation intermédiaire (sécurité : usage unique, expiration)

### Clients & Piscines (CLI)

- [ ] **CLI-01**: Le pro crée et modifie une fiche client (nom, contact, adresse, notes)
- [ ] **CLI-02**: Le pro enregistre la piscine d'un client (volume, type, filtration, équipements)
- [ ] **CLI-03**: Le pro recherche et filtre ses clients

### Suivi des passages (PASS)

- [ ] **PASS-01**: Le pro saisit un passage sur smartphone (mesures pH / chlore libre / TAC / sel, actions menées, notes)
- [ ] **PASS-02**: Le pro ajoute une ou plusieurs photos à un passage
- [ ] **PASS-03**: La saisie d'un passage fonctionne hors-ligne et se synchronise au retour réseau, sans créer de doublon (idempotence `client_uuid`)
- [ ] **PASS-04**: Les photos s'envoient via une file d'attente résiliente (retry par photo, compression avant stockage)
- [ ] **PASS-05**: Le pro consulte l'historique des passages avec filtres (client, date)
- [ ] **PASS-06**: La PWA indique le nombre de passages en attente de synchronisation

### Portail client (PORT)

- [ ] **PORT-01**: Le client voit l'historique de ses passages en lecture seule
- [ ] **PORT-02**: Le client voit pour chaque passage les mesures, les photos et les notes du pro

### Facturation (FACT)

- [ ] **FACT-01**: Le pro gère un catalogue de produits/services facturables
- [ ] **FACT-02**: Le pro gère des contrats client (ponctuel / forfait mensuel / forfait saisonnier)
- [ ] **FACT-03**: Le pro génère une facture à la clôture d'un passage, en franchise de TVA (mention obligatoire « TVA non applicable, art. 293 B du CGI », pas de colonne TVA — Pierre est auto-entrepreneur) et numérotation séquentielle conforme (CGI art. 242 nonies A)
- [ ] **FACT-04**: La facture est poussée vers Odoo (API XML-RPC) **ou** exportée en CSV selon le plan Odoo (déterminé par le POC)
- [ ] **FACT-05**: Le statut de paiement est récupéré (Odoo) et affiché au client
- [ ] **FACT-06**: Un compte-rendu PDF est généré après chaque passage
- [ ] **FACT-07**: Le client signe électroniquement le passage sur le téléphone du pro

### Notifications (NOTIF)

- [ ] **NOTIF-01**: Le client reçoit le compte-rendu par email après un passage
- [ ] **NOTIF-02**: Le pro/client reçoit un rappel de passage à J-1 par email
- [ ] **NOTIF-03**: Option WhatsApp pour l'envoi du compte-rendu / rappel

### Diagnostic commercialisable (DIAG)

- [ ] **DIAG-01**: Un visiteur lance un wizard de diagnostic « ma piscine est verte » (arbre de décision : eau verte / trouble / marron, électrolyseur…)
- [ ] **DIAG-02**: Le diagnostic calcule des doses selon le volume du bassin (**calcul côté serveur**) et produit un plan d'action chiffré
- [ ] **DIAG-03**: Un disclaimer légal s'affiche avant tout conseil de dosage chimique
- [ ] **DIAG-04**: Monétisation via Stripe — abonnement particulier (piste A) et module premium en upsell client (piste B)
- [ ] **DIAG-05**: L'utilisateur du diagnostic suit ses mesures dans le temps (multi-mesures)

## v2 Requirements

Reconnu mais différé — hors roadmap actuelle.

### Évolutions

- **STAT-01**: Tableau de bord statistiques / reporting avancé (CA, fréquence, consommables)
- **PORT-03**: Paiement en ligne de la facture par le client dans le portail
- **CLI-04**: Gestion de plusieurs piscines par client en UI (le modèle de données le permet déjà)

## Out of Scope

Exclusions explicites pour éviter le scope creep.

| Feature | Raison |
|---------|--------|
| Rôles techniciens / multi-opérateurs | L'opérateur travaille seul |
| Multi-tenant / marque blanche (piste C diagnostic) | Changerait la nature du projet ; cible A+B seulement |
| Application native (App Store / Play Store) | Une PWA suffit, évite l'instabilité et les stores |
| Routing GPS / calendrier de tournées drag-and-drop | Inutile pour un solo ~10 passages/semaine |
| Scan de bandelettes via caméra | Complexité/fiabilité disproportionnées |
| Inventaire / gestion de stock | Hors périmètre métier |
| Portail client en écriture (prise de RDV, édition) | Le portail reste en lecture seule |
| Chat in-app | WhatsApp couvre déjà le besoin |
| Construction / vente de piscines | Le métier est l'entretien uniquement |
| React / SPA | Stack server-rendered Laravel verrouillé |

## Traceability

Mapping finalisé par la roadmap. Chaque requirement → une phase.

| Requirement | Phase | Status |
|-------------|-------|--------|
| SITE-01 | Phase 1 — Vitrine & Fondations | Pending |
| SITE-02 | Phase 1 — Vitrine & Fondations | Pending |
| SITE-03 | Phase 1 — Vitrine & Fondations | Pending |
| SITE-04 | Phase 1 — Vitrine & Fondations | Pending |
| SITE-05 | Phase 1 — Vitrine & Fondations | Pending |
| SITE-06 | Phase 1 — Vitrine & Fondations | Pending |
| SITE-07 | Phase 1 — Vitrine & Fondations | Complete |
| AUTH-01 | Phase 1 — Vitrine & Fondations | Pending |
| AUTH-02 | Phase 2 — MVP Suivi Offline-First | Pending |
| AUTH-03 | Phase 2 — MVP Suivi Offline-First | Pending |
| AUTH-04 | Phase 2 — MVP Suivi Offline-First | Pending |
| CLI-01 | Phase 2 — MVP Suivi Offline-First | Pending |
| CLI-02 | Phase 2 — MVP Suivi Offline-First | Pending |
| CLI-03 | Phase 2 — MVP Suivi Offline-First | Pending |
| PASS-01 | Phase 2 — MVP Suivi Offline-First | Pending |
| PASS-02 | Phase 2 — MVP Suivi Offline-First | Pending |
| PASS-03 | Phase 2 — MVP Suivi Offline-First | Pending |
| PASS-04 | Phase 2 — MVP Suivi Offline-First | Pending |
| PASS-05 | Phase 2 — MVP Suivi Offline-First | Pending |
| PASS-06 | Phase 2 — MVP Suivi Offline-First | Pending |
| PORT-01 | Phase 2 — MVP Suivi Offline-First | Pending |
| PORT-02 | Phase 2 — MVP Suivi Offline-First | Pending |
| FACT-01 | Phase 3 — Facturation & Odoo | Pending |
| FACT-02 | Phase 3 — Facturation & Odoo | Pending |
| FACT-03 | Phase 3 — Facturation & Odoo | Pending |
| FACT-04 | Phase 3 — Facturation & Odoo | Pending |
| FACT-05 | Phase 3 — Facturation & Odoo | Pending |
| FACT-06 | Phase 3 — Facturation & Odoo | Pending |
| FACT-07 | Phase 3 — Facturation & Odoo | Pending |
| NOTIF-01 | Phase 4 — Notifications | Pending |
| NOTIF-02 | Phase 4 — Notifications | Pending |
| NOTIF-03 | Phase 4 — Notifications | Pending |
| DIAG-01 | Phase 5 — Diagnostic Commercialisable | Pending |
| DIAG-02 | Phase 5 — Diagnostic Commercialisable | Pending |
| DIAG-03 | Phase 5 — Diagnostic Commercialisable | Pending |
| DIAG-04 | Phase 5 — Diagnostic Commercialisable | Pending |
| DIAG-05 | Phase 5 — Diagnostic Commercialisable | Pending |

**Coverage:**
- v1 requirements: 37 total
- Mapped to phases: 37
- Unmapped: 0

---
*Requirements defined: 2026-05-27*
*Last updated: 2026-05-27 — traceability finalisée par roadmap*

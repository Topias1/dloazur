# Roadmap: Dlo Azur Piscines

## Overview

Cinq phases dérivent naturellement des requirements : une fondation vitrine + infra, le coeur de valeur offline-first, la facturation avec gate POC Odoo, les notifications, et le diagnostic commercialisable. L'ordre est imposé par les dépendances techniques — l'infra avant tout, le coeur de valeur avant la facturation, le diagnostic presque indépendant en dernier.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3) : travail planifié du milestone
- Decimal phases (2.1, 2.2) : insertions urgentes (marquées INSERTED)

- [ ] **Phase 1: Vitrine & Fondations** - Scaffold Laravel Cloud, auth pro, migrations complètes, vitrine SEO remplaçant Zyro
- [ ] **Phase 2: MVP Suivi Offline-First** - Saisie passage offline + photos, clients/piscines, portail client — coeur de valeur
- [ ] **Phase 3: Facturation & Odoo** - POC Odoo, catalogue, contrats, factures TVA 8,5 %, PDF, signature
- [ ] **Phase 4: Notifications** - Email compte-rendu + rappel J-1, option WhatsApp
- [ ] **Phase 5: Diagnostic Commercialisable** - Wizard eau verte, doses serveur, disclaimer, Stripe A+B

## Phase Details

### Phase 1: Vitrine & Fondations
**Goal**: Le site public est en ligne sur Laravel Cloud, l'opérateur peut se connecter, et toutes les migrations sont déployées — la vitrine remplace Zyro.
**Mode:** mvp
**Depends on**: Nothing (first phase)
**Requirements**: SITE-01, SITE-02, SITE-03, SITE-04, SITE-05, SITE-06, SITE-07, AUTH-01
**Success Criteria** (what must be TRUE):
  1. Un visiteur voit la page d'accueil (hero, services, réalisations, CTA WhatsApp) sur mobile et desktop
  2. Un visiteur consulte les pages services, galerie, blog et contact sans erreur
  3. Les balises meta, sitemap XML et données structurées LocalBusiness sont présents et valides
  4. L'opérateur se connecte avec email + mot de passe et accède au back-office
  5. Le schéma de base de données complet est déployé (inclut `client_uuid`, `odoo_id`, `signature_path`)
**Plans**: TBD
**UI hint**: yes

### Phase 2: MVP Suivi Offline-First
**Goal**: L'opérateur saisit un passage sur le terrain sans réseau, les données se synchronisent à la reconnexion sans doublon, et le client consulte son historique via magic link.
**Mode:** mvp
**Depends on**: Phase 1
**Requirements**: AUTH-02, AUTH-03, AUTH-04, CLI-01, CLI-02, CLI-03, PASS-01, PASS-02, PASS-03, PASS-04, PASS-05, PASS-06, PORT-01, PORT-02
**Success Criteria** (what must be TRUE):
  1. Le pro crée/modifie une fiche client avec piscine et la retrouve via recherche/filtre
  2. Le pro saisit mesures + actions + notes + photos sur iPhone sans réseau et voit le badge « N passages en attente »
  3. À la reconnexion, les passages se synchronisent sans doublon (idempotence `client_uuid`) et les photos passent via file résiliente
  4. Le client reçoit un magic link, se connecte et consulte l'historique de ses passages avec mesures et photos en lecture seule
  5. La PWA est installable en Home Screen avec `storage.persist()` actif (résistance à l'éviction iOS)
**Plans**: TBD
**UI hint**: yes

### Phase 3: Facturation & Odoo
**Goal**: L'opérateur génère des factures conformes (TVA 8,5 %, numérotation séquentielle CGI), les pousse vers Odoo ou en CSV, et le client signe le passage sur le téléphone du pro.
**Mode:** mvp
**Depends on**: Phase 2
**Requirements**: FACT-01, FACT-02, FACT-03, FACT-04, FACT-05, FACT-06, FACT-07
**Success Criteria** (what must be TRUE):
  1. Le POC Odoo est terminé : le mode API ou CSV est déterminé et configuré via `ODOO_MODE`
  2. Le pro gère un catalogue produits/services et des contrats ponctuel/forfait
  3. Le pro génère une facture avec TVA 8,5 % et numérotation séquentielle sans trou (conforme CGI art. 242 nonies A)
  4. La facture est poussée vers Odoo (XML-RPC) ou exportée en CSV selon le plan ; le statut de paiement s'affiche côté client
  5. Un PDF de compte-rendu est généré et le client peut signer électroniquement sur le téléphone du pro
**Plans**: TBD

### Phase 4: Notifications
**Goal**: L'opérateur et le client reçoivent les communications automatisées (compte-rendu après passage, rappel J-1) par email, avec option WhatsApp.
**Mode:** mvp
**Depends on**: Phase 3
**Requirements**: NOTIF-01, NOTIF-02, NOTIF-03
**Success Criteria** (what must be TRUE):
  1. Le client reçoit le compte-rendu PDF par email après la clôture d'un passage
  2. Le pro et le client reçoivent un rappel email la veille d'un passage planifié
  3. L'opération peut activer l'envoi du compte-rendu et du rappel via WhatsApp (template Business approuvé)
**Plans**: TBD

### Phase 5: Diagnostic Commercialisable
**Goal**: Un visiteur ou client utilise le wizard « eau verte » pour obtenir un plan d'action chiffré avec doses calculées côté serveur, après acceptation d'un disclaimer légal, et peut accéder aux fonctionnalités premium via Stripe.
**Mode:** mvp
**Depends on**: Phase 2
**Requirements**: DIAG-01, DIAG-02, DIAG-03, DIAG-04, DIAG-05
**Success Criteria** (what must be TRUE):
  1. Le disclaimer légal s'affiche sur le 1er écran du wizard avant tout conseil de dosage ; l'utilisateur doit l'accepter explicitement
  2. Le wizard produit un plan d'action avec doses calculées côté serveur (jamais en JS exposé) selon le volume du bassin
  3. Un abonné Stripe (piste A) ou un client premium (piste B) accède aux fonctionnalités avancées du diagnostic
  4. L'utilisateur peut consigner plusieurs mesures dans le temps et consulter leur évolution
**Plans**: TBD

## Progress

**Execution Order:**
Les phases s'exécutent dans l'ordre numérique : 1 → 2 → 3 → 4 → 5 (Phase 5 dépend uniquement de Phase 2 et peut démarrer en parallèle de 3-4 si besoin).

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Vitrine & Fondations | 0/? | Not started | - |
| 2. MVP Suivi Offline-First | 0/? | Not started | - |
| 3. Facturation & Odoo | 0/? | Not started | - |
| 4. Notifications | 0/? | Not started | - |
| 5. Diagnostic Commercialisable | 0/? | Not started | - |

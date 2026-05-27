# Feature Research

**Domain:** Field service / pool maintenance — single-operator, mobile-first, offline-first, client portal, billing/Odoo, commercializable water diagnostic
**Researched:** 2026-05-27
**Confidence:** HIGH (competitive landscape well-documented; diagnostic monetization MEDIUM)

---

## Feature Landscape

### Table Stakes (Users Expect These)

Features the pro and clients assume exist. Missing = product feels broken or amateur.

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| Saisie d'un passage avec relevés chimiques (pH, chlore libre, TAC, sel) | Tous les concurrents (Skimmer, Pool Brain, Pool Office Manager) le font ; c'est la raison d'être de l'outil | LOW | Champs structurés + validation de plages normales. Sel conditionnel (has_electrolyseur). Température et actions texte également. |
| Offline-first sur la saisie du passage | Réseau Martinique hasardeux ; sans ça l'outil est inutilisable sur le terrain | HIGH | IndexedDB + Service Worker + Alpine.js. Seule brique offline. Idempotence via client_uuid. Sync au retour réseau. |
| Capture et upload de photos à chaque passage | Systématique selon le client ; standard dans tous les outils (Skimmer, Pool Office Manager) | MEDIUM | Upload différé (file d'attente offline). Stockage Scaleway Object Storage Paris. Multi-photos par passage. |
| Historique des passages côté pro (filtres client / date) | Attendu dans tout outil de suivi terrain | LOW | Liste + filtres. Online uniquement (Livewire/Blade). |
| Fiche client + fiche piscine | Base de toute gestion de clientèle (volume, type, filtration, équipements) | LOW | Mono-piscine en UI ; données flexibles pour multi-piscine future. |
| Espace client en lecture seule (historique, mesures, photos) | Standard dans Skimmer (Customer Portal 2025), ServiceTitan, Pool Brain ; les clients attendent de pouvoir consulter leur historique | MEDIUM | Magic link auth. Lecture seule. Passages + mesures + photos. |
| Catalogue produits/services facturables | Requis pour toute facturation ; présent dans tous les concurrents | LOW | Libellé, prix unitaire, TVA, type (service/produit). |
| Contrats (forfait mensuel/saisonnier + ponctuel) | Mix 50/50 selon le client ; standard dans Skimmer AutoPay, Service Autopilot, Jobber | MEDIUM | Statut actif/expiré. Lié à invoice génération. |
| Génération de facture (draft ou émise) | Indispensable — outil sans facturation n'est pas un outil métier complet | MEDIUM | Lié à passage ou contrat. Numérotation auto. Statut draft/sent/paid. |
| Compte-rendu PDF par passage | Standard (Pool Office Manager, Skimmer Service Emails, ServiceTitan) | MEDIUM | Généré server-side (Laravel PDF ou DomPDF/Browsershot). Envoyé par email. |
| Email de compte-rendu après passage | Tous les concurrents le font (Skimmer "Service Emails", ProValet "proof of service") | LOW | Déclenchement automatique à la clôture du passage. Template HTML. |
| Vitrine marketing SEO local | Le site Zyro existant n'a aucune fonctionnalité ; une vitrine professionnelle est la baseline | LOW | Pages : accueil, services, réalisations, blog, contact. CTA WhatsApp et Google Reviews. |
| Auth pro (email + mot de passe) | Évident | LOW | Laravel Breeze ou Auth custom. |
| Auth client (magic link) | Attendu dans portail client moderne ; supprime le besoin de gérer des mots de passe clients | LOW | Laravel Notifications + signed URL. |

---

### Differentiators (Competitive Advantage)

Features that set this product apart vs Skimmer/Pool Brain (US-centric, English-only, généralistes).

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| Offline-first natif dans une PWA Laravel (pas une app native) | Les concurrents pool sont des apps natives iOS/Android ($29-99/mois). Une PWA installable sans app store est unique pour ce segment | HIGH | Service Worker + IndexedDB + Alpine.js. Skimmer est natif et peut régresser sur Android. Ce choix élimine les problèmes d'Android instability rapportés sur Skimmer. |
| Signature client électronique sur le téléphone du pro | Skimmer ne mentionne pas de signature ; ServiceTitan et FieldAx le font mais à prix élevé. Crée preuve de service légale | MEDIUM | Signature canvas HTML5 → image PNG → liée au passage. Capturée en fin de passage avant upload. |
| Intégration Odoo (pont CSV ou API selon plan) | Aucun concurrent pool ne supporte Odoo ; tous ciblent QuickBooks (US) | HIGH | POC obligatoire en Phase 1a. Voie A (XML-RPC) si plan Custom, Voie B (CSV export) sinon. Source de vérité = l'app. |
| Diagnostic piscine commercialisable (wizard eau verte) | Aucun concurrent field-service ne propose un diagnostic vendu en B2C ou en upsell pro. Orenda est un outil pro gratuit mais non-monétisé standalone | HIGH | Arbre de décision + formules de doses (extraites de la maquette React). Plan d'action chiffré selon volume bassin. Paiement Stripe. Pistes A (B2C abonnement) + B (upsell sur clients). |
| Calcul de doses contextualisé au bassin (volume connu) | Pool Math / Orenda calculent pour tout bassin anonyme. Ici le volume du bassin est connu → doses précises sans re-saisie | MEDIUM | Utilise le volume_m3 de la fiche piscine. Pour les clients d'entretien, le diagnostic est pré-rempli. |
| Notifications WhatsApp (option) | Martinique = forte pénétration WhatsApp. Aucun concurrent US ne l'intègre nativement | MEDIUM | WhatsApp Business API (ex. Twilio/360dialog). Option désactivable. Complément email. Rappel J-1 + envoi compte-rendu. |
| Localisation française intégrale | Tous les concurrents dominants (Skimmer, Pool Brain, ServiceTitan) sont en anglais uniquement | LOW | UI, PDFs, emails en français. Spécificités fiscales françaises (TVA). Fuseau horaire Martinique (UTC-4). |
| Interface ultra-simplifiée pour opérateur solo | Skimmer, ServiceTitan etc. ont des UX conçues pour des équipes de techniciens. Un solo-operator n'a pas besoin de gestion d'équipe | LOW | Pas de roster, pas de dispatch, pas de timesheet. Flux directs : piscine → passage → facture. |

---

### Anti-Features (Deliberately NOT Built)

| Feature | Why Requested | Why Problematic | Alternative |
|---------|---------------|-----------------|-------------|
| Gestion multi-techniciens / dispatch | Standard dans Skimmer Business, ServiceTitan, Jobber | Client travaille seul ; ajoute de la complexité (timesheets, permissions, routing multi-users) sans valeur | Si besoin futur : ajouter un rôle technicien en extension, pas en refonte |
| Optimisation de tournée / routing GPS | Présent dans tous les concurrents pool (route optimizer) | ~10 passages/semaine, clients fixes — le pro connaît ses routes. Intégration Maps API = coût + complexité | Lien Google Maps vers adresse client suffit |
| Application native iOS/Android | Skimmer est natif, meilleures notes App Store | App Store review, $99/an Apple Developer, déploiement complexe, maintenance double. PWA installable couvre le besoin offline | PWA avec manifest + Service Worker = installable sur écran d'accueil iOS/Android |
| Real-time chat / messagerie intégrée | Certains FSM en proposent | Complexité (WebSockets), portail client est en lecture seule, WhatsApp Business couvre la communication | Notifications email + option WhatsApp ; pas de chat in-app |
| Portail client en écriture (prise de RDV en self-service) | ServiceTitan Customer Portal le propose | ~10 clients fixes, planning non-critique. Ajoute workflow de validation RDV, notifications, slots dispo | Le pro planifie ; le client est notifié — suffisant à cette échelle |
| Inventaire et gestion de stock produits | Pool Brain, Service Autopilot, Jobber l'ont | Opérateur solo → pas de stock centralisé à gérer. Ajoute tables + UI sans ROI clair | Catalogue produits pour facturation suffit ; pas de gestion de stock |
| Scan de bandelettes de test via caméra | Pooli App le fait ($109/an) | Précision discutable, dépend de l'éclairage, complexité ML/image processing. Le pro mesure correctement | Saisie manuelle des valeurs = rapide + fiable |
| Multi-tenant / marque blanche | Piste C du diagnostic (revendre à d'autres piscinistes) | Change la nature du projet (auth, isolation données, billing per-tenant, support), scope explosion | Construire single-tenant propre d'abord ; multi-tenant est une v2 distincte |
| Paiement en ligne dans le portail client | ServiceTitan et Skimmer AutoPay le proposent | Intégration Stripe dans le portail + gestion des contestations + PCI compliance. La relation pisciniste-client est souvent sur facture mensuelle / virement | Statut de paiement récupéré depuis Odoo ; paiement hors-app |
| Planning / agenda visuel (calendrier drag-and-drop) | Skimmer, Pool Brain ont des calendriers complets | Inutile pour solo-operator avec ~10 passages/semaine. Coût dev élevé. | Liste simple des passages à venir suffit |
| Module blog/CMS avancé | Skimmer The Pool Deck (communauté) | Surcharge pour une vitrine de PME. Le blog existant sur Zyro est minimal | Quelques pages blog statiques en Blade suffisent |

---

## Feature Dependencies

```
[Auth pro + Auth client]
    └──requires──> [Gestion clients + piscines]
                       └──requires──> [Saisie passage offline-first]
                                          └──requires──> [Offline: IndexedDB + Service Worker]
                                          └──requires──> [Upload photos (queue offline)]
                                          └──enhances──> [Signature client (fin de passage)]

[Saisie passage]
    └──requires──> [Historique passages côté pro]
    └──requires──> [Portail client (lecture seule)]
    └──requires──> [Compte-rendu PDF]
                       └──requires──> [Email compte-rendu]
                       └──requires──> [Signature client]

[Catalogue produits/services]
    └──requires──> [Contrats (forfait / ponctuel)]
                       └──requires──> [Génération de factures]
                                          └──requires──> [Intégration Odoo (API ou CSV)]
                                          └──requires──> [Statut de paiement]

[Notifications WhatsApp]
    └──requires──> [Email compte-rendu]  (même déclencheur)
    └──requires──> [WhatsApp Business API (Twilio/360dialog)]

[Diagnostic piscine wizard]
    └──enhances──> [Fiche piscine (volume connu)]  (pré-remplit le bassin)
    └──requires──> [Paiement Stripe]  (monétisation B2C)
    └──requires──> [Arbre de décision + formules doses]  (logique extraite maquette React)

[Rappel passage J-1]
    └──requires──> [Planning minimal (date prévue d'un passage)]
```

### Dependency Notes

- **Offline-first requires Service Worker + IndexedDB avant tout le reste de la saisie** : toute la brique "passage" repose sur cette fondation. C'est le risque technique le plus élevé du projet — doit être validé en Phase 0.
- **Signature client requires que le passage soit en cours / non-synced** : capturée sur le téléphone du pro à la fin de l'intervention, avant la sync. Liée à visit_id (FK).
- **Compte-rendu PDF enhances Signature** : le PDF incluant la signature est plus complet, mais peut être généré sans.
- **Intégration Odoo is a POC gate** : tout le module facturation (contrats, factures, statut paiement) doit attendre la validation de la Voie A vs B. Ne pas architecturer avant le POC.
- **Diagnostic Phase 2 conflicts with MVP scope** : le diagnostic est standalone et n'a pas de dépendance sur les modules pro. Il peut être développé sans clients d'entretien. Mais l'upsell (Piste B) requiert la fiche piscine et l'auth client.
- **WhatsApp notifications conflicts with early phases** : l'API WhatsApp Business a des coûts (templates approuvés, conversations) et doit être une option, pas une dépendance. Email est la baseline.

---

## MVP Definition

### Phase V — Vitrine (prérequis business)

- [x] Vitrine marketing refaite (remplace Zyro) — pages accueil/services/réalisations/blog/contact, SEO local Martinique, CTA WhatsApp/Google Reviews
- [x] Déploiement Laravel Cloud EU/Francfort avec domaine existant

### Phase 0 — Launch With (MVP Suivi, v1)

- [ ] Auth pro (email + mot de passe) — point d'entrée obligatoire
- [ ] Auth client (magic link) — portail client
- [ ] Gestion clients + piscines (fiche mono-piscine en UI)
- [ ] Saisie d'un passage offline-first (pH, chlore, TAC, sel, temperature, actions, notes) + photos — core value du produit
- [ ] Idempotence offline (client_uuid par passage)
- [ ] Historique des passages côté pro (filtres client/date)
- [ ] Portail client en lecture seule (passages + mesures + photos)

### Phase 1a — Add After Validation (Facturation)

- [ ] POC Odoo (2-3 jours) — bloque toute la suite facturation
- [ ] Catalogue produits/services
- [ ] Contrats (ponctuel + forfait mensuel/saisonnier)
- [ ] Génération de factures (PDF, numérotation auto, statut)
- [ ] Intégration Odoo (Voie A API ou Voie B CSV selon POC)
- [ ] Compte-rendu PDF par passage
- [ ] Signature client (canvas HTML5 → image sur passage)

### Phase 1b — Notifications

- [ ] Email compte-rendu automatique après passage
- [ ] Rappel passage J-1 (email + option WhatsApp)
- [ ] Option WhatsApp via API Business (Twilio/360dialog)

### Phase 2 — Future (Diagnostic Commercialisé)

- [ ] Wizard diagnostic "ma piscine est verte" — arbre de décision + formules doses selon volume bassin (réécriture logique de la maquette React)
- [ ] Plan d'action chiffré + estimatif produits
- [ ] Paiement Stripe (Piste A : abonnement B2C)
- [ ] Upsell sur clients d'entretien (Piste B : accès premium depuis espace client)
- [ ] Historique multi-mesures dans le diagnostic

---

## Feature Prioritization Matrix

| Feature | User Value | Implementation Cost | Priority |
|---------|------------|---------------------|----------|
| Saisie passage offline-first + photos | HIGH | HIGH | P1 |
| Offline sync (IndexedDB + SW) | HIGH | HIGH | P1 |
| Auth pro + auth client (magic link) | HIGH | LOW | P1 |
| Fiche client + piscine | HIGH | LOW | P1 |
| Historique passages pro | HIGH | LOW | P1 |
| Portail client lecture seule | HIGH | MEDIUM | P1 |
| Vitrine marketing SEO | MEDIUM | LOW | P1 (Phase V) |
| Compte-rendu PDF | HIGH | MEDIUM | P2 |
| Signature client | MEDIUM | MEDIUM | P2 |
| Catalogue + contrats + factures | HIGH | MEDIUM | P2 |
| Intégration Odoo (API ou CSV) | HIGH | HIGH | P2 (bloqué POC) |
| Email compte-rendu automatique | MEDIUM | LOW | P2 |
| Rappel J-1 email | MEDIUM | LOW | P2 |
| Notifications WhatsApp | MEDIUM | MEDIUM | P2 |
| Diagnostic wizard eau verte (Stripe) | HIGH (différenciateur) | HIGH | P3 (Phase 2) |
| Calcul doses selon volume bassin | HIGH (diagnostic) | MEDIUM | P3 (Phase 2) |
| Optimisation tournée GPS | LOW | HIGH | NEVER |
| Multi-techniciens / dispatch | LOW | HIGH | NEVER |
| Scan bandelettes camera | LOW | HIGH | NEVER |
| Portail client en écriture (RDV) | LOW | HIGH | NEVER |
| Multi-tenant / marque blanche | LOW (now) | VERY HIGH | NEVER (v1) |

**Priority key:** P1 = MVP launch · P2 = add before/after first real usage · P3 = next major version · NEVER = anti-feature for this scope

---

## Competitor Feature Analysis

| Feature | Skimmer | Pool Brain | Pool Office Manager | Jobber | ServiceTitan | **Dlo Azur (approach)** |
|---------|---------|------------|---------------------|--------|--------------|--------------------------|
| Relevés chimiques (pH/chlore/TAC/sel) | Oui (+ Orenda LSI) | Oui (auto-dosage) | Oui | Non (généraliste) | Partiel | Oui — champs structurés simples |
| Offline mobile | Oui (app native iOS/Android) | Oui (app native) | Oui (app) | Oui (app) | Oui (app) | Oui — PWA installable (SW + IndexedDB) |
| Capture photo | Oui | Oui | Oui | Oui | Oui | Oui — offline queue + Scaleway |
| Portail client | Oui (Customer Portal 2025) | Oui | Non mentionné | Oui | Oui (complet) | Oui — lecture seule, magic link |
| Email compte-rendu / preuve de service | Oui (Service Emails) | Oui (Proof of Service) | Oui | Oui | Oui | Oui — PDF auto |
| Signature client | Non mentionné | Non mentionné | Non mentionné | Non | Oui | Oui — canvas HTML5 sur passage |
| Facturation récurrente (forfaits) | Oui (AutoPay) | Oui | Non clair | Oui | Oui | Oui — contrats ponctuel/forfait |
| Intégration comptable | QuickBooks Online | QuickBooks Online | Non clair | QuickBooks | QuickBooks | **Odoo** (API ou CSV) — différenciateur |
| Notifications SMS/WhatsApp | SMS (Service Texts) | Non | Non | SMS | SMS | Email + option WhatsApp |
| Rappel J-1 | Oui | Oui | Non clair | Oui | Oui | Oui — email (+ WA option) |
| Calcul de doses / LSI | Orenda integration (partenariat) | Orenda integration | Non | Non | Non | Custom — bassin connu, doses contextualisées |
| Diagnostic "eau verte" wizard | Non (alertes seulement) | Non (alertes seulement) | Non | Non | Non | **Oui — différenciateur Phase 2** |
| Monétisation diagnostic standalone | Non | Non | Non | Non | Non | **Oui (Stripe A+B) — différenciateur Phase 2** |
| Langue française | Non | Non | Non | Non | Non | **Oui — différenciateur marché FR/Caraïbes** |
| Planning / calendrier | Oui (drag-and-drop) | Oui | Non clair | Oui | Oui | Non — anti-feature pour solo |
| Optimisation tournée GPS | Oui | Oui | Non | Oui | Oui | Non — anti-feature pour ~10 passages/semaine |
| Multi-techniciens | Oui | Oui | Non | Oui | Oui | Non — anti-feature (solo operator) |
| Tarification | $29-99/mois (US) | Non public | Non public | $35-140/mois | $125+/mois | Single-tenant custom — pas de licence mensuelle |

---

## Sources

- [Skimmer Features page](https://www.getskimmer.com/features) — scrape direct
- [Skimmer Technician Features](https://www.getskimmer.com/product/technicians)
- [Skimmer Review 2026 — PoolDial](https://www.pooldial.com/resources/articles/software-reviews/skimmer-review)
- [Pool Brain Features page](https://www.poolbrain.com/features/) — scrape direct
- [Pool Office Manager — Reporting Tool](https://poolofficemanager.com/pool-service-reporting-tool)
- [ServiceTitan Pool Service Software](https://www.servicetitan.com/industries/pool-service-software)
- [ServiceTitan Customer Portal](https://www.servicetitan.com/features/customer-portal-software)
- [Jobber Field Service Management](https://www.getjobber.com/features/field-service-management-software/)
- [Orenda LSI & Dosing Calculator — Pool Brain integration](https://www.poolbrain.com/orenda-calculator-integration/)
- [The 6 Best Pool Chemical Calculator Apps 2026 — Swim University](https://www.swimuniversity.com/pool-chemical-calcuator-apps/)
- [Automated Notifications for Pool Services — ProValet](https://www.provalet.io/guides-posts/automated-notifications-for-pool-services-453a8)
- [GetApp — Pool Service Software with SMS](https://www.getapp.com/industries-software/pool-service/f/sms-integration/)
- [PWA Offline-First with IndexedDB — Veduis Blog](https://veduis.com/blog/building-offline-first-pwas-service-workers-indexeddb/)
- [Note de cadrage Dlo Azur v1 — 2026-05-27](../../../docs/superpowers/specs/2026-05-27-dloazur-refonte-design.md)

---

*Feature research for: pisciniste d'entretien — field service / pool maintenance, single-operator, Martinique*
*Researched: 2026-05-27*

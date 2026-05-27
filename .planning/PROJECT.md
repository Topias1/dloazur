# Dlo Azur Piscines — Plateforme métier & vitrine

## What This Is

Application web unifiée (Laravel) pour **Dlo Azur Piscines**, pisciniste d'entretien en Martinique : une **vitrine marketing refondue** + un **outil métier de suivi des passages** d'entretien avec **portail client**, et (à terme) un **outil de diagnostic piscine commercialisable**. Elle remplace l'actuel site Zyro qui n'a aucune fonctionnalité métier. Utilisateurs : l'opérateur (le pro, qui travaille seul) et ses clients d'entretien.

## Core Value

**L'opérateur enregistre chaque passage d'entretien sur le terrain de façon fiable — même sans réseau — et le client consulte l'historique de ses interventions.** Si tout le reste échoue, ça doit marcher.

## Requirements

### Validated

(None yet — ship to validate)

### Active

- [ ] Vitrine marketing refondue (remplace Zyro), SEO local Martinique
- [ ] Auth pro (email + mot de passe) et client (magic link)
- [ ] Gestion des clients et de leur piscine (volume, type, filtration, équipements)
- [ ] Saisie d'un passage **offline-first** (mesures pH/chlore/TAC/sel, actions, notes, photos) avec synchro au retour réseau
- [ ] Historique des passages côté pro (filtres client / date)
- [ ] Espace client en lecture seule (passages, mesures, photos)
- [ ] Catalogue produits/services facturables + contrats (ponctuel / forfait)
- [ ] Génération de factures + intégration Odoo (API si plan Custom, sinon pont CSV) + statut de paiement
- [ ] Compte-rendu PDF après chaque passage + signature client
- [ ] Notifications (email compte-rendu, rappel passage J-1, option WhatsApp)
- [ ] Diagnostic piscine (wizard + calcul de doses) commercialisable via Stripe (pistes A + B)

### Out of Scope

- Rôles techniciens / multi-opérateurs — le pro travaille seul
- Multi-tenant / marque blanche (piste C du diagnostic) — pas pour cette version, changerait la nature du projet
- Gestion multi-piscines par client en UI — ~1 piscine/client (modèle gardé flexible quand même)
- Application native — une PWA suffit
- Construction / vente de piscines — le métier est l'entretien uniquement
- React / SPA — stack volontairement server-rendered (Laravel)

## Context

- Entreprise martiniquaise, entretien de piscines & spas, **opérateur seul** : ~10 passages/semaine, ~une dizaine de clients à l'année.
- Existant : vitrine **Zyro** (builder Hostinger) sans fonctionnalité métier, pas d'espace client. Contact : +596 696 94 00 54 · contact@dloazurpiscines.com
- **Réseau mobile « hasardeux »** en Martinique → offline-first nécessaire sur la saisie d'un passage.
- **Photos systématiques** à chaque passage. **Smartphone uniquement** (mobile-first).
- Facturation : mix **ponctuel / forfait ~50/50**. **Odoo** déjà utilisé (plan probablement sans accès API).
- Une **maquette React** (diagnostic + espace client) existe mais jugée **insatisfaisante** → jetée. On conserve uniquement sa **logique métier de diagnostic** (arbre de décision + formules de doses) comme spécification.
- Note de cadrage détaillée (v2) : `docs/superpowers/specs/2026-05-27-dloazur-refonte-design.md`
- Design system & prompts Claude Design : `docs/superpowers/specs/2026-05-27-dloazur-design-system.md` (palette eau/Caraïbes, Plus Jakarta Sans + Inter, prompts écran saisie passage + accueil vitrine)

## Constraints

- **Tech stack**: Laravel 11 + Livewire + Alpine.js + Tailwind + PostgreSQL — fluence PHP du dev, maintenance solo durable, profil CRUD/portail/SEO
- **Offline**: saisie d'un passage offline-first (IndexedDB + Service Worker + Alpine ; **pas Livewire**, qui exige le réseau)
- **Hébergement**: Laravel Cloud région **EU/Francfort** (scale-to-zero, ~4-7 €/mois, Postgres managé) ; photos sur **Scaleway Object Storage (Paris)**
- **RGPD**: données clients hébergées en EU ; AWS + SCCs acceptable car données peu sensibles
- **Odoo**: API externe réservée au plan **Custom** (29,90 €/user/mois, vérifié doc officielle) — sinon **pont CSV** ; **POC Odoo** en tout début de phase facturation
- **Budget**: petite entreprise — managé, simple, pas cher

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Stack Laravel + Livewire + Alpine (pas React) | Fluence PHP du dev + maintenance solo + profil CRUD/SEO | — Pending |
| Maquette React jetée | Jugée insatisfaisante par le client ; on garde la logique diagnostic comme spec | — Pending |
| Offline-first dès le MVP (saisie passage) | Réseau hasardeux Martinique + photos systématiques | — Pending |
| Single-tenant (pas de marque blanche) | Lot 2 diagnostic ciblé pistes A + B, pas C | — Pending |
| Hébergement Laravel Cloud EU + photos Scaleway Paris | Managé / pas cher + conformité RGPD | — Pending |
| Odoo : POC d'abord, puis API ou pont CSV selon le plan | API réservée au plan Custom (vérifié) | — Pending |
| Design UI via le skill **impeccable** (in-repo, Claude Code) | Génère/itère de l'UI Tailwind implémentable, intégré au repo ; alimenté par le design-system. Claude Design (externe) reste optionnel pour des maquettes standalone | — Pending |
| Palette **« Eau/Caraïbes »** — bleu azur (sky-500, aligné au logo), turquoise en accent | Logo bleu azur + nom « Azur » ; turquoise reste un accent | — Pending |

## Evolution

This document evolves at phase transitions and milestone boundaries.

**After each phase transition** (via `/gsd-transition`):
1. Requirements invalidated? → Move to Out of Scope with reason
2. Requirements validated? → Move to Validated with phase reference
3. New requirements emerged? → Add to Active
4. Decisions to log? → Add to Key Decisions
5. "What This Is" still accurate? → Update if drifted

**After each milestone** (via `/gsd:complete-milestone`):
1. Full review of all sections
2. Core Value check — still the right priority?
3. Audit Out of Scope — reasons still valid?
4. Update Context with current state

---
*Last updated: 2026-05-27 after initialization*

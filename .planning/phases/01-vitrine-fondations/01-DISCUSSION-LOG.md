# Phase 1: Vitrine & Fondations - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-05-28
**Phase:** 1-Vitrine & Fondations
**Areas discussed:** Blog (SITE-04) — authoring, Contact (SITE-05) — delivery, Back-office post-login (AUTH-01), Cutover Zyro → Laravel Cloud (SITE-07)

---

## Blog (SITE-04) — authoring

| Option | Description | Selected |
|--------|-------------|----------|
| Markdown-in-repo | Articles en .md dans `resources/content/blog/`, parser `spatie/laravel-markdown`, front matter YAML. Git push pour publier. Idéal volume ~3-6/an. | ✓ |
| CRUD admin Livewire (DB) | Table posts + migration + form Livewire + WYSIWYG + upload images via medialibrary. +1-2 jours scope. | |
| Placeholder v1, vrai blog en v2 | 1-2 articles stubs hardcodés en Blade pour route + SEO. SITE-04 partiellement couvert. | |

**User's choice:** Markdown-in-repo (Recommended)
**Notes:** Liste chronologique simple, pas de tags/catégories à v1 (~6 articles/an ne le justifie pas). Tags déferrables si volume augmente.

---

## Contact (SITE-05) — delivery

| Option | Description | Selected |
|--------|-------------|----------|
| Email-only + honeypot | Form Livewire → `Mail::to('contact@dloazurpiscines.com')` via Laravel Mail. Honeypot + rate-limit IP. Pas de captcha. WhatsApp visible en fallback. | ✓ |
| DB persisté + email | Table contact_submissions + mail. Historique des leads consultable (utile suivi B2B hospitalité). +1 migration + Livewire index. | |
| WhatsApp deep-link only, pas de form | Page /contact = gros bouton WhatsApp + email mailto + tél. Plus simple, mais SITE-05 demande explicitement un formulaire. | |

**User's choice:** Email-only + honeypot (Recommended)
**Notes:** Driver mail (Mailgun vs Postmark vs SES) à confirmer par recherche-phase. Pas de captcha — friction UX inutile. Persistance DB déférée à v2 si suivi B2B devient un besoin.

---

## Back-office post-login (AUTH-01)

| Option | Description | Selected |
|--------|-------------|----------|
| Shell admin pré-câblé | Layout admin (sidebar + topbar) + nav avec Clients/Passages/Factures grisés + écran tableau de bord stub. Phase 2 branche les vraies routes. Économise 0,5 j en Phase 2. | ✓ |
| Minimal « Hello Pierre » | Juste `/admin` = page de bienvenue + bouton logout. Pas de layout admin. Phase 2 construit tout le shell. | |
| Layout + CRUD scaffolds vides | Layout + nav active + `/clients`, `/passages` avec tables vides. Anticipe trop sur Phase 2, risque de recompiler. | |

**User's choice:** Shell admin pré-câblé (Recommended)
**Notes:** Sidebar nav avec modules grisés (mention « bientôt »), tableau de bord stub avec placeholders de stats à `—`. Le shell est réutilisé par Phase 2 sans refactor.

---

## Cutover Zyro → Laravel Cloud (SITE-07)

| Option | Description | Selected |
|--------|-------------|----------|
| Staging + DNS switch + redirects | Phase 1 déploie sur `preprod.dloazurpiscines.com` (ou laravel.cloud URL). Validation lighthouse/sitemap/structured data. TTL DNS baissé, switch CNAME, 301 redirects pour ~3-5 URLs Zyro. Zéro downtime SEO. | ✓ |
| Big-bang day-of | Phase 1 publiée directement sur dloazurpiscines.com en une fois. Plus simple, mais risque downtime/regression SEO. | |
| Cutover hors périmètre Phase 1 | Phase 1 livre sur subdomain laravel.cloud, le switch DNS est une tâche opérationnelle séparée. | |

**User's choice:** Staging + DNS switch + redirects (Recommended)
**Notes:** Phase 1 livre la vitrine validée sur staging. Le DNS switch reste un acte opérationnel déclenché par Pierre quand il est prêt — la livraison technique ne dépend pas du switch. Inventaire URLs Zyro à faire avant (Google Search Console) ; si site plat (juste `/`), aucun redirect nécessaire.

---

## Claude's Discretion

- Choix précis du driver mail (Mailgun vs Postmark vs SES) — recherche-phase évaluera prix/délivery EU et tranchera.
- Structure exacte des Blade layouts et des composants Livewire vs Blade components purs.
- Naming Postgres : convention Laravel par défaut.
- Mise en cache des pages publiques (full-page cache) ou pas — recherche-phase évaluera ROI vs scale-to-zero.

## Deferred Ideas

- Tags/catégories blog si volume > 10 articles (v2)
- DB-backed admin pour blog si Pierre veut éditer sans dev intervention (v2)
- Persistance DB des soumissions contact (v2 — utile suivi B2B hospitalité)
- Google Reviews widget embed (lien externe à la place pour préserver la perf)
- Préchargement / cache full-page vitrine (v2 selon trafic réel)
- 2FA auth pro (Fortify le supporte, déférable — un seul utilisateur)
- Email verification sur signup (Pierre pré-créé via seeder, pas de signup public)

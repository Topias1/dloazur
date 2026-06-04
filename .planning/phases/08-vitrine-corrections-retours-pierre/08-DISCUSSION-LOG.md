# Phase 08: Vitrine — corrections retours Pierre - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-06-04
**Phase:** 08-vitrine-corrections-retours-pierre
**Areas discussed:** Hero géo, Page Dépannage, Consolidation voix

---

## Hero géo — niveau de détail

| Option | Description | Selected |
|--------|-------------|----------|
| Communes explicites | "du Lorrain à Vauclin côté Atlantique, de Schoelcher à Rivière-Salée côté caraïbe" | |
| Corridors décrits | "côte atlantique nord et côte caraïbe" sans communes | |
| Invitation à appeler seulement | Zone non révélée, qualification au téléphone | ✓ (+ zone d'intervention) |

**User's choice:** "dans notre zone d'intervention" (3e personne) + invitation à appeler conservée. Communes non nommées dans le hero.
**Notes:** Antoine a explicitement dit "non on va pas nommer les communes" — on diverge du SC1 du ROADMAP sur ce point. Les pages zones (`resources/views/vitrine/zones/`) couvrent déjà le détail géo commune par commune. L'honnêteté geo est assurée par le retrait de "toute la Martinique" + l'invitation à appeler.

---

## Page Dépannage — profondeur

| Option | Description | Selected |
|--------|-------------|----------|
| Légère | Hero + pitch urgence + 4 bullets + CTA WhatsApp | ✓ |
| Complète | Même structure que entretien-recurrent (héro, détail, FAQ, confiance, breadcrumb) | |

**User's choice:** Légère.
**Notes:** CTA principal = WhatsApp direct (wa.me pré-rempli). Contenu des 4 bullets = Claude's discretion (pannes courantes Martinique).

---

## Consolidation voix — structure vs copy seulement

| Option | Description | Selected |
|--------|-------------|----------|
| Copy seulement | Chaque section garde un angle différent, vocabulaire varié | |
| Fusionner philosophie + engagements | 3→2 sections, titre "Notre approche" | ✓ |

**User's choice:** Fusion → "Notre approche".

| Titre section fusionnée | Description | Selected |
|-------------------------|-------------|----------|
| "Notre approche" | Neutre, couvre valeurs + engagements, 3e personne | ✓ |
| "Nos engagements" | Garde l'angle promesse, perd les valeurs | |

**Mentions call-center à conserver (2 max) :**

| Sections | Retenu | Selected |
|----------|--------|----------|
| pierre + final-cta | Biographie + dernière impression avant conversion | ✓ |
| Laisser à Claude | | |

**Notes:** Formulations restantes (philosophie, engagements, services-detail) → argument positif orthogonal, sans négation. `pierre` reste section autonome (angle biographie).

---

## Revue point-par-point doc Pierre (V1–V14)

Antoine a demandé une vérification complète des points V1–V14 avant de valider. Bilan :
- **V1, V5, V12, V14, V6** : couverts Phase 8
- **V2, V3, V4, V8, V9, V10, V11, V13** : déjà faits sur la branche (hors scope)
- **V7** : hors scope Phase 8 (laissé tel quel, différé)

---

## Claude's Discretion

- Copy exact de chaque section modifiée (tant que D-09/D-10/D-11 respectés)
- Wording des 4 bullets pannes de la page Dépannage (pompe, fuite, eau verte, filtration)
- Désactivation du faux curseur avant/après si triviale (V7)
- Structure HTML de "Notre approche"
- Ordre des items dans les sections modifiées
- Suppression de l'orphelin `urgence-eau-verte.blade.php`

## Deferred Ideas

- Slider avant/après drag → différé jusqu'à 2 vraies photos Pierre (Phase future)
- Blog SEO chantiers réels → backlog
- SEO dépannage approfondi → Phase 999.x
- Sitemap lastmod mise à jour → à vérifier en review Phase 8

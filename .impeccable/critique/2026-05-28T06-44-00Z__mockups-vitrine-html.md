---
target: mockups/vitrine.html
total_score: 31
p0_count: 0
p1_count: 0
timestamp: 2026-05-28T06-44-00Z
slug: mockups-vitrine-html
---
## Design Health Score (after fixes)

| # | Heuristic | Score | Key Issue |
|---|---|---|---|
| 1 | Visibility of System Status | 4 | Inchangé. |
| 2 | Match System / Real World | 4 | Inchangé. |
| 3 | User Control and Freedom | 4 | Mobile anchor strip ajoutée : wayfinding restauré. |
| 4 | Consistency and Standards | 4 | Inchangé. |
| 5 | Error Prevention | n/a | Pas de formulaire. |
| 6 | Recognition Rather Than Recall | 4 | Inchangé. |
| 7 | Flexibility and Efficiency | 4 | Ancres mobile = navigation au pouce. |
| 8 | Aesthetic and Minimalist Design | 4 | Hero-metric supprimé, em-dashes (4) supprimés, kicker repetition supprimée, glass décoratif retiré. |
| 9 | Error Recovery | n/a | Pas de workflow critique. |
| 10 | Help and Documentation | 3 | Inchangé : WhatsApp = aide implicite. |
| Total | | 31/40 | +4 vs 27/40 ; tous les P1 et P2 résolus |

## Anti-Patterns Verdict (after fixes)

LLM: les 3 patterns IA résiduels précédents (hero-metric, em-dashes, kicker repetition) sont éliminés. La section « Le pisciniste » lit maintenant comme un encart artisan, pas comme un dashboard. Les sections sont portées par leurs Fredoka headings seules, sans grammaire de scaffolding.

Détecteur (patché localement, vitrine seule) : 0 errors, 0 warns, 2 info (glassmorphism nav flottante + bouton hero ghost sur photo) — les deux usages fonctionnels et défendables.

## What's Working (mis à jour)

1. Photo héros Pierre + baie + overlay marine = capital marque, intouché.
2. Bandeau hospitalité B2B navy + « 12 villas » : confiance B2B en une section, intouché.
3. NOUVEAU : section « Le pisciniste » avec phrase Fredoka + petite photo « entretien dos logo » = encart humain qui remplace l'ancien dashboard SaaS.
4. NOUVEAU : mobile anchor strip sous la pill nav — Karim peut sauter direct à Hospitalité d'une tape.

## Priority Issues (after fixes)

Aucun P0/P1/P2 restant.

Restant à considérer (P3, optionnels):
- 2 INFO glassmorphism (nav flottante + bouton ghost sur photo) — tous fonctionnels. Ignorer.
- 2 CTAs hero au poids quasi-équivalent : pas adressé ; choix de stratégie ouverte.
- Heuristique 10 (Help) à 3 : WhatsApp est l'aide ; pourrait être explicite (FAQ) ultérieurement.

## Minor Observations

- Trust strip héros / teaser portail : duplication mineure non adressée, reste à arbitrer.

## Questions to Consider

- Faut-il pousser plus loin la section Pierre (témoignage typographique, vidéo TikTok intégrée, etc.) ?
- Le hero benefit-stack en 2 CTAs : commit à un seul ?
- Étendre l'anchor strip mobile au-dessus de la pill ou en flottant collant en haut ?

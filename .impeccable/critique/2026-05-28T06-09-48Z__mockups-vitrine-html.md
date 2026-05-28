---
target: mockups/vitrine.html
total_score: 27
p0_count: 0
p1_count: 2
timestamp: 2026-05-28T06-09-48Z
slug: mockups-vitrine-html
---
## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|---|---|---|
| 1 | Visibility of System Status | 4 | Focus rings + hover states cohérents. |
| 2 | Match System / Real World | 4 | Voix native (français, WhatsApp omniprésent). |
| 3 | User Control and Freedom | 3 | Ancres OK ; pas de skip-to-content. |
| 4 | Consistency and Standards | 4 | Tokens respectés partout. |
| 5 | Error Prevention | n/a | Pas de formulaire sur cette surface. |
| 6 | Recognition Rather Than Recall | 4 | Labels explicites, structure lisible. |
| 7 | Flexibility and Efficiency | 3 | WhatsApp = raccourci canal direct ; pas de skip-link. |
| 8 | Aesthetic and Minimalist Design | 2 | 4 em-dashes, hero-metric template, glass décoratif. |
| 9 | Error Recovery | n/a | Pas de workflow critique. |
| 10 | Help and Documentation | 3 | WhatsApp = aide implicite directe. |
| Total | | 27/40 | Solide, quelques anti-patterns IA résiduels |

## Anti-Patterns Verdict

LLM: Non au premier coup d'œil grâce à la photo authentique de Pierre, la section hospitalité B2B, le SVG de goutte signature. Mais 3 patterns IA résiduels: hero-metric stats block (Pierre section), 4× em-dashes en copie, répétition de kicker labels uppercase à 3 sections.

Scan déterministe: détecteur bundlé absent ; fallback grep manuel. Em-dashes 4×, glassmorphism 3× (1 décoratif sans raison), hero-metric pattern confirmé, kickers 3 répétitions, identical service cards 3 (entourées d'asymétrie donc OK).

Overlays visuels: indisponibles (bundle détecteur absent). Console: 0 errors, 1 warning (Tailwind CDN production, attendu).

## Overall Impression

Vitrine vraiment bien faite sur les fondations (couleur engagée, photographie authentique, voix, palette extraite des supports réels) qui se trahit par quelques tics IA-générés dans le détail (em-dashes, hero-metric, kicker repetition). Le squelette est solide ; il manque une passe distill+polish. Plus grande opportunité unique: supprimer le bloc stats SaaS dans la section Pierre.

## What's Working

1. Photo héros (Pierre + baie martiniquaise) tient toute la marque, anti-stock par construction.
2. Bandeau hospitalité B2B navy avec carte flottante "12 villas" — confiance B2B sans corporate.
3. SVG de goutte azur flottant sur portrait de Pierre — signature spécifique extraite du logo.

## Priority Issues

### [P1] Hero-metric template dans la section Pierre
- **Why**: Pattern SaaS interdit nommément par impeccable. La section devrait être la plus humaine, on en fait un dashboard. "972 toute la Martinique" lit ambigu, "1 interlocuteur" sonne forcé.
- **Fix**: Supprimer le bloc 3 chiffres. Remplacer par une phrase forte Fredoka 600, ou une seconde photo de Pierre, ou un témoignage pull-quote court intégré.
- **Command**: /impeccable distill

### [P1] Em-dashes (—) en copie, 4×
- **Why**: Loi impeccable explicite. Marqueur typographique fortement associé aux générations IA en 2026.
- **Fix**: Title, 2 testimonial footers, footer copyright → remplacer par virgules ou points médians (·) déjà utilisés ailleurs.
- **Command**: /impeccable clarify

### [P2] Nav mobile = logo + WhatsApp seulement
- **Why**: Aucun moyen de sauter à Hospitalité/Réalisations/Pierre. Un gérant de conciergerie sur mobile doit scroller toute la page consumer.
- **Fix**: Hamburger menu OU strip d'ancres horizontal scrollable sous le logo OU mini-jump-bar sous le hero. Garder WhatsApp séparé.
- **Command**: /impeccable adapt

### [P2] Kicker labels uppercase à 3 sections = grammaire IA
- **Why**: brand.md cite "repeated tiny uppercase tracked labels as section grammar" comme "AI scaffolding".
- **Fix**: Garder le kicker UNIQUEMENT sur hospitalité (pivot d'audience). Retirer sur services, réalisations, pierre.
- **Command**: /impeccable distill

### [P2] Voix incohérente dans "Comment ça marche"
- **Why**: Steps 1-2 = verbes d'action, step 3 = nom statique. Casse le parallélisme.
- **Fix**: Step 3 en verbe parallèle. "On revient régulièrement" / "L'eau reste claire" / "On garde l'œil".
- **Command**: /impeccable clarify

## Persona Red Flags

**Marie (52, particulier, Trois-Îlets)**: Hero photo → confiance. Mais hospitalité navy au milieu = interruption "pas pour moi". Bloc stats "972" friction cognitive. Atterrit sur CTA finale OK mais détour B2B gratuit.

**Karim (32, gérant conciergerie, Diamant, mobile)**: Pas de nav mobile, scroll à l'aveugle pour trouver son angle. Hospitalité band lui parle (12 villas, villa standing). Mais "Devenir partenaire" → CTA générale partagée avec particuliers, pas de chemin B2B dédié.

**Jordan (scan-reader mobile)**: Hero capte. 3 répétitions de kicker → rythme prévisible. Stats block pige pas immédiatement. WhatsApp flottant en bas = conversion ramp robuste.

## Minor Observations

- Glass décoratif sur "Par e-mail" dans CTA finale azur solide (pas de fond complexe en dessous).
- Trust strip héros duplique partiellement le teaser Espace client plus bas.
- 2 CTAs hero au poids quasi-équivalent ; si WhatsApp est le vrai canal, hiérarchie franche.
- Meta description contient encore "par Pierre" (aperçu Google, à aligner).
- "Voir plus de chantiers" caché sur mobile (hidden sm:inline-flex).

## Questions to Consider

- Et si la section Pierre n'avait qu'une phrase forte + photo + témoignage, sans chiffres ?
- Un seul CTA au-dessus de la ligne de flottaison : WhatsApp, devis, ou portail ?
- Kicker labels : système assumé ou réflexe à supprimer ? Choisir.
- Karim mérite-t-il une section B2B accessible en une tape depuis la nav mobile ?

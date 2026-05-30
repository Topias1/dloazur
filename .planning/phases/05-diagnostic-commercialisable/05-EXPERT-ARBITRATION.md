# Arbitrage Plan A vs Plan B — juge de paix : leads qualifiés pour Pierre

> **STATUT : AUTORITAIRE (scope/priorité).** Réponse de l'expert (produit artisan + growth/lead-gen TPE) au prompt `05-EXPERT-COMPARISON-PROMPT.md`. **Verrouille le scope Phase 5 = hybride** (supersède le lean « App complète »). L'infra de rétention chère de B est différée V2.

## Verdict en une ligne

Ni A ni B en entier. **Hybride : acquisition + canal de delivery de A ; profondeur de diagnostic + escalade contextualisée de B ; différer en V2 l'infra de rétention de B (push, carnet, dashboard).** La valeur de B est dans ses briques **pas chères** (logique de diagnostic et d'escalade). Les briques **chères** sont précisément celles qu'on peut différer sans perdre un seul lead de départ.

## 1. Comparatif A vs B (dimensions de conversion)

| Dimension | Gagnant | Pourquoi (leads) |
|---|---|---|
| Volume de leads (capture brute) | **A** | Formulaire fin + WhatsApp + PDF = barre basse. Mais volume gonflé de bricoleurs purs. Volume brut à A, volume **utile** à B. |
| Qualité du lead | **B** | « j'ai choqué, re-testé, toujours vert, voici mesures + photo » >> « Prénom/Commune/Email ». Pierre devine le devis, moins de visites perdues. |
| Confiance | **B** | Pour le persona compétent-bloqué, un plan générique qui redit ce qu'il a raté détruit la confiance. Diagnostic action-aware + escalade honnête = crédibilité → bouche-à-oreille → avis → SEO → acquisition. |
| ROI construction | **A** (coût brut) → **Hybride** (vrai ROI) | A plus vite en ligne = leads plus tôt. Mais les parties de B qui convertissent sont de la **logique**, pas de l'infra : peu chères, fort impact. |
| Rétention → récurrent | **B** | Carnet/re-test/multi-bassins = lead one-shot → relation. Mais rétention ≠ génération du **premier** lead. → V2. |

## 2. Fuites de conversion du Plan A
1. **Diagnostic générique trahit le persona (fuite n°1)** : pas de « déjà essayé ? » → re-propose ce qui a raté → perte de crédibilité, rebond, zéro lead.
2. **Capture en formulaire de fin = friction au mauvais moment** ; le pic d'intention est l'instant où il réalise qu'il est coincé.
3. **Aucune logique d'escalade = hand-off non qualifié** ; Pierre reçoit du bruit.
4. **Un seul coup, pas de seconde prise** : le lead le plus chaud (a tenté, a échoué) est perdu sans boucle de re-test.
5. **Contexte de lead trop maigre** : Pierre redécouvre tout au téléphone.
6. **WhatsApp + route `/diagnostic` publique = vraies forces de A** que B doit **adopter, pas remplacer**.

## 3. Briques chères de B : différables sans perte de lead
| Brique | Sert | 1er lead ? | Verdict |
|---|---|---|---|
| Push notifications | rappel re-test | Non (rétention ; iOS PWA limité) | **V2** → remplacer par relance e-mail légère |
| Carnet offline + courbes | suivi temps | Non | **V2** |
| Multi-bassins | B2B | Marginal | **V2** |
| Espace Pierre (dashboard) | qualification pro | Non (artisan vit dans WhatsApp) | **Différer / WhatsApp** |
| App native / store | distribution | Contre-productif (install avant valeur tue le funnel) | **Abandonner — web-first PWA** |

## 4. Ordre de construction (pour générer du lead au plus vite)
1. **Route `/diagnostic` publique, indexée, mobile-first + parcours symptôme sans mesures + disclaimer avant tout chiffre.** [A] · FORT · FAIBLE-MOYEN. Le robinet d'acquisition (vitrine, `/services/eau-verte-urgence`, pages communes).
2. **Hand-off WhatsApp pré-rempli + PDF.** [A] · FORT · FAIBLE. Canal de delivery du lead pour un artisan seul.
3. **« Qu'as-tu déjà essayé ? » + diagnostic action-aware (cas critiques) + wizard chimie (doses serveur).** [B+A] · FORT · MOYEN. La crédibilité qui convertit ce persona.
4. **Moteur d'escalade contextualisé → CTA WhatsApp portant le contexte riche.** [B] · FORT · MOYEN. L'escalade EST le moment de conversion, au pic d'intention.
5. **Indice de confiance + photo optionnelle jointe au lead.** [B] · MOYEN · FAIBLE.
6. **Boucle re-test légère (relance e-mail ou in-session, PAS de push).** [B allégé] · MOYEN · FAIBLE-MOYEN. Seconde prise sur le lead chaud.

**— Frontière V2 —**
7. **Push, carnet offline, courbes, multi-bassins, espace Pierre.** · FAIBLE sur le 1er lead · ÉLEVÉ. Une fois le flux de leads établi.

Briques 1-4 = déjà une machine à leads qualifiés. 5-6 = multiplicateurs peu coûteux.

## 5. Garde-fous (la conversion n'entame jamais la sécurité)
Invariants conservés : doses **serveur** (DIAG-02), disclaimer **avant** dose, EPI + « ne jamais mélanger », ordre TAC→pH→TH→chlore→stabilisant→sel, opt-in/redirection sur l'acide. Deux pièges interdits :
1. **Ne jamais réduire la friction au prix de la sécurité** (pas de saut de disclaimer, pas de dose sans bloc sécurité, pas de masquage du « appelle un pro » sur l'acide/électricité).
2. **Ne pas sur-escalader pour gonfler les leads court terme** : crier « appelle Pierre » sur un cas DIY facile détruit la confiance → l'escalade honnête est aussi l'optimum long terme.

*Réf. : `05-BLUEPRINT-APP.md`, `05-CDC-SAFE-DIAGNOSTIC.md`, `05-DIAGNOSTIC-EXPERT-AUDIT.md`, `05-FLOCULANT-BRANCH-SPEC.md`.*

# Prompt expert — Critique notre plan initial & compare-le à ton blueprint

> Prompt à coller à l'expert LLM. Objectif : faire arbitrer, par un tiers, **notre plan initial (Plan A)** contre **ton blueprint (Plan B)** — avec un seul juge de paix : **la génération de leads qualifiés pour Dloazur (conversion).**

---

Tu es expert en conception de produits numériques pour artisans **ET** en growth/conversion (lead-gen pour TPE de services). Tu connais déjà ce projet : tu as produit un blueprint d'app de diagnostic piscine pour Dloazur (Pierre, pisciniste auto-entrepreneur, Martinique). Je te redonne ci-dessous **deux plans** et je te demande un **arbitrage critique** orienté conversion.

## Le contexte (rappel)

- **Commanditaire :** Pierre, pisciniste seul, Martinique. Clientèle particuliers + quelques locations B2B.
- **Persona principal (verrouillé) :** *« J'ai une piscine, je faisais l'entretien moi-même, mais j'ai un souci que je n'arrive pas à résoudre. J'utilise l'app Dloazur pour m'en sortir seul — et si ce n'est pas gérable, elle me redirige vers Dloazur. »* Utilisateur **compétent mais bloqué** : l'évident a déjà échoué.
- **🎯 OBJECTIF FINAL N°1 — CONVERSION / LEAD.** L'app n'est pas une œuvre de charité technique : sa raison d'être est de **générer des leads qualifiés** (intervention, vente de produit, contrat de suivi) pour Pierre. Le conseil gratuit fiable est le **moyen** (gagner la confiance) ; le lead converti est la **fin**. Objectifs 2 et 3 : conseil fiable, et zéro danger / zéro responsabilité juridique.
- **Contraintes déjà verrouillées (ne pas les re-débattre) :** diagnostic **gratuit**, **pas de Stripe**, doses **calculées côté serveur** (jamais en JS), chimie **déjà auditée** (corrections P0/P1 actées, branche floculant figée), stack **Laravel + Livewire + Alpine + Tailwind, web public `/diagnostic` mobile-first / PWA**. Parc d'appareils : iOS + Android, y compris anciens (le push PWA iOS est limité/récent).

---

## Plan A — ce qu'on avait initialement prévu (notre SPEC d'origine)

Un **diagnostic web gratuit** pensé d'abord comme *outil de génération de lead depuis la vitrine* :

- **Deux parcours** : (1) un **wizard chimie** (volume + mesures pH/chlore/TAC/stabilisant/sel → plan d'action chiffré, doses côté serveur) ; (2) un arbre **« Dépannage rapide »** par symptôme (eau verte/trouble/marron/claire, électrolyseur…) qui donne un plan **sans exiger de mesures**.
- **Disclaimer légal** accepté avant tout conseil de dosage.
- **Conversion :** un **formulaire de capture de lead** (Prénom, Commune, Email, Site web) + un **hand-off WhatsApp** pré-rempli vers Pierre + un **PDF** téléchargeable du diagnostic.
- **Point d'entrée :** route **publique `/diagnostic`** liée depuis la vitrine (nav + hero « Diagnostic piscine gratuit »), depuis la page `/services/eau-verte-urgence` (trafic à plus forte intention), et les pages communes — page elle-même indexable (SEO).
- **Persistance :** diagnostic utilisable anonymement, rattaché au compte si connecté.
- **Différé explicitement :** monétisation Stripe, et le **dashboard d'historique multi-mesures** (carnet, courbes, rappels).
- **Chimie :** formules reprises de la maquette comme base, validées ensuite par l'expert (toi).

En une phrase : **un bon diagnostic web gratuit, branché sur la vitrine, qui capture le lead à la fin** (formulaire + WhatsApp + PDF).

---

## Plan B — ton blueprint

Une app construite autour d'une **boucle centrale** pour le propriétaire *compétent mais bloqué* :

- **Symptôme → « qu'as-tu déjà essayé ? » → diagnostic approfondi conscient des actions tentées** (un choc raté oriente vers chlore-lock / métaux / algues résistantes, pas vers « refais un choc ») → **indice de confiance** → plan sûr → **re-test** → succès (carnet/prévention) **ou** échec → **escalade Dloazur**.
- **Moteur d'escalade** préemptif (hors-DIY : équipement, acide, électricité) et réactif (DIY tenté + re-test sans amélioration). Escalade **en un geste**, transmettant un **contexte riche** (symptôme, mesures, **actions tentées + résultats**, diagnostic, photo). « Pierre n'a aucune question à reposer. »
- **Fermeture de boucle native** : **notifications push** de re-test, **carnet offline**, caméra (photo en aide au lead, pas de diagnostic auto), **espace Pierre** de qualification des leads.
- Thèse de conversion : l'**honnêteté de l'escalade** (« là ça dépasse le DIY, appelle Pierre ») produit un lead **mieux qualifié et plus facile à signer** qu'une capture de contact en fin de tunnel.

En une phrase : **une boucle d'accompagnement qui convertit par la confiance et l'escalade contextualisée**, avec rétention mobile (push/carnet).

---

## Ce que je te demande

1. **Critique honnête du Plan A**, du point de vue conversion : où perd-il des leads ? (capture trop tardive ou trop tôt ? friction ? abandon ? leads non qualifiés ? pas de boucle de rappel ?). Sois précis et sans complaisance — c'est *notre* plan, démonte-le.
2. **Comparaison A vs B, critère par critère, avec la conversion comme juge de paix.** Pour chaque dimension ci-dessous, dis qui gagne et **pourquoi, en termes de leads** :
   - Taux de capture de contact (volume de leads).
   - **Qualité** du lead (probabilité de signer un RDV / vendre un produit / contrat).
   - Confiance générée (ce qui fait revenir et recommander).
   - Coût/risque de construction vs gain de conversion (ROI).
   - Rétention → revenus récurrents (le lead one-shot vs la relation suivie).
3. **Là où B coûte cher (push, offline, carnet, espace Pierre, éventuel natif) : le surcoût est-il justifié *par la conversion* ?** Quelles briques sont rentables tout de suite, lesquelles peuvent attendre une V2 sans saboter le lead-gen ?
4. **Recommande une synthèse** : le plan qui maximise les leads qualifiés pour Pierre au meilleur coût. Si c'est un hybride A+B, dis exactement quoi prendre de chaque, et **dans quel ordre de construction** (quoi en premier pour générer du lead au plus vite).
5. **Garde-fous :** vérifie que ta reco ne sacrifie jamais la sécurité chimique / la responsabilité juridique (surdosage, acide, mélange) au nom de la conversion. Un lead obtenu en dégradant une piscine ou en blessant l'utilisateur est un échec.

## Format de réponse attendu

- Un **tableau comparatif** A vs B sur les 5 dimensions de conversion (qui gagne + raison lead).
- La **liste des fuites de conversion** du Plan A.
- Une **reco synthèse hiérarchisée** : « pour générer du lead au plus vite → fais X d'abord, puis Y, puis Z », avec pour chaque brique son impact lead estimé (fort/moyen/faible) et son coût (faible/moyen/élevé).
- Les **briques rentables tout de suite** vs **différables sans perte de lead**.

> Rappelle-toi : tout se mesure à l'aune des **leads qualifiés pour Pierre**. Une fonctionnalité élégante qui ne fait pas signer un client est un luxe, pas une priorité.

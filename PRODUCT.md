# Product

## Register

product

> Registre par défaut = **product** (l'app métier est le cœur). La **vitrine** (`mockups/vitrine.html`, futures pages marketing) se traite en **brand** au cas par cas : sur ces surfaces, le design EST le produit.

## Users

- **L'opérateur — Pierre ADAM** (pisciniste solo, Martinique). ~10 passages/semaine, ~une dizaine de clients à l'année. Travaille **seul**, sur le terrain, smartphone à une main, souvent en plein soleil, réseau **hasardeux**. Son job sur l'app : enregistrer chaque passage (mesures pH/chlore/TAC/sel, actions, photos) de façon **fiable même hors-ligne**, suivre ses clients et leurs piscines, puis facturer.
- **Les clients d'entretien** : (1) **particuliers** propriétaires de piscine ; (2) **B2B hospitalité** — agences de location saisonnière, conciergeries, villas de standing. Ils consultent l'historique de leurs passages en **lecture seule**, par lien magique (sans mot de passe). Contexte : un particulier le soir chez lui ; un gestionnaire qui veut une preuve photo horodatée pour ses propriétaires.
- **Les visiteurs de la vitrine** : propriétaires martiniquais cherchant un pisciniste de confiance, et professionnels de l'hospitalité évaluant un partenaire fiable.

## Product Purpose

Plateforme web unifiée (Laravel) qui **remplace l'actuel site Zyro**, lequel n'a aucune fonctionnalité métier. Cœur de valeur : **l'opérateur enregistre chaque passage d'entretien sur le terrain de façon fiable, même sans réseau ; le client consulte l'historique de ses interventions.** S'ajoutent une **vitrine marketing** (SEO local Martinique) et, par phases, la **facturation/Odoo** et un **diagnostic piscine commercialisable**. Le succès : un passage se saisit en **moins de 2 minutes sans jamais perdre une donnée**, et chaque client dispose d'une **preuve claire** (mesures + photos) de chaque intervention.

## Brand Personality

**Chaleureux · artisan · fiable.** Voix directe, sans jargon, rassurante, à taille humaine : on parle au pro qui plonge l'épuisette, pas à un centre d'appel. La marque doit tenir le grand écart entre un **particulier** et une **conciergerie de villas de standing** : humaine ET sérieuse, jamais deux marques séparées. Émotions visées : **confiance, sérénité, soulagement** (« eau claire, rien à gérer »). La preuve (photos, mesures, ponctualité) remplace le superlatif marketing.

## Anti-references

- **Stock tropical générique** : palmiers vectoriels, fausses piscines turquoise de banque d'images, drapeaux. On assume la **vraie** Martinique (Pierre au travail, vraies villas, mer, soleil), pas le cliché touristique.
- **Corporate glacial / bleu SaaS impersonnel** : dégradés génériques, ton d'entreprise froid, zéro présence humaine.
- **Le rendu builder bas de gamme** de l'actuel site Zyro/Hostinger qu'on remplace.
- **Surchargé / clinquant** : glassmorphism décoratif, effets et animations gadget, trop d'éléments en compétition.

## Design Principles

1. **Le terrain d'abord.** L'écran de saisie d'un passage doit fonctionner en plein soleil, à une main, hors-ligne. Si tout le reste échoue, *ça* doit marcher. Tout le reste se subordonne à cette fiabilité.
2. **Ne jamais perdre une donnée.** Sauvegarde locale immédiate, synchro au retour réseau, le hors-ligne se dit **calmement** (ambre, jamais rouge ni alarmant). La fiabilité prime sur la richesse fonctionnelle.
3. **À taille humaine.** Un seul interlocuteur (Pierre) ; l'interface parle comme lui — simple, directe, sans jargon. On montre (photos, mesures), on ne promet pas.
4. **Authentique, pas touristique.** La crédibilité vient du réel : photos vraies du travail et des lieux, pas de banque d'images. Caraïbes assumées sans tomber dans le décor de carte postale.
5. **Deux publics, une seule voix.** Chaleureux pour le particulier, irréprochable pour l'hospitalité : le même ton et le même soin servent les deux, sans se dédoubler.

## Accessibility & Inclusion

- **WCAG AA** au minimum, contraste fort partout (texte sur photo via overlays marine, jamais de gris pâle pour le corps de texte).
- **Cibles tactiles ≥ 44px** et steppers au pouce : la saisie se fait debout, sur le terrain, à une main.
- **Lisibilité plein soleil** : neutres tièdes clairs en fond, encre marine sombre pour le texte ; thème clair par défaut (le dark serait illisible au soleil).
- **`prefers-reduced-motion` respecté** ; animations sur `transform`/`opacity` uniquement.
- **Mobile-first systématique** (l'opérateur est sur smartphone uniquement).
- **UI en français**, accents complets garantis par les polices retenues (Fredoka, Inter).

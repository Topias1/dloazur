# Réponses aux retours de Pierre — 2026-06-03

Travail sur la branche `claude/pierre-feedback-website-app-A59QE`.
Chaque point a été relu de façon critique : rien n'est présumé « bon » sans vérification du code et, quand c'est possible, d'un test.

## Résultats des tests

`php artisan test --filter='HomePage|ContactForm|PassageTimeline|Portail|DemoLogin'` → **41 passés, 0 échec** (112 assertions, ~0,8 s, SQLite en mémoire, résultat réel non deviné).
Deux réserves honnêtes : (1) le terme `PassageTimeline` n'a matché **aucun** test — l'historique dépliable n'a donc PAS de couverture de régression sous ce filtre (ne pas lire « timeline testée et verte ») ; (2) `AccessibilityTest`, `PwaConfigTest`, `TailwindTokensTest` n'ont pas été lancés (dépendent d'un build Vite / CDN de polices bloqué en local) — statut inconnu.

---

## Vitrine

- **[V1] « Partout sur l'île » est mensonger ; filtrer par zone (Lorrain↔Vauclin Atlantique, Schoelcher↔Rivière-Salée Caraïbe via Lamentin), inviter à appeler.**
  - Statut branche : 🟡 partiel — fidèle : partiellement
  - Réponse à Pierre : On a commencé à corriger, mais c'est incomplet et il reste deux problèmes.
  - 🔎 Critique : (1) Le hero dit « entre dans **MA** tournée » — seule occurrence de « je/ma » sur la home, ça casse la voix 3e personne du reste du site. (2) « du nord-atlantique au centre de la Martinique » ne décrit pas la vraie couverture (deux corridors) — le « centre » est flou. (3) « toute la Martinique » subsiste sur 2 pages service (`entretien-recurrent.blade.php:105`, `analyse-eau.blade.php:117`) — l'overclaim que Pierre dénonce est encore en ligne.
  - 👉 Décision proposée : réécrire le hero en 3e personne avec la vraie géo + invitation à appeler, puis purger « toute la Martinique » des pages service. **[CORRIGER]**

- **[V2] Enlever « Nous écrire » (redondant avec « Devis gratuit »).**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Fait, un seul bouton dans le hero. WhatsApp reste accessible en nav/footer.
  - 🔎 Critique : RAS, conforme.
  - 👉 Décision proposée : rien. **[GARDER]**

- **[V3] Enlever « photo à chaque passage ».**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Fait, reformulé en « photos quand c'est utile » — plus de promesse systématique.
  - 🔎 Critique : RAS.
  - 👉 Décision proposée : rien. **[GARDER]**

- **[V4] Pas fan de « pensé pour le climat antillais ».**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Slogan retiré, remplacé par « Un entretien complet, pour une eau toujours claire. »
  - 🔎 Critique : plus aucune occurrence.
  - 👉 Décision proposée : rien. **[GARDER]**

- **[V5] Les cartes services sont-elles cliquables ?**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Oui, chaque carte mène à sa page détail. Une exception : « Dépannage rapide » renvoie vers l'index des services, pas une page dédiée.
  - 🔎 Critique : 4 cartes sur 5 pointent vers une page détail propre ; la 5e (dépannage) atterrit sur l'index générique — incohérence légère pour l'utilisateur.
  - 👉 Décision proposée : créer une page détail « Dépannage » (recommandé) ou assumer le renvoi vers l'index. **[DÉCISION ANTOINE]**

- **[V6] Retirer la section « Urgence eau verte » de la home.**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Retirée de la home ; la page service dédiée reste en ligne.
  - 🔎 Critique : le partial `urgence-eau-verte.blade.php` n'est plus inclus mais traîne encore (fichier orphelin, dette propre).
  - 👉 Décision proposée : supprimer le partial orphelin (ménage). **[DIFFÉRÉ]**

- **[V7] L'avant/après à animer.**
  - Statut branche : 🟡 partiel — fidèle : partiellement
  - Réponse à Pierre : C'est aujourd'hui une seule image avec une barre lumineuse qui balaie + un léger zoom — joli mais ce n'est PAS un vrai comparatif glissant que l'on déplace.
  - 🔎 Critique : le « curseur » suggère qu'on peut interagir mais il ne réagit à rien (risque de frustration). Le zoom tourne en boucle (peut fatiguer). Bon point : neutralisé sous `prefers-reduced-motion`. « Animer » est ambigu — d'où la question.
  - 👉 Décision proposée : demander à Pierre s'il veut un vrai slider 2 images (drag) ou juste du mouvement. Si vrai slider → l'implémenter ; sinon réduire le zoom et retirer le faux curseur. **[DÉCISION ANTOINE]**

- **[V8] « Simple, du premier message » peut-être en trop.**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Section retirée de la home (conservée sur /services).
  - 🔎 Critique : conforme.
  - 👉 Décision proposée : rien. **[GARDER]**

- **[V9] « Nos chantiers » à enlever (→ éventuel blog SEO).**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Grille retirée de la home ; la page /realisations reste (bon pour le SEO).
  - 🔎 Critique : l'idée blog SEO reste un sujet futur, hors périmètre.
  - 👉 Décision proposée : noter le blog SEO pour plus tard. **[DIFFÉRÉ]**

- **[V10] « Une dizaine de clients à l'année » à enlever.**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Chiffre retiré, remplacé par « Un seul interlocuteur, qui connaît votre bassin ».
  - 🔎 Critique : plus aucune occurrence.
  - 👉 Décision proposée : rien. **[GARDER]**

- **[V11] « WhatsApp réactif 7j/7 » → non.**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Toutes les mentions « 7j/7 » supprimées, reformulé « Joignable sur WhatsApp ».
  - 🔎 Critique : scrub complet vérifié (0 occurrence).
  - 👉 Décision proposée : rien. **[GARDER]**

- **[V12] « Solo artisanal, pas un call-center » pas vendeur → reformuler.**
  - Statut branche : 🟡 partiel — fidèle : partiellement
  - Réponse à Pierre : La carte principale est bien reformulée en positif, mais le ton « pas de standard / pas de call-center » a juste été déplacé et répété à 5 endroits — exactement la formulation que vous trouviez peu vendeuse.
  - 🔎 Critique : « jamais un standard / pas de centre d'appel / pas de sous-traitance » subsiste dans `final-cta`, `engagements`, `services-detail`, `pierre`, `philosophie`. Sur-répétition d'un argument défensif.
  - 👉 Décision proposée : réduire à 1-2 occurrences max et tourner positif (« vous parlez directement à Pierre »). **[CORRIGER]**

- **[V13] Formulaire : téléphone obligatoire + séparer Nom/Prénom.**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Fait — Nom et Prénom séparés, téléphone obligatoire. Implémenté et testé (succès + champs requis).
  - 🔎 Critique : complet. Bémol mineur : le téléphone est validé en texte (min 6 caractères) sans format — « abcdef » passerait. Acceptable car Pierre rappelle de toute façon.
  - 👉 Décision proposée : rien (regex légère optionnelle). **[GARDER]**

- **[V14] Globalement trop d'infos, à épurer.**
  - Statut branche : 🟡 partiel — fidèle : partiellement
  - Réponse à Pierre : 3 sections retirées + hero allégé (vraie épuration), mais la home garde 11 sections et 3 d'entre elles (philosophie / engagements / pierre) répètent le même message « interlocuteur unique ».
  - 🔎 Critique : l'épuration est structurelle mais pas sémantique — la redondance de contenu n'est pas traitée (lié à V12).
  - 👉 Décision proposée : dé-dupliquer le message « interlocuteur unique » entre les 3 sections ; envisager d'en fusionner/couper une. **[CORRIGER]**

---

## Espace client

- **[client-1] Si la piscine est au chlore, ne pas afficher la case Sel.**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Fait — la tuile Sel disparaît pour une piscine au chlore, et la démo le montre bien.
  - 🔎 Critique : logique vérifiée de bout en bout. Deux détails : (1) la liste des traitements « au sel » est codée en dur — une saisie libre future type « electrolyse au sel » ne matcherait pas et masquerait Sel à tort ; (2) la démo seede un « Électrolyseur » en équipement tout en disant traitement = chlore (incohérence d'affichage seulement, sans impact technique).
  - 👉 Décision proposée : garder. Optionnel : matcher « sel »/« electrolys » par `str_contains`, et corriger la donnée démo. **[GARDER]**

- **[client-2] Rendre les éléments d'historique cliquables.**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Fait — chaque passage se déplie au clic et montre mesures, actions et note. Fonctionne au clavier.
  - 🔎 Critique : accordéon Alpine correct, pas de flash avant init. Manques a11y mineurs non bloquants : pas de `aria-controls`/`id` reliant bouton et panneau. **Pas de test de régression automatisé sur cet écran** (cf. section Tests).
  - 👉 Décision proposée : garder ; ajout a11y optionnel + écrire un test. **[GARDER]**

- **[client-3] Le client doit récupérer ici son contrat et ses factures.**
  - Statut branche : 🟡 partiel — fidèle : partiellement
  - Réponse à Pierre : Pour l'instant c'est une **maquette** : la section « Mes documents » existe avec « Contrat » et « Factures » marqués « Bientôt », mais rien n'est téléchargeable. Ça montre l'emplacement et l'intention ; le vrai branchement dépend de la phase facturation.
  - 🔎 Critique : teaser non fonctionnel, aucun modèle Document, aucun lien. Défendable en démo MAIS deux boutons inertes peuvent décevoir Pierre qui demandait une vraie récupération.
  - 👉 Décision proposée : assumer le teaser + lui dire clairement « maquette, branché en phase facturation », et créer un ticket de dépendance Phase 3. **[DÉCISION ANTOINE]**

- **[client-4] Rendre l'espace plus « sexy » avec une photo de la piscine.**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Fait — un grand bandeau affiche la photo du dernier passage, avec une photo générique en secours si aucune photo n'existe.
  - 🔎 Critique : résolution d'URL via le disque r2 (conforme), double filet de sécurité (try/catch + fallback). Le fallback existe physiquement. Nits perf pour le mobile Martinique : le fallback JPG fait ~995 Ko (pas de `<picture>` webp/avif) et le hero est en `loading="lazy"` (mauvais pour le LCP au-dessus de la ligne de flottaison).
  - 👉 Décision proposée : garder ; optim perf optionnelles (`<picture>` + retirer le lazy sur le hero). **[GARDER]**

---

## Démo admin

- **[admin-1] Mon agenda : finir une piscine → cliquer sur le client → saisir valeurs/remarques.**
  - Statut branche : 🟡 partiel — fidèle : partiellement
  - Réponse à Pierre : La saisie rapide existe déjà (on peut ouvrir un passage pré-rempli pour un client), mais **l'agenda lui-même — la porte d'entrée que vous décrivez — n'existe pas encore**.
  - 🔎 Critique : aucun agenda/planning/tournée dans le code. La moitié aval est bonne (saisie `?client_id=X`, auto-sélection piscine, Alpine + IndexedDB, jamais Livewire). Il manque la vue qui liste les passages du jour et lie vers la saisie.
  - 👉 Décision proposée : nouvelle phase « Agenda du jour » admin. Reco : agenda **dérivé** d'une fréquence/jour de passage sur la piscine (zéro saisie pour un pro solo) plutôt que des rendez-vous explicites. **[NOUVELLE FEATURE]**

- **[admin-2] Top les notes internes. Notif possible ?**
  - Statut branche : 🟡 partiel — fidèle : partiellement
  - Réponse à Pierre : Attention — **vraie panne** : la note interne est saisie et envoyée mais **perdue à la synchro** (la colonne n'existe pas en base). La fonctionnalité que vous avez saluée est cassée. Côté notif : rien d'implémenté.
  - 🔎 Critique : le champ « Note interne » est dans le formulaire, le JS l'envoie, l'API le valide, MAIS `notes_privees` n'est pas dans la migration, pas dans `$fillable`, pas dans l'upsert → silencieusement effacé. Le push PWA n'est pas fiable sur iOS/Safari (device de Pierre inconnu).
  - 👉 Décision proposée : **corriger le bug** (migration `notes_privees` + fillable + upsert, avec test « persiste ET invisible côté client »). Pour la notif : ne pas promettre de push ; reco = flag « à revoir » remonté dans l'agenda admin-1, e-mail de rappel plus tard. **[CORRIGER]**

- **[admin-3] Vais-je avoir une appli pour moi ?**
  - Statut branche : ✅ fait — fidèle : oui
  - Réponse à Pierre : Oui — l'outil métier est déjà une vraie app installable (PWA). Depuis le navigateur : « Ajouter à l'écran d'accueil » → icône Dlo Azur, et la saisie marche sans réseau.
  - 🔎 Critique : `vite-plugin-pwa` configuré, manifest complet, SW présent, POST passages en offline-first via IndexedDB. À vérifier en démo réelle sur SON device : sur iOS l'installation est manuelle (pas de prompt) → prévoir une mini-instruction iOS in-app.
  - 👉 Décision proposée : confirmer à Pierre + tester l'install sur son téléphone réel. **[GARDER]**

- **[admin-4] Possible lier les factures ?**
  - Statut branche : ❌ non implémenté — fidèle : n/a
  - Réponse à Pierre : Le schéma est prêt (la table factures sait déjà rattacher un passage/contrat/client et a des crochets Odoo) mais **aucune logique n'est codée** — pas de contrôleur, pas de vue, Odoo pas installé.
  - 🔎 Critique : 0 % câblé. Détail important fiscal : la valeur TVA par défaut est 8,5 % alors que Pierre est auto-entrepreneur en franchise de base (art. 293 B) → factures **sans TVA**, mention « TVA non applicable, art. 293 B du CGI ».
  - 👉 Décision proposée : Phase 3. Reco par défaut pour un solo : facturation **maison** (spatie/laravel-pdf DomPDF déjà prévu), Odoo optionnel après POC plan Custom. Corriger le défaut TVA à 0 + mention 293 B. Confirmer le statut TVA AVANT toute facture. **[NOUVELLE FEATURE]** + **[DÉCISION ANTOINE]** (Odoo vs maison)

- **[admin-5] Cahier de fin de mois : page récap par client (nb passages + chimie consommée).**
  - Statut branche : ❌ non implémenté — fidèle : n/a
  - Réponse à Pierre : Le comptage des passages par client/mois est trivial (les données sont là). En revanche **« chimie consommée » n'est ni saisie ni modélisée** : aucune table ne relie un produit + une quantité à un passage.
  - 🔎 Critique : `actions` est un simple tableau de textes, pas des lignes {produit, dose}. La table `produits` existe mais sans aucune relation. Le DoseEngine du diagnostic ne persiste rien.
  - 👉 Décision proposée : feature avec schéma — pivot `passage_produit` (produit, quantité, prix snapshot) + mini-sélecteur « produits utilisés » dans la saisie (offline) + page « Récap mensuel par client » avec bouton « Générer la facture » (relie admin-4). Reco : produits pré-listés, quantité optionnelle, pour minimiser la saisie terrain. **[NOUVELLE FEATURE]**

---

## Appli & diagnostic

- **[diag-1] Reprendre mon proto pour le peaufiner. Pas fan de laisser le choix au départ.**
  - Statut branche : 🟡 partiel — fidèle : partiellement
  - Réponse à Pierre : Justement, c'est le point trahi : l'app **ajoute un écran de choix au départ** (« Trouver mon problème » vs « Analyser mon eau ») qui n'existe pas dans votre proto. Votre proto entre directement sur « Quel est ton problème ? ».
  - 🔎 Critique : l'arbre symptôme est bien transposé et le tutoiement du proto a été corrigé en vouvoiement (conforme aux règles, pas une trahison). Mais la fourche initiale à 3 cartes contredit frontalement « pas de choix au départ ».
  - 👉 Décision proposée : supprimer l'écran « mode » et entrer direct dans l'arbre symptôme ; reléguer « Analyser mon eau » en action secondaire dans le parcours. **Valider avec Pierre** que « le choix au départ » = bien cette fourche. **[REFAIRE]** + **[DÉCISION ANTOINE]**

- **[diag-2] (technique) Quel est le choix initial à retirer.**
  - Statut branche : ✅ fait (identification) — fidèle : n/a
  - Réponse à Pierre : Repéré précisément — c'est l'écran « mode » du wizard.
  - 🔎 Critique : cible = bloc Blade lignes 221-285 + bascule de l'état initial (`step:'mode'` → `step:'tree'`, `nodeId:'start'`). Ne PAS supprimer la logique serveur `$mode` (utilisée par le payload WhatsApp/created_via). Vérifier les `data-mode-*` : probablement utilisés par des tests Pest browser → adapter avant suppression pour ne pas casser la suite.
  - 👉 Décision proposée : appliquer le retrait ci-dessus dans le cadre de diag-1. **[CORRIGER]**

- **[diag-3] Comment le monétiser ?**
  - Statut branche : ❌ non implémenté — fidèle : n/a
  - Réponse à Pierre : Aujourd'hui le diagnostic est 100 % gratuit et sert d'**aimant à clients** (il génère des leads + vous notifie). Reco : ne pas faire payer le rapport.
  - 🔎 Critique : aucune monétisation câblée (ni Cashier ni Stripe). Le vrai ROI pour un pisciniste solo, c'est la conversion en intervention/contrat, pas 2-5 € par PDF. Contraintes fiscales : franchise TVA (mention 293 B) + Pierre **proche du plafond services 36 800 €/an** → encaisser des micro-paiements pousse le CA vers le seuil.
  - 👉 Décision proposée : garder le diag gratuit comme lead magnet. Si monétisation un jour : faire payer la **valeur pro** (intervention déclenchée depuis le diag) via Cashier one-shot, pas le rapport ; l'abonnement reste pour les contrats d'entretien. À trancher au POC facturation. **[DÉCISION ANTOINE]**

---

## Synthèse pour la nouvelle phase GSD

### Scope candidat — corrections (vitrine + bugs, rapides)
1. **[admin-2] BUG : notes internes perdues à la synchro** — migration `notes_privees` + fillable + upsert + test d'invariant vie privée. *Priorité haute (vraie perte de données saluée par Pierre).*
2. **[V1] Géo honnête** — hero en 3e personne + vraie couverture + invitation à appeler ; purger « toute la Martinique » des pages service.
3. **[V12 + V14] Voix & redondance** — réduire « pas de call-center » à 1-2 occurrences positives ; dé-dupliquer « interlocuteur unique » entre philosophie/engagements/pierre.
4. **[diag-1 + diag-2] Diagnostic fidèle au proto** — retirer l'écran « mode », entrer direct dans l'arbre symptôme (garder `$mode` serveur, adapter les tests).
5. **[V5] Page détail Dépannage** (si décision = créer).

### Scope candidat — nouvelles features (phases dédiées)
- **[admin-1] Agenda du jour admin** (porte d'entrée vers la saisie ; reco : dérivé d'une fréquence sur la piscine).
- **[admin-5] Récap mensuel + consommation chimie** (pivot `passage_produit`, mini-sélecteur de saisie, page récap par client).
- **[admin-4 + client-3] Facturation Phase 3** (modèle Document, PDF, lien passage→facture, espace client « Mes documents » réel ; corriger défaut TVA → 293 B).

### Questions ouvertes — [DÉCISION ANTOINE]
- **V5** : créer une page détail « Dépannage » ou assumer le renvoi vers l'index ?
- **V7** : vrai slider avant/après (drag 2 images) ou simple animation décorative ?
- **client-3** : assumer le teaser « Bientôt » en démo (avec mention claire) ou retirer la section jusqu'à la Phase 3 ?
- **admin-4** : facturation maison (reco) vs Odoo (POC plan Custom requis) ? Et **confirmer le statut TVA** de Pierre avant toute facture.
- **diag-1** : valider que « le choix au départ » à retirer = bien la fourche symptôme/chimie.
- **diag-3** : modèle de revenu — lead magnet gratuit (reco), one-shot sur l'intervention, ou bundle dans l'abonnement entretien ?

### Ménage différé
- **[V6]** supprimer le partial orphelin `urgence-eau-verte.blade.php`.
- **[V9]** noter le blog SEO comme item futur.
- **[client-2]** ajouter un test de régression sur l'historique dépliable (non couvert aujourd'hui).

---

## Décisions discuss (Antoine, 2026-06-03)

Tranchées en amont (avant /gsd-autonomous, profil repassé en balanced) :

- **[P8 · V7] Avant/après** → **laisser tel quel** (pas de 2 photos avant/après dispo). V7 **sort du scope** de la Phase 8 ; l'amélioration slider est différée jusqu'à ce que Pierre fournisse deux vraies photos.
- **[P8 · V5] Carte Dépannage** → **créer une page détail dédiée** (`/services/depannage`), cohérence + SEO local.
- **[P9 · client-3] « Mes documents »** → **garder le teaser « Bientôt »** avec mention claire « branché à la facturation (Phase 3) ». Dépendance Phase 3 tracée.
- **[P7 · admin-1] Agenda** → **dérivé d'une fréquence/jour de passage** par piscine (zéro saisie de RDV). Pas de calendrier à RDV explicites.

Défauts actés (modifiables au plan) : diag gratuit = lead magnet (pas de paiement du rapport) ; notif notes internes = pas de promesse de push ; diag-1 « choix au départ » = la fourche symptôme/chimie.

**Ordre d'exécution :** 7 (admin, contient le bug) → 8 (vitrine) → 9 (client) → 10 (diagnostic). Phases indépendantes (deps sur socles 1/2/5).

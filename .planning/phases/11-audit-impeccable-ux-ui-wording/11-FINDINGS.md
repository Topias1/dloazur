# Phase 11 — Audit Impeccable UX / UI / Wording

> Source : audit `/impeccable` (audit + critique + clarify) du 2026-06-04, register **product** (vitrine = brand).
> Méthode : 5 reviewers indépendants (vitrine, PWA passage offline, admin pro, portail client, auth), chacun ancré sur DESIGN.md / PRODUCT.md, lecture du **code réellement implémenté** (pas les maquettes).
> Verdict global : **Audit Health 14/20 (Good)**. AI-slop : LOW partout. Les vrais bloquants sont **fonctionnels** (perte/échouage de données, message d'erreur avalé), pas esthétiques. Faiblesse principale : **theming** (pur `#fff` systémique + tokens Tailwind v4 non définis qui n'émettent aucun CSS).

## Scorecard

| # | Dimension | Score | Finding clé |
|---|-----------|-------|-------------|
| 1 | Accessibility | 3/4 | Base solide (focus-visible, vrais labels, autocomplete, aria) ; gaps : landmarks `<aside>`/`<header>` imbriqués, `aria-live=assertive` sur bandeau calme, badge sync mobile muet |
| 2 | Performance | 3/4 | Aucun état loading/submitting (listes live-search, tous les boutons auth) |
| 3 | Responsive | 3/4 | Mobile-first, cibles ≥44px ; steppers au plancher 44px (signature = 56px), slot gâché en bottom-nav mobile |
| 4 | Theming | **2/4** | Système de tokens excellent, mais 2 ruptures systémiques : pur `#fff` partout + tokens non définis silencieux |
| 5 | Anti-Patterns | 3/4 | Slop faible ; résiduel : grille 4 cartes dashboard, faux témoignages, réflexe blanc |

**Vouvoiement client-facing : PASS total.** Zéro `tu` sur portail, emails client, magic-link, pane client login. La règle de marque la plus dure tient.

---

## P0 — Bloquant (perte de données / tâche cassée / risque légal live)

### [P0] File offline ingérable dès que Pierre quitte l'écran de saisie
- **Lens** UX · **Surface** PWA offline
- **Location** `resources/views/components/admin/topbar.blade.php:38` (badge, toutes pages) vs `admin/passages/create.blade.php:421` (drawer, create only) ; `resources/js/app.js:88-92`
- **Impact** Le badge « N en attente » s'affiche sur **toutes** les pages admin et dispatch `sync-drawer:open`, mais `<x-admin.sync-drawer>` n'est monté que sur create. La logique d'upload (`_flushQueue`→`_uploadPassage`) vit dans le composant Alpine `passageForm`, monté uniquement sur create. Le relais global `passage-form:flush` (`app.js:88`) ne fait que rafraîchir le compteur, jamais uploader. → passages en file impossibles à pousser hors de l'écran create. **Frappe le cœur de valeur.**
- **Fix** Monter `<x-admin.sync-drawer>` dans le layout admin (dispo partout où le badge est) ; déplacer la logique flush/upload dans un store partagé (`Alpine.store('offlineQueue').flush()`) appelé par `app.js` sur `passage-form:flush` que `passageForm` soit monté ou non. Le badge ne doit jamais exister sans drawer + flush fonctionnels.

### [P0] Items bloqués en `uploading` deviennent des zombies non-retentés
- **Lens** UX · **Surface** PWA offline
- **Location** `resources/js/passage-form.js:459` (`markStatus(...,'uploading')`) ; `offline-queue.js:96-105` (compte `uploading` comme pending) ; `sync-drawer.js:77-87` (flushAll ne re-queue que `error`) ; `passage-form.js:382-383` (`_flushQueue` ne lit que `pending`)
- **Impact** `_uploadPassage` passe en `uploading` avant le fetch. Si l'onglet meurt / tel déchargé / SW redémarre en plein upload (très plausible sur le terrain), l'enregistrement reste en `uploading` : compté « en attente » mais retenté par rien (`_flushQueue` lit `pending`, `flushAll`/`retry` ne promeuvent que `error`→`pending`). **Donnée silencieusement échouée — l'échec exact que l'archi doit empêcher.**
- **Fix** Au `init()` (et/ou dans `flushAll`), récupérer les orphelins : re-queue tout `uploading` plus vieux que quelques secondes → `pending`. Simple : `_flushQueue` traite aussi `getPassagesByStatus('uploading')` ; `flushAll()` promeut `uploading`→`pending`.

### [P0] Erreur magic-link expiré/invalide silencieusement avalée
- **Lens** UX / Wording · **Surface** Auth / Portail
- **Location** `app/Http/Controllers/Portail/MagicLinkController.php:99,107,121` redirigent vers `portail.magic-link.request` ; mais `resources/views/portail/magic-link-request.blade.php:48,67` ne rendent que `@error('email')` et `@error('throttle')` — **pas de bloc `@error('ml')`**.
- **Impact** Les 3 messages de récupération les plus importants (« Lien manquant », « Ce lien n'est plus valide… demandez un nouveau lien », « Une erreur est survenue ») ne s'affichent jamais. Client au lien expiré → rebondi vers le formulaire sans explication → croit le système cassé. Pire moment (verrouillé, frustré), message perdu. La copie est excellente, elle n'est juste pas câblée.
- **Fix** Ajouter près des autres erreurs : `@error('ml')<div class="mt-4 text-sm text-danger bg-danger/10 ring-1 ring-danger/30 rounded-xl p-3">{{ $message }}</div>@enderror`. Supprimer les blocs d'erreur morts dans `confirm.blade.php:38-50`.

### [P0] Faux témoignages présentés comme avis clients réels (site commercial live)
- **Lens** Wording · **Surface** Vitrine
- **Location** `resources/views/vitrine/partials/testimonials.blade.php:17-22`
- **Impact** « Sandrine M., Les Trois-Îlets » et « Conciergerie du Sud, Sainte-Anne » sont des citations attribuées inventées. Viole « la preuve remplace le superlatif » + risque légal/confiance (faux témoignage attribué) sur un site marchand en ligne.
- **Fix** Gater derrière le composant `GoogleReviews` réel (déjà câblé en dessous), ou remplacer par placeholder `[À fournir par Pierre — avis réel / capture Google]` jusqu'à vérification. Ne pas livrer de noms inventés.

---

## P1 — Majeur (rupture de rendu / WCAG AA / cohérence marque)

### [P1] Pur `#fff` (`bg-white`/`text-white`) systémique — viole la loi « jamais #fff »
- **Lens** UI · **Surface** Vitrine + Admin + Email (transverse)
- **Location** ~90 hits vitrine (`partials/services-grid.blade.php:29,38,47`, `philosophie.blade.php:15,26,37,48`, `avant-apres.blade.php:5`, `pierre.blade.php:2`…) ; toutes les cartes admin (`stat-card.blade.php:13`, `clients/show.blade.php:19/52/74/109`, `topbar.blade.php:29`…) ; email magic-link.
- **Impact** `white` non redéfini dans `@theme` → `oklch(1 0 0)` pur. DESIGN.md §6 : « jamais #000/#fff ». Cartes blanches froides sur shell sable tiède = exactement le rendu clinique que la marque évite. Plus gros tell « réflexe IA » du projet.
- **Fix** Option A (cheap, global) : `--color-white: var(--color-sand-50)` dans `@theme`. Option B : sweep `bg-white`→`bg-sand-50`, `text-white`→`text-sand-50`. Exceptions : `#25D366` WhatsApp, carte QR blanche intentionnelle.

### [P1] Tokens Tailwind v4 non définis → aucun CSS émis (rupture silencieuse)
- **Lens** UI · **Surface** Vitrine + Admin
- **Location** `vitrine/partials/philosophie.blade.php:4` (`bg-lagon-50/40`) ; `admin/agenda/index.blade.php:59` (`text-warn-700`), `:76` (`text-ink-600`)
- **Impact** En Tailwind v4 CSS-first, seules les nuances déclarées dans `@theme` génèrent des utilities. `lagon` commence à 300, `warn` n'a pas de `-700`, `ink` n'a pas de `-600`. Ces classes n'émettent **rien** → fond de section perdu, texte chip/note retombe sur couleur héritée (hiérarchie/contraste cassés en silence).
- **Fix** Ajouter les nuances manquantes à `@theme`, ou utiliser des tokens existants : `bg-lagon-50/40`→`bg-sand-100`/`bg-azure-50/40` ; `text-warn-700`→token ambre déclaré ; `text-ink-600`→`text-ink-700`. **Recommandation : ajouter un garde-fou (grep CI sur classes de tokens non déclarés).**

### [P1] tu/vous incohérent dans la copie opérateur
- **Lens** Wording · **Surface** Admin + PWA offline
- **Location** Dashboard `dashboard.blade.php:19-21` (« Ta semaine ») vs `client-index.blade.php:72` / `post-index.blade.php:78` (« votre premier… ») ; form `create.blade.php:55,88,344,404-408` (tu) vs `passage-index.blade.php:118` / `offline.blade.php:16-17` (vous) ; toasts JS `passage-form.js:184` (vérifiez) vs `:518` (corrige) — **incohérent dans le même fichier**.
- **Impact** Même utilisateur (Pierre), deux registres à deux clics d'écart. Le brief autorise `tu` pour l'outil de Pierre, mais exige la cohérence.
- **Fix** Choisir UN registre opérateur (`tu` colle à l'intimité outil-solo) et aligner empty states admin, `offline.blade.php`, et toutes les chaînes JS. `vous` reste strict pour le client-facing.

### [P1] Aucun état loading sur les listes live-search
- **Lens** UX · **Surface** Admin
- **Location** `livewire/client-index.blade.php:22-28`, `post-index.blade.php:22-28`
- **Impact** `wire:model.live.debounce.300ms` → aller-retour serveur sans aucune visibilité d'état (Nielsen #1). Sur réseau mobile Martinique la liste fige 300ms+latence puis swap. La loi demande skeleton plutôt que rien.
- **Fix** `wire:loading.class="opacity-50"` sur le conteneur + ligne skeleton `wire:loading`, `wire:target="search"`.

### [P1] Aucun état submitting sur les boutons auth (double-submit)
- **Lens** UX / UI · **Surface** Auth
- **Location** `login.blade.php:90`, `forgot-password:55`, `reset-password:72`, `magic-link-request:52`, `confirm:57`
- **Impact** POST serveur nu, sans feedback. L'envoi magic-link a un `usleep` délibéré 1-3s (`controller:37`) → double-tap « Recevoir mon lien » = 2 requêtes → throttle.
- **Fix** Alpine `@submit` : désactiver le bouton + label « Envoi… » (respecter prefers-reduced-motion, pas de spinner animé requis).

### [P1] Titre admin client edit rend littéralement `{{ $client->name }}`
- **Lens** Wording · **Surface** Admin
- **Location** `admin/clients/edit.blade.php:3`
- **Impact** Blade n'interpole pas `{{ }}` dans le 2e argument quoté de `@section`. L'onglet affiche littéralement `{{ $client->name }} — Modifier`. Cf. `blog/edit.blade.php:3` qui utilise correctement la forme bloc.
- **Fix** `@section('title', $client->name . ' · Modifier · Dlo Azur')`.

### [P1] Étoiles Google Reviews codées en dur à 5 + couleur off-token
- **Lens** UI / UX · **Surface** Vitrine
- **Location** `livewire/google-reviews.blade.php:17,49`
- **Impact** `text-yellow-400` = jaune Tailwind par défaut, pas le token `sun`. Les étoiles résumé `★★★★★` sont codées à 5 quel que soit `$avg` ; les étoiles par avis n'affichent que les pleines (pas de reste vide) → un avis 3★ paraît « ★★★ » sans échelle. Trompeur.
- **Fix** `text-sun-500` ; rendre pleines/vides sur 5 (arrondir `$avg`) ; rendre aussi les vides par avis.

### [P1] Email magic-link : hex codés en dur + side-stripe `border-left`
- **Lens** UI · **Surface** Email / Portail
- **Location** `resources/views/emails/magic-link.blade.php:8,9,14,16,31,37`
- **Impact** L'artefact de confiance n°1 du flux sans mot de passe : `background: white`, `#1a2c40`, `#0080ff`, `#154c79`, `border-left: 3px solid #e2e8f0` (la seule vraie side-stripe décorative bannie du projet). Hex tolérable en email (clients strippent OKLCH), mais transcription manuelle des couleurs marque qui dérivera des tokens.
- **Fix** Remplacer le `border-left` par un `border` complet / fond teinté (pas de side-stripe) ; commenter le mapping canonique hex↔token ; vérifier `#0080ff`/`#154c79` = azure-500/navy-900 rendus.

### [P1] « Eau saine » / « idéal » asserté sans lien à la preuve
- **Lens** UX · **Surface** Portail
- **Location** `livewire/portail/passage-timeline.blade.php:64-70, 99-100, 114-115, 129-130`
- **Impact** « Eau saine » s'affiche dès qu'un dernier passage existe (`@if ($lastPassage)`), pas parce que les mesures sont dans la plage. Un concierge B2B transférant ça comme preuve à un propriétaire pourrait montrer « Eau saine » sur un pH hors plage. Superlatif non adossé à la preuve d'à côté.
- **Fix** Gater le badge « Eau saine » sur la même logique in-range déjà calculée (`$phOk`/`$clOk`/`$tacOk`). Hors plage → supprimer le badge ou état neutre. La preuve pilote la revendication.

### [P1] Pierre nommé en copie marketing au-delà des 2 zones autorisées
- **Lens** Wording · **Surface** Vitrine — *décision Antoine requise*
- **Location** `philosophie.blade.php:32,54`, `final-cta.blade.php:11,24`, `contact-form.blade.php:12,24`, les 4 `zones/*.blade.php` (« Appeler Pierre »), `services/depannage.blade.php:14,20,53`, `services/analyse-eau.blade.php:79,82`, `diagnostic.blade.php:161,165`
- **Impact** DESIGN.md §6 interdit de dupliquer Pierre par son nom hors « Le pisciniste » + footer. ~10 surfaces le nomment. « Appeler Pierre » est chaleureux et peut-être délibéré.
- **Fix** Décider explicitement : si on garde → légaliser dans DESIGN.md ; sinon revenir à « Dlo Azur » / « nous » et garder Pierre dans `partials/pierre.blade.php` + footer + légal.

---

## P2 — Mineur (contournement existe)

### [P2] Layout admin : landmarks imbriqués + `<nav>` mort
- **Lens** UI / A11y · **Surface** Admin
- **Location** `layouts/admin.blade.php:18-20, 25-27, 35`
- **Impact** `<aside>` du layout enveloppe `@yield('sidebar')` mais `sidebar.blade.php:12` est déjà un `<aside>` → **landmarks complémentaires imbriqués** ; idem `<header>`/topbar (banner imbriqués). Ligne 35 émet un `<nav>` bordé vide permanent en mobile (la vraie bottom-nav est injectée par page) → barre vide parasite.
- **Fix** Wrappers layout en `<div>` (les composants possèdent le landmark) ; supprimer le `<nav>` mort L35.

### [P2] Dashboard = grille 4 cartes identiques + métriques mi-vanity sans liens
- **Lens** UX · **Surface** Admin
- **Location** `admin/dashboard.blade.php:30-49`
- **Impact** 4 cartes identiques = tell dashboard IA résiduel. Pour un solo ~10 passages/sem, « Clients actifs »/« Passages cette semaine » sont vanity ; les signaux actionnables (« À synchroniser », « Eau à surveiller ») sont des pairs noyés. **Aucune carte n'est cliquable** → « Eau à surveiller : 3 » est un cul-de-sac. La vraie vue utile existe déjà (`agenda/index` « Aujourd'hui » + « À revoir ») mais sur une autre page.
- **Fix** Rendre les cartes warn/offline cliquables vers vues filtrées ; démoter/fusionner les 2 comptes vanity ; remonter le bloc agenda du jour sur le dashboard (ou rediriger l'accueil opérateur vers agenda) ; casser l'uniformité de la grille.

### [P2] « Récap mensuel » orphelin — aucun lien de nav
- **Lens** UX · **Surface** Admin
- **Location** `admin/recap/index.blade.php` (absent de `sidebar.blade.php` et `mobile-bottom-nav.blade.php`)
- **Impact** Page complète avec route mais inatteignable sans taper l'URL (recognition-not-recall).
- **Fix** Ajouter un item « Récap » sidebar (+ envisager bottom-nav) ou lien depuis le dashboard.

### [P2] Historique client : dates nues, sans contexte ni lien, pagination intra-carte
- **Lens** UX · **Surface** Admin
- **Location** `admin/clients/show.blade.php:111-117`
- **Impact** Lignes `d/m/Y` sans résumé (ni chimie, ni « à revoir ») ni lien vers le passage. `->paginate(10)` dans un forelse rend des contrôles de pagination en plein milieu de carte qui rechargent toute la page client.
- **Fix** Rendre les lignes cliquables vers le détail passage, ajouter un résumé 1-ligne (chlore/pH/actions), montrer « N derniers + voir tout » ou déplacer l'historique complet.

### [P2] Bottom-nav mobile : Blog absent, slot gâché sur Factures grisé
- **Lens** UX / Consistency · **Surface** Admin
- **Location** `mobile-bottom-nav.blade.php:8` (`grid-cols-5`) vs `sidebar.blade.php` (6 items)
- **Impact** Desktop expose Accueil/Agenda/Clients/Passages/Blog ; mobile expose Accueil/Agenda/Clients/Passages/Factures(grisé) → **Blog inatteignable en mobile** et un slot précieux pris par un teaser désactivé. Badge pending mobile `aria-hidden` (`:55`) vs desktop `aria-live=polite` (`sidebar:93`) → opérateur mobile pas notifié des syncs.
- **Fix** Retirer Factures grisé du mobile ; ajouter Blog si utile sinon documenter le sous-ensemble ; mirrorer `aria-live=polite` + label sur le badge mobile.

### [P2] Steppers au plancher 44px, pas la signature 56px
- **Lens** UI · **Surface** PWA offline
- **Location** `admin/passages/create.blade.php:120-124, 135-139` (`w-11 h-12`)
- **Impact** 44×48px : WCAG OK mais sous la signature ~56px pour usage pouce/soleil confiant. Le split azure(+)/sable(−) et les tokens sont corrects.
- **Fix** Passer à `w-14 h-14` (56px) si la grille le permet, ou au moins `w-12`.

### [P2] `aria-live="assertive"` sur le bandeau hors-ligne (trop agressif)
- **Lens** UX / A11y · **Surface** PWA offline
- **Location** `admin/passages/create.blade.php:76`
- **Impact** Le bandeau est rassurant, pas urgent ; `assertive` coupe la parole au lecteur d'écran. La loi dit hors-ligne **calme** (le visuel ambre est correct).
- **Fix** `aria-live="polite"`.

### [P2] Pas de feedback succès quand la file se vide réellement
- **Lens** UX · **Surface** PWA offline
- **Location** `sync-drawer.js:77-87`, `app.js:88-92`
- **Impact** À la synchro réussie le badge disparaît (count→0) sans confirmation positive (« Tout est synchronisé ✓ ») là où Pierre a déclenché. Nielsen #1 faible au moment qui compte.
- **Fix** Après flush réussi, surface brève confirmation calme (token success) sur la page déclencheuse même drawer fermé.

### [P2] CTA vitrine au libellé trompeur
- **Lens** UX · **Surface** Vitrine
- **Location** `partials/espace-client-teaser.blade.php:9` (« Voir un exemple d'espace client » → route `contact`)
- **Impact** Le bouton promet une démo/preview mais atterrit sur le formulaire contact (mismatch).
- **Fix** Pointer vers un vrai portail démo lecture-seule, ou relibeller « Demander un accès » / « En savoir plus ».

### [P2] Portail : pas d'état vide pour photos / hero / historique à 1 passage
- **Lens** UX · **Surface** Portail
- **Location** `passage-timeline.blade.php:208` (`count() > 1`), `180`, `30`
- **Impact** Client avec exactement 1 passage voit « Historique » nu (branche `>1` sautée, vide ne se déclenche qu'à `isEmpty()`). Passage sans photo → preuve absente sans message. Pour l'hospitalité « où est la preuve photo ? » EST le produit.
- **Fix** État 1-passage (« Votre premier passage apparaît ci-dessus. L'historique se remplira à chaque entretien. ») ; si pas de photo, « Photo non disponible pour ce passage ».

### [P2] Portail : échec `temporaryUrl` rend un `<img src="">` cassé
- **Lens** UX / UI · **Surface** Portail
- **Location** `passage-timeline.blade.php:183-196`
- **Impact** Sur exception Storage/R2, catch met `$firstPhotoUrl=''` puis rend `<img src="">` → icône image cassée dans le slot preuve. Pire surface d'échec pour la promesse « preuve ».
- **Fix** Garde `@if ($firstPhotoUrl)` autour du `<img>`, message fallback en `@else`.

### [P2] Glyphes emoji bruts off-token pour statuts (vitrine)
- **Lens** UI · **Surface** Vitrine
- **Location** `contact-form.blade.php:10` (`✓` text-3xl), `services/eau-verte-urgence.blade.php:124,139`
- **Impact** Checkmarks unicode nus rendus en police emoji système, incohérents avec `<x-icon.check>` SVG utilisé partout.
- **Fix** `<x-icon.check>` dans un chip `bg-success/10`.

### [P2] Auth : tab client désactivé sur /login double la vraie page magic-link
- **Lens** UX · **Surface** Auth
- **Location** `login.blade.php:97-121` (« Bientôt disponible ») vs `portail/magic-link-request.blade.php` (live)
- **Impact** Deux portes « Espace client » : `/login` dit pas-encore-dispo, `/auth/magic` est fonctionnel. État incohérent.
- **Fix** Faire du CTA du pane désactivé un lien vers `route('portail.magic-link.request')`, ou cacher le tab jusqu'au lancement.

### [P2] Démo-login correctement gaté mais visuellement = CTA primaire réel
- **Lens** UI · **Surface** Auth
- **Location** `magic-link-request.blade.php:71-100` (gaté `config('app.demo_login')`)
- **Impact** « Démo Client » réutilise `bg-azure-500` primaire → 2 primaires azur empilés sur serveur démo.
- **Fix** Démoter « Démo Client » au style secondaire (blanc/ring) comme « Démo Admin ».

---

## P3 — Polish (sans impact réel)

- **[P3] Em dashes dans la prose** — Vitrine `philosophie.blade.php:32`, `services/depannage.blade.php:56`, `services/entretien-recurrent.blade.php:105` ; Admin titres `clients/edit.blade.php:3`, `blog/edit.blade.php:3`. → virgule/`·`. (Les `–` en plages numériques `7,2–7,6` sont des en-dashes, OK.)
- **[P3] tu/vous dashboard** « Ta semaine » vs empty states « votre » (déjà couvert P1, nuance dashboard). → aligner.
- **[P3] `number_format` séparateur littéral** — `google-reviews.blade.php:15` `'\u{202F}'` en quotes simples = chaîne littérale `\u{202F}`. Invisible aujourd'hui (1 décimale) mais bug latent. → `"\u{202F}"` ou narrow no-break space direct.
- **[P3] `oklch()` inline décoratifs** — `diagnostic.blade.php:121,125,133` redéclarent lagon inline. → `bg-lagon-500/12 text-lagon-600`.
- **[P3] Topbar search désactivée morte** — `topbar.blade.php:28-31` input grisé sur dashboard/agenda/recap/show. → cacher sauf où câblé, ou bouton vers `clients.index?search=`.
- **[P3] Chip count agenda en azur** — `agenda/index.blade.php:29-31` azur (couleur sélection) pour un compte neutre. → chip sable `bg-sand-100 text-ink-500`.
- **[P3] Portail alt hero générique** — `passage-timeline.blade.php:37` `alt="Votre piscine"` vs `:193` daté. → alt daté quand `$heroPhotoUrl` est la vraie photo.
- **[P3] Portail « Cl libre » sous-label unit/verdict** — `:115,130` `idéal` remplace `mg/L` quand in-range (l'unité disparaît quand tout va bien, 3 tuiles incohérentes). → garder l'unité toujours, verdict dans un élément séparé (dot/check).
- **[P3] Portail « Mot du pisciniste » avatar `P` codé en dur** — `:168-176`. → initiale dérivée du nom opérateur ; envisager « Le mot de Pierre ».
- **[P3] Portail expiry contradictoire** — `confirm.blade.php:67` (« Usage unique par session ») vs `magic-link.blade.php:38` (« jusqu'à 3 fois »). → aligner sur la vraie config backend.
- **[P3] Auth hauteur boutons `h-12` (48px)** — brief = `≥3.25rem` (52px = `h-13`, utilisé par 404). → bump submits primaires à `h-13`.
- **[P3] WhatsApp hex inline** — `magic-link-request.blade.php:109`, `404.blade.php:23` `bg-[#25D366]` alors que token `--color-whatsapp` existe. → `bg-whatsapp`.
- **[P3] Composant-state : pas de succès inline** — `client-form`/`piscine-form`/`post-form` reposent sur redirect sans toast succès inline.

---

## Forces à préserver

- **Discipline « ne jamais perdre une donnée » réelle et commentée** : UUID au mount persisté en IDB avant toute saisie, autosave debouncé, échecs transitoires → `pending` (pas `error` mort), sync produit soft-fail. Posture correcte.
- **Moment hors-ligne géré émotionnellement** : bandeau ambre rassurant, double écran de confirmation synchronisé/sauvegardé, jamais de rouge.
- **Copie sécurité textbook** (auth) : anti-énumération bout en bout, délai aléatoire 1-3s, message non-divulguant ; confirm page statique SafeLinks-safe documentée (D-50/D-52/D-54).
- **Vouvoiement client + deux-publics-une-voix nailés** : chaleureux (« À bientôt sur le bassin ») et irréprochable B2B (« Données hébergées en Europe », preuve photo datée).
- **Système de tokens OKLCH bespoke** : rampes réelles, ombres teintées marine, focus-visible + prefers-reduced-motion globaux, tabular-nums sur les mesures.
- **Empty states admin qui enseignent** (client-index/post-index : titre + action + CTA), confirm inline progressif au lieu de modal.
- **AI-slop LOW partout** : pas de gradient text, pas de glassmorphism décoratif, pas de hover:scale, pas de modal-first.

---

## Plan d'action recommandé (ordre de priorité)

1. **P0 PWA offline (data)** — monter sync-drawer dans le layout + flush en store partagé ; récupérer les zombies `uploading`. *Le cœur de valeur.*
2. **P0 Auth** — câbler `@error('ml')` (message déjà écrit).
3. **P0 Vitrine** — retirer/remplacer les faux témoignages.
4. **P1 Theming** — `--color-white: sand-50` + ajouter/corriger tokens non définis (`lagon-50`, `warn-700`, `ink-600`) + garde-fou CI.
5. **P1 Wording** — uniformiser tu/vous opérateur ; fix titre client edit ; étoiles Google reviews.
6. **P1 États** — loading listes live-search + submitting boutons auth.
7. **P1 Portail** — gater « Eau saine » sur in-range ; email side-stripe.
8. **Décision Antoine** — Pierre nommé en marketing : légaliser ou revert.
9. **P2 puis P3** — passes successives (`/impeccable clarify`, `/impeccable layout`, `/impeccable harden`, `/impeccable polish`).

Commandes impeccable adaptées : `clarify` (wording/tu-vous/erreurs), `harden` (états, edge cases, empty states), `layout` (dashboard grille/hiérarchie), `polish` (passe finale). Re-run `/impeccable audit` après corrections pour voir le score remonter.

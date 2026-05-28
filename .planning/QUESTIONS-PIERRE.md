# Questions ouvertes à arbitrer avec Pierre

> **But.** Liste consolidée des inputs et décisions qui ne peuvent pas être tranchés sans Pierre — extraits de tous les artefacts planning (`PROJECT.md`, `REQUIREMENTS.md`, `ROADMAP.md`, CONTEXT/PLAN/SUMMARY de Phase 1, recherche). À parcourir lors d'une session dédiée avec lui.
>
> **Légende.** 🔴 bloquant cutover Phase 1 · 🟠 bloquant pour démarrer une phase ultérieure · 🟡 à clarifier mais non bloquant · 🔵 stratégique (pas d'urgence)
>
> **Mise à jour.** 2026-05-28 (générée pendant `gsd-discuss-phase 4`, étendue à toutes les phases à la demande)

---

## 0 — Cohérence à régler en premier (3 min, gratuit)

### 0.1 Numéro de téléphone pro 🟡
- Tout le code et la mémoire (`brand-identity.md`) référencent `+596 696 94 00 54`.
- Vérifier qu'il s'agit bien du **WhatsApp Business** (vs simple ligne mobile) — Phase 4 a besoin d'un compte Meta Business attaché à ce numéro pour valider les templates.

### 0.2 Email expéditeur 🟡
- `contact@dloazurpiscines.com` est utilisé partout. OK pour la vitrine.
- → **Phase 4** : faut-il un alias `notifications@` séparé pour les compte-rendus / rappels (UX + tracking) ou tout part de `contact@` ?

---

## 1 — Phase 1 (Vitrine & Fondations) — avant cutover Plan 01-06

### 1.1 Photos réelles 🔴
Source unique acceptable (PRODUCT.md anti-references rejette le stock tropical). À fournir avant la mise en ligne :
- `assets/brand/photos/hero-pierre-piscine.jpg` — Pierre au travail, hero + OG image
- `assets/brand/photos/pierre-portrait.jpg` — portrait bio pour le bloc « Le pisciniste »
- `assets/brand/photos/entretien-dos-logo.jpg` — Pierre dos avec logo
- Galerie « Réalisations » (4-6 photos avant/après ou interventions notables)
- Galerie « Hospitalité B2B » (1-2 photos prouvant l'expérience conciergerie / location saisonnière)

### 1.2 Mentions légales 🔴
Stub `vitrine/mentions-legales.blade.php` marqué `TODO: Pierre à compléter avant cutover`. Il faut :
- **SIRET** de Dlo Azur Piscines
- **RCS** (Registre du Commerce et des Sociétés) — ville + numéro
- **Forme juridique** (EI / SASU / autre) et **capital social** si société
- **Adresse pro** (siège social)
- **Nom du DPO** ou contact RGPD (Pierre lui-même si pas de DPO désigné — c'est légalement acceptable pour une TPE)
- **Hébergeur à mentionner** : Laravel Cloud (Mastodon Capital Inc., Frankfurt) + Cloudflare R2 (US, DPA signable)

### 1.3 CGV — mention TVA 🟡 (résolu côté principe)
**Pierre est auto-entrepreneur → franchise en base de TVA (art. 293 B du CGI).**
- CGV + factures doivent porter la mention obligatoire : **« TVA non applicable, art. 293 B du CGI »**.
- Le stub `cgv.blade.php` TODO « confirmer taux TVA » devient : remplacer par la mention art. 293 B + suppression de toute colonne TVA dans les factures.
- → Reste à vérifier avec Pierre : **seuil de CA en cours** ? (En métropole 2026 : 36 800 € prestations services + tolérance 39 100 €. En DOM Martinique, mêmes seuils que métropole pour la franchise en base.) Quand Pierre s'approche du seuil, il faut basculer en assujetti — donc le modèle de données factures **doit prévoir la colonne TVA en nullable / disabled** dès Phase 1 pour ne pas refactor en urgence le jour J.

### 1.4 Tarif indicatif « À partir de XX€/passage » 🔴
Décision D-32 : afficher un tarif d'appel pour se démarquer de la concurrence locale qui reste opaque.
- Variable d'env `PRICING_PASSAGE_STARTING` (default 80 € dans config)
- → **Quelle valeur exacte** Pierre veut-il afficher ? (Idée : prix d'entrée pour piscine standard 30 m³, accès simple.)

### 1.5 Avis Google — clés API 🔴
Décision D-28 : intégration server-side via Google Places API + cache DB.
- → **Créer / fournir** :
  - `GOOGLE_PLACES_API_KEY` (générer dans Google Cloud Console, restreindre à l'IP Laravel Cloud + API "Places")
  - `GOOGLE_PLACE_ID` (récupérer via [Place ID Finder](https://developers.google.com/maps/documentation/places/web-service/place-id) pour la fiche Google Business de Dlo Azur)
- Si pas configuré : la section avis Google se masque toute seule (graceful), mais on perd un levier de social proof important.

### 1.6 DNS Brevo (email transactionnel) 🔴
Plan 01-04 a câblé Brevo mais `MAIL_MAILER=log` en staging.
- → **Activer les enregistrements DNS Brevo sur Hostinger** :
  - DKIM (clé fournie par Brevo)
  - SPF (`v=spf1 include:spf.brevo.com ~all`)
  - DMARC (`v=DMARC1; p=none; rua=mailto:...`)
- Une fois DNS « Active » côté Brevo dashboard → flip `MAIL_MAILER=log` → `brevo` dans les vars Laravel Cloud (Carry-over #4 du Summary 01-01).

### 1.7 Choix sous-domaine staging 🟡
D-21 laisse deux options :
- **Option A** : `preprod.dloazurpiscines.com` (sous-domaine du domaine final) → meilleure UX, montre que c'est sérieux.
- **Option B** : URL Laravel Cloud par défaut `dloazur-main-s8e8er.laravel.cloud` → zéro friction DNS.
- Aujourd'hui Pierre est sur **Option B** (staging tourne déjà). → Vouloir basculer sur A avant cutover ?

### 1.8 Cutover Zyro → Laravel Cloud 🔴
D-24/D-25 : acte opérationnel piloté par Pierre.
- **Inventaire URLs Zyro indexées** : Pierre fait-il un export depuis Google Search Console ? Sinon, audit manuel.
- **Date de bascule** : quand est-il prêt ? (Suggestion : viser un milieu de semaine, hors saison haute, baisser le TTL DNS à 300 s 24 h avant.)
- **Accès Hostinger DNS** : confirmer que Pierre a les credentials (mémoire `hostinger-access.md`) ou qu'on les a sous la main pour modifier les CNAME.

### 1.9 Upgrade Postgres Dev → Prod tier 🔴
Carry-over #5 du Summary 01-01 : Dev tier hiberne à 300 s et n'a pas de backups.
- Avant cutover, upgrade vers Prod tier (~10-15 €/mois supplémentaires) + activer PITR 7 jours.
- → **Pierre OK pour ce coût récurrent ?** (Total estimé Laravel Cloud + Postgres Prod ≈ 20-25 €/mois.)

### 1.10 Rotation secrets exposés en session 🔴
Carry-overs #1 + #2 : `DB_PASSWORD` et `LARAVEL_CLOUD_API_TOKEN` ont transité par des transcripts Anthropic.
- À **rotater avant toute donnée client réelle**. Pierre n'a rien à faire côté UX, mais à savoir que ça doit être planifié.

---

## 2 — Phase 2 (MVP suivi offline-first)

### 2.1 Volume et import des clients existants 🟠
- **Combien de clients actifs** aujourd'hui ? (PROJECT.md dit « une dizaine à l'année ».)
- Pierre a-t-il déjà un **fichier client** (Excel, Numbers, contacts iPhone, Odoo, …) ?
- **Stratégie d'import** : on lui prépare un template CSV ? Saisie manuelle accompagnée ? Migration Odoo si plan permet ?

### 2.2 Données piscines par client 🟠
Pour chaque piscine il faut : **volume (m³)**, **type filtration** (sable / cartouche / diatomée), **type traitement** (chlore, sel/électrolyseur, brome…), **équipements** (PAC, projecteur, surpresseur…), **adresse**, **photo si possible**.
- → **Forme de cette saisie** : Pierre fait-il tout à la création initiale, ou progressive lors des premiers passages ?
- → Cas multi-piscines / même client (PROJECT.md hors-scope UI mais modèle flexible) : combien de clients concernés ?

### 2.3 Magic link client 🟡
- **Durée d'expiration** d'un magic link : 24 h ? 7 jours ? 30 jours ?
- **Nombre max d'utilisations** avant péremption ? (Recommandation : illimité, expire seulement par durée.)
- **Première installation** : qui envoie le premier lien aux clients ? Email automatique de bienvenue à activer ?

### 2.4 Téléphone client / format WhatsApp 🟡
- Pierre stocke-t-il systématiquement les numéros au format international `+596 …` ?
- → Décision : forcer `+596` par défaut dans le formulaire client, autoriser override manuel.

### 2.5 Photos passage : combien par passage ? 🟡
- Estimation actuelle : 2-5 photos par passage (bassin avant, après, équipement éventuel).
- Impact stockage R2 : ~15 ko/photo × 5 × 10 passages × 50 sem ≈ 40 Mo/an → free tier R2 couvre 250 ans à ce rythme. Pas d'angle financier à creuser.

---

## 3 — Phase 3 (Facturation & Odoo)

> **⚠ Divergence ROADMAP** : Phase 3 success criterion #3 mentionne « TVA 8,5 % et numérotation séquentielle CGI ». Or Pierre est en franchise en base (art. 293 B) → **pas de TVA**. À corriger dans ROADMAP.md et REQUIREMENTS.md (FACT-03) au moment de discuter Phase 3. Le modèle `factures` doit malgré tout prévoir une colonne `tva_taux` nullable pour le jour où Pierre franchira le seuil et basculera en assujetti.

### 3.1 POC Odoo — sortie binaire 🔴 (bloquant Phase 3)
**Plan Odoo actuel** :
- Custom (29,90 €/user/mois) → API XML-RPC accessible → intégration directe (FACT-04)
- Plan inférieur → **pont CSV** (export Odoo → import Laravel ou inverse)
- → **Quel est le plan Odoo de Pierre aujourd'hui ?** (À vérifier dans son back-office Odoo.)

### 3.2 Numérotation factures 🟠
Article 242 nonies A du CGI impose une séquence ininterrompue.
- **Continuer la numérotation Odoo existante** (ex : F2026-0042 → on prend la suite) ?
- Ou **redémarrer à 1** sur Dlo Azur (avec préfixe distinct ex : `DLA-2026-001`) ?
- Pierre a-t-il déjà émis des factures sous Dlo Azur Piscines ? Si oui, c'est imposé : on continue la séquence.

### 3.3 Catalogue produits / services 🟠
- **Liste des prestations** facturables (entretien, dépannage filtration, traitement choc, hivernage, mise en service…) avec **prix unitaire ou tarif horaire**.
- **Liste des produits** (chlore lent, choc, anti-algues, sel, tablettes, floculant…) avec **prix de revente**.
- Pierre peut-il extraire ce catalogue depuis Odoo ou il faut le ressaisir ?

### 3.4 Modèles de contrats 🟠
- **Forfait mensuel** : combien (50 € ? 80 € ? 120 €/mois) ? Inclut quoi (1 passage / semaine ? 2 / mois ?) ?
- **Forfait saisonnier** : période exacte (mars-octobre ?) + montant ?
- **Ponctuel** : tarif passage standard + suppléments éventuels (déplacement zone éloignée, traitement choc inclus ou non…).

### 3.5 Signature électronique 🟡
- FACT-07 : signature client sur le téléphone du pro. Niveau de conformité requis pour un compte-rendu de passage : **signature simple** (image base64 + timestamp + IP + hash PDF) suffit légalement en France pour ce type d'acte.
- → Pierre veut-il quelque chose de plus formel (e-IDAS niveau avancé) ? Non recommandé (coût + friction terrain pour aucun gain pratique).

### 3.6 Compte Stripe pro 🟡
(Anticipation pour Phase 3 si paiement portail v2 et pour Phase 5 diagnostic commercialisable.)
- Pierre a-t-il déjà un compte Stripe pro vérifié (KYC) ?
- Si non : créer en amont (vérification IBAN + pièce d'identité prend 1-3 jours).

---

## 4 — Phase 4 (Notifications) — la phase qui a déclenché ce document

### 4.1 WhatsApp : utile ou pas pour les notifs auto ? 🔵
- Aujourd'hui Pierre a un **CTA WhatsApp manuel** (boutons partout) pour le contact entrant.
- NOTIF-03 propose en plus des **notifications automatiques sortantes** (compte-rendu, rappel J-1) via WhatsApp.
- Coût Meta Business : 0,015 €-0,07 €/message selon le type de template (utility / marketing).
- Lead time : approbation template Meta = **1 à 5 jours ouvrés** (peut être plus pour nouveaux comptes).
- → **Pierre veut-il vraiment cette feature ?** Ou un email suffit et WhatsApp reste pour le contact manuel entrant ?

### 4.2 Si WhatsApp activé : compte Business existant ? 🟠
- Pierre a-t-il déjà un **compte WhatsApp Business** sur son téléphone, ou seulement WhatsApp perso ?
- Le numéro `+596 696 94 00 54` est-il déjà inscrit sur **Meta Business Platform** (différent de WhatsApp Business simple — c'est l'accès API) ?
- Si non : création + vérification du compte Meta Business est un prérequis (compte 1 semaine de procédure pour un BSP / Cloud API).

### 4.3 Vendor WhatsApp 🔵
Trois pistes principales si on active :
- **Brevo WhatsApp** : même vendor que Brevo Mail → single dashboard, facturation EU consolidée, ~0,03 €/msg
- **Twilio** : pilier historique, doc dense, ~0,005 €/msg, dashboard séparé
- **Meta Cloud API directe** : gratuit, mais configuration plus technique (webhooks, tokens longue vie)
- → Décision recommandée : **Brevo WhatsApp** par défaut (single dashboard, RGPD natif). À confirmer avec Pierre selon coût attendu.

### 4.4 Templates WhatsApp — texte exact à approuver 🟡
Si activation, il faut soumettre 2 templates à Meta pour approbation. Pierre doit valider le texte exact (variables entre `{{ }}`) :
- **Compte-rendu** : *« Bonjour {{1}}, votre passage du {{2}} est terminé. Compte-rendu disponible : {{3}}. — Pierre, Dlo Azur Piscines »* (3 variables : nom client, date, lien portail ou note simple)
- **Rappel J-1** : *« Bonjour {{1}}, petit rappel : passage prévu demain {{2}} chez vous. À demain ! — Pierre »* (2 variables : nom client, date)

### 4.5 Heure d'envoi du rappel J-1 🟡
- 18 h la veille (en fin de journée, lu avant la nuit) ?
- 8 h le matin de la veille ?
- → Recommandation : **18 h locale (Martinique UTC-4)**.

### 4.6 Rappel J-1 : Pierre aussi destinataire ? 🟡
NOTIF-02 dit « le pro et le client ». À confirmer : Pierre reçoit aussi un récap de sa journée du lendemain ? Sous quelle forme (email digest avec liste des passages + adresses) ?

### 4.7 Compte-rendu : PJ PDF ou lien portail ? 🟠
- **PDF en pièce jointe** : autonome, lisible offline, persiste dans la boîte mail (clients pas tech).
- **Lien magique vers portail client** : révocable, tracking, RGPD-friendly.
- → Recommandation : **les deux** (lien + PDF joint) ; le lien sert au tracking, le PDF rassure les clients moins tech.

### 4.8 Opt-out 🟡
- RGPD : transactionnel (compte-rendu) = exempt d'opt-in, mais **lien de désinscription** recommandé même pour transactionnel en UE.
- Où stocker l'opt-out : toggle dans la fiche client par Pierre côté admin ?
- WhatsApp : Meta exige un opt-out exprès (mot-clé « STOP » côté client → automation).

---

## 5 — Phase 5 (Diagnostic commercialisable)

### 5.1 Disclaimer légal 🟠
DIAG-03 : disclaimer obligatoire avant tout conseil de dosage chimique (responsabilité produits dangereux).
- → **Texte exact** à valider (idéalement par avocat ou modèle issu d'un site équivalent).
- Comment afficher : modale modale d'entrée ? Acceptation explicite (checkbox) ?

### 5.2 Stripe piste A (abonnement particulier) 🔵
- **Prix mensuel** d'un abonnement diagnostic ? (Ex : 4,90 €/mois pour suivi illimité ?)
- **Inclusions** : combien de diagnostics ? Historique conservé combien de temps ?
- **Période d'essai** (7j gratuits ?) ?

### 5.3 Stripe piste B (upsell client) 🔵
- **Prix one-shot premium** pour upgrade client existant ?
- **Différenciateur** vs version gratuite : doses plus précises ? Calendrier perso ? Photos avant/après ?

### 5.4 Arbre de décision diagnostic 🟠
- La logique métier vient de la **maquette React jetée**. Où est-elle archivée ?
  - `docs/superpowers/specs/2026-05-27-dloazur-refonte-design.md` ? À vérifier.
  - Sinon : Pierre la décrit oralement, on la retranscrit en JSON dans `config/diagnostic.php`.
- Cas couverts : eau verte, eau trouble, eau marron, problème filtration, problème électrolyseur — combien et lesquels précisément ?

### 5.5 Calcul de doses 🟠
- **Formules** par produit : Pierre les a en tête ou écrites quelque part ?
- **Validation chimique** : qui valide les formules (Pierre seul, ou consultant chimiste, ou fournisseur pro de produits) ?
- **Produits référencés** : on calcule en générique (« 25 g/m³ de chlore choc ») ou nominatif (« 2 sachets HTH Shock ») ?

---

## 6 — Identité légale, contenu et conformité (transverse)

### 6.1 Identité Dlo Azur Piscines 🔴
Récap des items à fournir pour les pages légales + factures + JSON-LD (statut **auto-entrepreneur / micro-entreprise** confirmé) :
- SIRET / SIREN
- N° RCS ou RM (artisan ?) + ville d'immatriculation
- Adresse pro (siège déclaré URSSAF)
- Code APE / NAF (probablement `81.29B` — autres activités de nettoyage)
- ~~Capital social~~ : n/a en EI
- ~~N° TVA intracommunautaire~~ : n/a (franchise en base art. 293 B)

### 6.2 Logo officiel 🟡
- Version vectorielle SVG existe-t-elle ? (Hors raster JPG/PNG des supports print.)
- Charte de couleurs déjà validée (mémoire `brand-identity.md` confirme azure `#0080ff` + marine + turquoise) — pas de changement prévu.

### 6.3 Politique de confidentialité 🟠
- Pages stub à rédiger : RGPD, droits utilisateur, cookies (zéro cookie tiers = mention courte suffit).
- Rédacteur : Pierre seul ? Avec assistance ? Modèle CNIL ?

### 6.4 CGV diagnostic 🟠
À rédiger spécifiquement pour Phase 5 (vente de prestations en ligne via Stripe — obligation légale).

### 6.5 DPA fournisseurs 🟡
À demander / vérifier :
- **Brevo** : DPA accessible dans le dashboard (Settings > Privacy)
- **Cloudflare R2** : DPA téléchargeable [ici](https://www.cloudflare.com/cloudflare-customer-dpa/) — accord signable par Pierre.
- **Laravel Cloud (Mastodon Capital)** : DPA à demander à `support@laravel.cloud`.
- **Google Places API** : Terms d'usage standard, pas de DPA séparé pour ce volume.
- **Stripe** (Phase 5) : DPA dans le dashboard sous Settings > Compliance.

### 6.6 Hébergeur DNS (Cloudflare ou Hostinger) 🟡
RESEARCH §"Décision DNS" : aujourd'hui le DNS est chez Hostinger (hérité du site Zyro).
- **Garder Hostinger** : zéro friction, mais panel DNS basique.
- **Migrer vers Cloudflare DNS** (gratuit) : meilleur panel, anycast plus rapide, prépare l'option R2 + Cloudflare Pages pour assets si besoin futur.
- → Recommandation : rester sur Hostinger en Phase 1, évaluer Cloudflare DNS en Phase 2 ou plus tard si besoin.

---

## 7 — Roadmap : décisions stratégiques 🔵

### 7.1 Ordre 3 ↔ 4
Phase 4 (Notifications) dépend de Phase 3 (PDF compte-rendu) selon la roadmap. Mais le **lead time approbation Meta** (1-5 jours) pourrait justifier de **lancer en parallèle** :
- Pendant Phase 2 (ou même Phase 1 si Pierre confirme l'envie WhatsApp), démarrer la création du compte Meta Business + soumettre les templates.
- Quand Phase 3 livre le PDF, Phase 4 peut envoyer dès J1.
- → À acter avec Pierre quand on aborde Phase 2.

### 7.2 Articles de blog 🔵
D-10/D-11 : blog markdown-in-repo, ~3-6 articles/an, pas de tags.
- → **Premiers articles** à écrire : Pierre rédige seul ou avec assistance ? Liste de sujets initiaux à lui demander (ex : « Eau verte : que faire ? », « Choisir son traitement chlore vs sel », « Hivernage piscine en Martinique »).

### 7.3 Saisonnalité Martinique 🔵
Climat tropical = pas de vraie morte-saison. Mais :
- **Pic d'algues** (saison humide juillet-octobre) → potentiellement marketing « Traitement eau verte d'urgence » (D-34).
- **Tourisme hospitalité B2B** : pic décembre-avril (haute saison touristique) → CTA conciergeries à booster à ce moment.

---

## 8 — Backlog d'éclaircissements techniques (Claude side)

Items que Claude peut trancher seul mais qui méritent que Pierre soit informé :

- **Cron Laravel Cloud** : le scheduler tourne en mode serverless → confirmer cadence (recommandation : 1×/h pour Phase 2-3, +1×/jour à 18 h locale pour rappels J-1).
- **Backup Postgres** : passer en Prod tier active 7d PITR auto. Pas d'action Pierre.
- **Monitoring / alerting** : Laravel Cloud envoie des alertes mail si build/deploy échoue. À configurer pour aller chez Pierre (vs `contact@`).
- **Logs Sentry / error tracking** : pas budgétisé. Si erreurs en prod, on lit les logs Laravel Cloud manuellement. À reconsidérer en Phase 2 si volume grandit.

---

*Fichier maintenu manuellement. Mettre à jour à chaque réponse de Pierre.*
*Source : génération automatique depuis `.planning/` 2026-05-28.*

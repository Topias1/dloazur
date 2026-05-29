# Zyro Content Harvest — dloazurpiscines.com

**Crawled:** 2026-05-29 (read-only GET, no writes)
**Method:** `curl -sL --max-time 30 -A "Mozilla/5.0 (compatible; DloAzurCrawler/1.0)"` on each URL from sitemap + footer discovery
**Crawler constraint:** T-9991-02-01 (threat model) — GET only; no POST/PUT/DELETE, no deploy, no DNS action.

---

## URL Inventory

Extends `.planning/phases/01-vitrine-fondations/ZYRO-URL-INVENTORY.md` (Phase 1, 8 sitemap URLs already mapped).

### All Zyro pages (crawled 2026-05-29)

| # | Zyro URL | HTTP | Title (confirmed) | Suggested Laravel Route | 301 Action |
|---|----------|------|-------------------|------------------------|------------|
| 1 | `https://dloazurpiscines.com/` | 200 | Entretien et nettoyage de piscines et spa | `/` | None (same URL post-cutover) |
| 2 | `/services-et-nettoyage` | 200 | Entretien et nettoyage de piscines professionnelles à Dlo Azur | `/services` | **301** (Phase 1) |
| 3 | `/nos-realisations` | 200 | Nos réalisations Dlo Azur piscines : Avant / Après | `/realisations` | **301** (Phase 1) |
| 4 | `/blog-list-nettoyage-piscine-professionnel` | 200 | Blog : Entretien piscine, nettoyage piscine professionnel | `/blog` | **301** (Phase 1) |
| 5 | `/page-article-blog-vierge` | 200 | Page article blog vierge (template/test page — no real content) | `/blog` | **301** (Phase 1) |
| 6 | `/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines` | 200 | De la passion à l'entrepreneuriat : l'histoire de Dlo Azur Piscines | `/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines` | Served directly (Phase 1 — Option 1 implemented) |
| 7 | `/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscine` | 200 | Same as above (typo variant — no trailing 's') | `/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines` | **301** (Phase 1) |
| 8 | `/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique` | 200 | Les 3 étapes indispensables pour un entretien de piscine parfait en Martinique | `/blog/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique` | Served directly (Phase 1 — Option 1 implemented) |
| 9 | `/conditions-generales` | 200 | Conditions générales - Terms and conditions | `/mentions-legales` | **301** recommended |

**Note:** `/mentions-legales` returns 404 on Zyro (no such page). Legal content lives at `/conditions-generales`. The Phase 1 cutover mentions-legales Laravel page should map the Zyro `/conditions-generales` → `/mentions-legales` 301.

**Discovery:** `/conditions-generales` was NOT in the sitemap but IS live — found via footer link scraping. Always crawl footer links, not only sitemap.

**Phase 1 carries over:** The 301 redirect map for URLs 2–8 (+ `/conditions-generales`) is already partially implemented in `routes/web.php` per Phase 1 work. URL 9 (`/conditions-generales` → `/mentions-legales`) may need to be added.

---

## Body Copy by Page

### Homepage (`/`)

**Page title:** "Entretien et nettoyage de piscines et spa | Dlo Azur piscines - Entretien et Nettoyage de Piscines à votre Service"

**Meta description:** "Service d'entretien et nettoyage de piscines en Martinique avec Dlo Azur Piscine. Experts locaux pour garder votre piscine propre, claire et fonctionnelle toute l'année. Offrez à votre piscine un soin professionnel avec des solutions sur mesure adaptées à vos besoins."

**H1:** "Dlo Azur Piscine — L'entretien de votre piscine en Martinique, notre priorité."

**Hero sub:** "Pour des piscines éclatantes et impeccables toute l'année."

**Section headings:**
- "Pourquoi choisir Dlo Azur Piscines ?"
- "Votre piscine mérite une eau parfaite"
- "Nos clients nous adorent !"
- "Contactez-nous pour vos besoins"

**"Pourquoi choisir" body (verbatim):**
> Dlo Azur Piscine, votre partenaire de confiance en Martinique pour le nettoyage, l'entretien et le traitement de vos piscines. Profitez d'une eau limpide et de services personnalisés adaptés à vos besoins. votre sérénité est notre priorité. Parce que votre piscine doit être que du plaisir ! Profitez d'une expertise locale en Martinique. Nous vous proposons des solutions adaptées à tous types de piscines. Service fiable et réactif.

**"Eau parfaite" body (verbatim):**
> Une piscine à l'eau limpide, équilibrée et saine est bien plus qu'un simple espace de détente : c'est le reflet de votre confort, de votre sécurité et de votre bien-être. Une eau parfaite garantit non seulement une baignade agréable, mais protège également vos équipements des dommages liés à un mauvais entretien. En Martinique, où les températures élevées et l'humidité peuvent favoriser la prolifération des algues et des bactéries, il est essentiel d'adopter un entretien rigoureux et adapté. Avec Dlo Azur Piscine, profitez d'une eau parfaitement traitée, grâce à des solutions professionnelles et sur mesure, pour faire de chaque baignade un moment de plaisir en toute sérénité.

**Testimonials section:** "Nos clients nous adorent !" — Zyro section is a call-to-action ("Laissez nous le votre 😍") linking to Google Maps, **no actual client quotes or attributed testimonials are present on the Zyro site**. The section is an empty slot with a CTA to leave a Google review.

**JSON-LD on homepage:**
```json
{
  "@context": "https://schema.org/",
  "name": "Entretien et nettoyage de piscines et spa",
  "url": "https://dloazurpiscines.com",
  "description": "Service d'entretien et nettoyage de piscines en Martinique avec Dlo Azur Piscine...",
  "@type": "WebSite"
}
```
(Note: type is `WebSite`, not `LocalBusiness` — D-01 in Phase 999.1 will fix this.)

---

### Services page (`/services-et-nettoyage`)

**Page title:** "Entretien et nettoyage de piscines professionnelles à Dlo Azur | Dlo Azur piscines"

**Meta description:** "Découvrez les services Dlo Azur piscines en Martinique. Des solutions sur mesure pour garder votre piscine impeccable, avec des prestations professionnelles adaptées à vos besoins : nettoyage, entretien régulier, traitement de la chimie de l'eau, réparations..."

**H1:** "Nos services"

**Section intro (verbatim):**
> Dlo Azur piscines, une expertise locale adaptée à notre climat et aux intempéries que peut subir la Martinque. Vous apprécierez notre service client réactif, professionnel et personnalisé. Decouvrez nos solutions sur mesure pour répondre à vos besoins, qu'ils soient ponctuels ou réguliers.

**Service 1: Nettoyage de piscine et remise en état**

Body (verbatim):
> Le climat tropical de la Martinique, avec son fort taux d'humidité, ses épisodes de pluies intenses, de brume de sables et son ensoleillement toute l'année, peut rapidement détériorer l'état de votre piscine. L'accumulation de feuilles, le développement d'algues et l'eau trouble sont des problèmes fréquents. Chez Dlo Azur Piscine, nous transformons même les piscines les plus oubliées en bassins limpides et accueillants.
>
> Les remises en état de piscines sont souvent réalisées en plusieurs fois. Un premier passage pour gérer la chimie de l'eau et frotter les parois et les autres passages pour aspirer le fond du bassin ainsi que nettoyer les derniers équipement (skimmer, refoulement, éclairages, pompe, filtration...)
>
> Cette formule est idéale pour les piscines oubliées plusieurs semaines/mois, les bassins affectés par les pluies tropicales, brume de sables ou périodes d'inactivité, une remise en état avant une saison de baignade ou un événement important, ainsi que toute réparation de système de filtration : Electrolyseur, filtre, pompe...

Checklist items:
- **Nettoyage intensif:** Élimination des feuilles, insectes et débris flottants. Brossage des parois et du fond pour enlever les dépôts et les algues. Aspiration des saletés incrustées avec des équipements professionnels.
- **Traitement de l'eau:** Analyse et ajustement des paramètres chimiques (pH, chlore ou sel, alcalinité, stabilisant). Chloration choc et anti-algues si nécessaire. Prévention des eaux troubles ou verdâtres, courantes sous notre climat.
- **Révision complète des équipements:** Vérification et entretien de la pompe et du filtre. Contrôle des skimmers et buses de refoulement pour une circulation optimale de l'eau. Rinçage et nettoyage du filtre pour éviter l'accumulation de saletés.

Astuce: "Après un nettoyage intensif, un entretien régulier permet de prolonger la propreté et la clarté de l'eau sans effort."

**Service 2: Entretien de piscines hebdomadaire**

Body (verbatim):
> Le secret d'une piscine impeccable sous le soleil martiniquais ? Un entretien régulier pour éviter l'accumulation d'algues, la détérioration du système de filtration et les déséquilibres chimiques.
>
> Dlo Azur Piscine vous propose des forfaits d'entretien adaptés à vos besoins. Vous cherchez une expertise locale ? Nous connaissons les défis posés par l'eau chaude, l'évaporation rapide et les fortes précipitations. Un entretien sur-mesure : Interventions hebdomadaires, bimensuelles ou à la demande, selon votre usage. Un suivi professionnel : nous adaptons nos traitements aux conditions météo et aux spécificités de votre piscine.

Checklist items:
- **Nettoyage et entretien:** Enlèvement des impuretés (feuilles, insectes, sable). Nettoyage des parois et de la ligne d'eau. Aspiration du fond de la piscine.
- **Analyse et ajustement de l'eau:** Vérification du pH, du taux de chlore ou de sel, de l'alcalinité et du stabilisant. Ajustement des produits pour une eau saine et confortable.
- **Contrôle des équipements:** Vérification et nettoyage du préfiltre de la pompe. Backwash (rétrolavage) du filtre à sable pour une filtration efficace. Contrôle des buses de refoulement et skimmers.

Astuce: "Optez pour un contrat annuel et bénéficiez d'un bassin parfaitement entretenu toute l'année, sans y penser !"

**Service 3: Montage de piscine hors sol (et jacuzzi)**

Body (verbatim):
> Vous souhaitez installer une piscine hors sol pour vous rafraîchir rapidement sans engager de gros travaux ? Que ce soit une piscine en acier, en bois ou autoportante, nous vous accompagnons de A à Z pour une mise en place rapide et efficace.

Checklist items:
- **Conseil et sélection du modèle:** Aide au choix selon votre terrain, budget et utilisation. Explication des avantages des différents types de piscines hors sol (bois, acier, autoportante). Conseil sur le type de filtration adapté au climat tropical.
- **Préparation du terrain:** Vérification du nivellement du sol pour éviter les affaissements. Mise en place d'un revêtement adapté (dalle, tapis de sol). Vérification du système de drainage pour éviter les stagnations d'eau en saison humide.
- **Montage et installation:** Assemblage sécurisé de la structure. Installation et raccordement de la pompe, du filtre et des accessoires. Test complet pour garantir un fonctionnement optimal dès le premier bain !

**Nav explicitly lists: "Montage de piscine hors sol et jacuzzi"** — spa/jacuzzi is mentioned in the navigation label and in the title ("Entretien et nettoyage de piscines et spa"), confirming D-11.

Why trust us (verbatim):
> Une expertise locale adaptée au climat tropical : Nous connaissons les défis spécifiques des piscines en Martinique et apportons des solutions efficaces.
> Un service client réactif, professionnel et amical : Nous sommes à l'écoute de vos besoins et vous conseillons au mieux.
> Des prestations sur-mesure : Que vous ayez besoin d'un nettoyage ponctuel, d'un entretien régulier ou d'une installation de piscine, nous avons la solution idéale pour vous.
> Contactez nous dès aujourd'hui pour un devis gratuit et plongez dans une piscine impeccable sans contrainte !

---

### Réalisations page (`/nos-realisations`)

**Title:** "Nos réalisations Dlo Azur piscines : Avant / Après"

**Body (verbatim):**
> Coming soon... Dlo Azur piscines se fait une beauté. En attendant, n'hésitez pas à nous suivre sur nos réseaux.

**Assessment:** The Zyro /nos-realisations page has zero real case study content — it's a "coming soon" placeholder. The current Laravel `/realisations` page is also thin (~108–145w per audit). D-13 case study expansion requires Pierre to supply real chantier facts (commune, pool type, before/after measures). No Zyro content to harvest here.

---

### Blog index (`/blog-list-nettoyage-piscine-professionnel`)

**Title:** "Blog : Entretien piscine, nettoyage piscine professionnel, filtration et traitement de l'eau"

**H1:** "Blog Dlo Azur piscines"

**Intro (verbatim):**
> Découvrez tous nos conseils et astuces pour l'entretien de votre piscine en Martinique ! Nettoyage, filtration, équilibre de l'eau : Dlo Azur Piscine vous guide pour un bassin éclatant toute l'année. Profitez d'une eau limpide sans effort grâce à nos expertises et recommandations professionnelles.

---

### Blog article 1: De la passion à l'entrepreneuriat

**Slug:** `/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines`
**Date (Zyro metadata):** `2025-01-31T14:08:26.999Z`
**Author:** Dlo Azur piscines
**Read time:** not extracted (Zyro field not visible in server-rendered HTML)

**H1:** "De la passion à l'entrepreneuriat : l'histoire de Dlo Azur Piscines"

**Article body sections and verbatim content:**

*L'eau, le bleu, et moi : une histoire de longue date / Quand la piscine devient une contrainte (intro):*
> Depuis toujours, j'ai été fasciné par les beaux extérieurs. Un jardin bien entretenu, une terrasse accueillante, et surtout une piscine aux reflets cristallins : voilà, pour moi, l'image du bien-être absolu. J'ai toujours vu la piscine comme un lieu de convivialité, un endroit où l'on se retrouve en famille ou entre amis, où l'on oublie le stress du quotidien en plongeant dans une eau rafraîchissante. Mais lorsque j'ai commencé à travailler dans le domaine, j'ai découvert une autre facette de cette réalité…

*L'aventure entrepreneuriale : du rêve à la réalité:*
> Eau verte, pompe en panne, produits mal dosés, liner qui se tache… À chaque problème, les clients arrivaient découragés, avec la sensation de ne jamais en voir le bout. Certains regrettaient presque d'avoir investi dans une piscine, tant l'entretien leur semblait compliqué. J'ai alors commencé à les conseiller, à leur expliquer comment simplifier la gestion de leur bassin, et surtout à les aider à retrouver le plaisir d'une piscine toujours propre, sans effort. C'est là que j'ai eu un déclic : et si j'allais plus loin en proposant un service clé en main ?
>
> Créer ma propre entreprise, ça a toujours été dans un coin de ma tête. Mais entre l'idée et la mise en œuvre, il y a un monde… et un paquet d'administratif ! Quand j'ai décidé de me lancer avec Dlo Azur Piscine, je savais que l'entrepreneuriat ne serait pas de tout repos.

*Prior experience mentioned:*
> J'ai passé plusieurs années en tant que directeur adjoint d'un magasin spécialisé dans les piscines. Ce poste m'a permis d'acquérir une expertise technique solide, mais aussi d'être en contact direct avec les propriétaires de piscines.

*Et demain ?:*
> Aujourd'hui, Dlo Azur Piscine continue de grandir. Un des moments qui me marquent le plus, c'est quand un propriétaire me dit : "Avant, ma piscine, c'était une galère. Aujourd'hui, c'est un vrai plaisir."

---

### Blog article 2: Les 3 étapes indispensables

**Slug:** `/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique`
**Date (Zyro metadata):** `2025-01-31T14:08:26.999Z`
**Author:** Dlo Azur piscines
**Categories visible in nav:** "ENTREPRENARIAT", "ENTRETENIR SA PISCINE"
**Read time (Zyro metadata):** 4 minutes

**H1:** "Les 3 étapes indispensables pour un entretien de piscine parfait en Martinique"

**Key technical content (verbatim extracts):**

pH targets: `7,2 - 7,6`
Water params to monitor weekly: désinfectant (chlore ou sel), pH, alcalinité, stabilisant.
Key claims:
- "80 % de la qualité de l'eau dépend du bon entretien de votre bassin et de votre filtration"
- "Un filtre mal entretenu augmente votre consommation d'énergie et réduit l'efficacité du chlore jusqu'à 50 %"
- "un pH mal équilibré peut réduire de 50 % l'efficacité du chlore"

Climate-specific content: brume de sables (Saharan dust), fortes pluies tropicales.

FAQ included in article:
- "À quelle fréquence dois-je nettoyer ma piscine ?" → "Une fois par semaine minimum pour éviter l'accumulation d'impuretés."
- "Comment savoir si mon filtre fonctionne bien ?" → "Si l'eau devient trouble malgré le traitement, un nettoyage du filtre ou un backwash est nécessaire."
- "Que faire si mon eau devient verte malgré l'entretien ?" → "Un traitement choc et un ajustement du pH sont souvent nécessaires. Faites appel à un professionnel pour un diagnostic précis."

---

### Conditions générales (`/conditions-generales`)

**Title:** "Conditions générales - Terms and conditions"

**Legal sections:** Mentions Légales, Politique de Confidentialité, Politique de Cookies, CGU (5 articles)

**Full legal identity:**
- Nom ou raison sociale : **DLO AZUR EI (entreprise individuelle) — Pierre ADAM**
- Adresse : **29 montée du Clapotage, 97231 Le Robert, Martinique**
- Numéro SIRET : **934 053 281 000 10**
- Contact : (email not visible in legal text — listed in footer as `contact@dloazurpiscines.com`)
- Hébergeur du site : **Hostinger**

---

## Services (incl. spa)

Services confirmed present on the live Zyro site:

| # | Service | Zyro evidence | Laravel route (planned) |
|---|---------|---------------|------------------------|
| 1 | Entretien récurrent (hebdomadaire / bimensuel / à la demande) | `/services-et-nettoyage` section "Entretien de piscines hebdomadaire" | `/services/entretien-recurrent` |
| 2 | Nettoyage / remise en état (eau verte, piscines oubliées) | `/services-et-nettoyage` section "Nettoyage de piscine et remise en état" | `/services/eau-verte-urgence` (thin — expand D-13) |
| 3 | Analyse et traitement de l'eau | Embedded in all service descriptions; "analyse et ajustement des paramètres chimiques (pH, chlore ou sel, alcalinité, stabilisant)" | `/services/analyse-eau` |
| 4 | **Spa / jacuzzi** | Page title "Entretien et nettoyage de piscines **et spa**"; nav label "Montage de piscine hors sol **et jacuzzi**"; site-wide branding confirms spa marketed alongside piscines | `/services/spa` |
| 5 | Montage piscine hors sol | `/services-et-nettoyage` section | Included in entretien-recurrent or separate (TBD) |

**Spa confirmation for D-11:** The Zyro site title and nav explicitly market "spa" and "jacuzzi" alongside pools. The `/services/spa` page in Plan 03 is directly justified by this Zyro positioning. The body copy on the service page does not have a dedicated spa section — it is folded under the "Montage de piscine hors sol et jacuzzi" heading. Plan 03 will author the dedicated spa page from scratch (with Pierre input on spa-specific specifics).

**Climate-specific angles to reuse:**
- Brume de sables (Saharan dust) — named twice, causes pool to cloud rapidly
- Fortes pluies tropicales — déséquilibre chimique
- Chaleur + humidité + évaporation rapide = algue prolifération
- Entretien hebdomadaire minimum obligatoire en Martinique

---

## Testimonials

**No attributed client testimonials exist on the Zyro site.**

The homepage section "Nos clients nous adorent !" contains only a call-to-action button "Laissez nous le votre 😍" linking externally to a Google Maps review page. No customer names, quotes, star ratings, or review text are embedded in the Zyro HTML.

**Consequence for Plan 03/04/content plans:** Do NOT invent testimonials. The D-05 constraint (no `aggregateRating` without real GBP reviews) applies. Any review display must wait for GBP creation + real review accumulation (Pierre-action). The placeholder pattern `<!-- [AVIS CLIENTS REQUIS — après création GBP et premiers avis Google] -->` should be used in any review section template.

The blog article "De la passion à l'entrepreneuriat" references "témoignages clients" in its meta description but no actual client quotes appear in the article body — only Pierre's first-person narrative.

---

## NAP and Identity

| Field | Value | Source | Confidence |
|-------|-------|--------|------------|
| Brand name | Dlo Azur Piscines (also written "Dlo Azur Piscine" without the 's' — used interchangeably on Zyro) | Homepage title, services, blog | HIGH |
| Legal entity name | DLO AZUR EI | `/conditions-generales` | HIGH |
| Operator | Pierre ADAM | `/conditions-generales` | HIGH |
| Phone | `+596 696 94 00 54` | Footer of every Zyro page | HIGH — confirmed |
| Email | `contact@dloazurpiscines.com` | Footer of every Zyro page | HIGH — confirmed |
| Address | 29 montée du Clapotage, 97231 Le Robert, Martinique | `/conditions-generales` | HIGH |
| Zone de chalandise | Martinique (mentions Fort-de-France context in "expertise locale" copy; 4 communes in `areaServed` in Laravel schema are not on Zyro) | Throughout | HIGH |
| Hébergeur | Hostinger (Zyro/Hostinger Website Builder) | Server headers + `/conditions-generales` | HIGH |

**NAP consistency note:** The Zyro site does NOT display the street address publicly on any page other than `/conditions-generales`. Per D-04, `streetAddress`/`postalCode` must NOT be added to the Laravel schema markup (SAB policy — address not visible on-page). This is confirmed correct.

**Brand name inconsistency on Zyro:** "Dlo Azur Piscine" (no 's') vs "Dlo Azur Piscines" (with 's') used interchangeably. The `conditions-generales` legal entity is "DLO AZUR EI". For Laravel vitrine: use "Dlo Azur Piscines" (with 's') as the canonical brand name — it appears in the page title and in the Laravel codebase already.

---

## SIRET Candidate (Pierre-Action — NOT for code here)

**Found on:** `/conditions-generales`

**Value as displayed:** `934 053 281 000 10`

**Digits only:** `93405328100010` (14 digits — valid SIRET format)
- SIREN: `934053281`
- NIC (établissement): `00010`

**Status:** This is a 14-digit SIRET displayed on the live Zyro `/conditions-generales` page. It is NOT confirmed against the official SIRENE registry.

**Pierre-action required:** Pierre must verify this SIRET against [annuaire-entreprises.data.gouv.fr](https://annuaire-entreprises.data.gouv.fr) or INSEE SIRENE before it is used in the Laravel `/mentions-legales` page.

**Phase assignment:** This is a **Phase 1 cutover blocker** (D-07). The Laravel `/mentions-legales` page currently has a placeholder SIRET. Do NOT use the Zyro SIRET value in code until Pierre confirms it. Cross-referenced in the Phase 1 cutover checklist.

**Fiscal note:** Pierre is auto-entrepreneur, franchise en base TVA (art. 293 B) — see memory `pierre-statut-fiscal`. The `/mentions-legales` page should NOT show a TVA intracom number (there is none for auto-entrepreneur in franchise en base).

---

## Pierre-Action Facts Required

This section lists all facts that Plans 03 (service pages), 04 (city hubs), and related content plans **cannot author without Pierre**. Executors MUST use the specified placeholder rather than inventing facts.

### 1. Real local fact per commune — Hard gate for Plan 04 (D-12)

Plan 04 (city hubs) gates each city page on ≥1 Pierre-supplied local fact. The Zyro site contains no commune-specific content. All 4 communes require a fact:

| Commune | Gap description | Placeholder in template | Blocks |
|---------|-----------------|------------------------|--------|
| **Fort-de-France** | No local fact available: specific water quality (calcaire, salinité), a real past job address context, or access constraint in the city. The Zyro site mentions "expertise locale en Martinique" generically — no Fort-de-France specificity. | `<!-- [FAIT LOCAL REQUIS: spécificité eau/chantier à Fort-de-France] -->` | City hub `/services/fort-de-france` — DO NOT publish without |
| **Le Lamentin** | No local fact available. Le Lamentin is near the airport / industrial zone — water hardness or pool types may differ. Pierre must supply. | `<!-- [FAIT LOCAL REQUIS: spécificité eau/chantier à Le Lamentin] -->` | City hub `/services/le-lamentin` — DO NOT publish without |
| **Schoelcher** | No local fact available. Schoelcher is a coastal commune near Fort-de-France — salt air, sea proximity may affect pool chemistry. Pierre must supply. | `<!-- [FAIT LOCAL REQUIS: spécificité eau/chantier à Schoelcher] -->` | City hub `/services/schoelcher` — DO NOT publish without |
| **Les Trois-Îlets** | No local fact available. Les Trois-Îlets is a prestige/tourism zone with high villa density — may have distinctive pool types (infinity, sea-view). Pierre must supply. | `<!-- [FAIT LOCAL REQUIS: spécificité eau/chantier à Les Trois-Îlets] -->` | City hub `/services/les-trois-ilets` — DO NOT publish without |

**D-12 gate:** A city page with an unfilled `<!-- [FAIT LOCAL REQUIS` placeholder MUST NOT be published. This is enforced in the UI-SPEC §Copywriting Contract.

### 2. Real case study facts — Hard gate for /realisations expansion (D-13)

The Zyro `/nos-realisations` is "coming soon" — no real case studies exist on the site. Plan 03 (or a dedicated realisations plan) requires Pierre to supply:

- 2–3 real chantiers with: **commune**, **pool type** (carrelage/liner/béton/hors sol), **problème constaté**, **protocole appliqué**, **paramètres avant** (pH, chlore/sel, TAC mesurés), **paramètres après**.
- Placeholder: `<!-- [CHANTIER RÉEL REQUIS — Pierre ADAM doit fournir: commune, type, mesures avant/après] -->`
- Blocks: `/realisations` case study sections — DO NOT publish fictitious before/after measures.

### 3. Real blog article publication dates

**Context:** The Zyro builder stores `2025-01-31T14:08:26.999Z` for **both** articles in its internal metadata. However, this date likely reflects when the Zyro page was last saved/built, not the actual article publish date.

- Article 1 "De la passion à l'entrepreneuriat" — Zyro metadata date: `2025-01-31`
- Article 2 "Les 3 étapes indispensables" — Zyro metadata date: `2025-01-31`

Pierre must confirm whether these dates represent the actual first-publish date or the last-edit date. If both articles were published the same day, that is plausible but should be verified. The Laravel blog Markdown frontmatter `date:` field should reflect the **real first-publish date**.

**Blocks:** Blog `date:` frontmatter accuracy; sitemap `lastmod` for blog URLs; `og:article:published_time` meta tag (Plan 999.1-05).

### 4. Price-range figure

The Zyro service page contains no pricing information. Plan 03 may include a "à partir de X€" price signal per D-13/D-09. Pierre must supply:
- A minimum service call-out rate (e.g., "à partir de 60€")
- Or confirm the `priceRange: '€€'` in the schema is sufficient without showing a number

**Placeholder:** `<!-- [PRIX REQUIS: à partir de X€ — Pierre ADAM doit confirmer le tarif plancher] -->`
**Blocks:** Any page displaying a price. Do NOT show a fabricated price.

### 5. SIRET confirmation (Phase 1 cutover — NOT Phase 999.1)

**Cross-reference only.** The SIRET `934 053 281 000 10` appears on the Zyro site but must be verified before use in Laravel. This is a Phase 1 cutover blocker (D-07) — it is NOT in scope for Phase 999.1 plans. Flagged here for completeness.

### 6. GBP (Google Business Profile) creation

Pierre-owned, zero-dev action. Unblocks:
- `sameAs` URL in `LocalBusinessSchema` (D-09)
- `aggregateRating` (D-05) — gates any on-page review display
- `hasOfferCatalog` second wave (D-06)

**No placeholder in code.** The `sameAs: []` in `LocalBusinessSchema.php` remains empty until Pierre sends the GBP URL.

---

*Harvest completed: 2026-05-29. All data from read-only GET crawl of https://dloazurpiscines.com. No write operations performed against the live site. Threat model T-9991-02-01 satisfied.*

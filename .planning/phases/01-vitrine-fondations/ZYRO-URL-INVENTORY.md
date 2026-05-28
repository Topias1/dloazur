# Inventaire URLs Zyro — capture pré-cutover (D-24)

**Responsable :** Pierre ADAM
**Capturé le :** 2026-05-28 (automatiquement depuis `https://dloazurpiscines.com/sitemap.xml`)
**Méthode :** `curl -sL https://dloazurpiscines.com/sitemap.xml` → 8 URLs trouvées

---

## URLs vivantes sur Zyro (sitemap)

| # | Ancienne URL Zyro | Priorité | Nouvelle URL Phase 1 | Action | Notes |
|---|-------------------|----------|----------------------|--------|-------|
| 1 | `https://dloazurpiscines.com/` | 1.0 | `/` | Aucune (même URL après DNS switch) | Home |
| 2 | `/services-et-nettoyage` | 0.5 | `/services` | **301** | Mapping direct |
| 3 | `/nos-realisations` | 0.5 | `/realisations` | **301** | Mapping direct |
| 4 | `/blog-list-nettoyage-piscine-professionnel` | 0.5 | `/blog` | **301** | Index du blog |
| 5 | `/page-article-blog-vierge` | 0.5 | `/blog` | **301** | Page test Zyro, aucun contenu réel |
| 6 | `/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines` | 0.5 | `/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines` | **Servi directement** | Article porté en Markdown — SEO préservé (Option 1 retenue) |
| 7 | `/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscine` | 0.5 | `/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines` | **301** | Variante typo (sans 's') → slug canonique de l'article |
| 8 | `/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique` | 0.5 | `/blog/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique` | **Servi directement** | Article porté en Markdown — SEO préservé (Option 1 retenue) |

**Total redirects 301 :** 5 (URLs 2-5 + variante typo URL 7)
**Articles vivants :** 2 (URLs 6 et 8 — servis par `BlogController@show` avec slug Zyro préservé)

---

## Décision

Per D-24, comme >3 URLs indexées : **redirect map obligatoire** dans `routes/web.php`.

- [x] **5 redirections 301 dans `routes/web.php`** (URL 2-5 + variante typo URL 7)
- [x] **2 articles Zyro portés en Markdown** dans `resources/content/blog/` — slugs Zyro préservés, SEO récupéré
- Commit initial : `feat(01-06): add 301 redirect map for 7 Zyro legacy URLs (D-24)`
- SEO recovery : `feat(01-06): import 2 Zyro blog articles to resources/content/blog with preserved slugs`

Décision initiale : 2026-05-28 (automatique, validée via `--auto`).
**Mise à jour :** Option 1 retenue — articles portés en Markdown, SEO préservé.

---

## §A — Articles de blog Zyro : option de récupération SEO

Trois articles Zyro redirigent vers `/blog` (index) :

- `de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines` (+ variante typo)
- `les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique`

**Option 1 — Sauvegarder le SEO existant :** ✅ **RETENUE** — Les 2 articles ont été portés en Markdown dans `resources/content/blog/` avec leurs slugs Zyro exacts préservés. `BlogController@show` les sert directement sans redirect. Couvertures téléchargées dans `public/assets/blog/`.

**Option 2 — Acceptable :** Les redirects vers `/blog` (index) suffisaient. ~~Recommandée pour le cutover, Option 1 en follow-up.~~ (Option 1 appliquée immédiatement.)

---

## Vérification post-DNS (CUTOVER.md Phase C)

Après bascule DNS, exécuter ce script pour valider les 5 redirections + 2 articles vivants :

```bash
# 5 redirects 301
for url in services-et-nettoyage nos-realisations blog-list-nettoyage-piscine-professionnel page-article-blog-vierge de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscine; do
  echo "--- $url ---"
  curl -sI -L -o /dev/null -w "HTTP %{http_code} → %{url_effective}\n" "https://dloazurpiscines.com/$url"
done

# 2 articles vivants (200 attendu)
for slug in de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique; do
  echo "--- blog/$slug ---"
  curl -sI -o /dev/null -w "HTTP %{http_code}\n" "https://dloazurpiscines.com/blog/$slug"
done
```

Résultats attendus :
- Redirects : `HTTP 301` + `HTTP 200` en destination (suivis avec `-L`)
- Articles : `HTTP 200` direct

---

## Source

- Sitemap live : `https://dloazurpiscines.com/sitemap.xml` (capturé 2026-05-28)
- Robots : `https://dloazurpiscines.com/robots.txt` → autorise tout, pointe sur le sitemap
- Total URLs sitemap : 8 (1 home + 7 secondaires)

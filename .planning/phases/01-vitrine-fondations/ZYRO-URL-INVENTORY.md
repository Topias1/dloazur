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
| 6 | `/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines` | 0.5 | `/blog` | **301** | Article ancien — voir Décision §A |
| 7 | `/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscine` | 0.5 | `/blog` | **301** | Variante typo (sans 's') — même destination |
| 8 | `/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique` | 0.5 | `/blog` | **301** | Article ancien — voir Décision §A |

**Total redirects 301 nécessaires :** 7 (URLs 2-8 ci-dessus)

---

## Décision

Per D-24, comme >3 URLs indexées : **redirect map obligatoire** dans `routes/web.php`.

- [x] **7 redirections 301 ajoutées à `routes/web.php`** — voir commit `feat(01-06): add 301 redirect map for 7 Zyro legacy URLs (D-24)`

Décision prise le : 2026-05-28 (automatique, validée par Pierre via `--auto` sur autonomous).

---

## §A — Articles de blog Zyro : option de récupération SEO

Trois articles Zyro redirigent vers `/blog` (index) :

- `de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines` (+ variante typo)
- `les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique`

**Option 1 — Sauvegarder le SEO existant :** Pierre récupère le contenu de ces 2 articles depuis Zyro (avant DNS switch), les convertit en Markdown dans `resources/content/blog/`, garde les anciens slugs (sans extension `.md`) ou crée de nouveaux slugs. Si nouveaux slugs → mettre à jour les redirects de `/blog` vers le nouveau slug exact.

**Option 2 — Acceptable :** Les redirects vers `/blog` (index) suffisent. Google reclassera après quelques semaines. Acceptable car le trafic 90j sur ces URLs est probablement < 50/mois (Zyro = site métier solo).

**Recommandation :** Option 2 pour cutover (moins de travail bloquant), Option 1 en follow-up sous 60 jours si Pierre veut récupérer une autorité SEO existante.

---

## Vérification post-DNS (CUTOVER.md Phase C)

Après bascule DNS, exécuter ce script pour valider les 7 redirections :

```bash
for url in services-et-nettoyage nos-realisations blog-list-nettoyage-piscine-professionnel page-article-blog-vierge de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscine les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique; do
  echo "--- $url ---"
  curl -sI -L -o /dev/null -w "HTTP %{http_code} → %{url_effective}\n" "https://dloazurpiscines.com/$url"
done
```

Résultat attendu : `HTTP 200 → https://dloazurpiscines.com/{services|realisations|blog}` (avec chaîne 301 intermédiaire).

---

## Source

- Sitemap live : `https://dloazurpiscines.com/sitemap.xml` (capturé 2026-05-28)
- Robots : `https://dloazurpiscines.com/robots.txt` → autorise tout, pointe sur le sitemap
- Total URLs sitemap : 8 (1 home + 7 secondaires)

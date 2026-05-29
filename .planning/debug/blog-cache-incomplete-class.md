---
slug: blog-cache-incomplete-class
status: resolved
trigger: "/blog et /sitemap.xml renvoient 500 — BlogRepository::all() retourne __PHP_Incomplete_Class au lieu de Collection quand CACHE_STORE=database"
created: 2026-05-29
updated: 2026-05-29
---

# Debug: blog-cache-incomplete-class

## Symptoms

- **Expected behavior:** `/blog` (BlogIndex) et `/sitemap.xml` (SitemapController@index) répondent 200. `App\Support\BlogRepository::all()` retourne une `Illuminate\Support\Collection` de posts (arrays avec `date` Carbon), lue depuis le cache `blog.index`.
- **Actual behavior:** Les deux routes renvoient HTTP 500. `BlogRepository::all()` retourne un `__PHP_Incomplete_Class` au lieu d'une `Collection` → `TypeError` (le return type `: Collection` n'est pas satisfait).
- **Error message (logs prod Laravel Cloud):** `App\Support\BlogRepository::all(): Return value must be of type Illuminate\Support\Collection, __PHP_Incomplete_Class returned` — TypeError à `app/Http/Controllers/SitemapController.php:32` (et même symptôme via la route `/blog`).
- **Timeline:** Apparu au 1er déploiement réel du code Phase 1+2 sur staging Laravel Cloud (commit `0300fc8`, 2026-05-29). Jamais vu en local (où `CACHE_STORE` peut différer / cache vidé fréquemment).
- **Reproduction:** Sur l'env prod/staging où `CACHE_STORE` n'est pas défini → défaut `database` (table `cache` Postgres, migration `0001_01_01_000001_create_cache_table.php` présente et exécutée). **Déterministe** : après `php artisan cache:clear`, la 1ère requête `/sitemap.xml` (cache miss → `loadPosts()` régénère) = **200** ; les requêtes suivantes (cache hit → `unserialize` du blob) = **500**. Donc `cache:clear` NE corrige PAS — c'est une incompatibilité write→read du store `database`, pas une donnée périmée.

## Hypothèses de départ (à valider/éliminer)

1. La `Collection` mise en cache contient un objet dont la classe n'est pas chargeable au `unserialize` (→ `__PHP_Incomplete_Class`). Candidats : un objet `Carbon`/`CarbonImmutable`, un reliquat `Symfony\Component\Finder\SplFileInfo`, ou un objet du parseur `YamlFrontMatter` qui fuiterait dans les arrays de `loadPosts()`.
2. Le store `database` round-trip mal le payload (encodage Postgres `text`/`bytea`, troncature) — mais une troncature donnerait plutôt une erreur unserialize, pas une classe incomplète.
3. `loadPosts()` retourne en réalité une `Collection` dont les items ne sont pas de purs arrays (vérifier la chaîne `collect(File::files)->filter->map(parse)->filter->sortByDesc->values()`).

## Investigation à mener

- Reproduire en local avec `CACHE_STORE=database` (+ `php artisan migrate`), hit `/sitemap.xml` deux fois, observer le 500 au 2e hit avec `APP_DEBUG=true` pour la stacktrace complète.
- Inspecter la valeur sérialisée réelle de la clé `blog.index` dans la table `cache` (ex: `php artisan tinker` → `DB::table('cache')->where('key','like','%blog.index')->value('value')`) pour identifier le nom de classe `O:NN:"..."` non désérialisable.
- Fichiers : `app/Support/BlogRepository.php` (méthode `all()` + `loadPosts()` + `parse()`), `app/Http/Controllers/SitemapController.php`, `config/cache.php`.

## Current Focus

- hypothesis: CONFIRMÉE — `config/cache.php` ligne 134 : `'serializable_classes' => false`. Laravel 11+ bloque la désérialisation PHP pour protéger contre les gadget chains. La méthode `parse()` stocke un objet `Carbon` dans `$post['date']`. Au cache write, le `Carbon` est sérialisé. Au cache read, PHP refuse de l'instancier → `__PHP_Incomplete_Class`.
- next_action: RESOLVED — fix appliqué dans `app/Support/BlogRepository.php`.

## Evidence

- timestamp: 2026-05-29T00:00:00Z
  source: config/cache.php:134
  finding: "`'serializable_classes' => false` — Laravel bloque unserialize() de toute classe PHP dans le store database."

- timestamp: 2026-05-29T00:00:00Z
  source: app/Support/BlogRepository.php:parse()
  finding: "La méthode `parse()` retourne `'date' => Carbon::parse(...)` — un objet Carbon dans chaque post array. La Collection entière (avec ces objets) est passée à `Cache::remember()`."

- timestamp: 2026-05-29T00:00:00Z
  source: tests/Feature/BlogTest.php
  finding: "8/8 tests passent après le fix."

## Eliminated

- Hypothèse 2 (troncature Postgres) : éliminée — une troncature donnerait une erreur PHP unserialize, pas `__PHP_Incomplete_Class`.
- Hypothèse 3 (SplFileInfo / YamlFrontMatter fuite) : éliminée — `loadPosts()` mappe bien en arrays purs, le seul objet résiduel est le `Carbon` dans `date`.

## Resolution

- root_cause: "`config/cache.php` a `serializable_classes => false` (défaut Laravel 11+, sécurité anti-gadget-chain). `BlogRepository::parse()` stockait un objet `Carbon` dans le champ `date` de chaque post. Quand la Collection était mise en cache via le store `database` (Postgres), le `Carbon` était sérialisé. Au read-back, PHP refusait de l'instancier → `__PHP_Incomplete_Class` → `TypeError`."
- fix: "Dans `BlogRepository::all()`, le closure de `Cache::remember` appelle désormais `serializablePosts()` qui convertit `date` en string ISO-8601 avant stockage. La méthode `hydrateDates()` re-convertit les strings en `Carbon` après lecture du cache. Les callers (`app/Http/Controllers/SitemapController.php`, les vues Blade, `BlogController`) reçoivent toujours un objet `Carbon` — aucun changement requis côté consommateurs."
- fix_applied: true
- files_changed:
  - app/Support/BlogRepository.php
- tests_run: "pest tests/Feature/BlogTest.php — 8/8 passed"

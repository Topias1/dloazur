# Playbook de mise en production — Dlo Azur Piscines

**Document :** Plan 01-06 · CUTOVER.md  
**Responsable :** Pierre ADAM  
**Objectif :** Passer la vitrine de Zyro/Hostinger vers Laravel Cloud de façon sûre et traçable (D-21..D-25).

---

## Phase 0 — Prérequis avant de commencer

Vérifier ces points AVANT d'exécuter les phases A..D :

- [ ] Plans 01-01 à 01-05 déployés sur l'environnement staging Laravel Cloud
- [ ] Secrets `.env` configurés dans Laravel Cloud (APP_KEY, BREVO_API_KEY, DB_*, GOOGLE_PLACES_API_KEY, HONEYPOT_*)
- [ ] Domaine `dloazurpiscines.com` vérifié dans Brevo (onglet Expéditeurs → Domaines)
- [ ] CI GitHub Actions en vert sur la branche `main` : `./vendor/bin/pest --ci` → 0 failure

---

## Phase A — Validation pré-cutover (D-22)

À exécuter sur l'URL staging (ex : `https://dloazur-staging.laravel.cloud` — récupérer dans le tableau de bord Laravel Cloud).

### Checklist Phase A

1. - [ ] **Tests automatisés** : `./vendor/bin/pest --ci` en local → exit 0
2. - [ ] **Rendu visuel** : visiter chaque page sur staging en Chrome desktop + iOS Safari mobile
   - [ ] `/` (accueil)
   - [ ] `/services`
   - [ ] `/realisations`
   - [ ] `/contact`
   - [ ] `/blog`
   - [ ] `/mentions-legales`, `/cgv`, `/confidentialite`
3. - [ ] **Lighthouse mobile** (Chrome DevTools → Lighthouse → Mobile, sur la page d'accueil staging)
   - Performance : ≥ 85 (tolérance cold-wake scale-to-zero — RESEARCH Pitfall 10)
   - SEO : ≥ 90
   - Accessibilité : ≥ 90
   - Si perf < 85 au 1er passage : réchauffer l'instance (`curl ${STAGING_URL}/` × 3) puis refaire l'audit
   - Scores obtenus : Perf = ___, SEO = ___, A11y = ___ (remplir)
4. - [ ] **Sitemap validator** : coller `${STAGING_URL}/sitemap.xml` sur https://www.xml-sitemaps.com/validate-xml-sitemap.html → « Sitemap is valid »
5. - [ ] **Google Rich Results** : coller l'URL staging sur https://search.google.com/test/rich-results → type « Plumber » détecté
6. - [ ] **OG debugger Facebook** : https://developers.facebook.com/tools/debug/ → entrer l'URL staging → cliquer « Récupérer à nouveau » → vérifier og:title, og:description, og:image (photo hero Pierre)
7. - [ ] **WhatsApp CTA mobile** : ouvrir l'URL staging sur iPhone ou Android → taper sur le bouton WhatsApp → WhatsApp s'ouvre avec le numéro **0696 94 00 54** pré-rempli
8. - [ ] **Test d'envoi de mail** : soumettre le formulaire de contact staging avec l'adresse de Pierre → email reçu à contact@dloazurpiscines.com → vérifier dans le tableau de bord Brevo que l'envoi passe par l'endpoint EU (Paris) et non AWS us-east-1
9. - [ ] **Login staging** : aller sur `${STAGING_URL}/login` → saisir les identifiants Pierre (seedés) → atterrir sur `/admin` → cliquer Déconnexion → revenir sur `/`

**Si un point A échoue :** ne pas passer à la Phase C. Diagnostiquer, créer un correctif (branche feature, PR, redeploiement), relancer Phase A.

---

## Phase B — Inventaire URLs Zyro (D-24) — ✅ Pré-validée le 2026-05-28

> **Statut :** automatisée pendant `--auto` autonome. Sitemap Zyro capturé via `curl https://dloazurpiscines.com/sitemap.xml` → 8 URLs trouvées → 7 redirections 301 ajoutées à `routes/web.php` → test Pest `ZyroRedirectTest` valide chaque mapping. Plus rien à faire ici manuellement avant la bascule DNS.

10. - [x] **Inventaire capturé** : voir `ZYRO-URL-INVENTORY.md` (8 URLs depuis le sitemap live, 7 redirects nécessaires)
11. - [x] **Décision redirections** : map 301 appliquée (`services-et-nettoyage → /services`, `nos-realisations → /realisations`, 5 articles blog → `/blog`)
12. - [x] **Tests automatisés** : `./vendor/bin/pest --filter=ZyroRedirect` → 7/7 ✓
13. - [ ] **(optionnel)** Confirmation Google Search Console : se connecter à GSC pour vérifier qu'aucune URL indexée n'a été oubliée du sitemap. Si oui, ajouter à `ZYRO-URL-INVENTORY.md` + nouvelle `Route::redirect()` dans `routes/web.php` + commit.
14. - [ ] **Post-DNS** : exécuter le script de vérification live (voir `ZYRO-URL-INVENTORY.md` §Vérification post-DNS) après la bascule.

---

## Phase C — Bascule DNS (D-23, Pierre exécute — optionnel dans Plan 06 per D-25)

Pierre peut exécuter cette phase immédiatement après Phase A ou la différer. Si différée, Phase 1 est marquée « validée sur staging, bascule DNS en attente ». Les phases A et B doivent être vertes avant de procéder.

### Sous-étape C1 — Ajouter le domaine dans Laravel Cloud

14. - [ ] Dans le tableau de bord Laravel Cloud → **Environments → Domains** → ajouter :
    - `dloazurpiscines.com`
    - `www.dloazurpiscines.com`
15. - [ ] Attendre que les certificats SSL (Let's Encrypt auto-provisioned) affichent le statut **Active**
16. - [ ] Récupérer la valeur CNAME affichée par Laravel Cloud (ex : `dloazur-prod.laravel.cloud`) — nécessaire pour l'étape C2

### Sous-étape C2 — Préparer Hostinger

17. - [ ] Se connecter au panneau Hostinger pour `dloazurpiscines.com`
18. - [ ] Dans DNS / Zone editor, trouver l'enregistrement A ou CNAME pour `@` (racine) de `dloazurpiscines.com`
19. - [ ] **Baisser le TTL à 300 secondes** (5 minutes) — sauvegarder
20. - [ ] **Attendre** au moins la durée de l'ancien TTL (souvent 14 400 s = 4 h) pour que les caches DNS expirent partout

### Sous-étape C3 — Bascule

21. - [ ] Dans Hostinger DNS, **modifier le CNAME (ou A)** de `@` pour pointer vers la valeur Laravel Cloud récupérée en C1
22. - [ ] Vérifier la propagation :
    - `dig dloazurpiscines.com +short` (en local) → doit retourner l'IP/CNAME Laravel Cloud
    - https://dnschecker.org → entrer `dloazurpiscines.com` → cocher ≥ 3 régions distantes (US, EU, Asia) → toutes green
23. - [ ] Optionnel : remonter le TTL à 3 600 s une fois la propagation confirmée

---

## Phase D — Validation post-bascule (si Phase C exécutée)

24. - [ ] `curl -I https://dloazurpiscines.com` → `HTTP/2 200` + header `server: Laravel Cloud` (ou Cloudflare)
25. - [ ] Relancer toute la checklist Phase A sur `https://dloazurpiscines.com` (pas l'URL staging)
26. - [ ] **Re-scraper OG** : https://developers.facebook.com/tools/debug/ sur l'URL de production → purger le cache → vérifier l'image hero
27. - [ ] **Google Search Console** : cliquer **Inspection d'URL** → entrer `https://dloazurpiscines.com/` → **Demander l'indexation**
28. - [ ] Confirmer en monitoring Laravel Cloud que les requêtes arrivent bien (dashboard Metrics)

---

## Phase E — Rollback (en cas de problème post-bascule)

**L'ancien TTL est de 5 min — la réversion est rapide si exécutée dans l'heure qui suit la bascule.**

29. - [ ] Dans Hostinger DNS, **rétablir l'enregistrement A/CNAME vers la valeur Zyro** (noter la valeur Zyro originale avant toute modification — étape C2 ci-dessus)
30. - [ ] Attendre ≤ 5 min (TTL abaissé) pour la propagation du retour arrière
31. - [ ] Vérifier avec `dig dloazurpiscines.com +short` → retour vers Zyro
32. - [ ] Analyser les logs Laravel Cloud (onglet Logs) pour identifier la cause de l'échec
33. - [ ] Créer un issue GitHub pour le correctif avant de relancer Phase C

---

## Récapitulatif des résultats (à remplir par Pierre)

| Phase | Statut | Date | Notes |
|-------|--------|------|-------|
| Phase A — Validation staging | ☐ | | |
| Phase B — Inventaire Zyro | ☐ | | |
| Phase C — Bascule DNS | ☐ (ou différée) | | |
| Phase D — Validation production | ☐ | | |

**Scores Lighthouse** (Phase A) : Perf = ___ · SEO = ___ · A11y = ___

**Résultat livraison mail Brevo** (Phase A, étape 8) : ☐ EU endpoint confirmé (timestamp : ___)

**Verdict Phase 1 :** Phase 1 (Vitrine & Fondations) est _____ sur staging à l'URL _____. Bascule DNS : _____.

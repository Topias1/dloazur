# Inventaire URLs Zyro — capture pré-cutover (D-24)

**Responsable :** Pierre ADAM  
**À remplir AVANT la Phase C (bascule DNS)** — voir CUTOVER.md Phase B.

---

## Instructions

1. Se connecter à Google Search Console : https://search.google.com/search-console
2. Sélectionner la propriété `dloazurpiscines.com`
3. Aller dans **Indexation → Pages**
4. Copier la liste complète des URLs indexées dans le tableau ci-dessous
5. Pour chaque URL, décider s'il faut une redirection 301 vers l'équivalent Phase 1

---

## URLs indexées depuis Google Search Console

> _Pierre ADAM remplit ce tableau lors de la Phase B du playbook CUTOVER.md_

| Ancienne URL Zyro | Indexée ? | Trafic (90 j) | Nouvelle URL Phase 1 | Redirection 301 ? |
|-------------------|-----------|--------------|----------------------|-------------------|
| `https://dloazurpiscines.com/` | ☐ | — | `/` | Non (même URL) |
| _(ajouter d'autres lignes si nécessaire)_ | | | | |

---

## Décision

> _Pierre ADAM complète cette section après avoir analysé le tableau ci-dessus._

- [ ] **Aucune redirection nécessaire** — Zyro avait uniquement `/` d'indexé (site monopages ou faible profondeur)
- [ ] **N redirections ajoutées à `routes/web.php`** — voir détail dans le tableau + commit `fix(01-06): add 301 redirects for Zyro URLs`

Décision prise le : _____

---

## Vérification des redirections (si applicables)

Pour chaque ancienne URL redirigée, exécuter après redéploiement :

```bash
curl -I -L https://dloazurpiscines.com/<ancienne-url>
```

Résultat attendu :
```
HTTP/2 301
location: https://dloazurpiscines.com/<nouvelle-url>
...
HTTP/2 200
```

| Ancienne URL | Résultat curl | Status |
|-------------|---------------|--------|
| _(remplir)_ | | ☐ |

---

## Commandes utiles

```bash
# Lister toutes les URLs indexées via l'outil site: (approximatif, compléter avec GSC)
# Recherche Google : site:dloazurpiscines.com

# Vérifier la propagation d'un redirect depuis staging
curl -I -L https://dloazur-staging.laravel.cloud/<ancienne-url>

# Exemple de syntaxe pour routes/web.php si redirects nécessaires
# Route::redirect('/ancienne-page', '/services', 301);
```

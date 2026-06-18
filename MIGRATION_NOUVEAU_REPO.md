# Migrer IQRA vers un nouveau dépôt propre

> Copier-coller pour repartir d'un **historique git neuf** (aucun secret du
> passé) vers un nouveau compte/dépôt GitHub. À exécuter dans **Git Bash**
> (Windows) ou un terminal Unix. Remplace `<NOUVEAU_COMPTE>` par ton compte.

---

## 0. Avant de commencer (sécurité)

1. **Travaille sur une COPIE** du dossier (garde l'ancien repo intact) :
   ```bash
   cp -r ppp ppp-clean && cd ppp-clean
   ```
2. **Crée le dépôt sur GitHub** (nouveau compte) : un dépôt **privé** nommé
   `iqra`, **sans** README/licence/.gitignore (vide), pour éviter les conflits.

---

## 1. Repartir d'un historique vierge

```bash
# Supprime tout l'historique (et donc tout secret eventuellement commite avant)
rm -rf .git

# Nouvel historique
git init -b main
```

## 2. Vérifier qu'AUCUN secret ne partira

```bash
git add -A

# Ces deux commandes doivent renvoyer VIDE :
git diff --cached --name-only | grep -iE '(^|/)\.env$'        # aucun .env
git diff --cached --name-only | grep -iE 'google-services|GoogleService-Info|\.keystore|key\.properties'

# Verif manuelle rapide du contenu indexe
git status
```
> Si une de ces commandes affiche quelque chose → **NE PAS committer**, ajouter
> le fichier au `.gitignore`, puis `git rm --cached <fichier>` et recommencer.

## 3. Premier commit propre + dépôt distant

```bash
git commit -m "chore: IQRA v1.0.0 — initial clean import"

git remote add origin https://github.com/<NOUVEAU_COMPTE>/iqra.git
git branch -M main
git push -u origin main
```

## 4. Taguer la version 1.0.0

```bash
git tag -a v1.0.0 -m "IQRA v1.0.0"
git push origin v1.0.0
```
> Le tag `v1.0.0` déclenche `deploy.yml` (CD) — ne pousse le tag qu'une fois le
> serveur prêt et les secrets CI configurés.

---

## 5. Après le push — à vérifier sur GitHub

- [ ] Dépôt **privé**.
- [ ] `.gitignore` bien présents (racine, `flutter_app`, `job-backoffice`, `job-app-frontend`).
- [ ] **Aucun** `.env`, `node_modules/`, `vendor/`, `public/build/` dans le dépôt.
- [ ] **Settings → Secrets and variables → Actions** : ajouter les secrets pour
  `deploy.yml` (ex. `SSH_HOST`, `SSH_USER`, `SSH_KEY`).
- [ ] **Settings → Actions** : activer les workflows (quota gratuit).

---

## Rappel — fichiers volontairement NON poussés (gérés en local/serveur)

| Fichier | Où il vit |
|---|---|
| `*/.env` | local + variables d'env du serveur de prod |
| `vendor/`, `node_modules/` | régénérés par `composer install` / `npm install` |
| `public/build/` | régénéré par `npm run build` |
| `google-services.json`, `*.keystore` | secrets mobiles, hors dépôt |

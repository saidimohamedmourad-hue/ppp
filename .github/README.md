# GitHub Actions — CI / CD

Pipeline complet pour le monorepo IQRA. Vue d'ensemble et instructions de setup.

---

## 🗂️ Fichiers

```
.github/
├── workflows/
│   ├── backend.yml     # Pest tests Laravel + composer audit
│   ├── web.yml         # TypeScript check + Vite build + Playwright E2E
│   ├── lint.yml        # Laravel Pint + ESLint
│   └── deploy.yml      # SSH deploy en prod sur tag v*.*.*
└── dependabot.yml      # Updates auto hebdo composer + npm + actions
```

---

## ⚙️ Quand chaque workflow tourne

| Workflow | Déclencheur | Durée typique |
|----------|-------------|---------------|
| **backend** | Push/PR qui touche `job-backoffice/**` ou `job-shared/**` | ~3 min |
| **web** | Push/PR qui touche `job-app-frontend/**` ou backend | ~5 min |
| **lint** | Tous les push + PR | ~1 min |
| **deploy** | Push d'un tag `v*.*.*` OU déclenchement manuel | ~5 min |
| **dependabot** | Tous les lundi 06:00 Algiers | n/a (ouvre des PRs) |

Les `paths:` filtrent intelligemment : modifier juste un README ne va pas relancer toute la suite Pest.

---

## 🔐 Secrets à configurer

GitHub repo → **Settings → Secrets and variables → Actions**.

### Pour le déploiement (obligatoires uniquement quand vous activez deploy.yml)

| Secret | Valeur | Pour quoi |
|--------|--------|-----------|
| `PROD_SSH_HOST` | `1.2.3.4` ou `iqra.app` | Cible SSH |
| `PROD_SSH_USER` | `iqra` | Utilisateur sur le serveur |
| `PROD_SSH_PORT` | `22` (optionnel) | Port SSH custom |
| `PROD_SSH_KEY` | Clé privée OpenSSH complète (avec `BEGIN/END`) | Auth SSH |
| `PROD_KNOWN_HOSTS` | Sortie de `ssh-keyscan -H <host>` | Anti-MITM |

> 💡 Pour générer une clé SSH dédiée au CI :
> ```bash
> ssh-keygen -t ed25519 -f iqra-deploy -N ""    # sur ta machine
> ssh-copy-id -i iqra-deploy.pub iqra@iqra.app  # ajoute la pub sur le serveur
> # Puis copie le contenu de `iqra-deploy` (clé privée) dans le secret PROD_SSH_KEY
> ```

### Pour le `known_hosts`

```bash
ssh-keyscan -H iqra.app
# Colle la sortie complète dans le secret PROD_KNOWN_HOSTS
```

---

## 🛡️ Environnement protégé `production`

Pour éviter qu'un push de tag déploie automatiquement sans approbation humaine :

1. Repo GitHub → **Settings → Environments → New environment** → nom `production`
2. **Required reviewers** : t'ajouter (ou un autre maintainer)
3. **Deployment branches** : seulement `main` ou `v*.*.*` tags

Avec ça, le job `deploy` attend une approbation manuelle avant de SSH.

---

## 🚀 Déclencher un déploiement

### Option A — release via tag (recommandé)

```bash
git tag v1.0.0
git push origin v1.0.0
```

GitHub détecte le tag → lance `deploy.yml` → exécute `backend.yml` (preflight) → SSH au serveur → lance `deploy.sh` → smoke check sur `/api/health`.

### Option B — déclenchement manuel depuis l'UI

Repo GitHub → **Actions** → **deploy** → **Run workflow** → choisir branche/SHA/tag.

---

## 🧪 Faire tourner la CI sans push

Avec [`act`](https://github.com/nektos/act) (Docker-based runner local) :

```bash
# macOS : brew install act
# Linux : curl https://raw.githubusercontent.com/nektos/act/master/install.sh | bash

cd ppp
act push -W .github/workflows/backend.yml          # toute la workflow backend
act -j pest                                         # juste le job pest
act -j typecheck -W .github/workflows/web.yml      # juste tsc
```

---

## 📊 Que valide chaque workflow

### `backend.yml` (job-backoffice + job-shared)
- ✅ PHP 8.3 installé
- ✅ MariaDB 11.4 démarré en service container
- ✅ Composer install (cached)
- ✅ Migrations OK
- ✅ **59 tests Pest passent**
- ⚠️ `composer audit` (non-bloquant — warning seulement)

### `web.yml` (job-app-frontend)
- ✅ `tsc --noEmit` (0 erreur TS)
- ✅ `npm run build` réussit (la bundle prod compile)
- ✅ Backend Laravel + Vite spin up
- ✅ **8 tests Playwright passent**
- 📦 Si Playwright fail → upload du report HTML en artifact (téléchargeable depuis l'UI)

### `lint.yml`
- ✅ Laravel Pint passe (`--test` mode, signale les diffs)
- ✅ ESLint passe (skip silencieux si pas de script `lint`)

### `deploy.yml`
- ✅ Réutilise `backend.yml` comme preflight
- ✅ SSH au serveur prod
- ✅ Lance `deploy.sh` côté serveur (voir [PRODUCTION_CHECKLIST.md §11](../PRODUCTION_CHECKLIST.md))
- ✅ Smoke check `https://api.iqra.app/api/health` répond 200

---

## 🤖 Dependabot — updates auto

Tous les lundi à 06:00 Algiers, ouvre des PRs groupées :

| Groupe | Contenu |
|--------|---------|
| `laravel` | `laravel/*`, `illuminate/*` (minor + patch) |
| `php-minor-patch` | Tout le reste (minor + patch) |
| `php-major` | Major bumps PHP (séparés pour review attentif) |
| `react` | `react`, `react-dom`, `react-router-dom` |
| `vite` | `vite` + plugins |
| `types` | `@types/*` |
| `js-minor-patch` / `js-major` | Reste JS |
| `github-actions` (mensuel) | Versions des actions elles-mêmes |

Limites :
- Max 5 PRs ouverts par écosystème (évite la surcharge)
- Les PRs déclenchent normalement la CI → merge auto une fois vert (si tu actives auto-merge dans les Settings du repo).

---

## ⚡ Optimisations en place

- **Cache Composer** clé sur `composer.lock` → installe en <30s après le premier run
- **Cache npm** clé sur `package-lock.json` → idem
- **Concurrency cancel-in-progress** : un nouveau commit annule les builds en cours sur la même branche → on n'attend pas 10 min pour du code obsolète
- **Paths filters** : modifier un README ne lance pas la suite Pest
- **`--parallel`** Pest sur 2 processes → divise par ~2 le temps des tests backend

---

## 🐛 Troubleshooting CI

| Erreur | Cause probable | Fix |
|--------|----------------|-----|
| `MariaDB never came up` | Service container lent à démarrer | Augmenter `--health-retries` |
| `Laravel never came up` | `.env` mal configuré | Regarder le step "Configure backend .env (CI)" |
| `Playwright timeout` | SMTP réel utilisé | Vérifier `MAIL_MAILER=log` dans le .env CI |
| `pint --test` fail | Code non formaté | En local : `./vendor/bin/pint` puis commit |
| `tsc --noEmit` fail | Erreur TypeScript | `cd job-app-frontend && npx tsc --noEmit` en local |
| `deploy.yml` ne s'auto-trigger pas | Tag mal formé | `v1.0.0` (pas `1.0.0`, pas `release-1.0.0`) |

---

## 📚 Documents liés

Ces fichiers vivent **hors du repo Git** (dans `appppp/` parent), pour l'instant
non versionnés. Penser à les déplacer dans `ppp/` quand l'équipe grossit :

- `appppp/README.md` — monorepo overview
- `appppp/PRODUCTION_CHECKLIST.md` — déploiement détaillé
- `appppp/PLAN_AUTH_SOCIAL.md` — features auth + état des phases
- `appppp/SETUP_CREDENTIALS.md` — comment obtenir les credentials Gmail/Google/Meta

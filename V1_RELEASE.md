# IQRA — Préparation de la v1.0.0 (commercialisation)

> Document maître pour **structurer le projet, repartir sur un dépôt propre, et
> ne rien oublier** (en particulier les `.env` / secrets) avant la mise en
> production commerciale. À lire **dans l'ordre**.

**Cible : v1.0.0** · **Dernière mise à jour : 13 juin 2026**

---

## 0. Ce qu'est IQRA (rappel)

Monorepo : `job-backoffice` (API Laravel + admin Blade), `job-app-frontend`
(site React), `flutter_app` (mobile + web), `job-shared` (modèles Eloquent
partagés). Détails : [`README.md`](./README.md) · [`ARCHITECTURE.md`](./ARCHITECTURE.md).

---

## 1. 🔐 Secrets & `.env` — LE point critique (à lire en premier)

**Règle d'or : aucun secret réel ne doit jamais être commité.** Audit actuel :

| Vérification | État |
|---|---|
| Aucun `.env` réel suivi par git | ✅ OK |
| `.gitignore` ignorent `.env` (racine, backend, front, flutter) | ✅ OK |
| `.env.example` fournis (backend + front) | ✅ OK |
| Aucun App Password / client secret en clair dans les fichiers suivis | ✅ OK (seulement des placeholders dans `SETUP_CREDENTIALS.md`) |

**À faire pour la v1 :**
- [ ] **Régénérer (rotation) tous les secrets** qui ont pu être partagés en dev
  avant la prod : **App Password Gmail**, clés **OpenAI**, **Turnstile** (passer
  des clés de test aux vraies), **Facebook App Secret**, mots de passe **DB**.
- [ ] En prod, stocker les secrets dans les **variables d'environnement du
  serveur** (ou un coffre : Vault / AWS Secrets Manager), pas dans un fichier
  versionné.
- [ ] Vérifier une dernière fois avant push : `git status` ne doit montrer aucun
  `.env`.

Variables à renseigner : voir [`job-backoffice/.env.example`](./job-backoffice/.env.example)
(App, DB, Mail, Google, Facebook, Turnstile, **OpenAI**, S3) et
[`job-app-frontend/.env.example`](./job-app-frontend/.env.example).

---

## 2. 🆕 Repartir sur un dépôt propre (nouveau compte)

Objectif : un dépôt **neuf, sans historique pollué**, sur un compte sans
restriction de facturation (pour réactiver CI/CD).

**Option recommandée — historique neuf (le plus propre) :**

```bash
# 1. Depuis une COPIE du projet (garde l'ancien repo de côté par sécurité)
cd ppp

# 2. Repartir d'un historique vierge
rm -rf .git
git init -b main

# 3. Vérifier qu'aucun secret/.env ne sera ajouté
git add -A
git status                      # AUCUN .env ne doit apparaître
git diff --cached --name-only | grep -i "\.env$"   # doit être vide

# 4. Premier commit propre
git commit -m "chore: IQRA v1.0.0 — initial clean import"

# 5. Brancher le nouveau dépôt (créé sur le nouveau compte) et pousser
git remote add origin https://github.com/<NOUVEAU_COMPTE>/iqra.git
git push -u origin main

# 6. Taguer la version
git tag -a v1.0.0 -m "IQRA v1.0.0"
git push origin v1.0.0
```

> Pourquoi historique neuf ? Pour garantir qu'**aucun secret éventuellement
> commité par le passé** ne traîne dans l'historique du dépôt commercial.

**À vérifier sur le nouveau dépôt :**
- [ ] Dépôt **privé**.
- [ ] `.gitignore` bien présents (racine + flutter + backend + front).
- [ ] Réactiver **GitHub Actions** (facturation OK) → les 5 workflows tournent.

---

## 3. ⚙️ Configuration des `.env` (par application)

| App | Fichier | Action |
|---|---|---|
| Backend | `job-backoffice/.env` | `cp .env.example .env` puis remplir DB, MAIL, GOOGLE_WEB_CLIENT_ID, OPENAI, Turnstile ; `php artisan key:generate` |
| Front React | `job-app-frontend/.env` | `cp .env.example .env` puis `VITE_GOOGLE_WEB_CLIENT_ID`, `VITE_TURNSTILE_SITE_KEY` |
| Flutter | `flutter_app/web/index.html` | meta `google-signin-client_id` ; (Android/iOS : `google-services.json` / `GoogleService-Info.plist` — **non commités**) |

Procédure d'obtention des clés : [`SETUP_CREDENTIALS.md`](./SETUP_CREDENTIALS.md).

---

## 4. ✅ Checklist qualité v1.0.0

**Code & tests (doivent être verts) :**
- [ ] Backend : `cd job-backoffice && php artisan test` → **0 échec**
- [ ] Front : `cd job-app-frontend && npx tsc --noEmit` → **0 erreur** + `npm run build`
- [ ] Flutter : `cd flutter_app && flutter analyze` → **0 issue** + `flutter build web --release`
- [ ] Lint : Pint (PHP) + ESLint (JS) propres

**Sécurité prod :**
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, **HTTPS** forcé
- [ ] Comptes de **démo/seed supprimés** (`admin@admin.com`, etc.)
- [ ] Secrets en variables serveur, sauvegardes DB automatiques

**Conformité (loi 18-07) :**
- [ ] Publier **politique de confidentialité** + **CGU**
  ([`POLITIQUE_CONFIDENTIALITE.md`](./POLITIQUE_CONFIDENTIALITE.md) · [`CGU.md`](./CGU.md))
- [ ] Déposer le dossier **ANPDP** ([`REGISTRE_TRAITEMENTS.md`](./REGISTRE_TRAITEMENTS.md))
- [ ] Voir les actions restantes : [`ACTIONS_EXTERNES.md`](./ACTIONS_EXTERNES.md)

**Déploiement :**
- [ ] Suivre [`GUIDE_DEPLOIEMENT.md`](./GUIDE_DEPLOIEMENT.md) (serveur, nginx,
  **queue worker** pour l'IA, HTTPS) + [`PRODUCTION_CHECKLIST.md`](./PRODUCTION_CHECKLIST.md)

---

## 5. 🚀 CI/CD (nouveau compte)

Les 5 workflows existent déjà dans `.github/workflows/` (`backend`, `web`,
`flutter`, `lint`, `deploy`). Sur le nouveau compte :
- [ ] Activer la **facturation GitHub Actions** (gratuit jusqu'au quota).
- [ ] Ajouter les **secrets du dépôt** (clé SSH serveur, hôte) pour `deploy.yml`.
- [ ] `deploy.yml` se déclenche sur un **tag** `vX.Y.Z` → déploiement auto.

---

## 6. 🔖 Versionnement

- Flutter : `flutter_app/pubspec.yaml` → `version: 1.0.0+1` ✅ (déjà à jour).
- Convention : **SemVer** (`MAJOR.MINOR.PATCH`). Taguer chaque release
  (`git tag v1.0.0`).

---

## 7. Résumé « avant de pousser le repo commercial »

1. Rotation des secrets ✔
2. `.env` jamais commités (vérifié) ✔
3. Tests verts (backend / front / flutter) ✔
4. Politique de confidentialité + CGU publiées ✔
5. Dossier ANPDP déposé ✔
6. Déploiement selon `GUIDE_DEPLOIEMENT.md` ✔

> Une fois ces 6 points cochés, la v1.0.0 est prête à être commercialisée.

# IQRA — Plateforme emploi & formation

Plateforme algérienne de mise en relation **candidats / entreprises / écoles**, avec analyse IA des candidatures.

---

## 📚 Documentation

### Dossier business plan (synthèse)
| Doc | Contenu |
|-----|---------|
| [`RESUME_1PAGE.md`](./RESUME_1PAGE.md) | Résumé exécutif (1 page) |
| [`ETAT_AVANCEMENT.md`](./ETAT_AVANCEMENT.md) | État d'avancement détaillé |
| [`ARCHITECTURE.md`](./ARCHITECTURE.md) | Architecture (schéma, composants, rôles, flux) |
| [`CONFORMITE_DONNEES.md`](./CONFORMITE_DONNEES.md) | Conformité loi 18-07 / RGPD-DZ (analyse d'écart interne) |
| [`POLITIQUE_CONFIDENTIALITE.md`](./POLITIQUE_CONFIDENTIALITE.md) | Politique de confidentialité (brouillon prêt à publier) |
| [`CGU.md`](./CGU.md) | Conditions Générales d'Utilisation (brouillon) |
| [`REGISTRE_TRAITEMENTS.md`](./REGISTRE_TRAITEMENTS.md) | Registre des traitements (dossier ANPDP) |
| [`IQRA_Dossier_BusinessPlan.docx`](./IQRA_Dossier_BusinessPlan.docx) | Dossier Word fusionné |
| *EN :* [`PROJECT_STATUS.md`](./PROJECT_STATUS.md) · [`DATA_COMPLIANCE.md`](./DATA_COMPLIANCE.md) | Versions anglaises (statut + conformité) |

### Plans de conception (technique / roadmap)
| Doc | Contenu |
|-----|---------|
| [`PLAN_AUTH_SOCIAL.md`](./PLAN_AUTH_SOCIAL.md) | Auth e-mail + Google/Facebook, comptes liés, hardening |
| [`PLAN_DASHBOARD_JOBS_TRAINING.md`](./PLAN_DASHBOARD_JOBS_TRAINING.md) | Tableaux de bord offres / formations |
| [`PLAN_TECHNIQUE_FLUTTER.md`](./PLAN_TECHNIQUE_FLUTTER.md) | Architecture de l'app Flutter |

### Exploitation
| Doc | Contenu |
|-----|---------|
| [`PRODUCTION_CHECKLIST.md`](./PRODUCTION_CHECKLIST.md) | Checklist de mise en production |
| [`GUIDE_DEPLOIEMENT.md`](./GUIDE_DEPLOIEMENT.md) | Guide de déploiement pas-à-pas (serveur, nginx, queue, HTTPS) |
| [`ACTIONS_EXTERNES.md`](./ACTIONS_EXTERNES.md) | Actions hors-code (hébergeur, juridique, ANPDP, CI, services prod) |
| [`SETUP_CREDENTIALS.md`](./SETUP_CREDENTIALS.md) | Configurer Gmail / Google / Meta |

> Les fichiers `PLAN_*` sont des **notes de conception** ; l'**état réel à jour**
> est dans [`ETAT_AVANCEMENT.md`](./ETAT_AVANCEMENT.md).

---

## 🗂️ Structure du monorepo

```
appppp/
├── ppp/                        # Backend + frontend web (Laravel + React)
│   ├── job-backoffice/         # API Laravel + admin Blade (port 8000)
│   ├── job-app-frontend/       # SPA React/Vite (port 3000)
│   └── job-shared/             # Models Eloquent partagés (composer path repo)
│
├── flutter_app/                # App mobile + web Flutter (port 8090 en web)
│
├── PLAN_AUTH_SOCIAL.md         # Plan + status auth (Phases 1-6)
├── PLAN_TECHNIQUE_FLUTTER.md   # Architecture Flutter
├── SETUP_CREDENTIALS.md        # Guide pour configurer Gmail/Google/Meta
└── CLAUDE.md                   # Contexte agents Claude (gitignored)
```

---

## 🚀 Démarrer les services

### Pré-requis
- **PHP 8.2+** avec Composer
- **Node 20+** avec npm
- **Flutter 3.9+** (pour mobile/web Flutter)
- **MySQL 8** ou **MariaDB 10.6+** (BDD)
- **Compte Gmail + App Password** (pour SMTP en dev)

### 1. Backend Laravel — port 8000

```bash
cd ppp/job-backoffice
composer install
cp .env.example .env             # puis remplir DB_*, MAIL_*, etc. — voir PLAN_AUTH_SOCIAL.md
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8000
```

API disponible sur `http://localhost:8000/api/*`.
Backoffice admin Blade sur `http://localhost:8000/` (login : `admin@admin.com` / `12345678`).

### 2. Web React/Vite — port 3000

```bash
cd ppp/job-app-frontend
npm install
cp .env.example .env             # voir doc auth pour VITE_GOOGLE_WEB_CLIENT_ID etc.
npm run dev -- --port 3000 --strictPort
```

Disponible sur `http://localhost:3000`.

> ⚠️ Toujours `--strictPort` : sinon Vite peut basculer sur 3001 si 3000 est occupé par un zombie, ce qui casse les CORS Google OAuth.

### 3. Flutter — web sur port 8090 (fiable)

```bash
cd flutter_app
flutter pub get
run_web.bat          # libère 8090, lance le serveur web, ouvre le navigateur
```

`run_web.bat` (ou `run_web.ps1`) utilise le device **`web-server`** sur le port
fixe **8090** et **libère d'abord le port** si un `flutter run` précédent y est
resté bloqué — c'est la cause classique des erreurs « port déjà utilisé » sur
8090. Arrêt propre : **Ctrl+C** dans la fenêtre, ou `stop_web.bat`.

> Le port **fixe 8090** est requis pour Google Sign-In (origine enregistrée dans
> Google Cloud) ; un port aléatoire casserait l'auth Google.

Pour Android : `flutter run -d <device-id>`. Pour iOS : `flutter run -d <ipad/iphone>`.

---

## 🔐 Auth — où on en est

5 phases sur 6 livrées (voir [PLAN_AUTH_SOCIAL.md](./PLAN_AUTH_SOCIAL.md)) :

| Phase | Status |
|-------|--------|
| **1** Reset password par email | ✅ Production (SMTP Gmail OK) |
| **2** Google Sign-In | ✅ Production |
| **3** Meta (Facebook + Instagram) | ⏳ Code prêt, en attente App Review Meta |
| **4** Phone OTP Firebase | 🔄 Backlog |
| **5** UI "Comptes liés" sur profil | ✅ Production |
| **6** Tests + Hardening (rate-limit, audit, Turnstile) | ✅ Production |

> **Juin 2026** : inscription **Google/Facebook pour tous les profils**
> (candidat / entreprise / école) sur **web + Flutter** ; **vitrine publique**
> d'offres/formations sur l'accueil (clic → connexion) ; **run Flutter Web
> fiabilisé** sur le port 8090 (`run_web.bat`).

---

## 🧪 Tests

| Stack | Cmd | Compte |
|-------|-----|--------|
| Backend Pest (59 tests) | `cd ppp/job-backoffice && ./vendor/bin/pest` | ~15s |
| Flutter unit (5 tests) | `cd flutter_app && flutter test` | ~30s |
| Web E2E Playwright (8 tests) | `cd ppp/job-app-frontend && npm run test:e2e` | ~1min |

**Total : 72 tests automatisés.**

Pour des E2E rapides : passer `MAIL_MAILER=log` dans le `.env` backend pendant les tests.

---

## 👥 Comptes de test (dev seulement)

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| `admin@admin.com` | `12345678` | admin |
| `exemple@exemple.com` | `password12345678` | job-seeker |
| `mohamed@mohamed.com` | `password12345678` | company-owner |
| `ecole@ecole.com` | `password12345678` | school-owner |

⚠️ **À retirer en prod** : ces comptes existent uniquement parce qu'on a un seeder de demo.

---

## 📞 Téléphone obligatoire

Ajouté en bonus de la phase 6 :
- **Candidats** : doivent saisir leur téléphone à la **première candidature** (puis persisté sur le profil)
- **Entreprises & Écoles** : doivent saisir leur téléphone à **l'inscription**
- Affiché aux candidats sur les pages offres/formations (lien `tel:` cliquable)
- Affiché aux recruteurs sur la liste des candidats (lien `tel:` cliquable)

---

## 🗃️ Migrations BDD récentes

| Date | Migration | Effet |
|------|-----------|-------|
| 2026-05-25 | `add_type_and_cancellation_to_training_sessions` | Type (en_ligne/accelerer/presentiel) + raison annulation |
| 2026-05-25 | `remove_draft_status_from_training_sessions` | Enum sans `draft` |
| 2026-05-28 | `extend_users_for_social_auth` | `auth_providers` + `password` nullable |
| 2026-05-28 | `create_login_audits` | Table de tracking forensique |
| 2026-05-30 | `add_phone_to_users_companies_schools` | Colonne `phone` sur 3 tables |
| 2026-06-09 | `add_education_level_to_applications` | Niveau d'études sur candidatures emploi + formation |
| 2026-06-09 | `add_min_education_level_to_training_sessions` | Niveau d'études min. requis sur les sessions |
| 2026-06-09 | `add_longue_duree_to_training_type_enum` | Type formation **longue durée** (garde-fou driver MySQL/MariaDB) |

---

## 🛠️ Outils utiles

- **`SETUP_CREDENTIALS.md`** : pas-à-pas pour créer un compte Gmail App Password, un OAuth Client Google, une app Meta
- **`PLAN_AUTH_SOCIAL.md`** : architecture détaillée auth + status par phase
- **`PLAN_TECHNIQUE_FLUTTER.md`** : structure Flutter

---

## 🆘 Problèmes courants

| Symptôme | Cause probable | Fix |
|----------|----------------|-----|
| Vite tourne sur 3001 au lieu de 3000 | Zombie node sur 3000 | `Get-NetTCPConnection -LocalPort 3000` → `Stop-Process -Id <pid>` |
| Gmail SMTP timeout sur 587 | Antivirus / Defender / ISP scan | Basculer sur 465 SSL : `MAIL_PORT=465 MAIL_ENCRYPTION=ssl` |
| Google OAuth `origin_mismatch` | Vite sur mauvais port | Vérifier l'URL exacte dans **Google Cloud Console → OAuth → Authorized JavaScript origins** |
| Turnstile widget invisible | Hostname pas autorisé | Cloudflare Turnstile → ajouter `localhost` dans Hostname Management |
| Flutter web Google ne marche pas | Meta tag manquant | Vérifier `<meta name="google-signin-client_id" content="…">` dans `flutter_app/web/index.html` |

---

## 📦 Stack technique

| Couche | Tech | Why |
|--------|------|-----|
| Backend API + Admin | Laravel 12 + Sanctum + Pest | Mature, RBAC simple, factories solides |
| Models partagés | Composer path repository (`job-shared`) | Modèles Eloquent définis une seule fois, réutilisés par `job-backoffice` |
| Frontend candidat | React 18 + Vite 6 + TypeScript | DX rapide, HMR instantané |
| Frontend admin | Blade + Tailwind | Pas besoin d'une SPA pour le CRUD admin |
| Mobile + Web | Flutter 3.9 + Riverpod + go_router | Single codebase iOS/Android/Web |
| IA scoring CV | OpenAI gpt-4o-mini via `openai-php/laravel` | Coût ≈ $0.0001 par analyse |
| BDD | MySQL 8 / MariaDB 10.6+ | UUID partout pour IDs |
| Mail | Gmail SMTP (dev) / SendGrid à prévoir (prod) | Gratuit ≤ 500 mails/jour |
| CAPTCHA | Cloudflare Turnstile | Pas de tracking, gratuit illimité |

---

## 📝 Licence

Propriétaire — © 2026 IQRA.

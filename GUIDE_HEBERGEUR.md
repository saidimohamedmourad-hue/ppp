# IQRA — Choisir et configurer l'hébergeur (production)

> Guide de décision + procédure pas-à-pas pour héberger IQRA. Pour les détails
> serveur (nginx, queue, HTTPS), voir [`GUIDE_DEPLOIEMENT.md`](./GUIDE_DEPLOIEMENT.md).

---

## 1. Quel hébergeur ?

| Profil | Choix | Coût indicatif | Remarque |
|---|---|---|---|
| **Démarrage rapide, peu de devops** ⭐ | **Hetzner (VPS) + Laravel Forge** | ~5 € + 12 $/mois | Forge automatise nginx, PHP, queue, HTTPS, déploiements |
| **Conformité 18-07 stricte (données en Algérie)** | Hébergeur **algérien** (ICOSnet, Ayrade…) | variable | évite l'autorisation de transfert pour la base |
| **VPS classique, tout à la main** | OVH / DigitalOcean / Contabo | 5–12 $/mois | suivre `GUIDE_DEPLOIEMENT.md` manuellement |

> ⚠️ Quel que soit l'hébergeur de la **base**, les services **Google / Facebook
> / OpenAI / Cloudflare** restent des transferts hors Algérie → relèvent de
> l'ANPDP (voir `ACTIONS_EXTERNES.md` / `BRIEF_AVOCAT.md`).

**Dimensionnement de départ** : 2 vCPU / 4 Go RAM / ~60 Go SSD (tient : Laravel
+ MariaDB + nginx + queue worker). On scale plus tard si besoin.

---

## 2. Option recommandée — Hetzner + Laravel Forge (pas-à-pas)

### A. Créer le serveur Hetzner
1. Compte sur **console.hetzner.cloud** → **Add Server**.
2. **Location** : Falkenstein/Nuremberg (UE) — ou la plus proche.
3. **Image** : Ubuntu 22.04 (ou 24.04).
4. **Type** : **CX22** (2 vCPU / 4 Go) pour démarrer.
5. Ajoute ta **clé SSH**. Crée le serveur, note son **IP publique**.

### B. Connecter Laravel Forge
1. Compte sur **forge.laravel.com** → connecte ton **provider Hetzner** (API
   token Hetzner) — ou « Custom VPS » avec l'IP + accès SSH.
2. Forge **provisionne** le serveur : nginx, PHP 8.3, MariaDB/MySQL, Redis,
   supervisor, certbot — automatiquement.

### C. Créer le site
1. Forge → serveur → **New Site** : domaine `iqra.dz`, type **PHP**, web
   directory **`/public`**.
2. **Git Repository** : `https://github.com/<COMPTE>/iqra` (Forge a une clé de
   déploiement à ajouter dans GitHub → Deploy keys).
3. **Repository path** : `job-backoffice` *(le site Laravel n'est pas à la
   racine du monorepo)*. → adapter le « root directory » du site sur ce dossier.

### D. Variables d'environnement
- Forge → site → **Environment** : coller le `.env` de prod (depuis
  `job-backoffice/.env.example`, valeurs prod : `APP_ENV=production`,
  `APP_DEBUG=false`, DB, MAIL, GOOGLE_WEB_CLIENT_ID, **OPENAI_API_KEY**,
  Turnstile réelles…).

### E. Script de déploiement (Forge → Deploy Script)
```bash
cd $FORGE_SITE_PATH                 # .../job-backoffice
git pull origin $FORGE_SITE_BRANCH
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart

# Front React (servi en statique par un autre site/sous-domaine)
cd ../job-app-frontend && npm ci && npm run build
```

### F. Queue worker (analyse IA — indispensable)
- Forge → serveur → **Daemons** : `php artisan queue:work --tries=2`
  (working dir = le site). Sans ça, les **scores IA** et l'**analyse des CV** ne
  se calculent pas.

### G. HTTPS
- Forge → site → **SSL** → **Let's Encrypt** → activer (auto-renouvellement).

### H. Front React + Flutter web
- **React** : créer un 2ᵉ site `iqra.dz` (statique) servant
  `job-app-frontend/dist`, ou un sous-domaine ; build via le deploy script.
- **API** : la plus simple = sous-domaine `api.iqra.dz` → site Laravel
  (`job-backoffice/public`). Adapter `APP_URL`, `APP_FRONTEND_URL` et
  `VITE_*` en conséquence.
- **Flutter web** (option) : `flutter build web --release` → servir
  `build/web` sur `app.iqra.dz`.

---

## 3. Option « tout à la main » (sans Forge)

Suivre intégralement [`GUIDE_DEPLOIEMENT.md`](./GUIDE_DEPLOIEMENT.md) (§1 à §9) :
prérequis, MariaDB, Laravel, **queue via Supervisor**, build React, nginx +
certbot, services externes, mises à jour.

---

## 4. Coût mensuel estimé (démarrage)

| Poste | Coût |
|---|---|
| VPS Hetzner CX22 | ~5 €/mois |
| Laravel Forge (option) | 12 $/mois |
| Domaine `.dz` | ~quelques €/an |
| OpenAI (IA CV) | ≈ $0.0001 / analyse (+ alerte budget) |
| Google / Facebook / Turnstile | gratuit |
| **Total infra** | **~5–18 $/mois** au démarrage |

---

> Checklist de mise en prod complète : [`V1_RELEASE.md`](./V1_RELEASE.md) §4 ·
> actions externes : [`ACTIONS_EXTERNES.md`](./ACTIONS_EXTERNES.md).

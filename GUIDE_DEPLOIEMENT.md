# Guide de déploiement — IQRA (production)

> Guide pas-à-pas pour mettre IQRA en production sur un serveur Linux (Ubuntu
> 22.04+). À lire avec [`PRODUCTION_CHECKLIST.md`](./PRODUCTION_CHECKLIST.md).
> Remplacer `iqra.dz` et les `[…]` par tes vraies valeurs.

**Dernière mise à jour : 13 juin 2026**

---

## 0. Architecture cible

```
                      Internet (HTTPS)
                            │
                        ┌───▼────┐
                        │ Nginx  │  (TLS, reverse proxy)
                        └───┬────┘
        ┌───────────────────┼─────────────────────┐
        ▼                   ▼                     ▼
  React (statique)    /api -> PHP-FPM        Flutter web
  iqra.dz             (Laravel)              app.iqra.dz (option)
                            │
                     ┌──────┴───────┐
                     ▼              ▼
                  MariaDB     Queue worker (php artisan queue:work)
                  (etude_db)  -> analyse IA (OpenAI) en asynchrone
```

Tout peut tenir sur **un seul serveur** au démarrage (≥ 2 vCPU / 4 Go RAM).

---

## 1. Prérequis serveur

```bash
sudo apt update
sudo apt install -y nginx mariadb-server php8.3-fpm php8.3-cli \
  php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip \
  php8.3-bcmath php8.3-gd unzip git supervisor certbot python3-certbot-nginx

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node 20 (build du front)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 2. Base de données

```bash
sudo mysql_secure_installation        # mot de passe root, durcissement
sudo mysql
```
```sql
CREATE DATABASE etude_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'iqra'@'localhost' IDENTIFIED BY '[mot_de_passe_fort]';
GRANT ALL PRIVILEGES ON etude_db.* TO 'iqra'@'localhost';
FLUSH PRIVILEGES;
```

---

## 3. Backend Laravel (`job-backoffice`)

```bash
cd /var/www
sudo git clone [URL_DEPOT] iqra && cd iqra/job-backoffice
composer install --no-dev --optimize-autoloader
cp .env.example .env
```

Éditer `.env` (valeurs **production**) :
```env
APP_NAME=IQRA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://iqra.dz
APP_FRONTEND_URL=https://iqra.dz

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=etude_db
DB_USERNAME=iqra
DB_PASSWORD=[mot_de_passe_fort]

# Cache / sessions / file d'attente gérés par la base (pas de Redis)
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Services externes (voir §7)
GOOGLE_WEB_CLIENT_ID=...
FACEBOOK_CLIENT_ID=...
FACEBOOK_CLIENT_SECRET=...
TURNSTILE_SITE_KEY=...
TURNSTILE_SECRET=...
OPENAI_API_KEY=...
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@iqra.dz"
MAIL_FROM_NAME=IQRA
```

Finaliser :
```bash
php artisan key:generate
php artisan migrate --force          # --force = obligatoire en prod
php artisan db:seed --force          # uniquement si données de base voulues
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions (l'utilisateur de php-fpm = www-data)
sudo chown -R www-data:www-data storage bootstrap/cache
```

> ⚠️ **`.env` ne doit jamais être commité** ni accessible publiquement.

---

## 4. File d'attente (analyse IA asynchrone) — **critique**

L'analyse des CV (OpenAI) tourne en **tâche asynchrone**. Sans worker, les scores
IA ne sont jamais calculés. Configurer un worker permanent via **Supervisor** :

`/etc/supervisor/conf.d/iqra-worker.conf`
```ini
[program:iqra-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/iqra/job-backoffice/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/iqra/job-backoffice/storage/logs/worker.log
stopwaitsecs=3600
```
```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start iqra-worker:*
```

> Après chaque déploiement de code : `php artisan queue:restart` (les workers
> rechargent le nouveau code).

---

## 5. Frontend React (`job-app-frontend`)

```bash
cd /var/www/iqra/job-app-frontend
cp .env.example .env        # renseigner VITE_GOOGLE_WEB_CLIENT_ID, VITE_TURNSTILE_SITE_KEY, etc.
npm ci
npm run build               # genere dist/ (statique)
```
`dist/` sera servi directement par Nginx (statique, très rapide).

---

## 6. Nginx + HTTPS

`/etc/nginx/sites-available/iqra` :
```nginx
server {
    server_name iqra.dz www.iqra.dz;
    root /var/www/iqra/job-app-frontend/dist;
    index index.html;

    # API Laravel -> PHP-FPM
    location /api {
        try_files $uri $uri/ @laravel;
    }
    location @laravel {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /var/www/iqra/job-backoffice/public/index.php;
        fastcgi_param REQUEST_URI $request_uri;
    }

    # Back-office Blade (admin) sur /admin -> Laravel (optionnel, si exposé)
    # location /admin { ... même bloc fastcgi ... }

    # SPA React : tout le reste -> index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    client_max_body_size 10M;   # upload CV (PDF)
}
```
```bash
sudo ln -s /etc/nginx/sites-available/iqra /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d iqra.dz -d www.iqra.dz   # HTTPS automatique (Let's Encrypt)
```

> Pour servir l'API et l'admin Blade proprement, l'approche la plus simple est de
> pointer le `root` Nginx vers `job-backoffice/public` sur un sous-domaine
> `api.iqra.dz` dédié, et le front React sur `iqra.dz`. Adapter `APP_URL`,
> `APP_FRONTEND_URL` et le proxy en conséquence.

---

## 7. Configuration des services externes (prod)

| Service | À faire en prod |
|---|---|
| **Google OAuth** | Ajouter `https://iqra.dz` (et le domaine Flutter web) dans **Authorized JavaScript origins** ; mettre à jour `GOOGLE_WEB_CLIENT_ID` |
| **Facebook Login** | App Meta en mode **Live**, domaine de prod autorisé, `FACEBOOK_CLIENT_ID/SECRET` |
| **Cloudflare Turnstile** | Créer une **vraie clé** (pas les clés de test), ajouter `iqra.dz` dans *Hostname Management*, mettre `TURNSTILE_SITE_KEY/SECRET` |
| **OpenAI** | Clé API de prod + **budget/alerte** de dépense |
| **SMTP** | Compte d'envoi de prod (Gmail App Password ou SendGrid/Mailgun pour la délivrabilité) |

---

## 8. Flutter web (optionnel)

```bash
cd /var/www/iqra/flutter_app
flutter build web --release
# servir build/web/ via un sous-domaine app.iqra.dz (bloc nginx statique)
```
Penser à ajouter le domaine Flutter dans les origines Google OAuth.

---

## 9. Sécurité prod (checklist rapide)

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] HTTPS forcé (certbot) + redirection HTTP→HTTPS
- [ ] `.env` hors web root, jamais commité, permissions `600`
- [ ] Mots de passe DB/SMTP/API forts et uniques
- [ ] Supprimer les **comptes de démo** (seeder) en prod
- [ ] Sauvegardes DB automatiques (`mysqldump` + cron)
- [ ] Pare-feu : n'exposer que 80/443 (et 22 restreint)
- [ ] `php artisan config:cache` à jour après tout changement d'`.env`

---

## 10. Déploiement d'une mise à jour

```bash
cd /var/www/iqra && git pull
# Backend
cd job-backoffice
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart            # recharge les workers
# Frontend
cd ../job-app-frontend && npm ci && npm run build
sudo systemctl reload nginx
```

**Rollback** : `git checkout <tag_précédent>` puis rejouer les étapes ci-dessus
(et, si besoin, `php artisan migrate:rollback`).

---

## 11. CI/CD

Les workflows GitHub Actions existent déjà (`.github/workflows/` :
`backend.yml`, `web.yml`, `flutter.yml`, `lint.yml`, `deploy.yml`). Ils se
réactivent dès que la **facturation GitHub Actions** est rétablie. `deploy.yml`
se déclenche sur un **tag** de version — adapter ses secrets (clé SSH serveur,
hôte) pour automatiser les étapes du §10.

---

> *Guide opérationnel. Adapter les chemins, domaines et versions à ton
> infrastructure réelle.*

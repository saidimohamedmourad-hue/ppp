# Production Checklist — IQRA

Document de référence pour passer **du dev local à la prod**. À lire en entier au moins une fois avant le premier déploiement, puis utilisé comme checklist pour chaque release.

**Dernière màj** : 2026-05-30

---

## 📋 Sommaire

1. [Pré-requis serveur](#1-pré-requis-serveur)
2. [Avant-déploiement — checklist code](#2-avant-déploiement--checklist-code)
3. [Configuration des secrets](#3-configuration-des-secrets)
4. [Étapes de déploiement initial](#4-étapes-de-déploiement-initial)
5. [Sécurité — durcissement](#5-sécurité--durcissement)
6. [Monitoring & alerting](#6-monitoring--alerting)
7. [Backup BDD](#7-backup-bdd)
8. [Rollback procedure](#8-rollback-procedure)
9. [Disaster recovery](#9-disaster-recovery)
10. [Vérifications post-déploiement](#10-vérifications-post-déploiement)
11. [Releases suivantes (process court)](#11-releases-suivantes-process-court)

---

## 1. Pré-requis serveur

### Hardware minimum (start)

| Ressource | Min | Recommandé |
|-----------|-----|------------|
| CPU | 2 vCPU | 4 vCPU |
| RAM | 4 GB | 8 GB |
| Disque | 40 GB SSD | 80 GB SSD |
| Bande passante | 1 Gbps | 1 Gbps |

Pour 0–5 000 users actifs/mois, **un seul VPS Hetzner CX22** (4 €/mois) suffit. Au-delà, séparer BDD sur un instance dédiée.

### Stack OS

- **Ubuntu Server 24.04 LTS** (support jusqu'à 2029)
- Firewall **ufw** activé dès le départ
- Pas de Docker au démarrage — direct sur le système. Docker uniquement si l'équipe atteint 2+ devs déployants.

### Logiciels à installer

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y \
  nginx \
  mariadb-server \
  redis-server \
  php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-mysql php8.3-gd php8.3-zip php8.3-bcmath php8.3-intl \
  certbot python3-certbot-nginx \
  git unzip supervisor ufw fail2ban
```

Composer :
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Node (pour build du SPA) :
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### Domaines & DNS

| Sous-domaine | Pointe vers | Usage |
|--------------|-------------|-------|
| `iqra.app` | IP serveur | SPA React (frontend candidat) |
| `api.iqra.app` | IP serveur | API Laravel |
| `admin.iqra.app` | IP serveur | Backoffice Blade admin |

3 records DNS A vers la même IP. Idéalement TTL 300s pendant la phase d'install (permet de bouger vite si besoin).

---

## 2. Avant-déploiement — checklist code

À cocher **avant chaque release** (pas juste la première fois).

### Tests
- [ ] `cd ppp/job-backoffice && ./vendor/bin/pest` → 100 % vert
- [ ] `cd ppp/job-app-frontend && npm run test:e2e` → 100 % vert
- [ ] `cd flutter_app && flutter test` → 100 % vert
- [ ] `cd ppp/job-app-frontend && npx tsc --noEmit` → 0 erreur TypeScript
- [ ] `cd flutter_app && flutter analyze` → 0 issue

### Vérifs de code
- [ ] Pas de `dd()`, `var_dump()`, `console.log` débug oubliés (rechercher avant de push)
- [ ] Pas de credentials hardcodés (chercher `password`, `secret`, `api_key` dans le diff)
- [ ] `composer.lock` et `package-lock.json` committés (déterminisme)
- [ ] Migration de la release a une fonction `down()` qui marche (test en local : `migrate:rollback`)
- [ ] Migration non-destructive (ne PAS dropper de colonne qui peut contenir des données prod — créer une nouvelle release de cleanup plus tard)

### Vérifs métier
- [ ] La feature touche aux paiements ou aux comptes utilisateurs ? → review obligatoire par une 2e paire d'yeux
- [ ] Changement de schéma ? → tester la migration sur une copie de la BDD prod avant
- [ ] Changement d'API consommé par le Flutter app ? → publier nouvelle version mobile **avant** de déployer le backend

---

## 3. Configuration des secrets

### Où stocker les secrets

Dans le `.env` sur le serveur, dans `/var/www/iqra/backend/.env`, avec permissions `640` :

```bash
sudo chown www-data:www-data /var/www/iqra/backend/.env
sudo chmod 640 /var/www/iqra/backend/.env
```

**Ne jamais commit** un `.env` rempli. Utiliser `.env.example` comme template (voir [ppp/job-backoffice/.env.example](./ppp/job-backoffice/.env.example)).

### Liste des secrets à provisionner

| Service | Variable | Comment l'obtenir |
|---------|----------|-------------------|
| Laravel | `APP_KEY` | `php artisan key:generate --show` |
| Database | `DB_PASSWORD` | Générer avec `openssl rand -base64 32` (jamais à la main) |
| Mail | `MAIL_PASSWORD` | App Password Gmail (16 chars) — voir [SETUP_CREDENTIALS.md](./SETUP_CREDENTIALS.md) |
| Google OAuth | `GOOGLE_WEB_CLIENT_ID` | Google Cloud Console (créé en Phase 2) |
| Meta | `FACEBOOK_CLIENT_ID` + `_SECRET` | Meta for Developers, après App Review |
| Turnstile | `TURNSTILE_SECRET` | Cloudflare Turnstile dashboard, vraie clé (PAS la `1x0000...AA` de dev) |
| OpenAI | `OPENAI_API_KEY` | platform.openai.com — créer une clé prod-only avec budget cap |

### Vault recommandé pour évolution

Une fois 2+ devs : passer sur **HashiCorp Vault** ou **AWS Secrets Manager** plutôt que des `.env` sur disque. Pour 1 dev seul, le `.env` chmod 640 suffit.

---

## 4. Étapes de déploiement initial

### 4.1 Créer l'utilisateur applicatif

```bash
sudo adduser --disabled-password --gecos "" iqra
sudo usermod -aG www-data iqra
```

### 4.2 Cloner le projet

```bash
sudo mkdir -p /var/www/iqra
sudo chown iqra:iqra /var/www/iqra
sudo -u iqra git clone <REPO_URL> /var/www/iqra/source
```

### 4.3 Backend Laravel

```bash
cd /var/www/iqra/source/ppp/job-backoffice
sudo -u iqra composer install --no-dev --optimize-autoloader
sudo -u iqra cp .env.example .env
sudo -u iqra php artisan key:generate
# Remplir .env (DB, mail, OAuth, etc.) — voir section 3
sudo -u iqra php artisan migrate --force
sudo -u iqra php artisan storage:link
sudo -u iqra php artisan config:cache
sudo -u iqra php artisan route:cache
sudo -u iqra php artisan view:cache
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R iqra:www-data storage bootstrap/cache
```

### 4.4 Frontend SPA React

```bash
cd /var/www/iqra/source/ppp/job-app-frontend
sudo -u iqra cp .env.example .env
# Remplir VITE_GOOGLE_WEB_CLIENT_ID, VITE_TURNSTILE_SITE_KEY, VITE_API_BASE_URL=https://api.iqra.app
sudo -u iqra npm ci
sudo -u iqra npm run build
# Le bundle est dans /var/www/iqra/source/ppp/job-app-frontend/dist/
```

### 4.5 Nginx — config 3 vhosts

**`/etc/nginx/sites-available/iqra-spa.conf`** (SPA candidat) :
```nginx
server {
    server_name iqra.app www.iqra.app;
    root /var/www/iqra/source/ppp/job-app-frontend/dist;
    index index.html;

    # SPA fallback — toutes les routes inconnues servent index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache long pour les assets versionnés par Vite
    location ~* \.(js|css|woff2?|svg|png|jpg|webp)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    listen 80;
}
```

**`/etc/nginx/sites-available/iqra-api.conf`** (API Laravel) :
```nginx
server {
    server_name api.iqra.app;
    root /var/www/iqra/source/ppp/job-backoffice/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }

    client_max_body_size 25M;   # uploads CV
    listen 80;
}
```

**`/etc/nginx/sites-available/iqra-admin.conf`** : même config que `iqra-api.conf` mais avec `server_name admin.iqra.app;`

```bash
sudo ln -s /etc/nginx/sites-available/iqra-*.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### 4.6 HTTPS avec Let's Encrypt

```bash
sudo certbot --nginx -d iqra.app -d www.iqra.app -d api.iqra.app -d admin.iqra.app
```

Vérifier le renouvellement auto :
```bash
sudo certbot renew --dry-run
```

### 4.7 Queue worker (pour les jobs IA en arrière-plan)

Si OpenAI est appelé en queue (recommandé pour ne pas bloquer la requête HTTP), configurer Supervisor :

**`/etc/supervisor/conf.d/iqra-queue.conf`** :
```ini
[program:iqra-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/iqra/source/ppp/job-backoffice/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=iqra
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/iqra/worker.log
stopwaitsecs=3600
```

```bash
sudo mkdir -p /var/log/iqra && sudo chown iqra:iqra /var/log/iqra
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl start iqra-queue:*
```

### 4.8 Scheduler Laravel (cron)

```bash
sudo crontab -u iqra -e
```
Ajouter :
```cron
* * * * * cd /var/www/iqra/source/ppp/job-backoffice && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Sécurité — durcissement

### 5.1 Firewall

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 5.2 SSH

`/etc/ssh/sshd_config` :
```
PasswordAuthentication no    # clé SSH uniquement
PermitRootLogin no
AllowUsers iqra
```
```bash
sudo systemctl restart ssh
```

### 5.3 Headers HTTP

Ajouter dans chaque vhost Nginx :
```nginx
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
# Adapter le CSP au cas par cas — démarrer permissif puis serrer
add_header Content-Security-Policy "default-src 'self' https:; img-src * data:; script-src 'self' https://challenges.cloudflare.com https://accounts.google.com 'unsafe-inline'; style-src 'self' https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; frame-src https://challenges.cloudflare.com https://accounts.google.com;" always;
```

### 5.4 fail2ban

`/etc/fail2ban/jail.local` :
```ini
[sshd]
enabled = true
maxretry = 5
bantime = 3600

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/error.log
maxretry = 10
findtime = 60
bantime = 3600
```

### 5.5 BDD

- [ ] `mysql_secure_installation` → root password, drop test DB, supprimer users anonymes
- [ ] User MariaDB applicatif **non-root** avec accès uniquement à `iqra` :
  ```sql
  CREATE USER 'iqra_user'@'localhost' IDENTIFIED BY '<password-fort>';
  GRANT SELECT, INSERT, UPDATE, DELETE, INDEX, ALTER ON iqra.* TO 'iqra_user'@'localhost';
  FLUSH PRIVILEGES;
  ```
  > ⚠️ Pas de `DROP` en prod → un attaquant ayant compromis l'app ne peut pas tout effacer.
- [ ] Connections en local seulement (`bind-address = 127.0.0.1` dans `/etc/mysql/mariadb.conf.d/50-server.cnf`)

### 5.6 Laravel — passer en mode prod

Dans `.env` :
```env
APP_ENV=production
APP_DEBUG=false        # critique — sinon stack traces exposées
APP_URL=https://api.iqra.app
LOG_LEVEL=warning
SESSION_DRIVER=redis
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
CACHE_STORE=redis
```

Puis :
```bash
sudo -u iqra php artisan config:cache route:cache view:cache event:cache
```

### 5.7 Comptes de test à **supprimer absolument**

Ces comptes existent en seeding. Les retirer en prod :

```bash
php artisan tinker --execute="
App\Models\User::whereIn('email',[
  'admin@admin.com',
  'exemple@exemple.com',
  'mohamed@mohamed.com',
  'ecole@ecole.com',
  'fff@fff.com',
])->forceDelete();
echo 'Comptes de demo supprimés';
"
```

Puis créer un vrai admin :
```bash
php artisan tinker --execute="
\$u = App\Models\User::create([
  'name' => 'Admin IQRA',
  'email' => 'admin@iqra.app',
  'password' => bcrypt(env('ADMIN_INITIAL_PASSWORD')),
  'role' => 'admin',
]);
echo 'admin créé id='.\$u->id;
"
```

### 5.8 Turnstile en prod

- [ ] Créer une **vraie clé** sur https://dash.cloudflare.com/turnstile (PAS la `1x00000000000000000000AA` de dev)
- [ ] **Hostname Management** : ajouter `iqra.app` (sans port, sans protocole)
- [ ] Mettre la site key dans `VITE_TURNSTILE_SITE_KEY` (frontend) et la secret dans `TURNSTILE_SECRET` (backend)

### 5.9 OAuth en prod

- [ ] Google Cloud Console → ton OAuth client → **Authorized JavaScript origins** : ajouter `https://iqra.app`
- [ ] Idem **Authorized redirect URIs**
- [ ] Meta : passer l'App en **Live Mode** (App Review pour `email` + `public_profile`, auto-approuvés)
- [ ] Meta : **Valid OAuth Redirect URIs** = `https://iqra.app/`

---

## 6. Monitoring & alerting

### 6.1 Logs

| Source | Path | Rotation |
|--------|------|----------|
| Laravel app | `/var/www/iqra/source/ppp/job-backoffice/storage/logs/laravel-*.log` | `LOG_STACK=daily` (auto) |
| Nginx access | `/var/log/nginx/access.log` | logrotate par défaut |
| Nginx error | `/var/log/nginx/error.log` | logrotate par défaut |
| Queue worker | `/var/log/iqra/worker.log` | logrotate à configurer |

Logrotate pour le worker :
```bash
sudo tee /etc/logrotate.d/iqra-worker <<'EOF'
/var/log/iqra/worker.log {
    daily
    rotate 14
    compress
    missingok
    notifempty
    create 0640 iqra iqra
}
EOF
```

### 6.2 Error tracking

Recommandé : **Sentry** (free tier : 5000 events/mois) ou **GlitchTip** (self-hosted gratuit).

Pour Laravel :
```bash
cd ppp/job-backoffice && composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=YOUR_DSN
```

Pour le SPA React, ajouter `@sentry/react` dans `main.tsx` avec le même DSN (project séparé).

### 6.3 Métriques santé

Endpoint `GET /api/health` à ajouter au backend (s'il n'existe pas déjà) :
```php
Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'db' => DB::connection()->getPdo() ? 'up' : 'down',
    'redis' => Redis::ping() ? 'up' : 'down',
    'time' => now()->toIso8601String(),
]));
```

Plug dans **UptimeRobot** (free 50 monitors) ou **BetterStack** pour ping toutes les 1-5 min.

### 6.4 Audit forensique

Déjà en place via `login_audits`. Querys utiles :

```sql
-- 10 dernières tentatives échouées (1h)
SELECT ip, attempted_email, failure_reason, created_at
FROM login_audits
WHERE success = 0 AND created_at > NOW() - INTERVAL 1 HOUR
ORDER BY created_at DESC LIMIT 10;

-- IPs avec > 50 échecs dans la dernière heure (signe de brute-force)
SELECT ip, COUNT(*) as tries
FROM login_audits
WHERE success = 0 AND created_at > NOW() - INTERVAL 1 HOUR
GROUP BY ip HAVING tries > 50
ORDER BY tries DESC;
```

### 6.5 Alertes à configurer

| Alerte | Outil | Seuil |
|--------|-------|-------|
| Site down | UptimeRobot | 2 min |
| Erreurs 5xx en hausse | Sentry | 10/min |
| Disque > 80 % | cron + sendmail | quotidien |
| Backup BDD échoué | cron + sendmail | quotidien |
| Queue trop longue | Horizon/Pulse | 1000 jobs |

---

## 7. Backup BDD

### 7.1 Quotidien (rétention 7 jours)

`/usr/local/bin/iqra-backup.sh` :
```bash
#!/bin/bash
set -e
DATE=$(date +%F)
BACKUP_DIR=/var/backups/iqra
mkdir -p "$BACKUP_DIR"
mariadb-dump --single-transaction --quick --routines --triggers iqra | gzip > "$BACKUP_DIR/iqra-$DATE.sql.gz"

# Rétention 7 jours
find "$BACKUP_DIR" -name "iqra-*.sql.gz" -mtime +7 -delete

# Upload off-site (recommandé)
# rclone copy "$BACKUP_DIR/iqra-$DATE.sql.gz" remote:iqra-backups/
```

```bash
sudo chmod +x /usr/local/bin/iqra-backup.sh
sudo crontab -e
# Ajouter :
0 3 * * * /usr/local/bin/iqra-backup.sh >> /var/log/iqra/backup.log 2>&1
```

### 7.2 Tester la restore régulièrement

**Tous les mois** sur un serveur de staging :
```bash
gunzip < iqra-2026-05-30.sql.gz | mariadb iqra_staging
```
Puis lancer la suite Pest sur staging avec le dump restauré.

> Un backup non testé n'est pas un backup. C'est un fichier potentiellement corrompu.

### 7.3 Off-site

Le backup local protège contre la corruption de fichiers, **pas** contre la destruction du serveur (panne disque, incendie datacenter, suppression accidentelle).

**Provider gratuit recommandé** : Backblaze B2 (10 GB gratuits, $0.006/GB ensuite). Via rclone :
```bash
sudo apt install -y rclone
rclone config       # configurer "b2-iqra"
```

---

## 8. Rollback procedure

### Cas A — rollback applicatif (code seulement)

Si la nouvelle version est cassée mais la BDD n'a pas migré :

```bash
cd /var/www/iqra/source
git fetch && git reset --hard <SHA-précédent>
cd ppp/job-backoffice && composer install --no-dev --optimize-autoloader
cd ../job-app-frontend && npm ci && npm run build
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
```

### Cas B — rollback avec migration

Si la migration a tourné mais doit être annulée :

```bash
cd /var/www/iqra/source/ppp/job-backoffice
php artisan migrate:rollback --step=1
# Puis rollback du code (cas A)
```

> ⚠️ Possible **uniquement si la migration est non-destructive**. Si la nouvelle migration a dropé une colonne, restaurer le dump BDD du backup quotidien.

### Cas C — rollback BDD complet

```bash
sudo systemctl stop nginx php8.3-fpm
mariadb -e "DROP DATABASE iqra; CREATE DATABASE iqra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
gunzip < /var/backups/iqra/iqra-<DATE>.sql.gz | mariadb iqra
# Rollback code (cas A)
sudo systemctl start php8.3-fpm nginx
```

---

## 9. Disaster recovery

Scénarios pris en compte :

| Scénario | RTO | RPO | Action |
|----------|-----|-----|--------|
| Serveur HS | 4h | 24h | Provisionner nouveau VPS, restaurer dernier backup off-site, redéployer |
| BDD corrompue | 1h | 24h | Section 8 cas C |
| Compromission (intrusion) | 24h | 24h | Stopper le service, audit complet `login_audits` + logs Nginx, rotation de **tous** les secrets, restaurer la BDD d'un point antérieur à l'intrusion |
| Quota Gmail dépassé | 0 | 0 | Switcher MAIL_MAILER vers SendGrid (avoir le compte créé d'avance) |
| OpenAI key compromise | 0 | 0 | Révoquer la clé, en créer une nouvelle dans la console, redéployer |

**RTO** = Recovery Time Objective (combien de temps avant retour en ligne)
**RPO** = Recovery Point Objective (combien de données on accepte de perdre)

---

## 10. Vérifications post-déploiement

Après chaque release, dans cet ordre :

### Smoke tests (5 min)
- [ ] `curl -s https://iqra.app | grep IQRA` → 200 OK
- [ ] `curl -s https://api.iqra.app/api/health` → `{"status":"ok"}`
- [ ] Se connecter avec un vrai compte sur https://iqra.app
- [ ] Naviguer vers `/dashboard/jobs` → la liste s'affiche
- [ ] Cliquer Postuler sur une offre → modal s'ouvre

### Vérifs auth
- [ ] **Forgot password** → email arrive bien
- [ ] **Google Sign-In** → popup s'ouvre et login complet
- [ ] **Facebook** (si activé) → idem
- [ ] **Profile → Méthodes de connexion** → s'affiche correctement

### Vérifs métier
- [ ] Page d'accueil affiche les bonnes stats
- [ ] Un candidat peut postuler à une offre
- [ ] Le score IA s'affiche après quelques secondes
- [ ] Un company-owner voit les candidatures à ses offres
- [ ] Backoffice admin (`https://admin.iqra.app`) → liste des Users, Companies, etc.

### Vérifs sécu
- [ ] `curl -I https://iqra.app` → `Strict-Transport-Security` présent
- [ ] `curl https://iqra.app/.env` → 404 (pas leaked)
- [ ] `curl https://api.iqra.app/storage/logs/laravel.log` → 404
- [ ] Logs `/var/www/iqra/source/ppp/job-backoffice/storage/logs/laravel-*.log` ne contiennent pas d'erreurs au démarrage

---

## 11. Releases suivantes (process court)

Pour les releases après la première (qui peut être scriptée plus tard via Deployer ou GitHub Actions) :

```bash
# 1. Vérifs locales
cd /path/to/repo
git pull
./vendor/bin/pest --parallel
npm test:e2e

# 2. Sur le serveur prod
ssh iqra@prod 'cd /var/www/iqra/source && bash deploy.sh'
```

Avec un `deploy.sh` minimal :
```bash
#!/bin/bash
set -e
cd /var/www/iqra/source

# Backup avant migration (paranoïa)
/usr/local/bin/iqra-backup.sh

# Maintenance mode pendant le déploiement
cd ppp/job-backoffice
php artisan down --retry=60 --secret=<some-secret>

# Pull + install + build
git pull
composer install --no-dev --optimize-autoloader
cd ../job-app-frontend && npm ci && npm run build
cd ../job-backoffice

# Migration + cache
php artisan migrate --force
php artisan config:cache route:cache view:cache event:cache

# Restart worker
sudo supervisorctl restart iqra-queue:*

# Up
php artisan up
```

---

## 📞 Contacts d'urgence

À remplir avant le go-live :

| Rôle | Nom | Téléphone | Email |
|------|-----|-----------|-------|
| Dev principal | … | … | … |
| Hébergement (Hetzner) | support | +49 … | support@hetzner.com |
| Registrar domaine | … | … | … |
| Cloudflare | dashboard | — | https://dash.cloudflare.com |

---

## 🎯 Niveau de maturité actuel (avant prod)

| Pilier | État | Action requise avant go-live |
|--------|------|------------------------------|
| Tests automatisés | ✅ 72 tests | RAS |
| Sécurité auth | ✅ Rate-limit + audit + Turnstile | Passer Turnstile en clé prod |
| Backup BDD | ⚠️ Script fourni mais pas en place | Configurer cron + off-site |
| Monitoring | ⚠️ Sentry/UptimeRobot pas configurés | Créer comptes + DSN |
| HTTPS | ⚠️ Certbot à lancer sur le serveur | Section 4.6 |
| Logs centralisés | ⚠️ Logs locaux uniquement | OK pour démarrer, à ajouter au-dessus de 1000 users |
| CI/CD | ❌ Pas de pipeline | Future iteration |
| Doc onboarding | ✅ README + ce checklist | RAS |

---

## 📚 Documents liés

- [README.md](./README.md) — vue d'ensemble monorepo + démarrage local
- [PLAN_AUTH_SOCIAL.md](./PLAN_AUTH_SOCIAL.md) — détail de l'auth et de toutes les phases
- [SETUP_CREDENTIALS.md](./SETUP_CREDENTIALS.md) — pas-à-pas pour obtenir les credentials des providers

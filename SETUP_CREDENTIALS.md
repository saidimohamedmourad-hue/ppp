# Guide de configuration des credentials sociaux

**Projet IQRA** — 3 providers à configurer pour activer Phase 1, 2 et 3.

À la fin de chaque section, tu me renvoies les valeurs marquées **📋 À me donner** et je mets à jour les `.env` + je teste en live.

---

## 1️⃣ Gmail SMTP (Phase 1 — reset password)

**Débloqu​e** : envoi réel d'emails de réinitialisation de mot de passe.

### Étapes

1. **Choisir un compte Gmail**
   - Soit créer un nouveau compte dédié : `noreply.iqra@gmail.com` (plus pro)
   - Soit utiliser ton compte perso : `saidimohamedmourad@gmail.com` (ok pour dev/MVP)

2. **Activer la validation en 2 étapes**
   - https://myaccount.google.com/security
   - Section "Connexion à Google" → **Validation en deux étapes** → l'activer
   - ⚠️ **Obligatoire** sinon Google refuse de générer un App Password

3. **Générer un App Password**
   - https://myaccount.google.com/apppasswords
   - App name : `IQRA Laravel`
   - Clic **Create**
   - Copier les **16 caractères** (format `xxxx xxxx xxxx xxxx`)
   - ⚠️ On ne peut plus le revoir après avoir fermé la fenêtre

### 📋 À me donner
```
Email Gmail : ............................
App Password : xxxx xxxx xxxx xxxx
```

---

## 2️⃣ Google Cloud Console (Phase 2 — Google Sign-In)

**Débloqu​e** : bouton "Continuer avec Google" sur web + mobile.

### Étapes

1. **Créer un projet Google Cloud**
   - https://console.cloud.google.com/projectcreate
   - Project name : `IQRA`
   - Clic **CREATE**, attendre 30 sec

2. **Activer Google Sign-In API** (optionnel, généralement déjà activé)
   - https://console.cloud.google.com/apis/library
   - Chercher "Google+ API" ou "People API" → activer si besoin

3. **Configurer l'écran de consentement OAuth**
   - https://console.cloud.google.com/apis/credentials/consent
   - User Type : **External** (puis CREATE)
   - App name : `IQRA`
   - User support email : ton email
   - Developer contact : ton email
   - Clic **SAVE AND CONTINUE** (laisser Scopes et Test users vides pour l'instant)

4. **Créer les credentials OAuth 2.0**
   - https://console.cloud.google.com/apis/credentials
   - **+ CREATE CREDENTIALS** → **OAuth client ID**
   - Application type : **Web application**
   - Name : `IQRA Web`
   - **Authorized JavaScript origins** :
     - `http://localhost:3000`
     - `http://localhost:5173` (si jamais)
   - **Authorized redirect URIs** :
     - `http://localhost:3000`
   - Clic **CREATE**
   - 📋 Une popup montre le **Client ID** et **Client Secret** — les copier

5. **(Optionnel) OAuth client Android**
   - Si tu veux Google Sign-In sur l'app mobile aussi
   - Type : **Android**
   - Package name : `com.example.job_flutter_app` (le voir dans `android/app/build.gradle`)
   - SHA-1 du keystore debug : `cd android && ./gradlew signingReport`

### 📋 À me donner
```
Google Web Client ID :     xxxxx.apps.googleusercontent.com
Google Web Client Secret : GOCSPX-xxxxxxxxxxxxxxxx
(Optionnel) Android Client ID : xxxxx.apps.googleusercontent.com
```

---

## 3️⃣ Meta for Developers (Phase 3 — Facebook + Instagram)

**Débloqu​e** : bouton "Continuer avec Facebook" sur web + mobile (les users Instagram avec compte FB lié pourront se connecter aussi).

### Étapes

1. **Créer un compte développeur Meta** (si pas déjà fait)
   - https://developers.facebook.com/
   - Se connecter avec ton compte Facebook personnel
   - Accepter les Terms

2. **Créer une App Meta**
   - https://developers.facebook.com/apps/
   - Clic **Create App**
   - Use case : **Authenticate and request data from users with Facebook Login**
   - App type : **Consumer**
   - App name : `IQRA`
   - Email : ton email
   - Clic **Create app** (peut demander mot de passe FB)

3. **Ajouter Facebook Login**
   - Dans le dashboard de l'app → **Add Products** → **Facebook Login** → **Set Up**
   - Choisir **Web**
   - Site URL : `http://localhost:3000`
   - Sauvegarder (peut juste cliquer "Save" même si étape suivante)

4. **Configurer les Valid OAuth Redirect URIs**
   - Sidebar → **Facebook Login** → **Settings**
   - **Valid OAuth Redirect URIs** : `http://localhost:3000/`
   - **Allowed Domains for the JavaScript SDK** : `localhost`
   - **Save Changes**

5. **Récupérer App ID + App Secret**
   - Sidebar → **App Settings** → **Basic**
   - **App ID** : visible en haut, copier
   - **App Secret** : clic **Show**, entrer ton mot de passe FB, copier

6. **Ajouter une politique de confidentialité (requis pour Live mode)**
   - **Privacy Policy URL** : tu peux mettre `http://localhost:3000/privacy` pour l'instant
   - **Save Changes**
   - Pas obligatoire en Development Mode mais sinon impossible de passer Live

7. **(Important) Rester en Development Mode pour tester**
   - En haut à droite : **App Mode = Development**
   - Seuls les "App Roles" (toi + amis ajoutés) peuvent se connecter
   - Pour ouvrir au public → passer en Live (demande review pour `email` + `public_profile` mais auto-approuvés)

### 📋 À me donner
```
Facebook App ID :     xxxxxxxxxxxxxxxx
Facebook App Secret : xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

## ✅ Checklist finale

Une fois que tu as les valeurs, reviens-moi avec un message du genre :

```
Voici les credentials :

Gmail : noreply.iqra@gmail.com / xxxx xxxx xxxx xxxx
Google : xxxxx.apps.googleusercontent.com / GOCSPX-xxxxxxxxxxxxx
Facebook : 1234567890 / abcdef1234567890abcdef
```

Je vais :
1. Mettre à jour `.env` du backend + frontend
2. Vider le cache Laravel (`php artisan config:clear`)
3. Recompiler le frontend si nécessaire
4. Tester chaque flow en live dans Edge :
   - Reset password : envoyer un mail réel et vérifier qu'il arrive
   - Google : cliquer le bouton et vérifier qu'on est loggué
   - Facebook : pareil
5. Te donner les screenshots de confirmation

---

## 🔒 Sécurité — ne pas commit les credentials

Les fichiers `.env` sont déjà dans `.gitignore`. Une fois renseignés ils restent en local. **Ne JAMAIS les coller dans le code source ni dans un commit**.

Pour la production, on utilisera des secrets stockés sur le serveur (variables d'environnement du serveur, ou un service type Vault/AWS Secrets Manager).

---

## ⏱️ Estimation totale

- Gmail SMTP : **5 min**
- Google Cloud : **10 min**
- Meta : **10-15 min** (création du compte dev FB peut être long)

**Total : ~30 minutes** si tu n'as jamais utilisé ces consoles.

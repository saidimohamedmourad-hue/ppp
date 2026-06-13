# Plan : Inscription sociale + récupération compte

**Projet IQRA** — Web (React) + Mobile (Flutter) + Backend (Laravel)
**Date plan initial** : 2026-05-28
**Dernière màj** : 2026-06-13
**Status global** : 🟢 5 phases sur 6 livrées (Phase 4 reste en backlog)

---

## ✅ État au 2026-05-30

| Phase | Code | Tests auto | En live |
|-------|------|------------|---------|
| **1 — Reset password email** | ✅ Backend + Web + Flutter | ✅ 9 Pest + 1 E2E + 1 Flutter | ✅ Gmail SMTP `587 TLS` (`mohamedsaidi17100@gmail.com`) |
| **2 — Google Sign-In** | ✅ Backend + Web + Flutter | ✅ 6 Pest + 1 Flutter | ✅ OAuth Client ID `163989191004-…` |
| **3 — Meta Login (FB + Instagram)** | ✅ Backend + Web + Flutter | ✅ 5 Pest | ⏳ Attente validation Meta App Review |
| **4 — Phone OTP Firebase** | ❌ Pas commencé | — | — |
| **5 — UI "Comptes liés" profil** | ✅ Backend + Web + Flutter | ✅ 9 Pest + 1 E2E | ✅ Visible sur `/dashboard/profile` |
| **6 — Hardening + tests** | ✅ Rate-limit + audit + Turnstile | ✅ 9 Pest + 13 backoffice + 8 E2E | ✅ Turnstile actif (clés test en dev) |

**Bonus livré hors plan** :
- 📞 **Téléphone obligatoire** : sur les comptes owner à l'inscription, sur les candidats à la première candidature (voir `§ Bonus — Téléphone obligatoire`)
- 🔐 **Audit forensique** : table `login_audits` qui trace chaque login/échec/reset avec IP + user-agent + raison
- 🚨 **Rate limiting** : `/login` (5/IP/min + 5/email/min), `/forgot-password` (10/IP/h + 3/email/h)
- 🤖 **Turnstile** : CAPTCHA Cloudflare invisible sur `/forgot-password`

**Mise à jour 2026-06-13** :
- 🌐 **Inscription sociale tous profils** : les boutons **Google / Facebook**
  sont désormais proposés à l'inscription pour **candidat, entreprise ET école**,
  sur le **web (React)** comme sur **Flutter** — le **rôle choisi** est transmis
  au backend et appliqué à la création du compte social (whitelist
  `job-seeker | company-owner | school-owner`).
- 🖥️ **Connexion Google sur Flutter Web fiabilisée** : lancement stable sur le
  port fixe **8090** (`run_web.bat` libère le port puis sert l'app en
  `web-server`) ; reste l'autorisation des origines `localhost:8090` /
  `127.0.0.1:8090` dans Google Cloud pour le test live.

---

## 1. Vue d'ensemble

Ajouter aux mécanismes d'authentification actuels (email/mot de passe) :

| Méthode | Inscription | Connexion | Récupération |
|---------|-------------|-----------|--------------|
| Email + mot de passe | ✅ Déjà fait | ✅ Déjà fait | ✅ Phase 1 — fait |
| Google (Gmail) | ✅ Phase 2 — fait | ✅ Phase 2 — fait | ✅ Reconnexion = récup |
| Meta (Facebook + Instagram) | ✅ Phase 3 — fait, attente review | ✅ Phase 3 | — |
| Téléphone (OTP SMS) | ❌ Phase 4 — backlog | ❌ | — |

**Décisions arrêtées** :
- **Instagram** : passe par Facebook Login (Meta Graph API) — la seule voie officielle depuis la dépréciation de Instagram Basic Display.
- **OTP SMS** : Firebase Phone Auth (gratuit jusqu'à 10k vérifs/mois).
- **Récupération** : double mécanisme — email de reset standard ET reconnexion via Google si compte lié.

---

## 2. Architecture : compte unique, providers multiples

Un même `User` IQRA peut être lié à plusieurs méthodes d'auth. Schéma :

```
users (existant)
  ├── id, name, email, password (nullable maintenant), role, phone (nouveau)
  │
  └─< auth_providers (nouvelle table)
        ├── user_id
        ├── provider (google | facebook | phone)
        ├── provider_user_id (sub Google, id Facebook, ou téléphone E.164)
        └── meta (json: email vérifié, photo, etc.)
```

**Règle de matching à la connexion sociale** :
1. Si le provider_user_id existe déjà → on connecte.
2. Sinon, si l'email retourné par le provider matche un `users.email` existant → on lie automatiquement (et marque le compte vérifié).
3. Sinon → on crée un nouveau compte, role par défaut = `job-seeker`.

---

## 3. Choix techniques

### Backend Laravel

| Besoin | Package | Pourquoi |
|--------|---------|----------|
| OAuth Google/Facebook | `laravel/socialite` 5.x | Standard Laravel, déjà éprouvé |
| Firebase Auth (vérif côté serveur des tokens) | `kreait/laravel-firebase` | Vérifie les ID tokens Firebase signés |
| Email reset | `Illuminate\Auth\Passwords` (built-in) | Déjà dans Laravel |
| SMTP outbound | Gmail SMTP ou Mailgun (dev: Mailtrap) | Gratuit jusqu'à 500 emails/jour Gmail |

### Web React

| Besoin | Package | Notes |
|--------|---------|-------|
| Google Sign-In | `@react-oauth/google` | One-tap + bouton |
| Facebook Login | `@greatsumini/react-facebook-login` | Simple, maintenu |
| Firebase Phone Auth | `firebase` SDK web | reCAPTCHA invisible inclus |

### Mobile Flutter

| Besoin | Package | Version cible |
|--------|---------|---------------|
| Google Sign-In | `google_sign_in` | ^6.2.x |
| Facebook Login | `flutter_facebook_auth` | ^7.x |
| Firebase Phone Auth | `firebase_auth` + `firebase_core` | latest |

---

## 4. Schéma BDD

### Migration 1 : étendre `users`

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('password')->nullable()->change();  // pour comptes social-only
    $table->string('phone', 32)->nullable()->after('email');
    $table->string('phone_country', 4)->nullable()->after('phone');  // 'DZ', 'FR'...
    $table->timestamp('phone_verified_at')->nullable()->after('phone_country');
    $table->string('avatar_url')->nullable()->after('phone_verified_at');
    $table->unique(['phone', 'phone_country']);
});
```

### Migration 2 : table `auth_providers`

```php
Schema::create('auth_providers', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->string('provider', 32);  // google | facebook | phone
    $table->string('provider_user_id');
    $table->json('meta')->nullable();
    $table->timestamps();
    $table->unique(['provider', 'provider_user_id']);
    $table->index('user_id');
});
```

### Migration 3 : utiliser le password_reset_tokens natif

```bash
php artisan make:notification ResetPasswordNotification
```
Déjà fournie par Laravel via `php artisan make:auth` historique. À configurer pour utiliser un mailer + template fr.

---

## 5. Backend Laravel — étapes détaillées

### 5.1 Setup packages

```bash
composer require laravel/socialite kreait/laravel-firebase
php artisan vendor:publish --provider="Laravel\Socialite\SocialiteServiceProvider"
```

### 5.2 Configuration `config/services.php`

```php
'google' => [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect'      => env('GOOGLE_REDIRECT_URI', '/api/auth/google/callback'),
],
'facebook' => [
    'client_id'     => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect'      => env('FACEBOOK_REDIRECT_URI', '/api/auth/facebook/callback'),
],
'firebase' => [
    'credentials' => env('FIREBASE_CREDENTIALS', base_path('storage/firebase-credentials.json')),
],
```

### 5.3 Routes API (`routes/api.php`)

```php
Route::prefix('auth')->group(function () {
    // OAuth (Web flow uses ID token; Mobile flow uses native SDK + send token)
    Route::post('/google',   [SocialAuthController::class, 'google']);
    Route::post('/facebook', [SocialAuthController::class, 'facebook']);

    // Phone OTP via Firebase
    Route::post('/phone/verify', [PhoneAuthController::class, 'verify']);

    // Password reset (email)
    Route::post('/forgot-password',  [PasswordResetController::class, 'sendLink']);
    Route::post('/reset-password',   [PasswordResetController::class, 'reset']);
});
```

### 5.4 Controllers (squelettes)

**`app/Http/Controllers/Api/SocialAuthController.php`**
- POST `/auth/google` : reçoit `{ id_token: string }`
  - Vérifie via `Socialite::driver('google')->stateless()->userFromToken($idToken)`
  - Cherche/lie/crée user via service `findOrCreateFromSocial`
  - Retourne `{ token, user }` (Sanctum)
- Idem pour Facebook

**`app/Http/Controllers/Api/PhoneAuthController.php`**
- POST `/auth/phone/verify` : reçoit `{ firebase_id_token: string }`
  - Décode via `Kreait\Firebase\Auth::verifyIdToken($idToken)`
  - Extrait `phone_number`, cherche/crée user
  - Retourne `{ token, user }` Sanctum

**`app/Http/Controllers/Api/PasswordResetController.php`**
- POST `/auth/forgot-password` : envoie email avec lien `/reset-password?token=...`
- POST `/auth/reset-password` : valide token + nouveau mot de passe

### 5.5 Service `AuthLinkingService`

```php
class AuthLinkingService {
    public function findOrCreateFromSocial(
        string $provider,
        string $providerUserId,
        ?string $email,
        ?string $name,
        ?string $avatar,
        array $meta = []
    ): User {
        // 1. Provider existant ?
        $existing = AuthProvider::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)->first();
        if ($existing) return $existing->user;

        // 2. Email matche un user existant ?
        if ($email && $user = User::where('email', $email)->first()) {
            $user->authProviders()->create([
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'meta' => $meta,
            ]);
            return $user;
        }

        // 3. Nouveau compte
        return DB::transaction(function () use ($provider, $providerUserId, $email, $name, $avatar, $meta) {
            $user = User::create([
                'name'  => $name ?? 'Utilisateur',
                'email' => $email,
                'role'  => 'job-seeker',
                'avatar_url' => $avatar,
                'email_verified_at' => now(),  // social = vérifié
            ]);
            $user->authProviders()->create([
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'meta' => $meta,
            ]);
            return $user;
        });
    }
}
```

---

## 6. Web React — implémentation

### 6.1 Composant `<SocialAuthButtons />` (login + register)

```tsx
// src/components/auth/SocialAuthButtons.tsx
import { GoogleLogin } from '@react-oauth/google'
import FacebookLogin from '@greatsumini/react-facebook-login'

export function SocialAuthButtons({ onSuccess }: { onSuccess: (token: string) => void }) {
  return (
    <div className="flex flex-col gap-3">
      <GoogleLogin
        onSuccess={async cred => {
          const r = await apiFetch('auth/google', {
            method: 'POST',
            body: { id_token: cred.credential },
          })
          onSuccess(r.token)
        }}
      />
      <FacebookLogin
        appId={import.meta.env.VITE_FACEBOOK_APP_ID}
        onSuccess={async resp => {
          const r = await apiFetch('auth/facebook', {
            method: 'POST',
            body: { access_token: resp.accessToken },
          })
          onSuccess(r.token)
        }}
      />
      <PhoneLoginButton onSuccess={onSuccess} />
    </div>
  )
}
```

### 6.2 Composant `<PhoneLoginButton />` (Firebase Phone Auth web)

```tsx
import { getAuth, RecaptchaVerifier, signInWithPhoneNumber } from 'firebase/auth'
import { firebaseApp } from '@/lib/firebase'

const [step, setStep] = useState<'phone' | 'otp'>('phone')
const [phone, setPhone] = useState('+213')
const [otp, setOtp] = useState('')
const confirmationRef = useRef<ConfirmationResult | null>(null)

async function sendOtp() {
  const auth = getAuth(firebaseApp)
  const verifier = new RecaptchaVerifier(auth, 'recaptcha-container', { size: 'invisible' })
  confirmationRef.current = await signInWithPhoneNumber(auth, phone, verifier)
  setStep('otp')
}
async function verifyOtp() {
  const cred = await confirmationRef.current!.confirm(otp)
  const idToken = await cred.user.getIdToken()
  const r = await apiFetch('auth/phone/verify', { method: 'POST', body: { firebase_id_token: idToken } })
  onSuccess(r.token)
}
```

### 6.3 Pages à modifier
- `src/pages/Login.tsx` — ajouter `<SocialAuthButtons />` sous le formulaire email
- `src/pages/Register.tsx` — idem + ajouter route `phone-signup` (étape 1 téléphone, étape 2 OTP + role)
- `src/pages/ForgotPassword.tsx` (**nouvelle**) — input email + bouton "Envoyer le lien"
- `src/pages/ResetPassword.tsx` (**nouvelle**) — token depuis URL + nouveau mot de passe

### 6.4 Provider setup `main.tsx`
```tsx
<GoogleOAuthProvider clientId={import.meta.env.VITE_GOOGLE_CLIENT_ID}>
  <App />
</GoogleOAuthProvider>
```

---

## 7. Mobile Flutter — implémentation

### 7.1 `pubspec.yaml`

```yaml
dependencies:
  firebase_core: ^3.6.0
  firebase_auth: ^5.3.1
  google_sign_in: ^6.2.1
  flutter_facebook_auth: ^7.1.1
```

### 7.2 `lib/main.dart`

```dart
import 'package:firebase_core/firebase_core.dart';
import 'firebase_options.dart';   // généré par flutterfire_cli

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp(options: DefaultFirebaseOptions.currentPlatform);
  runApp(const ProviderScope(child: JobApp()));
}
```

### 7.3 Service `lib/data/repositories/social_auth_repository.dart`

```dart
class SocialAuthRepository {
  final _client = ApiClient();

  Future<({String token, UserModel user})> signInWithGoogle() async {
    final google = GoogleSignIn(scopes: ['email', 'profile']);
    final account = await google.signIn();
    if (account == null) throw Exception('Annulé');
    final auth = await account.authentication;
    final res = await _client.dio.post('/auth/google', data: {'id_token': auth.idToken});
    return _parse(res.data);
  }

  Future<({String token, UserModel user})> signInWithFacebook() async {
    final r = await FacebookAuth.instance.login(permissions: ['email', 'public_profile']);
    if (r.status != LoginStatus.success) throw Exception('Annulé');
    final res = await _client.dio.post('/auth/facebook', data: {'access_token': r.accessToken!.tokenString});
    return _parse(res.data);
  }

  Future<({String token, UserModel user})> signInWithPhoneVerified(String firebaseIdToken) async {
    final res = await _client.dio.post('/auth/phone/verify', data: {'firebase_id_token': firebaseIdToken});
    return _parse(res.data);
  }
}
```

### 7.4 UI : `phone_login_screen.dart`

Deux étapes :
1. Saisie numéro → `FirebaseAuth.instance.verifyPhoneNumber(...)`
2. Saisie OTP → `PhoneAuthProvider.credential(...)` → `signInWithCredential` → récup `idToken` → POST `/auth/phone/verify`

### 7.5 Boutons sur `login_screen.dart` / `register_screen.dart`

Ajouter sous le bouton "Se connecter" :
- `_GoogleButton(onTap: () => repo.signInWithGoogle())`
- `_FacebookButton(...)`
- `_PhoneButton(onTap: () => context.push('/phone-login'))`

### 7.6 Configuration plateforme
- **Android** : `google-services.json` dans `android/app/`, SHA-1 + SHA-256 dans Firebase + Google Cloud, scheme deep link `fb<APP_ID>` pour Facebook.
- **iOS** : `GoogleService-Info.plist`, URL Schemes pour Google + Facebook dans `Info.plist`.
- **Web** : configurer domaines autorisés dans Firebase + Google Cloud.

---

## 8. Configuration cloud

### 8.1 Google Cloud Console
1. Créer projet "IQRA"
2. APIs & Services → OAuth consent screen → External, scopes `email`, `profile`, `openid`
3. Credentials → Créer un OAuth 2.0 Client ID :
   - Type **Web** → ajouter origins `http://localhost:3000`, `https://iqra.app`
   - Type **Android** → SHA-1 du keystore debug + release
   - Type **iOS** → bundle ID
4. Copier `client_id` et `client_secret` → `.env` Laravel
5. Web client ID → `.env` React + Flutter

### 8.2 Meta for Developers
1. Créer une "App" → type **Consumer**
2. Ajouter produit **Facebook Login** → Quickstart Web/Android/iOS
3. Settings → Basic → App ID + App Secret → `.env` Laravel
4. Settings → Advanced → "Allow API Access to App Settings" pour Instagram graph (si besoin lecture profil Insta lié)
5. App Review : demander `public_profile` + `email` (auto) ; pour aller plus loin (insta_basic) → review formel

### 8.3 Firebase
1. Créer projet "iqra-prod"
2. Authentication → Sign-in method → activer **Phone**
3. Authentication → Settings → Authorized domains : ajouter `localhost`, `iqra.app`
4. Pour SMS test : ajouter numéros de test (gratuit, code fixe)
5. Project settings → Service accounts → Generate new private key → `storage/firebase-credentials.json`

### 8.4 SMTP pour reset password
**Option A — Gmail SMTP (gratuit ≤ 500/jour)**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply.iqra@gmail.com
MAIL_PASSWORD=<app-password 16 chars>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply.iqra@gmail.com
MAIL_FROM_NAME=IQRA
```
Important : créer un **App Password** dans Google Account → Security (besoin 2FA activé).

**Option B — Mailgun / SendGrid** (recommandé prod, $0 jusqu'à 100/jour, meilleure délivrabilité).

---

## 9. Roadmap & estimation

| Phase | Tâches | Effort (jours-dev) |
|-------|--------|--------------------|
| **Phase 1 — Reset password email** | Migrations, controllers, vues mail, pages React `/forgot`, `/reset` | 1.5 j |
| **Phase 2 — Google Sign-In** | Socialite + service linking, bouton web React, bouton Flutter | 2 j |
| **Phase 3 — Facebook Login** | Idem Google côté backend + frontend | 1.5 j |
| **Phase 4 — Phone OTP** | Firebase setup, vérification backend, écrans web + Flutter | 3 j |
| **Phase 5 — Liaisons multi-providers** | UI "comptes liés" dans profil, fusion comptes, déliaison | 1.5 j |
| **Phase 6 — Hardening & tests** | Rate limiting, captcha forgot, tests d'intégration | 1.5 j |
| **TOTAL** | | **~11 jours-dev** |

**Ordre recommandé** : 1 → 2 → 4 → 3 → 5 → 6. (Reset password est urgent, Google est le plus utilisé, Phone débloque les users sans email, Facebook arrive après car Meta App Review prend du temps.)

---

## 10. Sécurité & UX

### Sécurité
- **Toujours vérifier les tokens côté backend** (jamais faire confiance à un user_id envoyé par le client).
- **Rate limiting** sur `/auth/forgot-password` (10 req / IP / heure) pour éviter spam et enumeration emails.
- **Tokens reset** : durée 60 min, single-use, hash en DB.
- **OTP** : limite 5 tentatives, expiration 5 min, cooldown 60s entre envois.
- **Logs** : tracer toute connexion sociale avec provider + IP (table `login_audit`).
- **PII** : ne pas stocker plus que nécessaire (pas la liste des amis FB, pas le contenu Insta, etc.).
- **Captcha** : reCAPTCHA v3 invisible sur `/forgot-password` et `/phone/send-otp`.

### UX
- **Détection compte existant** : si user essaie OAuth Google avec un email déjà utilisé en local, message clair *"Ce compte utilise déjà un mot de passe. Connectez-vous, puis liez Google dans votre profil."*  → ou auto-lier si email vérifié (= choix actuel dans le service).
- **Page profil → section "Comptes liés"** : afficher providers liés avec bouton "Délier" (sauf si c'est le seul moyen de connexion).
- **Onboarding role** : après signup social, si le user n'a pas de role, demander candidat / entreprise / école.

### Coûts estimés (mensuel, MVP)
- Google OAuth : **gratuit**
- Facebook Login : **gratuit**
- Firebase Phone : gratuit jusqu'à 10k vérifs/mois, puis ~$0.06 / SMS au-delà (DZ)
- Gmail SMTP : gratuit ≤ 500 emails/jour
- **Estimation pour 5k users actifs** : **0 €/mois** au démarrage.

---

## 11. Variables d'environnement à ajouter

**`job-backoffice/.env`**
```env
# Google OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=

# Facebook
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=

# Firebase Admin (vérif tokens téléphone)
FIREBASE_CREDENTIALS=storage/firebase-credentials.json

# SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply.iqra@gmail.com
MAIL_FROM_NAME=IQRA

# URL reset link (web)
APP_FRONTEND_URL=http://localhost:3000
```

**`job-app-frontend/.env`**
```env
VITE_GOOGLE_CLIENT_ID=
VITE_FACEBOOK_APP_ID=
VITE_FIREBASE_API_KEY=
VITE_FIREBASE_AUTH_DOMAIN=
VITE_FIREBASE_PROJECT_ID=
VITE_FIREBASE_APP_ID=
```

**`flutter_app`** → `firebase_options.dart` généré par `flutterfire configure`.

---

## 12. Checklist pré-implémentation

- [ ] Compte Google Cloud créé + facturation liée (gratuit mais requis)
- [ ] App Meta créée + en mode Live (pas dev)
- [ ] Projet Firebase créé
- [ ] Compte Gmail dédié `noreply.iqra@gmail.com` + 2FA + App Password
- [ ] Domaine de production prêt (pour URL de redirection OAuth en prod)
- [ ] SSL en prod (OAuth Google refuse les redirections HTTP en prod)

---

## 13. Risques & mitigation

| Risque | Probabilité | Mitigation |
|--------|-------------|------------|
| Meta App Review rejette `email` scope | Faible | Lire la doc, soumettre demo video |
| Coût Firebase Phone si fraude SMS | Moyen | reCAPTCHA + rate limit + budget alert GCP |
| Délivrabilité Gmail SMTP | Moyen | Passer Mailgun si > 100 emails/jour |
| Conflits email entre comptes social | Élevé | Service de linking + UX claire |
| Numéros DZ + Firebase | Faible | Tester avec vrais numéros +213 6/7/9 |

---

## Notes finales

Ce plan est conçu pour être implémenté **par phases incrémentales**. Chaque phase est livrable indépendamment et utilisable en production. Ne pas tout faire d'un coup — commencer par **Phase 1 (reset password)** car c'est le pain point le plus immédiat des utilisateurs actuels.

---

# 🎁 Bonus livrés hors plan (2026-05-29 → 30)

Au cours de l'implémentation, on a ajouté plusieurs garde-fous et features qui n'étaient pas dans le plan initial.

## A. Téléphone obligatoire (contact)

**Pourquoi** : les recruteurs et écoles ont besoin d'un canal de contact direct. L'email seul ne suffit pas (taux d'ouverture, latence).

**Règles** :
| Acteur | Quand est-il requis ? |
|--------|----------------------|
| Candidat | À la **première candidature** (job ou formation) |
| Entreprise (company-owner) | À **l'inscription** |
| École (school-owner) | À **l'inscription** |

**Implémentation** :
- Migration `2026_05_30_140000_add_phone_to_users_companies_schools.php` — colonnes `phone` (32 chars, nullable côté BDD, enforcement côté validation)
- Validation Laravel : `required_unless:role,job-seeker` au register, `required` au premier apply si pas déjà sur profil
- UI : champ téléphone avec helper-text contextuel sur Register, ApplyModal, JobApplicants, SessionApplicants, Profile
- Affichage : bloc 📞 cliquable (`tel:`) sur JobDetail / TrainingDetail / fiches candidats

**Format accepté** : freeform `[0-9+\-\s()]+` (6-32 chars). Pas de validation stricte du pays parce que IQRA accepte des candidats de la diaspora.

## B. Audit forensique (`login_audits`)

**Pourquoi** : sans trace, impossible d'enquêter après un incident (piratage, brute-force, plainte RGPD).

**Champs** : `user_id`, `provider` (`password`/`google`/`facebook`/…), `event` (`login`/`logout`/`refused`/`link`/`unlink`/`reset`), `success`, `ip`, `user_agent`, `attempted_email`, `failure_reason`, `created_at`.

**Hooks** : tous les controllers d'auth (login, refresh, reset, social, linked accounts) appellent `LoginAuditService` après chaque action.

**Index** : `(user_id, created_at)` pour requête historique par user. `(ip, created_at)` pour détection par IP.

**Rétention** : pas de purge auto pour l'instant. Si la table dépasse 1M lignes, ajouter `php artisan audit:prune --days=365`.

## C. Rate limiting anti brute-force

| Endpoint | Limite IP | Limite email |
|----------|-----------|--------------|
| `POST /login` | 5/min | 5/min (anti rotation IP) |
| `POST /forgot-password` | 10/h | 3/h |
| `POST /reset-password` | 10/h | — |
| `POST /auth/google` + `/auth/facebook` | 30/min | — |

Implémenté via `RateLimiter::for(...)` dans `AppServiceProvider`. Tous les hits 429 sont auditées dans `login_audits` avec `failure_reason='rate_limited'`.

## D. CAPTCHA Turnstile (Cloudflare) sur `/forgot-password`

**Pourquoi** : empêcher l'énumération massive d'emails et l'envoi de centaines de mails de reset par minute (gratuit pour l'attaquant, coûteux pour notre quota Gmail SMTP).

**Pourquoi Turnstile et pas reCAPTCHA** :
- Pas de tracking utilisateur (Cloudflare contrairement à Google)
- Aucun défi visuel dans 95 % des cas (score invisible)
- Gratuit illimité

**Implémentation** :
- Frontend : composant `<TurnstileWidget>` lazy-load le SDK + monte le widget conditionnellement (`/api/config` retourne `enabled: true|false`)
- Backend : `TurnstileService` valide le token via `/turnstile/v0/siteverify` avant d'envoyer le mail
- Test : phpunit.xml force `TURNSTILE_SITE_KEY=""` pour désactiver le check dans la suite Pest

**En dev** : on utilise les **clés de test Cloudflare** qui passent toujours :
```env
TURNSTILE_SITE_KEY=1x00000000000000000000AA
TURNSTILE_SECRET=1x0000000000000000000000000000000AA
```
Pour la prod, créer une vraie clé sur https://dash.cloudflare.com/turnstile et ajouter le domaine de prod dans **Hostname Management** (sinon le widget refuse de rendre l'iframe).

---

# 📊 Décompte tests (au 2026-05-30)

| Stack | Tests | Suite |
|-------|-------|-------|
| Backend Pest | **59** | Api/Auth, Api/Profile, Services, Backoffice |
| Web E2E Playwright | **8** | Public pages + forgot/reset + dashboard auth gate |
| Flutter | **5** | AuthRepository unit tests |
| **Total** | **72** | |

Lancer la suite complète :
```bash
# Backend (~15s)
cd ppp/job-backoffice && ./vendor/bin/pest

# Web E2E (~1min, nécessite Laravel+Vite up)
cd ppp/job-app-frontend && npm run test:e2e

# Flutter unit (~30s)
cd flutter_app && flutter test
```

⚠️ **Pour E2E rapide** : passer `MAIL_MAILER=log` dans `.env` backend (Gmail SMTP ajoute ~10s/test).

---

# 🔧 Variables d'environnement actives

## Backend (`ppp/job-backoffice/.env`)
```env
# Phase 1 — SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587            # 465 SSL aussi possible si 587 timeout
MAIL_USERNAME=mohamedsaidi17100@gmail.com
MAIL_PASSWORD=<app-password 16 chars sans espaces>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="mohamedsaidi17100@gmail.com"
MAIL_FROM_NAME="IQRA"
APP_FRONTEND_URL=http://localhost:3000

# Phase 2 — Google
GOOGLE_WEB_CLIENT_ID=163989191004-29t8tbl6al8splf9c4rmk1k36uacfn89.apps.googleusercontent.com

# Phase 3 — Facebook (en attente review)
# FACEBOOK_CLIENT_ID=
# FACEBOOK_CLIENT_SECRET=

# Phase 6 — Turnstile (clés test pour dev)
TURNSTILE_SITE_KEY=1x00000000000000000000AA
TURNSTILE_SECRET=1x0000000000000000000000000000000AA
```

## Web (`ppp/job-app-frontend/.env`)
```env
VITE_GOOGLE_WEB_CLIENT_ID=163989191004-29t8tbl6al8splf9c4rmk1k36uacfn89.apps.googleusercontent.com
# VITE_FACEBOOK_APP_ID=
VITE_TURNSTILE_SITE_KEY=1x00000000000000000000AA
```

## Flutter (`flutter_app/web/index.html`)
```html
<meta name="google-signin-client_id" content="163989191004-…apps.googleusercontent.com">
```

---

# 📌 Prochaines étapes possibles

1. **Phase 3 — finir Meta** : dès que l'App Review est validée, mettre `FACEBOOK_CLIENT_ID` + `FACEBOOK_CLIENT_SECRET` et tester live
2. **Phase 4 — Phone OTP Firebase** (~3 j, voir plan original section 7)
3. **Turnstile prod** : créer une vraie clé Cloudflare + ajouter `iqra.app` dans Hostname Management
4. **CI/CD GitHub Actions** : workflow qui run les 72 tests à chaque push (~1h setup)
5. **`audit:prune` command** : éviter que `login_audits` enfle (1M lignes / an au rythme actuel)

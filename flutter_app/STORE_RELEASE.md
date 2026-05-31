# Publication Play Store & App Store — Job & Formation

## Prérequis communs

1. **API en production** : Laravel accessible en **HTTPS** (ex. `https://api.votredomaine.com/api`).
2. **Comptes** : [Google Play Console](https://play.google.com/console) (~25 $), [Apple Developer](https://developer.apple.com) (~99 $/an).
3. **Politique de confidentialité** : URL publique (obligatoire sur les deux stores).
4. Remplacer `API_BASE_URL` par votre vraie URL à chaque build release.

---

## Android (Google Play)

### 1. Keystore (une fois)

```powershell
cd flutter_app
.\scripts\create_android_keystore.ps1
copy android\key.properties.example android\key.properties
# Éditer key.properties : mots de passe + storeFile=upload-keystore.jks
```

Ne commitez **jamais** `key.properties` ni `*.jks`.

### 2. Build release (.aab)

```powershell
cd flutter_app
flutter pub get
flutter build appbundle --release `
  --dart-define=API_BASE_URL=https://api.VOTRE_DOMAINE.com/api
```

Fichier produit : `build/app/outputs/bundle/release/app-release.aab`

### 3. Play Console

- Créer l’application (ID : `com.jobapp.job_flutter_app`).
- Téléverser l’AAB, fiche store, captures, classification, politique de confidentialité.

---

## iOS (App Store)

> Nécessite un **Mac** avec Xcode et un compte Apple Developer.

### 1. Xcode

1. Ouvrir `ios/Runner.xcworkspace` dans Xcode.
2. Cible **Runner** → **Signing & Capabilities** : équipe Apple, certificat **Distribution**.
3. Bundle ID : `com.jobapp.job_flutter_app` (aligné avec Android).

### 2. Build

```bash
cd flutter_app
flutter pub get
flutter build ipa --release \
  --dart-define=API_BASE_URL=https://api.VOTRE_DOMAINE.com/api
```

Ou : **Product → Archive** dans Xcode, puis **Distribute App** → App Store Connect.

### 3. App Store Connect

- Créer l’app avec le même bundle ID.
- Métadonnées, captures, politique de confidentialité, questionnaire contenu.

---

## Développement local (téléphone)

```powershell
# Backend
cd ppp\job-backoffice
php artisan serve --host=0.0.0.0 --port=8000

# App (debug, HTTP autorisé uniquement en debug Android)
cd flutter_app
flutter run
# IP dev par défaut dans api_config ; autre IP :
# flutter run --dart-define=API_BASE_URL_DEV=http://192.168.x.x:8000/api
```

---

## Versions

Dans `pubspec.yaml` : `version: 1.0.0+1`  
- `1.0.0` = version affichée (versionName / CFBundleShortVersionString)  
- `+1` = numéro de build (versionCode / CFBundleVersion)  

Incrémenter `+1` à chaque soumission store.

---

## Checklist avant soumission

- [ ] `API_BASE_URL` en HTTPS (pas d’IP locale)
- [ ] `android/key.properties` + keystore configurés
- [ ] Tests login, offres, candidature CV, formations
- [ ] Politique de confidentialité en ligne
- [ ] Nom affiché : **Job & Formation**
- [ ] Captures d’écran store

# 📱 Plan Technique - Application Flutter Job & Formation

## Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Stack Technologique](#stack-technologique)
3. [Architecture de l'Application](#architecture-de-lapplication)
4. [Structure du Projet](#structure-du-projet)
5. [Modèle de Données](#modèle-de-données)
6. [Fonctionnalités Détaillées](#fonctionnalités-détaillées)
7. [Plan d'Implémentation](#plan-dimplémentation)
8. [Sécurité & Authentification](#sécurité--authentification)

---

## Vue d'Ensemble

### Objectifs
- Application mobile multiplateforme (iOS & Android)
- Plateforme de gestion d'annonces d'emploi et de formation
- Trois types d'utilisateurs: Company/École, Candidat, Admin
- Interface intuitive et performante

### Cibles
- Entreprises et écoles postant des annonces
- Candidats cherchant emplois/formations
- Administrateurs gérant la plateforme

---

## Stack Technologique

### Frontend Mobile
```
Flutter
├── Version: 3.x+
├── Language: Dart 3.0+
└── SDK: Flutter SDK
```

### Backend
```
Node.js + Express.js
├── Version: 18.x+
├── Runtime: Node.js LTS
└── Framework: Express.js 4.x
```

### Base de Données
```
Primary: PostgreSQL 14+
├── Relationnel principal
├── Optimisé pour requêtes complexes
└── Support JSON natif

Cache: Redis
├── Cache sessions
├── Real-time notifications
└── Gestion des rate limits
```

### Authentification & Sécurité
```
JWT (JSON Web Tokens)
├── Access Token (15 min)
├── Refresh Token (7 jours)
└── Bearer scheme

OAuth 2.0 (optionnel)
├── Google Sign-In
└── Email/Password
```

### Services Externes
```
Cloud Storage:
├── Firebase Storage OU AWS S3
└── Pour: Images profil, CV, logos

Notifications:
├── Firebase Cloud Messaging (FCM)
└── Notifications push

Email:
├── SendGrid OU Nodemailer
└── Confirmations & alertes

Hosting:
├── Frontend: Firebase Hosting OU Heroku
├── Backend: Heroku OU AWS EC2
└── Base de données: Heroku PostgreSQL OU AWS RDS
```

### Dépendances Principales (pubspec.yaml)

#### Navigation & State Management
```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # State Management
  provider: ^6.0.0
  riverpod: ^2.3.0
  
  # Navigation
  go_router: ^9.0.0
  
  # HTTP Client
  http: ^1.1.0
  dio: ^5.2.0
  
  # Local Storage
  shared_preferences: ^2.1.0
  sqflite: ^2.2.0
  hive: ^2.2.0
  
  # API Communication
  json_serializable: ^6.6.0
  
  # Authentication
  firebase_auth: ^4.6.0
  
  # Push Notifications
  firebase_messaging: ^14.5.0
  
  # File Handling
  image_picker: ^1.0.0
  file_picker: ^5.3.0
  
  # UI/UX
  flutter_screenutil: ^5.8.0
  cached_network_image: ^3.2.0
  shimmer: ^3.0.0
  
  # Validation & Utilities
  validators: ^3.0.0
  intl: ^0.18.0
  uuid: ^3.0.0
  
  # Logging
  logger: ^1.3.0
  
  # Error Handling
  sentry_flutter: ^7.10.0

dev_dependencies:
  flutter_test:
    sdk: flutter
  build_runner: ^2.4.0
  json_serializable: ^6.6.0
  mockito: ^5.4.0
```

---

## Architecture de l'Application

### Architectural Pattern: Clean Architecture + MVVM

```
┌─────────────────────────────────────────────────┐
│           Presentation Layer (UI)               │
│  ┌──────────────────────────────────────────┐   │
│  │  Screens / Widgets / Pages               │   │
│  │  - AuthScreen, HomeScreen, etc           │   │
│  └──────────────────────────────────────────┘   │
│                      ↓                           │
│  ┌──────────────────────────────────────────┐   │
│  │  ViewModels / State Management           │   │
│  │  - Riverpod Providers                    │   │
│  │  - Business Logic                        │   │
│  └──────────────────────────────────────────┘   │
└─────────────────────────────────────────────────┘
           ↓
┌─────────────────────────────────────────────────┐
│           Domain Layer                          │
│  ┌──────────────────────────────────────────┐   │
│  │  Entities (Modèles métier)               │   │
│  │  - User, Job, Formation, Company         │   │
│  └──────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────┐   │
│  │  Use Cases / Repositories (Interface)    │   │
│  │  - Abstract repositories                 │   │
│  └──────────────────────────────────────────┘   │
└─────────────────────────────────────────────────┘
           ↓
┌─────────────────────────────────────────────────┐
│           Data Layer                            │
│  ┌──────────────────────────────────────────┐   │
│  │  Repository Implementations              │   │
│  │  - Remote (API)                          │   │
│  │  - Local (SQLite/Hive)                   │   │
│  └──────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────┐   │
│  │  Data Sources                            │   │
│  │  - API Client (HTTP)                     │   │
│  │  - Database                              │   │
│  │  - Local Storage                         │   │
│  └──────────────────────────────────────────┘   │
└─────────────────────────────────────────────────┘
```

### Design Patterns
- **Repository Pattern**: Abstraction de l'accès aux données
- **Provider Pattern**: Gestion d'état avec Riverpod
- **Factory Pattern**: Création d'instances
- **Singleton**: Services uniques (API client, local storage)

---

## Structure du Projet

```
job_formation_app/
│
├── lib/
│   ├── main.dart                          # Point d'entrée
│   ├── config/
│   │   ├── app_config.dart               # Configuration app
│   │   ├── api_config.dart               # Configuration API
│   │   ├── firebase_config.dart          # Configuration Firebase
│   │   └── routes.dart                   # Routes de navigation
│   │
│   ├── core/
│   │   ├── constants/
│   │   │   ├── app_constants.dart        # Constantes globales
│   │   │   ├── colors.dart               # Palette couleurs
│   │   │   ├── strings.dart              # Textes (i18n)
│   │   │   └── dimensions.dart           # Espacements, tailles
│   │   │
│   │   ├── utils/
│   │   │   ├── validators.dart           # Validations
│   │   │   ├── logger.dart               # Logging
│   │   │   ├── extensions.dart           # Extensions Dart
│   │   │   ├── date_formatter.dart       # Formatage dates
│   │   │   └── asset_manager.dart        # Gestion assets
│   │   │
│   │   ├── errors/
│   │   │   ├── exception.dart            # Custom exceptions
│   │   │   └── failure.dart              # Failure classes
│   │   │
│   │   └── widgets/
│   │       ├── custom_app_bar.dart
│   │       ├── custom_button.dart
│   │       ├── loading_widget.dart
│   │       ├── error_widget.dart
│   │       └── shared_widgets.dart
│   │
│   ├── data/
│   │   ├── datasources/
│   │   │   ├── remote/
│   │   │   │   ├── auth_api.dart        # Endpoints auth
│   │   │   │   ├── job_api.dart         # Endpoints jobs
│   │   │   │   ├── company_api.dart     # Endpoints companies
│   │   │   │   ├── user_api.dart        # Endpoints users
│   │   │   │   └── api_client.dart      # HTTP client config
│   │   │   │
│   │   │   └── local/
│   │   │       ├── storage_service.dart # SharedPreferences
│   │   │       ├── database_service.dart # SQLite/Hive
│   │   │       └── cache_service.dart   # Cache layer
│   │   │
│   │   ├── models/
│   │   │   ├── auth/
│   │   │   │   ├── login_request.dart
│   │   │   │   ├── login_response.dart
│   │   │   │   ├── register_request.dart
│   │   │   │   └── token_model.dart
│   │   │   │
│   │   │   ├── user/
│   │   │   │   ├── user_model.dart
│   │   │   │   └── user_type.dart
│   │   │   │
│   │   │   ├── job/
│   │   │   │   ├── job_model.dart
│   │   │   │   ├── job_filter.dart
│   │   │   │   └── job_application.dart
│   │   │   │
│   │   │   ├── formation/
│   │   │   │   ├── formation_model.dart
│   │   │   │   └── formation_type.dart
│   │   │   │
│   │   │   └── company/
│   │   │       ├── company_model.dart
│   │   │       └── company_profile.dart
│   │   │
│   │   └── repositories/
│   │       ├── auth_repository.dart
│   │       ├── job_repository.dart
│   │       ├── formation_repository.dart
│   │       ├── user_repository.dart
│   │       ├── company_repository.dart
│   │       └── admin_repository.dart
│   │
│   ├── domain/
│   │   ├── entities/
│   │   │   ├── user_entity.dart
│   │   │   ├── job_entity.dart
│   │   │   ├── formation_entity.dart
│   │   │   ├── company_entity.dart
│   │   │   └── application_entity.dart
│   │   │
│   │   └── repositories/
│   │       ├── auth_repository.dart     # Interface
│   │       ├── job_repository.dart      # Interface
│   │       ├── user_repository.dart     # Interface
│   │       └── company_repository.dart  # Interface
│   │
│   ├── presentation/
│   │   ├── providers/
│   │   │   ├── auth_provider.dart       # État authentification
│   │   │   ├── job_provider.dart        # État jobs
│   │   │   ├── user_provider.dart       # État user
│   │   │   ├── company_provider.dart    # État company
│   │   │   ├── admin_provider.dart      # État admin
│   │   │   └── app_providers.dart       # Providers globaux
│   │   │
│   │   ├── screens/
│   │   │   ├── auth/
│   │   │   │   ├── login_screen.dart
│   │   │   │   ├── register_screen.dart
│   │   │   │   ├── register_type_screen.dart
│   │   │   │   ├── forgot_password_screen.dart
│   │   │   │   └── verify_email_screen.dart
│   │   │   │
│   │   │   ├── user/
│   │   │   │   ├── home_screen.dart
│   │   │   │   ├── job_list_screen.dart
│   │   │   │   ├── job_detail_screen.dart
│   │   │   │   ├── formation_list_screen.dart
│   │   │   │   ├── formation_detail_screen.dart
│   │   │   │   ├── search_screen.dart
│   │   │   │   ├── profile_screen.dart
│   │   │   │   ├── saved_jobs_screen.dart
│   │   │   │   └── applications_screen.dart
│   │   │   │
│   │   │   ├── company/
│   │   │   │   ├── company_home_screen.dart
│   │   │   │   ├── job_management_screen.dart
│   │   │   │   ├── create_job_screen.dart
│   │   │   │   ├── edit_job_screen.dart
│   │   │   │   ├── formation_management_screen.dart
│   │   │   │   ├── create_formation_screen.dart
│   │   │   │   ├── applications_received_screen.dart
│   │   │   │   ├── company_dashboard_screen.dart
│   │   │   │   └── company_profile_screen.dart
│   │   │   │
│   │   │   ├── admin/
│   │   │   │   ├── admin_dashboard_screen.dart
│   │   │   │   ├── users_management_screen.dart
│   │   │   │   ├── companies_management_screen.dart
│   │   │   │   ├── jobs_moderation_screen.dart
│   │   │   │   ├── formations_moderation_screen.dart
│   │   │   │   ├── reports_screen.dart
│   │   │   │   ├── settings_screen.dart
│   │   │   │   └── analytics_screen.dart
│   │   │   │
│   │   │   └── common/
│   │   │       ├── splash_screen.dart
│   │   │       ├── error_screen.dart
│   │   │       └── onboarding_screen.dart
│   │   │
│   │   ├── widgets/
│   │   │   ├── auth/
│   │   │   │   ├── login_form.dart
│   │   │   │   ├── register_form.dart
│   │   │   │   └── user_type_selector.dart
│   │   │   │
│   │   │   ├── job/
│   │   │   │   ├── job_card.dart
│   │   │   │   ├── job_filter_widget.dart
│   │   │   │   ├── job_details_widget.dart
│   │   │   │   └── apply_button.dart
│   │   │   │
│   │   │   ├── company/
│   │   │   │   ├── company_card.dart
│   │   │   │   ├── company_info_widget.dart
│   │   │   │   └── company_stats_widget.dart
│   │   │   │
│   │   │   └── common/
│   │   │       ├── pagination_widget.dart
│   │   │       ├── search_bar.dart
│   │   │       ├── filter_chip.dart
│   │   │       └── confirmation_dialog.dart
│   │   │
│   │   └── theme/
│   │       ├── app_theme.dart           # ThemeData
│   │       ├── text_styles.dart         # Styles texte
│   │       └── app_colors.dart          # Couleurs
│   │
│   └── service/
│       ├── notification_service.dart    # FCM
│       ├── auth_service.dart           # Gestion auth
│       ├── location_service.dart       # Localisation
│       └── permission_service.dart     # Permissions
│
├── assets/
│   ├── images/
│   │   ├── splash_logo.png
│   │   ├── app_icon.png
│   │   ├── backgrounds/
│   │   └── icons/
│   │
│   ├── fonts/
│   │   ├── Poppins/
│   │   └── Roboto/
│   │
│   └── translations/
│       ├── en.json
│       └── fr.json
│
├── test/
│   ├── unit/
│   │   ├── repositories/
│   │   ├── providers/
│   │   └── utils/
│   │
│   ├── widget/
│   │   ├── screens/
│   │   └── widgets/
│   │
│   └── integration/
│       └── app_test.dart
│
├── pubspec.yaml
├── pubspec.lock
├── analysis_options.yaml
└── README.md
```

---

## Modèle de Données

### Entités Principales

#### 1. User (Candidat)
```dart
class User {
  String id;
  String email;
  String password; // Hashé
  String firstName;
  String lastName;
  String phone;
  String profileImage;
  String bio;
  String location;
  List<String> skills;
  String cv; // URL document
  UserType userType; // CANDIDATE, COMPANY, ADMIN
  DateTime createdAt;
  DateTime updatedAt;
  bool isEmailVerified;
  bool isActive;
}
```

#### 2. Company/School
```dart
class Company {
  String id;
  String adminUserId; // Référence User
  String name;
  String description;
  String logo;
  String website;
  String email;
  String phone;
  String location;
  String industry;
  int employeeCount;
  DateTime foundedAt;
  bool isVerified;
  List<String> jobIds; // Références aux jobs
  List<String> formationIds; // Références aux formations
  int totalApplications;
  double rating;
  DateTime createdAt;
  DateTime updatedAt;
}
```

#### 3. Job Posting
```dart
class Job {
  String id;
  String companyId; // Référence Company
  String title;
  String description;
  String requirements;
  String benefits;
  String location;
  String jobType; // FULL_TIME, PART_TIME, REMOTE, HYBRID
  String level; // JUNIOR, SENIOR, MANAGER
  String category;
  double salary;
  String salaryRange; // "10k - 15k"
  DateTime deadline;
  int views;
  int applicants;
  bool isActive;
  bool isFeatured;
  List<String> applicantIds; // Références aux candidatures
  DateTime createdAt;
  DateTime updatedAt;
}
```

#### 4. Formation
```dart
class Formation {
  String id;
  String companyId; // Référence Company
  String title;
  String description;
  String level; // BEGINNER, INTERMEDIATE, ADVANCED
  String category;
  double price;
  int duration; // En heures
  String instructor;
  int maxParticipants;
  int enrolledCount;
  DateTime startDate;
  DateTime endDate;
  String format; // ONLINE, IN_PERSON, HYBRID
  bool isActive;
  List<String> enrolledUserIds;
  double rating;
  DateTime createdAt;
  DateTime updatedAt;
}
```

#### 5. Job Application
```dart
class JobApplication {
  String id;
  String jobId; // Référence Job
  String userId; // Référence User
  String companyId; // Référence Company
  String status; // PENDING, REVIEWED, SHORTLISTED, REJECTED
  DateTime appliedAt;
  String coverLetter;
  DateTime updatedAt;
}
```

#### 6. Admin
```dart
class Admin {
  String id;
  String userId; // Référence User
  String role; // SUPER_ADMIN, MODERATOR
  List<String> permissions;
  DateTime createdAt;
  DateTime updatedAt;
}
```

---

## Fonctionnalités Détaillées

### 1. Authentification & Autorisation

#### Endpoints Backend
```
POST   /api/v1/auth/register      - Créer un compte
POST   /api/v1/auth/login         - Connexion
POST   /api/v1/auth/refresh-token - Renouveler token
POST   /api/v1/auth/logout        - Déconnexion
POST   /api/v1/auth/verify-email  - Vérifier email
POST   /api/v1/auth/forgot-password - Réinitialiser password
```

#### Flow
1. User remplit formulaire d'inscription
2. Sélecte type de compte (Candidat/Company/Admin)
3. Email de vérification envoyé
4. Token JWT généré après vérification
5. Tokens stockés localement en secure storage

### 2. Gestion des Offres d'Emploi

#### Endpoints Backend
```
# Public
GET    /api/v1/jobs              - Lister tous les jobs
GET    /api/v1/jobs/:id          - Détail job
GET    /api/v1/jobs/search       - Recherche filtrer
GET    /api/v1/jobs/:id/apply    - Soumettre candidature

# Company
POST   /api/v1/jobs              - Créer offre
PUT    /api/v1/jobs/:id          - Modifier offre
DELETE /api/v1/jobs/:id          - Supprimer offre
GET    /api/v1/jobs/:id/applicants - Voir candidatures

# Admin
DELETE /api/v1/admin/jobs/:id    - Supprimer offre (modération)
```

#### Fonctionnalités
- Filtrage: localité, type, niveau, salaire, catégorie
- Recherche full-text
- Sauvegarde d'offres (favoris)
- Notifications de nouvelles offres
- Pagination et tri

### 3. Gestion des Formations

Similaire aux jobs avec:
- Inscription aux formations
- Certificats de complétion
- Evaluation des formations
- Suivi de progression

### 4. Dashboard Company/School

```
┌─────────────────────────────────┐
│       Dashboard Company         │
├─────────────────────────────────┤
│ Stats:                          │
│ - Total jobs posted: 45         │
│ - Total applications: 230       │
│ - Formations created: 12        │
│ - Enrolled students: 450        │
│                                 │
│ Actions:                        │
│ - Créer offre                   │
│ - Gérer offres                  │
│ - Voir candidatures             │
│ - Créer formation               │
│ - Gérer formations              │
│ - Voir inscriptions             │
│ - Profil company                │
│ - Statistiques                  │
│ - Paramètres                    │
└─────────────────────────────────┘
```

### 5. Dashboard Admin

```
┌─────────────────────────────────┐
│      Dashboard Admin            │
├─────────────────────────────────┤
│ Stats Globales:                 │
│ - Users: 5,420                  │
│ - Companies: 234                │
│ - Jobs: 1,230                   │
│ - Formations: 456               │
│ - Applications: 12,340          │
│                                 │
│ Actions:                        │
│ - Gérer utilisateurs            │
│ - Gérer companies               │
│ - Modérer offres                │
│ - Modérer formations            │
│ - Voir rapports                 │
│ - Gestion des paramètres        │
│ - Analytics                     │
│ - Logs d'activité               │
└─────────────────────────────────┘
```

---

## Plan d'Implémentation

### Phase 1: Setup & Infrastructure (Semaine 1-2)
- [ ] Initialiser projet Flutter
- [ ] Configurer Firebase
- [ ] Setup backend Node.js + Express
- [ ] Configurer PostgreSQL
- [ ] Setup authentification JWT
- [ ] Configurer CI/CD

**Livrables:**
- Projet Flutter opérationnel
- API de base avec authentification
- Base de données structurée

### Phase 2: Authentification (Semaine 3)
- [ ] Implémenter register/login frontend
- [ ] Implémenter endpoints auth backend
- [ ] Intégrer JWT
- [ ] Email verification
- [ ] Password reset
- [ ] Secure token storage

**Livrables:**
- Système d'authentification complet
- Tests unitaires auth
- Documentation API auth

### Phase 3: Gestion Jobs (Semaine 4-5)
- [ ] Models & entities
- [ ] Repository pattern
- [ ] API endpoints (CRUD)
- [ ] UI job list
- [ ] Job detail page
- [ ] Search & filters
- [ ] Apply functionality
- [ ] Saved jobs (favoris)

**Livrables:**
- Module jobs complet
- Tests unitaires & widgets
- Documentation

### Phase 4: Gestion Formations (Semaine 6)
- [ ] Models & entities
- [ ] API endpoints
- [ ] UI formation list
- [ ] Formation details
- [ ] Enroll functionality
- [ ] Progress tracking

**Livrables:**
- Module formations complet

### Phase 5: Dashboard Company (Semaine 7-8)
- [ ] Company profile
- [ ] Job management (CRUD)
- [ ] Formation management
- [ ] View applications
- [ ] Statistics & analytics
- [ ] Settings

**Livrables:**
- Dashboard company fonctionnel

### Phase 6: Dashboard User (Semaine 8-9)
- [ ] User profile
- [ ] My applications
- [ ] My formations
- [ ] Notifications
- [ ] Preferences
- [ ] CV upload

**Livrables:**
- Dashboard user complet

### Phase 7: Dashboard Admin (Semaine 10)
- [ ] User management
- [ ] Company verification
- [ ] Content moderation
- [ ] Reports & analytics
- [ ] System settings
- [ ] Activity logs

**Livrables:**
- Dashboard admin fonctionnel

### Phase 8: Features Avancées (Semaine 11-12)
- [ ] Notifications push
- [ ] In-app messaging
- [ ] Real-time updates (WebSocket)
- [ ] Advanced analytics
- [ ] Email notifications
- [ ] Performance optimization

**Livrables:**
- Features avancées
- Performance tuning

### Phase 9: Testing & QA (Semaine 13-14)
- [ ] Tests unitaires complets
- [ ] Tests d'intégration
- [ ] Tests E2E
- [ ] Performance testing
- [ ] Security testing
- [ ] Bug fixes

**Livrables:**
- Application testée & optimisée

### Phase 10: Déploiement (Semaine 15)
- [ ] Build APK/AAB
- [ ] Google Play Store submission
- [ ] App Store (iOS) - si nécessaire
- [ ] Backend deployment
- [ ] Database backup setup
- [ ] Monitoring & logging

**Livrables:**
- App en production
- Documentation finale

---

## Sécurité & Authentification

### Authentification JWT
```
Header: Authorization: Bearer <token>

Token Structure:
{
  "sub": "user_id",
  "email": "user@example.com",
  "role": "CANDIDATE|COMPANY|ADMIN",
  "iat": 1516239022,
  "exp": 1516242622  // 1 heure
}

Refresh Token:
- Stocké en HttpOnly cookie côté backend
- Validité: 7 jours
- Rotation: Nouveau token à chaque refresh
```

### Sécurité Côté Frontend (Flutter)

#### Secure Storage
```dart
// Utiliser flutter_secure_storage
final secureStorage = FlutterSecureStorage();

// Sauvegarder token
await secureStorage.write(key: 'access_token', value: token);

// Récupérer token
String? token = await secureStorage.read(key: 'access_token');

// Supprimer token
await secureStorage.delete(key: 'access_token');
```

#### HTTPS & Certificate Pinning
```dart
// Configurer Dio avec certificate pinning
final client = HttpClientWithPinning();
final dio = Dio(
  BaseOptions(
    baseUrl: 'https://api.example.com',
  ),
);
```

### Sécurité Côté Backend

#### Password Hashing
```javascript
// Utiliser bcrypt
const bcrypt = require('bcrypt');
const saltRounds = 10;

const hashedPassword = await bcrypt.hash(password, saltRounds);
const isMatch = await bcrypt.compare(password, hashedPassword);
```

#### Rate Limiting
```javascript
const rateLimit = require('express-rate-limit');

const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100, // Limite 100 requêtes par IP
  message: 'Trop de tentatives, réessayez plus tard'
});

app.use('/api/', limiter);
```

#### CORS
```javascript
const cors = require('cors');

app.use(cors({
  origin: ['https://yourdomain.com'],
  credentials: true,
  optionsSuccessStatus: 200
}));
```

#### Input Validation & Sanitization
```javascript
const { body, validationResult } = require('express-validator');

app.post('/api/auth/register', [
  body('email').isEmail().normalizeEmail(),
  body('password').isLength({ min: 8 }),
  body('firstName').notEmpty().trim(),
], (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({ errors: errors.array() });
  }
  // Traiter la requête
});
```

#### SQL Injection Prevention
```javascript
// Utiliser prepared statements avec PostgreSQL
const query = 'SELECT * FROM users WHERE email = $1';
const values = [email];
const result = await pool.query(query, values);
```

### Données Sensibles
- Passwords: Jamais stocker en clair
- Tokens: Stockér en secure storage sur Flutter
- API Keys: Variables d'environnement sur backend
- CV/Documents: Chiffrer avant stockage S3

---

## Configuration & Déploiement

### Variables d'Environnement Backend
```
.env
NODE_ENV=production
PORT=5000
DATABASE_URL=postgresql://user:password@localhost:5432/job_db
JWT_SECRET=your_secret_key
JWT_REFRESH_SECRET=your_refresh_secret
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_PRIVATE_KEY=your_private_key
SENDGRID_API_KEY=your_api_key
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_REGION=eu-west-1
```

### Configuration Firebase
```yaml
# google-services.json (Android)
# GoogleService-Info.plist (iOS)
# Inclure automatiquement par FlutterFire
```

### Monitoring & Logging
```dart
// Utiliser Sentry pour error tracking
await Sentry.init(
  'https://examplePublicKey@o0.ingest.sentry.io/0',
  tracesSampleRate: 1.0,
  environment: 'production',
);
```

---

## Endpoints API Backend - Récapitulatif

### Authentication
```
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
POST   /api/v1/auth/refresh-token
POST   /api/v1/auth/verify-email
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/reset-password
```

### Users
```
GET    /api/v1/users/profile
PUT    /api/v1/users/profile
GET    /api/v1/users/:id
DELETE /api/v1/users/:id
POST   /api/v1/users/upload-cv
POST   /api/v1/users/upload-profile-pic
```

### Jobs
```
GET    /api/v1/jobs
GET    /api/v1/jobs/search
GET    /api/v1/jobs/:id
POST   /api/v1/jobs (Company)
PUT    /api/v1/jobs/:id (Company)
DELETE /api/v1/jobs/:id (Company)
POST   /api/v1/jobs/:id/apply (User)
GET    /api/v1/jobs/:id/applicants (Company)
POST   /api/v1/jobs/:id/save (User)
GET    /api/v1/users/saved-jobs
```

### Formations
```
GET    /api/v1/formations
GET    /api/v1/formations/:id
POST   /api/v1/formations (Company)
PUT    /api/v1/formations/:id (Company)
DELETE /api/v1/formations/:id (Company)
POST   /api/v1/formations/:id/enroll (User)
GET    /api/v1/users/formations
```

### Companies
```
GET    /api/v1/companies
GET    /api/v1/companies/:id
POST   /api/v1/companies (Company Admin)
PUT    /api/v1/companies/:id (Company Admin)
GET    /api/v1/companies/:id/dashboard
```

### Admin
```
GET    /api/v1/admin/users
GET    /api/v1/admin/companies
POST   /api/v1/admin/companies/:id/verify
DELETE /api/v1/admin/jobs/:id
DELETE /api/v1/admin/formations/:id
GET    /api/v1/admin/analytics
GET    /api/v1/admin/reports
```

---

## Performance & Optimisation

### Frontend Optimization
- **Lazy Loading**: Charger screens à la demande
- **Image Optimization**: Compresser images, utiliser WebP
- **State Management**: Riverpod pour minimiser rebuilds
- **Code Splitting**: Diviser code en bundles
- **Caching**: Mettre en cache données localement

### Backend Optimization
- **Database Indexing**: Indexer colonnes fréquemment requêtées
- **Query Optimization**: Utiliser `select` spécifique, éviter N+1
- **Pagination**: Limiter résultats par page
- **Redis Caching**: Cacher données fréquemment accédées
- **Compression**: Gzip responses

### Monitoring
- **Error Tracking**: Sentry
- **Performance Monitoring**: New Relic / Datadog
- **Log Aggregation**: ELK Stack / CloudWatch
- **Uptime Monitoring**: UptimeRobot

---

## Timeline Estimée
**Total: 15 semaines (3-4 mois)**
- Développement: 12 semaines
- Testing & QA: 2 semaines
- Déploiement: 1 semaine

---

## Technologies Recommandées - Résumé

| Layer | Technology | Version |
|-------|-----------|---------|
| **Frontend** | Flutter | 3.x+ |
| **Language** | Dart | 3.0+ |
| **State Mgmt** | Riverpod | 2.3.0+ |
| **Navigation** | GoRouter | 9.0.0+ |
| **Backend** | Node.js + Express | 18.x |
| **Database** | PostgreSQL | 14+ |
| **Cache** | Redis | 7.x |
| **Auth** | JWT | - |
| **Storage** | Firebase Storage / AWS S3 | - |
| **Notifications** | Firebase Cloud Messaging | - |
| **Hosting** | Heroku / AWS / GCP | - |
| **Email** | SendGrid / Nodemailer | - |
| **Error Tracking** | Sentry | - |

---

## Contact & Support
Pour des questions techniques, consultez:
- Documentation Flutter: https://flutter.dev/docs
- Documentation Express.js: https://expressjs.com
- Documentation PostgreSQL: https://www.postgresql.org/docs
- Documentation Riverpod: https://riverpod.dev

---

**Document Version:** 1.0  
**Date:** 2026-05-15  
**Auteur:** Architecture Team

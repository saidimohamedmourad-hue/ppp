# IQRA — Architecture de la plateforme (annexe)

> Annexe technique du dossier de synthèse, rédigée pour être **lisible** : un
> lecteur avec un minimum de bagage technique doit comprendre comment la
> plateforme est construite. Les éléments décrits reflètent l'état réel du code.

---

## 1. Vue d'ensemble en un schéma

```
        UTILISATEURS                         ADMINISTRATEURS
   (candidats / entreprises / écoles)
            │                                       │
   ┌────────┴─────────┐                             │
   ▼                  ▼                             ▼
┌────────────┐   ┌─────────────┐            ┌──────────────────┐
│  Site web   │   │ App mobile  │            │  Back-office      │
│  (React)    │   │ + web       │            │  d'administration │
│  port 3000  │   │ (Flutter)   │            │  (Blade/Laravel)  │
└──────┬──────┘   └──────┬──────┘            └─────────┬────────┘
       │   API REST (JSON, jeton sécurisé)             │
       └───────────────┬───────────────────────────────┘
                       ▼
            ┌─────────────────────────┐
            │   API & cœur métier      │
            │   Laravel (PHP)          │   ← règles métier, rôles, sécurité
            │   port 8000              │
            └───────────┬─────────────┘
                        ▼
            ┌─────────────────────────┐
            │   Base de données        │   ← une seule source de vérité
            │   relationnelle          │
            └─────────────────────────┘

   Services externes : Google / Facebook (connexion), Cloudflare (anti-bot),
   SMTP (emails), moteur de score IA.
```

**Idée clé :** trois interfaces différentes (web, mobile, administration)
parlent à **un seul cerveau** (l'API Laravel) qui détient les règles métier, la
sécurité et **une seule base de données**. Résultat : pas de duplication de
logique, données cohérentes partout, maintenance simplifiée.

---

## 2. Les composants

| Composant | Technologie | Rôle |
|---|---|---|
| **Site web** | React + TypeScript | Espace public + candidat / entreprise / école, rapide et moderne. |
| **Application mobile & web** | Flutter | Une seule base de code pour Android, iOS et web. |
| **API & cœur métier** | Laravel (PHP) | Authentification, règles métier, sécurité, exposition des données en JSON. |
| **Back-office d'administration** | Laravel (pages Blade) | Interface de gestion réservée aux administrateurs. |
| **Modèles partagés** | Paquet commun (`job-shared`) | Définition unique des entités, réutilisée par l'API et le back-office. |
| **Base de données** | **MariaDB** (relationnelle) | Stockage central de toutes les données. |
| **Moteur d'analyse IA** | **OpenAI GPT-4o** (via Laravel) | Lecture du CV (PDF) + score et retour de la candidature. |

> Les **modèles partagés** garantissent que l'API et le back-office voient
> exactement les mêmes entités et les mêmes règles — pas de divergence.
>
> **À ce stade**, le cache, les sessions et les files d'attente (queue) sont
> gérés par la **base de données** elle-même (pas de Redis). Les traitements IA
> longs sont exécutés en **tâches asynchrones (queue)** pour ne pas ralentir
> l'utilisateur.

---

## 3. Le système de rôles (qui a le droit de faire quoi)

L'accès est contrôlé côté serveur par un **filtre de rôle** (RBAC). Chaque
utilisateur possède **un rôle** parmi quatre, et l'API n'autorise que les
actions correspondantes :

| Rôle | Accès |
|---|---|
| **Candidat** (`job-seeker`) | Parcourir offres et formations, postuler / s'inscrire, gérer son profil et son CV, suivre ses candidatures. |
| **Entreprise** (`company-owner`) | Gérer son entreprise et ses offres, consulter les candidatures reçues (avec score IA), changer leur statut. |
| **École** (`school-owner`) | Gérer son école et ses sessions de formation, consulter les inscriptions (avec score IA), gérer les places. |
| **Administrateur** (`admin`) | Supervision globale via le back-office : tous les contenus et utilisateurs. |

Concrètement, l'API expose des **groupes d'accès séparés** : tout ce qui touche
un candidat est verrouillé derrière le rôle candidat, tout ce qui touche une
entreprise derrière le rôle entreprise, etc. Un utilisateur ne peut pas accéder
aux données d'un autre profil.

---

## 4. Modèle de données (les entités principales)

```
        Utilisateur (User)
        ├── possède éventuellement →  Entreprise (Company)  → Offres (JobVacancy)
        ├── possède éventuellement →  École (School)        → Sessions (TrainingSession)
        ├── dépose →  Candidatures emploi (JobApplication)   → liée à une Offre + un CV
        ├── dépose →  Inscriptions formation (TrainingApplication) → liée à une Session
        ├── possède →  CV (Resume)
        └── méthodes de connexion →  Comptes liés (AuthProvider : Google / Facebook)

   Offres   → classées par Catégorie d'emploi (JobCategory)
   Sessions → classées par Catégorie de formation (TrainingCategory)
```

| Entité | Description | Liens principaux |
|---|---|---|
| **User** | Compte (nom, email, téléphone, rôle, mot de passe haché) | entreprise, école, candidatures, CV, comptes liés |
| **Company** | Fiche entreprise (secteur, adresse, site, téléphone) | appartient à un User, publie des offres |
| **School** | Fiche école / centre (secteur, adresse, site, téléphone) | appartient à un User, publie des sessions |
| **JobVacancy** | Offre d'emploi (titre, type, lieu, salaire, description) | entreprise, catégorie, candidatures |
| **TrainingSession** | Session de formation (type, lieu, dates, prix, places, liste d'attente) | école, catégorie, inscriptions |
| **JobApplication** | Candidature à une offre (statut, **score IA + retour IA**) | user, offre, CV |
| **TrainingApplication** | Inscription à une session (statut, **score IA + retour IA**) | user, session |
| **Resume** | CV du candidat (résumé, compétences, formation, expérience) | user |
| **JobCategory / TrainingCategory** | Catégories pour classer offres et sessions | — |
| **AuthProvider** | Lien vers un compte Google / Facebook | user |

**Données techniques annexes :** notifications, journal des connexions
(audit), jetons d'authentification.

---

## 5. Les flux clés (parcours)

**a) Inscription & connexion**
```
Utilisateur → choisit son rôle → compte créé (mot de passe haché)
ou « Continuer avec Google / Facebook » → l'API vérifie le jeton côté serveur
→ délivrance d'un jeton de session sécurisé (Sanctum)
```

**b) Candidature avec présélection IA**
```
Candidat postule (CV PDF + téléphone)
→ l'API enregistre la candidature (tâche asynchrone déclenchée)
→ extraction du texte du CV (PDF) puis évaluation par un modèle de langage
  (OpenAI GPT-4o) : score (0–100) + retour détaillé
→ l'entreprise / école voit la candidature enrichie
→ elle décide : accepter / en attente / refuser  (l'humain tranche toujours)
```
> Le score n'est **pas** un modèle ML entraîné en interne : c'est un grand
> modèle de langage (OpenAI GPT-4o) guidé par des critères de notation définis
> par IQRA. Voir la note de conformité au §7 (envoi de données à un service
> tiers).

**c) Notifications**
```
Candidature reçue ou statut changé
→ notification en base (cloche dans l'interface) + email
```

**d) Sécurité d'accès (à chaque requête)**
```
Requête → jeton valide ? → rôle autorisé ? → action permise ? → réponse
(sinon : refus)
```

---

## 6. Sécurité (résumé technique)

- **Authentification par jeton** (Laravel Sanctum) — pas de mot de passe stocké
  côté client ; sessions révocables.
- **Mots de passe hachés** (jamais en clair).
- **Contrôle d'accès par rôle** (RBAC) à chaque requête.
- **Vérification serveur** des connexions Google / Facebook (les jetons sont
  validés auprès du fournisseur, jamais crus sur parole).
- **Anti-robot (Cloudflare Turnstile)** sur la réinitialisation de mot de passe.
- **Limitation de débit + anti-force-brute** sur la connexion.
- **Journal des connexions** (audit / traçabilité).
- **Secrets côté serveur** uniquement (jamais dans le code public).

---

## 7. Services externes & déploiement

| Service | Usage |
|---|---|
| **Google / Facebook** | Connexion sociale |
| **Cloudflare Turnstile** | Protection anti-robot |
| **SMTP** | Envoi des emails (réinitialisation, notifications) |
| **OpenAI (GPT-4o)** | Lecture du CV + score et retour de la candidature |

> **Note de conformité :** l'analyse du CV envoie le contenu du CV à **OpenAI
> (États-Unis)**. C'est un **transfert transfrontalier de données potentiellement
> sensibles** : à encadrer (base légale, information du candidat, formalités
> ANPDP) — voir `CONFORMITE_DONNEES.md`, §5.

- **En développement** : trois services tournent en local (web :3000, API
  :8000, app Flutter), une chaîne de tests automatisés est en place.
- **Vers la production** : déploiement à planifier (hébergement de l'API et de
  la base, HTTPS, configuration des services externes) — voir la feuille de
  route dans le dossier de synthèse et la conformité (loi 18-07) pour
  l'encadrement des transferts de données.

---

## 8. Pourquoi cette architecture

- **Cœur unique, plusieurs vitrines** : web, mobile et administration partagent
  la même logique et la même base → cohérence et coûts maîtrisés.
- **Sécurité par conception** : rôles, jetons, vérifications serveur, anti-bot.
- **Évolutivité** : ajouter une fonctionnalité se fait une fois dans l'API et
  bénéficie à tous les supports.
- **Multi-plateforme dès le départ** : présence web et mobile sans réécrire le
  produit pour chaque support.

---

*Annexe technique du dossier de synthèse IQRA. Reflète l'état réel du projet.*

---
name: Job App Formations
overview: Stabiliser et documenter la partie Formations dans job-app (deja implementee) — alignement base de donnees, visibilite statut open, validation, tests et UX.
todos:
  - id: routes-controllers-views
    content: Routes, TrainingSessionController, TrainingApplicationController, vues Blade, navigation job-seeker
    status: completed
  - id: apply-request-ai
    content: ApplyTrainingRequest + analyse formation dans ResumeAnalysisService
    status: completed
  - id: env-db-shared
    content: Verifier et aligner DB_DATABASE entre job-app et job-backoffice (meme base pour training_*)
    status: pending
  - id: ux-empty-list
    content: Ameliorer message liste vide Formations (hint statut open vs draft + lien doc interne)
    status: pending
  - id: validation-resume-owner
    content: Valider que resume_option (UUID) appartient a auth()->user() quand ce n est pas new_resume
    status: pending
  - id: db-unique-application
    content: Migration optionnelle index unique (userId, trainingSessionId) + gestion erreur doublon
    status: pending
  - id: feature-tests-training
    content: Tests Pest — visibilite open only, 404 draft, candidature, anti-doublon, scope utilisateur, mock OpenAI
    status: pending
isProject: false
---

# Partie Formations dans job-app (plan de stabilisation)

## Objectif actuel

La partie **Formations** existe deja dans `job-app`. Ce document decrit l’etat reel du code, les causes typiques du probleme « rien ne s’affiche », et les actions restantes pour la rendre **fiable** cote candidat (`job-seeker`), en coherence avec [`job-backoffice`](job-backoffice/).

## Ce qui est deja en place

| Zone | Fichiers |
|------|----------|
| Routes (auth + `role:job-seeker`) | [`job-app/routes/web.php`](job-app/routes/web.php) — `training-sessions.*`, `training-applications.index` |
| Liste / detail / candidature | [`job-app/app/Http/Controllers/TrainingSessionController.php`](job-app/app/Http/Controllers/TrainingSessionController.php) |
| Mes candidatures formation | [`job-app/app/Http/Controllers/TrainingApplicationController.php`](job-app/app/Http/Controllers/TrainingApplicationController.php) |
| Validation upload | [`job-app/app/Http/Requests/ApplyTrainingRequest.php`](job-app/app/Http/Requests/ApplyTrainingRequest.php) |
| IA formation | [`job-app/app/services/ResumeAnalysisService.php`](job-app/app/services/ResumeAnalysisService.php) — `analyzeResumeForTrainingSession()` |
| Vues | `job-app/resources/views/training-sessions/*.blade.php`, `training-applications/index.blade.php` |
| Navigation | [`job-app/resources/views/layouts/navigation.blade.php`](job-app/resources/views/layouts/navigation.blade.php) |
| Modeles | [`job-shared/src/models/TrainingSession.php`](job-shared/src/models/TrainingSession.php), `TrainingApplication`, `TrainingCategory`, `School`, `Resume`, `User` |

Flux candidat (intention produit) :

1. Voir les formations **ouvertes** (`status = open`).
2. Detail + compteur `viewCount` sur `show`.
3. Postuler (CV existant ou nouveau PDF) — analyse stockée sur `TrainingApplication`.
4. Liste « Mes candidatures formation » filtree par `auth()->id()`.

Reference parallele emploi : `JobVacancyController`, `ApplyJobRequest`, vues `job-vacancies/*`.

## Diagnostic « Formations not found » / liste vide

Deux causes frequentes dans ce projet :

### 1. Filtre `status = open` cote job-app

[`TrainingSessionController::index()`](job-app/app/Http/Controllers/TrainingSessionController.php) (et `show` / `apply` / `processApplication`) appliquent :

```php
->where('status', 'open')
```

Les sessions creees dans le backoffice sont en **`draft` par defaut** (voir formulaire [`job-backoffice/resources/views/training-session/create.blade.php`](job-backoffice/resources/views/training-session/create.blade.php)). Tant que le statut n’est pas passe a **`open`**, elles **n’apparaissent pas** dans `job-app`.

**Action** : dans le backoffice, editer chaque session et mettre `Status = open` pour publication candidat.

### 2. Bases de donnees differentes entre apps

Les exemples `.env` utilisent des bases distinctes (`job_app` vs `job_backoffice`). Si en local les vrais `.env` suivent ce decoupage, les donnees creees dans **backoffice** ne sont **pas** lues par **job-app**.

**Action** : utiliser la **meme** `DB_DATABASE` (et memes credentials) pour les deux apps sur l’environnement ou tu testes le flux bout en bout, puis `php artisan config:clear` dans chaque app si besoin.

Tables attendues (migrations backoffice) : `training_categories`, `schools`, `training_sessions`, `training_applications`.

## Plan d’action (reste a faire)

1. **Alignement DB** — Confirmer une seule base partagee ou documenter un flux de replication si tu dois garder deux bases (hors scope simple).
2. **UX liste vide** — Sur `training-sessions.index`, message explicite du type : « Aucune formation publiquee (statut open). Les brouillons restent dans le backoffice. »
3. **Validation** — Quand `resume_option` n’est pas `new_resume`, valider que l’ID correspond a un `Resume` du `userId` connecte (regle custom ou closure dans `ApplyTrainingRequest`).
4. **Capacite / places** (optionnel metier) — Refuser la candidature si `currentParticipants >= maxParticipants` (aligne avec la regle mentionnee dans l’ancien plan).
5. **Unicite candidature** — Migration unique `(userId, trainingSessionId)` sur `training_applications` + gestion propre en cas de course ; le controleur bloque deja le doublon en lecture.
6. **Tests Pest** — Couvrir guest 403, `job-seeker` voit uniquement `open`, session `draft` invisible / 404 sur show, postulation, doublon, liste candidatures scope utilisateur, mock `ResumeAnalysisService` ou facade OpenAI.

## Relations donnees (rappel)

- `TrainingSession` → `TrainingCategory`, `School` ; hasMany `TrainingApplication`.
- `TrainingApplication` → `TrainingSession`, `User`, `Resume`.
- Soft deletes sur sessions / applications : les sessions archivees ne doivent pas etre visibles cote candidat (verifier que les requetes excluent bien les supprimees si le comportement attendu est « archive = plus de liste »).

## Checklist verification manuelle

1. Meme `DB_DATABASE` dans `job-app/.env` et `job-backoffice/.env` (ou donnees copiees).
2. Migrations backoffice appliquees sur cette base ; tables `training_*` presentes.
3. Au moins une ecole, une categorie, une session avec **`status = open`**.
4. Compte utilisateur avec role **`job-seeker`**.
5. `GET /training-sessions` : la session apparait ; recherche / filtre categorie si utilises.
6. `GET /training-sessions/{id}` : detail OK ; `viewCount` augmente.
7. Postuler avec CV existant puis verifier ligne dans `training_applications` et page « Mes candidatures formation ».
8. Tenter une deuxieme candidature : redirection / message d’erreur attendu.
9. OpenAI / cloud storage : verifier `.env` (`OPENAI_*`, disque `cloud`) si le flux upload echoue.

## Tests recommandes (resume)

| Cas | Attendu |
|-----|---------|
| Guest sur `/training-sessions` | Redirection login ou 403 selon middleware |
| `job-seeker` + session `open` | 200 liste / detail |
| Session `draft` | Absente de la liste ; `show` → 404 |
| Postuler 2 fois | Bloque |
| `training-applications` | Uniquement les lignes du user connecte |
| IA | Mock pour ne pas appeler l’API en CI |

## Risques et notes

- Extraction PDF : dependance Poppler / `pdftotext` (comme pour les jobs).
- `TrainingApplication` exige un `resumeId` : toujours creer ou selectionner un CV valide.
- Ne pas exposer les formations non `open` ni les sessions d’une autre logique metier sans policy explicite.
- Modele OpenAI et cles : preferer `config()` plutot que `env()` hors fichiers de config si refactor futur.

## Ordre de travail conseille (stabilisation)

1. Verifier `.env` / base partagee.
2. Publier au moins une session en `open` et valider le flux UI.
3. Renforcer `ApplyTrainingRequest` (propriete du CV).
4. Ajouter tests Pest cibles.
5. Optionnel : message liste vide + migration unique + regle `maxParticipants`.

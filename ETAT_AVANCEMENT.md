# IQRA — État d'avancement de la plateforme

> Document de synthèse destiné à être intégré au dossier de business plan.
> Date : juin 2026. Rédigé pour un lecteur non technique ; les détails
> d'implémentation restent volontairement de haut niveau.

---

## 1. En une phrase

**IQRA** est une plateforme algérienne qui met en relation, d'un côté, les
**candidats** (chercheurs d'emploi et de formation) et, de l'autre, les
**entreprises** (offres d'emploi) et les **écoles / centres de formation**
(sessions de formation), avec une **présélection assistée par intelligence
artificielle** des candidatures.

---

## 2. Pour qui ? (les 4 profils)

| Profil | Ce qu'il peut faire |
|---|---|
| **Candidat** | Parcourir les offres d'emploi et les formations, postuler / s'inscrire, suivre l'état de ses candidatures, gérer son profil et son CV. |
| **Entreprise** | Publier des offres d'emploi, recevoir les candidatures avec les coordonnées du candidat **et un score IA**, accepter / mettre en attente / refuser. |
| **École / Centre** | Publier des sessions de formation (avec liste d'attente, type, lieu, dates), recevoir les inscriptions enrichies du score IA, gérer les places. |
| **Administrateur** | Superviser l'ensemble depuis un back-office : entreprises, écoles, offres, formations, candidatures, catégories, utilisateurs. |

---

## 3. Sur quels supports ? (multi-plateforme)

La plateforme existe sous **trois formes complémentaires**, qui partagent la
même base de données et la même logique métier :

1. **Site web public + espace candidat/entreprise/école** — application web
   moderne et rapide (React).
2. **Application mobile** (Android / iOS) **et version web** — application
   Flutter, pour une expérience native sur téléphone.
3. **Back-office d'administration** — interface web dédiée aux administrateurs
   (Laravel), pour piloter tout le contenu.

Le **cœur applicatif** (le « cerveau » : base de données, règles métier, API
sécurisée) est **unique et partagé** par les trois supports, ce qui garantit la
cohérence des données et réduit les coûts de maintenance.

---

## 4. Fonctionnalités déjà opérationnelles

### Comptes & connexion
- Inscription et connexion sécurisées, avec **4 rôles** (candidat, entreprise,
  école, administrateur) et un aiguillage automatique vers le bon espace.
- **Réinitialisation de mot de passe par email** (lien sécurisé).
- **Connexion / inscription via Google et Facebook** (« Continuer avec Google /
  Facebook ») — disponible pour **tous les profils** (candidat, entreprise,
  école).
- **Gestion des méthodes de connexion** depuis le profil (lier / délier Google,
  Facebook, définir un mot de passe).

### Emploi & formation
- **Offres d'emploi** : publication, consultation détaillée (salaire, lieu,
  type, description, **coordonnées du recruteur** : téléphone, site web,
  adresse), candidature avec CV.
- **Formations** : sessions avec **type** (présentiel, en ligne, accéléré,
  **longue durée**), **lieu**, **dates**, **prix / gratuité**, **places
  disponibles** et **liste d'attente** automatique, **motif d'annulation**, et
  **niveau d'études minimum requis**.
- **Niveau d'études obligatoire** à la candidature / l'inscription (liste
  adaptée à l'Algérie) — transmis au recruteur / à l'école.
- **CV** : **obligatoire** pour postuler à un emploi, **optionnel** pour
  s'inscrire à une formation.
- **Téléphone obligatoire** à la première candidature : entreprises et écoles
  reçoivent ainsi un moyen de contact direct.
- **Consultation sans quitter la liste** : un clic sur le titre ouvre les
  détails dans une fenêtre (et compte la vue).

### Intelligence artificielle
- Chaque candidature reçoit un **score IA (0–100)** et un **retour IA**
  (analyse du CV vs. l'offre).
- L'entreprise / l'école garde **toujours la décision finale** : elle peut
  accepter, mettre en attente ou refuser, **quel que soit le score**.

### Tableaux de bord & statistiques (entreprise / école)
- **Statistiques en temps réel** : nombre d'offres/formations, **vues
  cumulées**, candidatures (en attente / acceptées), **candidats actifs (30
  jours)**.
- **Top offres / formations** avec **taux de conversion** (vues → candidatures).
- **Candidatures récentes** d'un coup d'œil.
- Disponible sur le **web (React)** et l'**application Flutter**.

### Notifications
- **Notifications en temps réel** (cloche dans l'interface) **et par email**
  lorsqu'une candidature est reçue ou que son statut change.

### Back-office administrateur
- Gestion complète : entreprises, écoles, offres, sessions, candidatures
  (emploi et formation), catégories, utilisateurs.
- **Archivage** réversible (corbeille) sur tous les contenus sensibles, plutôt
  qu'une suppression définitive.
- Vues détaillées et symétriques côté entreprise **et** côté école.

---

## 5. Sécurité & qualité

- **Protection anti-robot (Cloudflare Turnstile)** sur la réinitialisation de
  mot de passe.
- **Limitation de débit** et **protection anti-force-brute** sur la connexion.
- **Journal des connexions** (audit) pour la traçabilité.
- **Vérification des jetons** Google / Facebook côté serveur (jamais de
  confiance aveugle envers le client).
- **Tests automatisés** : tests du back-end (API, authentification,
  notifications), tests bout-en-bout du site web, tests de l'application
  Flutter.
- **Secrets jamais exposés** : les clés sensibles restent côté serveur.

---

## 6. État d'avancement global

| Domaine | État |
|---|---|
| Architecture & base de données | ✅ Opérationnel |
| Authentification (email + Google + Facebook) | ✅ Opérationnel |
| Réinitialisation de mot de passe | ✅ Opérationnel |
| Offres d'emploi (publication, candidature, IA) | ✅ Opérationnel |
| Formations (sessions, liste d'attente, inscription, IA) | ✅ Opérationnel |
| Niveau d'études (candidature) + CV conditionnel + niveau min. requis | ✅ Opérationnel |
| Type formation « Longue durée » | ✅ Opérationnel |
| Tableaux de bord & statistiques (web + Flutter) | ✅ Opérationnel |
| Inscription sociale entreprise / école (Google, Facebook) | ✅ Opérationnel |
| Notifications (web + email) | ✅ Opérationnel |
| Back-office administrateur complet | ✅ Opérationnel |
| Coordonnées (téléphone) & contact recruteur | ✅ Opérationnel |
| Sécurité (anti-bot, anti-force-brute, audit) | ✅ Opérationnel |
| Application mobile Flutter | ✅ Fonctionnelle |
| Tests automatisés | ✅ En place |

**Stade global : MVP (produit minimum viable) fonctionnel et démontrable.**
La plateforme couvre déjà l'intégralité du parcours : un candidat peut
s'inscrire, postuler ; une entreprise / école reçoit la candidature enrichie
par l'IA et décide ; un administrateur supervise le tout.

---

## 7. Points en cours / prochaines étapes

| Sujet | Statut |
|---|---|
| Connexion Google sur **Flutter Web** | 🔧 Code corrigé + lancement fiabilisé ; reste l'autorisation des origines dans Google Cloud + test final |
| Captcha anti-bot dans l'app **Flutter** | ⏳ Présent sur le web React, **à ajouter** côté Flutter |
| Inscription sociale entreprise/école sur **Flutter** | ⏳ Fait sur le web ; à ajouter sur le register Flutter |
| Chaîne d'intégration continue (CI/CD) | ⏸️ En pause (déblocage administratif d'un compte) |
| Mise en production / déploiement | ⏳ À planifier |
| Affinage du moteur de score IA | ⏳ Amélioration continue prévue |

---

## 8. Conformité des données personnelles (loi 18-07 / RGPD-DZ)

IQRA traite des données personnelles à des fins de recrutement (identité,
coordonnées, CV, candidatures, score IA). La protection de ces données est
encadrée par la **loi algérienne n° 18-07 du 10 juin 2018**.

- **Bases techniques déjà solides** : mots de passe hachés, anti-force-brute,
  anti-robot, journal des connexions, vérification serveur des connexions
  sociales, secrets hors du code public.
- **Mise en conformité formelle planifiée** avant l'ouverture commerciale :
  politique de confidentialité / CGU, formalités auprès de l'**ANPDP**,
  encadrement des **transferts de données hors d'Algérie** (Google, Facebook,
  Cloudflare, IA), suppression / export des données par l'utilisateur.
- **Garde-fou décision automatisée** : le score IA est une **aide** — l'humain
  (entreprise / école) garde **toujours** la décision finale.

➡️ **Analyse d'écart complète, transferts internationaux et feuille de route
conformité détaillés dans [`CONFORMITE_DONNEES.md`](./CONFORMITE_DONNEES.md).**

> *Document informatif d'aide à la décision, à valider par un conseil juridique /
> DPO et auprès de l'ANPDP.*

---

## 9. Briques technologiques (annexe technique)

> Section optionnelle, utile si un interlocuteur technique lit le dossier.

- **Site web candidat/entreprise/école** : React + TypeScript (interface rapide
  et moderne).
- **Application mobile & web** : Flutter (un seul code pour Android, iOS et
  web).
- **API & back-office** : Laravel (PHP), base de données **MariaDB** (cache,
  sessions et files d'attente gérés par la base — pas de Redis à ce stade).
- **Analyse IA** : **OpenAI GPT-4o** (lecture du CV en PDF + score/retour),
  exécutée en tâches asynchrones.
- **Emails** : envoi transactionnel via SMTP.
- **Sécurité** : Cloudflare Turnstile, jetons d'authentification (Sanctum),
  limitation de débit.
- **Connexion sociale** : Google Identity Services, Facebook Login.

➡️ **Architecture détaillée** (schéma des composants, modèle de données, rôles,
flux clés, sécurité) dans [`ARCHITECTURE.md`](./ARCHITECTURE.md).

---

## 10. Proposition de valeur (résumé pour le business plan)

- **Un seul endroit** pour l'emploi **et** la formation en Algérie.
- **Gain de temps pour les recruteurs et les écoles** grâce à la
  **présélection IA** : ils voient d'abord les profils les plus pertinents,
  tout en gardant la main sur la décision.
- **Expérience fluide pour le candidat** : web + mobile, connexion en un clic
  (Google / Facebook), suivi des candidatures et notifications.
- **Plateforme déjà construite et fonctionnelle** (pas un simple concept) :
  l'essentiel du produit est opérationnel et démontrable dès aujourd'hui.

---

*Document généré comme support de synthèse. Les éléments « en cours » reflètent
l'état réel du développement et seront mis à jour au fil de l'avancement.*

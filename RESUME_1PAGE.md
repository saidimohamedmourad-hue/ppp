# IQRA — Résumé exécutif (1 page)

**IQRA** est une plateforme algérienne **emploi + formation** qui met en relation
les **candidats** avec les **entreprises** (offres d'emploi) et les **écoles /
centres** (formations), avec une **présélection assistée par intelligence
artificielle** des candidatures.

## Ce qui rend IQRA unique
- **Un seul endroit** pour l'emploi **et** la formation en Algérie.
- **Présélection IA** : chaque candidature reçoit un **score (0–100)** et un
  retour automatique → les recruteurs et écoles voient d'abord les profils les
  plus pertinents, **tout en gardant la décision finale**.
- **Web + mobile** : site React, application Flutter (Android / iOS / web),
  back-office d'administration — le tout sur un **cœur applicatif unique**.

## Architecture (en bref)
Trois interfaces (site React, app Flutter, back-office) parlent à **une seule
API sécurisée (Laravel)** qui détient les règles métier, le **contrôle d'accès
par rôle** (candidat / entreprise / école / admin) et **une seule base de
données**. Connexion par **jeton sécurisé** ; vérification serveur des
connexions Google/Facebook. *(Détail complet dans `ARCHITECTURE.md`.)*

## Ce qui est déjà opérationnel (MVP fonctionnel)
- **Comptes & connexion** : 4 rôles (candidat, entreprise, école, admin),
  connexion **et inscription** email + **Google + Facebook** (tous les profils),
  réinitialisation de mot de passe.
- **Emploi** : publication d'offres, candidature avec **CV (obligatoire)** +
  **niveau d'études**, coordonnées du recruteur (téléphone, site, adresse).
- **Formation** : sessions avec type (dont **longue durée**), lieu, dates, prix,
  **places + liste d'attente**, motif d'annulation, **niveau minimum requis** ;
  inscription avec niveau d'études (**CV optionnel**).
- **IA** : score + retour sur chaque candidature ; l'humain décide (accepter /
  en attente / refuser).
- **Tableaux de bord entreprise/école** (web + mobile) : vues, **taux de
  conversion**, candidats actifs, top offres/formations, candidatures récentes.
- **Notifications** web + email ; **back-office admin** complet avec archivage.

## Sécurité & conformité
- **Sécurité** : mots de passe hachés, anti-force-brute, **anti-robot
  (Turnstile)**, journal des connexions, vérification serveur des connexions
  sociales.
- **Conformité (loi 18-07 / RGPD-DZ)** : bases techniques en place ; mise en
  conformité formelle **planifiée** avant lancement (politique de
  confidentialité, formalités **ANPDP**, encadrement des transferts à
  l'étranger, suppression/export des données).

## Stade & prochaines étapes
- **Stade** : **MVP fonctionnel et démontrable** — parcours complet candidat →
  entreprise/école → admin opérationnel.
- **En cours** : finalisation Google sur Flutter Web, captcha côté Flutter,
  chaîne CI/CD, mise en production.

## Proposition de valeur (en une phrase)
> Faire gagner du temps aux recruteurs et aux écoles grâce à une présélection IA,
> tout en offrant aux candidats une expérience fluide web et mobile — une
> plateforme **déjà construite et fonctionnelle**, pas un simple concept.

---
*Documents détaillés : `ETAT_AVANCEMENT.md` (statut complet),
`ARCHITECTURE.md` (architecture technique) et `CONFORMITE_DONNEES.md`
(conformité loi 18-07). Version anglaise : `PROJECT_STATUS.md` /
`DATA_COMPLIANCE.md`. Dossier Word fusionné : `IQRA_Dossier_BusinessPlan.docx`.*

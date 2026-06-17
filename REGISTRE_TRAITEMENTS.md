# Registre des traitements de données personnelles — IQRA

> Inventaire des traitements de données à caractère personnel, destiné à appuyer
> la **déclaration / demande d'autorisation auprès de l'ANPDP** (loi n° 18-07).
> **À compléter** (champs `[…]`) et **à faire valider par un conseil juridique /
> DPO** avant dépôt. Voir l'analyse d'écart dans
> [`CONFORMITE_DONNEES.md`](./CONFORMITE_DONNEES.md).

**Dernière mise à jour : 13 juin 2026**

---

## Identification

| | |
|---|---|
| **Responsable de traitement** | [raison sociale] |
| **RC / NIF** | [registre du commerce] / [NIF] |
| **Adresse** | [adresse en Algérie] |
| **Contact / DPO** | [nom, email, ex. dpo@iqra.dz] |
| **Activité** | Plateforme de mise en relation emploi & formation, avec présélection IA |
| **Cadre** | Loi n° 18-07 du 10 juin 2018 |

---

## T1 — Gestion des comptes & authentification

| Rubrique | Détail |
|---|---|
| **Finalité** | Création et gestion des comptes ; connexion (email/mot de passe, Google, Facebook) ; réinitialisation de mot de passe |
| **Base légale** | Exécution du service / consentement |
| **Personnes concernées** | Candidats, entreprises, écoles, administrateurs |
| **Données** | Nom, email, **téléphone**, rôle, mot de passe (haché), identifiant Google/Facebook, avatar |
| **Destinataires** | Service interne (IQRA) |
| **Transferts hors Algérie** | **Google, Facebook** (authentification sociale) — États-Unis |
| **Durée de conservation** | Tant que le compte existe ; suppression/anonymisation après [24 mois] d'inactivité |
| **Sécurité** | Hachage des mots de passe, jetons Sanctum, vérification serveur des jetons sociaux |

## T2 — Candidatures à des offres d'emploi

| Rubrique | Détail |
|---|---|
| **Finalité** | Permettre la candidature à une offre et sa transmission au recruteur |
| **Base légale** | Exécution du service / consentement du candidat |
| **Personnes concernées** | Candidats ; entreprises (destinataires) |
| **Données** | Identité, coordonnées (dont **téléphone**), **CV (PDF)**, **niveau d'études**, statut de candidature |
| **Destinataires** | L'**entreprise** ciblée par la candidature ; administrateurs |
| **Transferts hors Algérie** | Indirect via l'analyse IA (voir T4) |
| **Durée de conservation** | [durée] après clôture du poste |
| **Sécurité** | Contrôle d'accès par rôle ; CV stocké de façon restreinte |
| **Donnée sensible** | ⚠️ Le CV peut contenir des données sensibles (régime renforcé) |

## T3 — Inscriptions à des sessions de formation

| Rubrique | Détail |
|---|---|
| **Finalité** | Permettre l'inscription à une formation et sa transmission à l'école |
| **Base légale** | Exécution du service / consentement |
| **Personnes concernées** | Candidats ; écoles (destinataires) |
| **Données** | Identité, coordonnées, **niveau d'études**, CV (optionnel), statut d'inscription |
| **Destinataires** | L'**école / centre** ciblé ; administrateurs |
| **Transferts hors Algérie** | Indirect via l'analyse IA (voir T4) |
| **Durée de conservation** | [durée] après clôture de la session |
| **Sécurité** | Contrôle d'accès par rôle |

## T4 — Présélection assistée par IA

| Rubrique | Détail |
|---|---|
| **Finalité** | Calculer un **score (0–100)** et un retour pour aider le recruteur/l'école |
| **Base légale** | Intérêt légitime du recruteur, **avec décision humaine finale** |
| **Personnes concernées** | Candidats |
| **Données** | **Contenu texte du CV** + détails de l'offre/formation |
| **Sous-traitant** | **OpenAI (GPT-4o)** |
| **Transferts hors Algérie** | ⚠️ **Oui — États-Unis** (OpenAI) ; point sensible nécessitant autorisation ANPDP |
| **Durée de conservation** | Score/retour conservés avec la candidature |
| **Décision automatisée** | Score = **aide** ; pas de décision 100 % automatisée (humain tranche) |

## T5 — Notifications

| Rubrique | Détail |
|---|---|
| **Finalité** | Informer (in-app + email) de la réception et du changement de statut d'une candidature |
| **Base légale** | Exécution du service |
| **Personnes concernées** | Candidats, entreprises, écoles |
| **Données** | Email, contenu de la notification |
| **Sous-traitant** | Fournisseur **SMTP** (ex. Gmail / SendGrid) |
| **Transferts hors Algérie** | Selon le fournisseur SMTP retenu |
| **Durée de conservation** | Notifications in-app : [durée] ; emails : selon le fournisseur |

## T6 — Sécurité, journalisation & lutte anti-fraude

| Rubrique | Détail |
|---|---|
| **Finalité** | Tracer les connexions, prévenir le force-brute et l'abus (audit, rate-limit, anti-robot) |
| **Base légale** | Intérêt légitime / obligation de sécurité |
| **Personnes concernées** | Tous les utilisateurs |
| **Données** | Identifiant, **IP**, user-agent, événement (login/échec/reset…), email tenté |
| **Sous-traitant** | **Cloudflare** (Turnstile anti-robot) |
| **Transferts hors Algérie** | Cloudflare (international) |
| **Durée de conservation** | Journaux : durée courte proportionnée [ex. 12 mois] |

## T7 — Administration & supervision (back-office)

| Rubrique | Détail |
|---|---|
| **Finalité** | Gérer le contenu (offres, formations, candidatures, utilisateurs), support |
| **Base légale** | Intérêt légitime / exécution du service |
| **Personnes concernées** | Tous |
| **Données** | Ensemble des données ci-dessus (accès restreint aux administrateurs) |
| **Destinataires** | Administrateurs IQRA |
| **Transferts hors Algérie** | Non (accès interne) |
| **Sécurité** | Rôle `admin`, archivage réversible plutôt que suppression directe |

---

## Synthèse des transferts hors d'Algérie (à autoriser — ANPDP)

| Sous-traitant | Pays | Données | Traitement |
|---|---|---|---|
| **Google** | États-Unis | email, identifiant, nom | T1 (auth), notifications |
| **Facebook / Meta** | États-Unis | email, identifiant, nom | T1 (auth) |
| **OpenAI** | États-Unis | **texte du CV** + offre | T4 (score IA) |
| **Cloudflare** | International | IP, signaux navigateur | T6 (anti-robot) |
| **SMTP** [fournisseur] | [pays] | email | T5 (notifications) |

---

> *Document support à la mise en conformité, **à valider par un conseil juridique /
> DPO** et à adapter au formulaire officiel de l'ANPDP avant dépôt.*

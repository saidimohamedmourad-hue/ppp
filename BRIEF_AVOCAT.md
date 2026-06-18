# IQRA — Brief pour validation juridique (avocat / DPO)

> Document de cadrage à remettre au **conseil juridique / DPO** pour valider la
> conformité d'IQRA avant la commercialisation. **Ce n'est pas un avis
> juridique** — il liste les points à faire trancher par un professionnel.

**Cadre : loi algérienne n° 18-07** (données personnelles) **+ loi n° 18-05**
(commerce électronique, si fonctions payantes).

---

## 1. Contexte (1 paragraphe)

IQRA est une plateforme algérienne de **mise en relation emploi & formation**
(web + mobile). Elle traite des données personnelles à des fins de recrutement :
identité, coordonnées (email, **téléphone**), **CV**, niveau d'études,
candidatures, et un **score généré par IA**. Elle s'appuie sur des prestataires
**hors d'Algérie** (Google, Facebook, OpenAI, Cloudflare).

## 2. Documents fournis à valider

- **Politique de confidentialité** → `POLITIQUE_CONFIDENTIALITE.md`
- **Conditions Générales d'Utilisation** → `CGU.md`
- **Registre des traitements** → `REGISTRE_TRAITEMENTS.md`
- Analyse d'écart interne → `CONFORMITE_DONNEES.md`

## 3. Points à faire valider (checklist avocat)

- [ ] **Mentions légales obligatoires** : raison sociale, **RC**, **NIF**,
  adresse, contact — les `[…]` sont à compléter dans tous les documents.
- [ ] **Bases légales** de chaque traitement (compte, candidature, IA,
  notifications, sécurité) — sont-elles correctes/suffisantes ?
- [ ] **Consentement** : recueil au bon moment, formulation, preuve.
- [ ] **Durées de conservation** (compte, candidatures, journaux) — les valeurs
  indicatives proposées sont-elles conformes ?
- [ ] **Droits des personnes** (accès, rectification, opposition, effacement,
  portabilité) — modalités d'exercice suffisantes ?
- [ ] **Clause d'intermédiaire technique** et limitation de responsabilité
  (IQRA n'est pas partie aux relations candidat ↔ entreprise/école).
- [ ] **Transferts hors d'Algérie** (Google, Facebook, **OpenAI**, Cloudflare,
  SMTP) : base légale + information + **autorisation ANPDP** requise ?
- [ ] **Données sensibles** : un CV peut contenir des données sensibles
  (santé, origine…) → régime renforcé ; la mention d'avertissement suffit-elle ?
- [ ] **Décision automatisée (score IA)** : information de la personne + droit à
  un réexamen humain — la formulation est-elle conforme ?
- [ ] **Loi 18-05 (e-commerce)** : applicable si des **fonctions payantes** sont
  ajoutées (abonnements entreprises, mise en avant d'offres) ?
- [ ] **Cookies / traceurs** : seulement essentiels aujourd'hui — bandeau requis
  si ajout de traceurs non essentiels ?

## 4. Formalités ANPDP

- [ ] **Déclaration préalable** du traitement auprès de l'**ANPDP**.
- [ ] **Demande d'autorisation** pour les points sensibles (CV + transferts hors
  d'Algérie).
- [ ] Confirmer la **procédure de dépôt actuelle** (formulaire, canal) auprès de
  l'ANPDP.

## 5. Questions ouvertes pour l'avocat

1. Faut-il une **autorisation** ANPDP (et pas une simple déclaration) vu les CV
   et les transferts hors Algérie ?
2. L'usage d'**OpenAI (USA)** pour analyser le CV est-il acceptable, ou faut-il
   une alternative locale / une anonymisation préalable ?
3. Un **DPO** doit-il être formellement désigné ?

---

> *À compléter avec l'éditeur réel (raison sociale, RC, NIF) avant transmission.*

# IQRA — Conformité des données personnelles

> **Cadre : loi algérienne n° 18-07 du 10 juin 2018** relative à la protection
> des personnes physiques dans le traitement des données à caractère personnel
> (équivalent algérien du RGPD), et bonnes pratiques RGPD (UE) en complément.
>
> ⚠️ **Avertissement :** document informatif d'aide à la décision, **pas un avis
> juridique**. Les références d'articles et l'état opérationnel de l'**ANPDP**
> (Autorité nationale de protection des données à caractère personnel) doivent
> être confirmés par un conseil juridique / un DPO avant toute mise en
> production commerciale.

---

## 1. Pourquoi c'est central pour IQRA

IQRA traite par nature des **données personnelles sensibles à des fins de
recrutement** : identité, coordonnées (email, **téléphone**), CV (parcours,
diplômes, expériences), candidatures, et un **score généré par IA**. Ce type de
traitement est précisément ce que la loi 18-07 encadre. La conformité n'est donc
pas un « bonus » : c'est une **condition d'exploitation** et un **argument de
confiance** vis-à-vis des candidats, des entreprises et des écoles.

---

## 2. Données traitées par la plateforme

| Catégorie | Exemples | Source |
|---|---|---|
| Identité | nom, prénom | inscription |
| Contact | email, **téléphone**, adresse | inscription / 1ʳᵉ candidature |
| Professionnelles | CV, diplômes, expériences, compétences, **niveau d'études** | profil candidat / candidature |
| Candidatures | offres/sessions visées, statut, historique | usage |
| Évaluation | **score IA + retour IA** sur la candidature | traitement automatisé |
| Compte | mot de passe (haché), rôles, journaux de connexion | sécurité |
| Identifiants sociaux | identifiant Google / Facebook (si connexion sociale) | OAuth |

> **Point d'attention :** un CV peut contenir des **données sensibles** au sens
> de la loi (ex. mentions de santé, affiliation, origine). Leur traitement
> bénéficie d'un **régime renforcé** — voir §6.

---

## 3. Les obligations clés de la loi 18-07 (synthèse)

1. **Licéité & finalité déterminée** — un traitement pour une finalité claire
   (mise en relation emploi/formation), pas de détournement d'usage.
2. **Consentement** de la personne concernée (sauf exceptions légales).
3. **Minimisation & exactitude** — ne collecter que le nécessaire, garder les
   données à jour.
4. **Durée de conservation limitée** — ne pas conserver indéfiniment.
5. **Droits des personnes** — information, **accès, rectification, opposition**,
   (et, en pratique RGPD, effacement / portabilité).
6. **Formalités auprès de l'ANPDP** — **déclaration préalable** ou
   **autorisation** selon la nature du traitement.
7. **Transfert hors d'Algérie** — encadré : nécessite un **niveau de protection
   suffisant** du pays destinataire **et l'autorisation de l'ANPDP**.
8. **Sécurité & confidentialité** — mesures techniques et organisationnelles ;
   responsabilité du **responsable de traitement** et de ses **sous-traitants**.
9. **Données sensibles** — interdiction de principe, sauf cas et garanties
   spécifiques.

---

## 4. État de conformité — analyse d'écart

> Légende : ✅ en place · 🟧 partiel / à formaliser · ⬜ à faire

### Sécurité (art. relatifs à la sécurité du traitement)
| Mesure | État |
|---|---|
| Mots de passe **hachés** (jamais en clair) | ✅ |
| Jetons d'authentification, expiration de session | ✅ |
| **Anti-force-brute** + limitation de débit sur la connexion | ✅ |
| **Anti-robot (Turnstile)** sur la réinitialisation de mot de passe | ✅ |
| **Journal des connexions** (audit / traçabilité) | ✅ |
| Vérification serveur des jetons Google / Facebook | ✅ |
| Secrets conservés côté serveur, hors du code public | ✅ |
| **Données des candidats non exposées publiquement** — la vitrine publique se limite aux **offres/formations** (données publiées par les entreprises/écoles) ; **toute consultation détaillée, candidature ou inscription exige l'authentification** | ✅ |
| Chiffrement en transit (HTTPS) en production | 🟧 à confirmer au déploiement |
| Chiffrement au repos des données sensibles (CV) | ⬜ à évaluer |

### Droits des personnes
| Droit | État |
|---|---|
| **Accès / rectification** de son profil (self-service) | ✅ (édition du profil) |
| **Information** (mention claire au moment de la collecte) | 🟧 à renforcer (politique de confidentialité) |
| **Opposition** / retrait du consentement | ⬜ procédure à définir |
| **Effacement** (suppression de compte + données) | 🟧 archivage réversible existe ; effacement définitif à exposer à l'utilisateur |
| **Portabilité** (export de ses données) | ⬜ à prévoir |

### Documentation & formalités
| Élément | État |
|---|---|
| **Politique de confidentialité** publiée | ⬜ à rédiger et publier |
| **Mentions légales / CGU** | ⬜ à rédiger |
| **Bandeau consentement** (cookies / traceurs) | ⬜ à ajouter |
| **Déclaration / autorisation ANPDP** | ⬜ démarche à engager |
| **Registre des traitements** | ⬜ à créer |
| Désignation d'un **point de contact / DPO** | ⬜ à décider |

### Transferts hors d'Algérie (point sensible — §5)
| Flux | État |
|---|---|
| Cartographie des transferts internationaux | 🟧 identifiés (voir §5), à formaliser |
| Base légale + autorisation ANPDP des transferts | ⬜ à instruire |

---

## 5. Transferts de données hors d'Algérie (à traiter en priorité)

Plusieurs briques techniques font **transiter des données personnelles vers
l'étranger** (principalement les États-Unis). La loi 18-07 **encadre
strictement** ces transferts. À cartographier et à sécuriser juridiquement :

| Service | Données concernées | Finalité |
|---|---|---|
| **Google** (connexion sociale + envoi d'emails) | email, identifiant, nom | authentification, emails transactionnels |
| **Facebook / Meta** (connexion sociale) | email, identifiant, nom | authentification |
| **Cloudflare** (Turnstile anti-bot) | signaux navigateur, IP | protection anti-robot |
| **OpenAI — GPT-4o** (États-Unis) | **contenu du CV (texte) + détails de l'offre** | lecture du CV, score et retour automatisés |

**Actions recommandées :**
- Documenter chaque transfert (destinataire, pays, finalité, garanties).
- Vérifier la base légale et **solliciter l'autorisation de l'ANPDP** si requise.
- Évaluer des alternatives **hébergées en Algérie / localement** lorsque
  possible (notamment pour le moteur IA et l'emailing), afin de réduire la
  surface de transfert.
- Informer clairement les utilisateurs de ces transferts dans la politique de
  confidentialité.

---

## 6. Données sensibles (CV) et décision automatisée (score IA)

- **CV → données potentiellement sensibles.** Prévoir une mention invitant le
  candidat à ne pas inclure d'informations sensibles non nécessaires, et un
  régime de traitement renforcé pour celles qui le seraient.
- **Score IA = traitement automatisé d'aide à la décision.** Deux garde-fous
  déjà en place dans le produit, à formaliser dans la politique :
  1. **L'humain garde la décision finale** — l'entreprise/l'école accepte, met
     en attente ou refuse **quel que soit le score** (pas de décision 100 %
     automatisée).
  2. Prévoir une **information** du candidat sur l'existence d'un score IA et,
     idéalement, une voie de **contestation / réexamen humain**.

---

## 7. Conservation des données (à définir)

Définir et publier une **politique de durées de conservation**, par exemple :
- Compte actif : tant que le compte existe.
- Compte inactif : suppression / anonymisation après une durée déterminée.
- Candidatures : conservation limitée après clôture du poste / de la session.
- Journaux de connexion : durée courte, proportionnée à la sécurité.

*(Durées exactes à valider juridiquement.)*

---

## 8. Feuille de route conformité (synthèse actionnable)

| Priorité | Chantier |
|---|---|
| 🔴 Haute | Rédiger & publier **politique de confidentialité + CGU + mentions légales** |
| 🔴 Haute | **Cartographier les transferts** hors d'Algérie et instruire l'**ANPDP** |
| 🔴 Haute | Engager la **déclaration / autorisation ANPDP** du traitement |
| 🟠 Moyenne | Exposer **suppression de compte** + **export des données** côté utilisateur |
| 🟠 Moyenne | **Bandeau consentement** (cookies/traceurs) + recueil du consentement |
| 🟠 Moyenne | Créer le **registre des traitements** + désigner un point de contact/DPO |
| 🟢 Continue | HTTPS partout, évaluer chiffrement au repos du CV, durées de conservation |

---

## 9. Message pour le business plan

IQRA a été conçue avec une **base technique de sécurité solide** (mots de passe
hachés, anti-force-brute, anti-robot, audit des connexions, vérification serveur
des connexions sociales). La **mise en conformité formelle** avec la **loi
18-07** — documentation, formalités ANPDP, encadrement des transferts
internationaux et transparence vis-à-vis des utilisateurs — constitue un
**chantier identifié, cadré et planifié** avant l'ouverture commerciale. C'est
un **gage de sérieux et de confiance** : la protection des données des candidats
et des partenaires est traitée comme une exigence, pas comme une option.

---

*Document informatif d'aide à la décision. À valider par un conseil
juridique / DPO et auprès de l'ANPDP avant exploitation commerciale.*

/*
 * Génère IQRA_Dossier_BusinessPlan.docx : fusion de l'état d'avancement et de
 * la conformité données (loi 18-07), en français, prêt pour le business plan.
 *
 * Usage : NODE_PATH=$(npm root -g) node build_dossier_docx.js
 */
const fs = require('fs');
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, LevelFormat, TabStopType, TabStopPosition,
  HeadingLevel, BorderStyle, WidthType, ShadingType, PageNumber, PageBreak,
  TableOfContents,
} = require('docx');

const CONTENT_W = 9360;            // US Letter, marges 1"
const border = { style: BorderStyle.SINGLE, size: 1, color: 'CCCCCC' };
const cellBorders = { top: border, bottom: border, left: border, right: border };
const HEADER_FILL = 'D5E8F0';
const cellMargins = { top: 80, bottom: 80, left: 120, right: 120 };

// ── Helpers ────────────────────────────────────────────────────────────────
const H1 = (t) => new Paragraph({ heading: HeadingLevel.HEADING_1, children: [new TextRun(t)] });
const H2 = (t) => new Paragraph({ heading: HeadingLevel.HEADING_2, children: [new TextRun(t)] });
const P = (t, opts = {}) => new Paragraph({ spacing: { after: 120 }, children: [new TextRun({ text: t, ...opts })] });
const SP = () => new Paragraph({ spacing: { after: 60 }, children: [] });

// Bloc monospace (schémas ASCII) : une ligne = un paragraphe, police Courier.
function mono(text) {
  return text.split('\n').map((line) => new Paragraph({
    spacing: { after: 0, line: 240 },
    shading: { fill: 'F4F6F8', type: ShadingType.CLEAR },
    children: [new TextRun({ text: line || ' ', font: 'Courier New', size: 16 })],
  }));
}

function bullets(items) {
  return items.map((it) => new Paragraph({
    numbering: { reference: 'bullets', level: 0 },
    spacing: { after: 60 },
    children: Array.isArray(it) ? it : [new TextRun(it)],
  }));
}
function numbered(items) {
  return items.map((it) => new Paragraph({
    numbering: { reference: 'nums', level: 0 },
    spacing: { after: 60 },
    children: Array.isArray(it) ? it : [new TextRun(it)],
  }));
}

// Tableau : cols = largeurs, header = [..], rows = [[..],..]
function table(cols, header, rows) {
  const mkCell = (txt, w, isHeader) => new TableCell({
    borders: cellBorders, width: { size: w, type: WidthType.DXA }, margins: cellMargins,
    shading: isHeader ? { fill: HEADER_FILL, type: ShadingType.CLEAR } : undefined,
    children: [new Paragraph({ children: [new TextRun({ text: String(txt), bold: !!isHeader })] })],
  });
  const headerRow = new TableRow({
    tableHeader: true,
    children: header.map((h, i) => mkCell(h, cols[i], true)),
  });
  const bodyRows = rows.map((r) => new TableRow({
    children: r.map((c, i) => mkCell(c, cols[i], false)),
  }));
  return new Table({
    width: { size: CONTENT_W, type: WidthType.DXA },
    columnWidths: cols,
    rows: [headerRow, ...bodyRows],
  });
}

// ── Contenu ─────────────────────────────────────────────────────────────────
const children = [];

// Page de titre
children.push(
  new Paragraph({ spacing: { before: 2400, after: 0 }, alignment: AlignmentType.CENTER,
    children: [new TextRun({ text: 'IQRA', bold: true, size: 72 })] }),
  new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 240 },
    children: [new TextRun({ text: 'Plateforme Emploi & Formation — Algérie', size: 28, color: '555555' })] }),
  new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 80 },
    children: [new TextRun({ text: 'Dossier de synthèse — Avancement · Conformité · Architecture', bold: true, size: 26 })] }),
  new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 80 },
    children: [new TextRun({ text: 'Support destiné au business plan', italics: true, size: 22, color: '777777' })] }),
  new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 1600 },
    children: [new TextRun({ text: 'Juin 2026', size: 22 })] }),
  new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 2000 },
    children: [new TextRun({ text: 'Document informatif. La partie conformité ne constitue pas un avis juridique.', italics: true, size: 18, color: '999999' })] }),
  new Paragraph({ children: [new PageBreak()] }),
);

// Sommaire
children.push(
  new Paragraph({ heading: HeadingLevel.HEADING_1, children: [new TextRun('Sommaire')] }),
  new TableOfContents('Sommaire', { hyperlink: true, headingStyleRange: '1-2' }),
  new Paragraph({ children: [new PageBreak()] }),
);

// ═══ PARTIE A — ÉTAT D'AVANCEMENT ═══
children.push(H1('Partie A — État d’avancement de la plateforme'));

children.push(H2('1. En une phrase'));
children.push(P('IQRA est une plateforme algérienne qui met en relation les candidats (chercheurs d’emploi et de formation) avec les entreprises (offres d’emploi) et les écoles / centres de formation (sessions de formation), avec une présélection assistée par intelligence artificielle des candidatures.'));

children.push(H2('2. Pour qui ? (les 4 profils)'));
children.push(table([2200, 7160],
  ['Profil', 'Ce qu’il peut faire'],
  [
    ['Candidat', 'Parcourir les offres et formations, postuler / s’inscrire, suivre ses candidatures, gérer profil et CV.'],
    ['Entreprise', 'Publier des offres, recevoir les candidatures avec coordonnées du candidat et un score IA, accepter / en attente / refuser.'],
    ['École / Centre', 'Publier des sessions (liste d’attente, type, lieu, dates), recevoir les inscriptions enrichies du score IA, gérer les places.'],
    ['Administrateur', 'Superviser l’ensemble via un back-office : entreprises, écoles, offres, formations, candidatures, catégories, utilisateurs.'],
  ]));

children.push(SP(), H2('3. Sur quels supports ? (multi-plateforme)'));
children.push(...numbered([
  'Site web public + espace candidat/entreprise/école — application web moderne (React).',
  'Application mobile (Android / iOS) et version web — application Flutter.',
  'Back-office d’administration — interface web dédiée aux administrateurs (Laravel).',
]));
children.push(P('Le cœur applicatif (base de données, règles métier, API sécurisée) est unique et partagé par les trois supports : cohérence des données et coûts de maintenance réduits.'));

children.push(H2('4. Fonctionnalités déjà opérationnelles'));
children.push(P('Comptes & connexion', { bold: true }));
children.push(...bullets([
  '4 rôles (candidat, entreprise, école, administrateur) avec aiguillage automatique.',
  'Réinitialisation de mot de passe par email (lien sécurisé).',
  'Connexion via Google et Facebook.',
  'Gestion des méthodes de connexion depuis le profil (lier / délier, définir un mot de passe).',
]));
children.push(P('Emploi & formation', { bold: true }));
children.push(...bullets([
  'Offres d’emploi : publication, vue détaillée (salaire, lieu, type, description, coordonnées du recruteur), candidature avec CV.',
  'Formations : sessions avec type, lieu, dates, prix / gratuité, places + liste d’attente, motif d’annulation.',
  'Téléphone obligatoire à la première candidature : contact direct pour entreprises et écoles.',
]));
children.push(P('Intelligence artificielle', { bold: true }));
children.push(...bullets([
  'Score IA (0–100) + retour IA (analyse du CV vs. l’offre) sur chaque candidature.',
  'L’entreprise / l’école garde toujours la décision finale, quel que soit le score.',
]));
children.push(P('Notifications & administration', { bold: true }));
children.push(...bullets([
  'Notifications en temps réel (cloche) et par email (candidature reçue, changement de statut).',
  'Back-office complet avec archivage réversible (corbeille) sur les contenus sensibles.',
]));

children.push(H2('5. Sécurité & qualité'));
children.push(...bullets([
  'Anti-robot (Cloudflare Turnstile) sur la réinitialisation de mot de passe.',
  'Limitation de débit et protection anti-force-brute sur la connexion.',
  'Journal des connexions (audit) pour la traçabilité.',
  'Vérification des jetons Google / Facebook côté serveur.',
  'Tests automatisés : back-end, bout-en-bout du site, application Flutter.',
  'Secrets jamais exposés : clés sensibles côté serveur uniquement.',
]));

children.push(H2('6. État d’avancement global'));
children.push(table([6660, 2700],
  ['Domaine', 'État'],
  [
    ['Architecture & base de données', 'Opérationnel'],
    ['Authentification (email + Google + Facebook)', 'Opérationnel'],
    ['Réinitialisation de mot de passe', 'Opérationnel'],
    ['Offres d’emploi (publication, candidature, IA)', 'Opérationnel'],
    ['Formations (sessions, liste d’attente, IA)', 'Opérationnel'],
    ['Notifications (web + email)', 'Opérationnel'],
    ['Back-office administrateur complet', 'Opérationnel'],
    ['Coordonnées (téléphone) & contact recruteur', 'Opérationnel'],
    ['Sécurité (anti-bot, anti-force-brute, audit)', 'Opérationnel'],
    ['Application mobile Flutter', 'Fonctionnelle'],
    ['Tests automatisés', 'En place'],
  ]));
children.push(SP(), P('Stade global : MVP (produit minimum viable) fonctionnel et démontrable. Le parcours complet est couvert : un candidat s’inscrit et postule ; une entreprise / école reçoit la candidature enrichie par l’IA et décide ; un administrateur supervise.', { bold: true }));

children.push(H2('7. Points en cours / prochaines étapes'));
children.push(table([6660, 2700],
  ['Sujet', 'Statut'],
  [
    ['Connexion Google sur Flutter Web', 'Correctif appliqué, test final en cours'],
    ['Captcha anti-bot dans l’app Flutter', 'Présent sur le web React, à ajouter sur Flutter'],
    ['Intégration continue (CI/CD)', 'En pause (déblocage administratif)'],
    ['Mise en production / déploiement', 'À planifier'],
    ['Affinage du moteur de score IA', 'Amélioration continue prévue'],
  ]));

children.push(SP(), H2('8. Briques technologiques (annexe)'));
children.push(...bullets([
  'Site candidat/entreprise/école : React + TypeScript.',
  'Application mobile & web : Flutter (Android, iOS, web).',
  'API & back-office : Laravel (PHP), base de données MariaDB (cache, sessions et files gérés par la base — pas de Redis).',
  'Analyse IA : OpenAI GPT-4o (lecture du CV PDF + score/retour), en tâches asynchrones.',
  'Emails : envoi transactionnel via SMTP.',
  'Sécurité : Cloudflare Turnstile, jetons d’authentification (Sanctum), limitation de débit.',
  'Connexion sociale : Google Identity Services, Facebook Login.',
]));

children.push(H2('9. Proposition de valeur'));
children.push(...bullets([
  'Un seul endroit pour l’emploi et la formation en Algérie.',
  'Gain de temps pour les recruteurs et écoles grâce à la présélection IA, tout en gardant la décision.',
  'Expérience fluide pour le candidat : web + mobile, connexion en un clic, suivi et notifications.',
  'Plateforme déjà construite et fonctionnelle, pas un simple concept.',
]));

children.push(new Paragraph({ children: [new PageBreak()] }));

// ═══ PARTIE B — CONFORMITÉ DES DONNÉES ═══
children.push(H1('Partie B — Conformité des données personnelles'));
children.push(P('Cadre : loi algérienne n° 18-07 du 10 juin 2018 relative à la protection des personnes physiques dans le traitement des données à caractère personnel (équivalent algérien du RGPD), et bonnes pratiques RGPD (UE) en complément.', { italics: true }));
children.push(P('Avertissement : document informatif d’aide à la décision, pas un avis juridique. Les références d’articles et l’état opérationnel de l’ANPDP doivent être confirmés par un conseil juridique / un DPO avant toute mise en production commerciale.', { italics: true, color: '999999' }));

children.push(H2('1. Pourquoi c’est central pour IQRA'));
children.push(P('IQRA traite des données personnelles à des fins de recrutement : identité, coordonnées (email, téléphone), CV (parcours, diplômes, expériences), candidatures, et un score généré par IA. C’est précisément ce qu’encadre la loi 18-07. La conformité est donc une condition d’exploitation et un argument de confiance vis-à-vis des candidats, entreprises et écoles.'));

children.push(H2('2. Données traitées par la plateforme'));
children.push(table([2600, 4360, 2400],
  ['Catégorie', 'Exemples', 'Source'],
  [
    ['Identité', 'nom, prénom', 'inscription'],
    ['Contact', 'email, téléphone, adresse', 'inscription / 1re candidature'],
    ['Professionnelles', 'CV, diplômes, expériences, compétences', 'profil candidat'],
    ['Candidatures', 'offres/sessions visées, statut, historique', 'usage'],
    ['Évaluation', 'score IA + retour IA', 'traitement automatisé'],
    ['Compte', 'mot de passe (haché), rôles, journaux', 'sécurité'],
    ['Identifiants sociaux', 'identifiant Google / Facebook', 'OAuth'],
  ]));
children.push(SP(), P('Point d’attention : un CV peut contenir des données sensibles au sens de la loi (santé, affiliation, origine). Leur traitement bénéficie d’un régime renforcé (voir §6).', { italics: true }));

children.push(H2('3. Obligations clés de la loi 18-07'));
children.push(...numbered([
  'Licéité & finalité déterminée — une finalité claire (mise en relation emploi/formation), pas de détournement.',
  'Consentement de la personne concernée (sauf exceptions légales).',
  'Minimisation & exactitude — ne collecter que le nécessaire, garder à jour.',
  'Durée de conservation limitée — ne pas conserver indéfiniment.',
  'Droits des personnes — information, accès, rectification, opposition (et, en pratique RGPD, effacement / portabilité).',
  'Formalités auprès de l’ANPDP — déclaration préalable ou autorisation selon le traitement.',
  'Transfert hors d’Algérie — encadré : niveau de protection suffisant du pays destinataire et autorisation de l’ANPDP.',
  'Sécurité & confidentialité — mesures techniques et organisationnelles ; responsabilité du responsable de traitement et des sous-traitants.',
  'Données sensibles — interdiction de principe, sauf cas et garanties spécifiques.',
]));

children.push(H2('4. État de conformité — analyse d’écart'));
children.push(P('Légende : ✅ en place · 🟧 partiel / à formaliser · ⬜ à faire', { italics: true }));
children.push(P('Sécurité', { bold: true }));
children.push(table([6660, 2700],
  ['Mesure', 'État'],
  [
    ['Mots de passe hachés (jamais en clair)', '✅'],
    ['Jetons d’authentification, expiration de session', '✅'],
    ['Anti-force-brute + limitation de débit sur la connexion', '✅'],
    ['Anti-robot (Turnstile) sur la réinitialisation', '✅'],
    ['Journal des connexions (audit)', '✅'],
    ['Vérification serveur des jetons Google / Facebook', '✅'],
    ['Secrets côté serveur, hors du code public', '✅'],
    ['Chiffrement en transit (HTTPS) en production', '🟧 à confirmer au déploiement'],
    ['Chiffrement au repos des données sensibles (CV)', '⬜ à évaluer'],
  ]));
children.push(SP(), P('Droits des personnes', { bold: true }));
children.push(table([6660, 2700],
  ['Droit', 'État'],
  [
    ['Accès / rectification du profil (self-service)', '✅'],
    ['Information (mention claire à la collecte)', '🟧 à renforcer'],
    ['Opposition / retrait du consentement', '⬜ à définir'],
    ['Effacement (suppression compte + données)', '🟧 archivage existe ; effacement à exposer'],
    ['Portabilité (export des données)', '⬜ à prévoir'],
  ]));
children.push(SP(), P('Documentation & formalités', { bold: true }));
children.push(table([6660, 2700],
  ['Élément', 'État'],
  [
    ['Politique de confidentialité publiée', '⬜ à rédiger'],
    ['Mentions légales / CGU', '⬜ à rédiger'],
    ['Bandeau consentement (cookies / traceurs)', '⬜ à ajouter'],
    ['Déclaration / autorisation ANPDP', '⬜ à engager'],
    ['Registre des traitements', '⬜ à créer'],
    ['Point de contact / DPO', '⬜ à décider'],
  ]));

children.push(H2('5. Transferts de données hors d’Algérie (priorité)'));
children.push(P('Plusieurs briques font transiter des données personnelles vers l’étranger (surtout les États-Unis). La loi 18-07 encadre strictement ces transferts.'));
children.push(table([2600, 4360, 2400],
  ['Service', 'Données', 'Finalité'],
  [
    ['Google', 'email, identifiant, nom', 'authentification, emails'],
    ['Facebook / Meta', 'email, identifiant, nom', 'authentification'],
    ['Cloudflare (Turnstile)', 'signaux navigateur, IP', 'protection anti-robot'],
    ['OpenAI — GPT-4o (États-Unis)', 'contenu du CV (texte) + détails de l’offre', 'lecture du CV, score et retour'],
  ]));
children.push(SP(), P('Actions recommandées :', { bold: true }));
children.push(...bullets([
  'Documenter chaque transfert (destinataire, pays, finalité, garanties).',
  'Vérifier la base légale et solliciter l’autorisation de l’ANPDP si requise.',
  'Évaluer des alternatives hébergées localement (moteur IA, emailing) pour réduire les transferts.',
  'Informer clairement les utilisateurs dans la politique de confidentialité.',
]));

children.push(H2('6. Données sensibles (CV) & décision automatisée (IA)'));
children.push(...bullets([
  'CV → données potentiellement sensibles : inviter à ne pas inclure d’informations sensibles non nécessaires ; régime renforcé pour celles présentes.',
  'Score IA = aide à la décision : l’humain garde la décision finale (pas de décision 100 % automatisée).',
  'Informer le candidat de l’existence d’un score IA et prévoir une voie de réexamen humain.',
]));

children.push(H2('7. Conservation des données (à définir)'));
children.push(...bullets([
  'Compte actif : tant que le compte existe.',
  'Compte inactif : suppression / anonymisation après une durée déterminée.',
  'Candidatures : conservation limitée après clôture du poste / de la session.',
  'Journaux de connexion : durée courte, proportionnée à la sécurité.',
]));
children.push(P('(Durées exactes à valider juridiquement.)', { italics: true }));

children.push(H2('8. Feuille de route conformité'));
children.push(table([2000, 7360],
  ['Priorité', 'Chantier'],
  [
    ['Haute', 'Rédiger & publier politique de confidentialité + CGU + mentions légales'],
    ['Haute', 'Cartographier les transferts hors d’Algérie et instruire l’ANPDP'],
    ['Haute', 'Engager la déclaration / autorisation ANPDP du traitement'],
    ['Moyenne', 'Exposer suppression de compte + export des données à l’utilisateur'],
    ['Moyenne', 'Bandeau consentement (cookies/traceurs) + recueil du consentement'],
    ['Moyenne', 'Créer le registre des traitements + désigner un point de contact/DPO'],
    ['Continue', 'HTTPS partout, chiffrement au repos du CV, durées de conservation'],
  ]));

children.push(SP(), H2('9. Message pour le business plan'));
children.push(P('IQRA a été conçue avec une base technique de sécurité solide (mots de passe hachés, anti-force-brute, anti-robot, audit des connexions, vérification serveur des connexions sociales). La mise en conformité formelle avec la loi 18-07 — documentation, formalités ANPDP, encadrement des transferts internationaux et transparence — constitue un chantier identifié, cadré et planifié avant l’ouverture commerciale. C’est un gage de sérieux et de confiance : la protection des données des candidats et partenaires est traitée comme une exigence, pas une option.'));

children.push(new Paragraph({ children: [new PageBreak()] }));

// ═══ PARTIE C — ARCHITECTURE ═══
children.push(H1('Partie C — Architecture de la plateforme'));
children.push(P('Annexe technique lisible : comment la plateforme est construite. Les éléments décrits reflètent l’état réel du code.', { italics: true }));

children.push(H2('1. Vue d’ensemble'));
children.push(...mono(
`        UTILISATEURS                         ADMINISTRATEURS
   (candidats / entreprises / écoles)
            |                                       |
   +--------+---------+                             |
   v                  v                             v
+------------+   +-------------+            +------------------+
|  Site web  |   | App mobile  |            |  Back-office     |
|  (React)   |   | + web       |            |  d'administration|
|  port 3000 |   | (Flutter)   |            |  (Blade/Laravel) |
+-----+------+   +------+------+            +--------+---------+
      |   API REST (JSON, jeton securise)            |
      +---------------+------------------------------+
                      v
           +-------------------------+
           |   API & coeur metier    |  <- regles metier, roles, securite
           |   Laravel (PHP) :8000   |
           +-----------+-------------+
                       v
           +-------------------------+
           |   Base de donnees       |  <- une seule source de verite
           |   relationnelle         |
           +-------------------------+

  Services externes : Google / Facebook (connexion), Cloudflare (anti-bot),
  SMTP (emails), moteur de score IA.`));
children.push(SP(), P('Idée clé : trois interfaces (web, mobile, administration) parlent à un seul cerveau — l’API Laravel — qui détient les règles métier, la sécurité et une seule base de données. Pas de duplication de logique, données cohérentes partout, maintenance simplifiée.'));

children.push(H2('2. Les composants'));
children.push(table([3000, 2600, 3760],
  ['Composant', 'Technologie', 'Rôle'],
  [
    ['Site web', 'React + TypeScript', 'Espace public + candidat/entreprise/école.'],
    ['Application mobile & web', 'Flutter', 'Un seul code pour Android, iOS et web.'],
    ['API & cœur métier', 'Laravel (PHP)', 'Authentification, règles métier, sécurité, données JSON.'],
    ['Back-office', 'Laravel (Blade)', 'Interface de gestion réservée aux administrateurs.'],
    ['Modèles partagés', 'Paquet commun', 'Définition unique des entités, réutilisée partout.'],
    ['Base de données', 'MariaDB (relationnelle)', 'Stockage central ; gère aussi cache, sessions et files (pas de Redis).'],
    ['Moteur d’analyse IA', 'OpenAI GPT-4o', 'Lecture du CV (PDF) + score et retour, en tâches asynchrones.'],
  ]));

children.push(SP(), H2('3. Système de rôles (qui peut faire quoi)'));
children.push(P('L’accès est contrôlé côté serveur (RBAC). Chaque utilisateur a un rôle parmi quatre, et l’API n’autorise que les actions correspondantes.'));
children.push(table([2600, 6760],
  ['Rôle', 'Accès'],
  [
    ['Candidat', 'Parcourir, postuler/s’inscrire, gérer profil et CV, suivre ses candidatures.'],
    ['Entreprise', 'Gérer son entreprise et ses offres, voir les candidatures (score IA), changer leur statut.'],
    ['École', 'Gérer son école et ses sessions, voir les inscriptions (score IA), gérer les places.'],
    ['Administrateur', 'Supervision globale via le back-office : tous contenus et utilisateurs.'],
  ]));

children.push(SP(), H2('4. Modèle de données (entités principales)'));
children.push(...mono(
`Utilisateur (User)
 |-- possede ->  Entreprise (Company)  -> Offres (JobVacancy)
 |-- possede ->  Ecole (School)        -> Sessions (TrainingSession)
 |-- depose  ->  Candidatures emploi (JobApplication)  -> Offre + CV
 |-- depose  ->  Inscriptions formation (TrainingApplication) -> Session
 |-- possede ->  CV (Resume)
 \\-- comptes lies -> AuthProvider (Google / Facebook)

 Offres   -> Categorie d'emploi (JobCategory)
 Sessions -> Categorie de formation (TrainingCategory)`));
children.push(SP(), table([2900, 6460],
  ['Entité', 'Description'],
  [
    ['User', 'Compte (nom, email, téléphone, rôle, mot de passe haché).'],
    ['Company', 'Fiche entreprise (secteur, adresse, site, téléphone).'],
    ['School', 'Fiche école / centre (secteur, adresse, site, téléphone).'],
    ['JobVacancy', 'Offre d’emploi (titre, type, lieu, salaire, description).'],
    ['TrainingSession', 'Session (type, lieu, dates, prix, places, liste d’attente).'],
    ['JobApplication', 'Candidature (statut, score IA + retour IA).'],
    ['TrainingApplication', 'Inscription (statut, score IA + retour IA).'],
    ['Resume', 'CV (résumé, compétences, formation, expérience).'],
    ['JobCategory / TrainingCategory', 'Catégories pour classer offres et sessions.'],
    ['AuthProvider', 'Lien vers un compte Google / Facebook.'],
  ]));
children.push(SP(), P('Données techniques annexes : notifications, journal des connexions (audit), jetons d’authentification.', { italics: true }));

children.push(H2('5. Flux clés'));
children.push(P('Inscription & connexion', { bold: true }));
children.push(...mono(
`Utilisateur -> choisit son role -> compte cree (mot de passe hache)
ou "Continuer avec Google / Facebook" -> l'API verifie le jeton cote serveur
-> delivrance d'un jeton de session securise (Sanctum)`));
children.push(SP(), P('Candidature avec présélection IA', { bold: true }));
children.push(...mono(
`Candidat postule (CV PDF + telephone)
-> l'API enregistre la candidature (tache asynchrone declenchee)
-> extraction du CV (PDF) puis evaluation par OpenAI GPT-4o : score + retour
-> l'entreprise / ecole voit la candidature enrichie
-> elle decide : accepter / en attente / refuser  (l'humain tranche toujours)`));
children.push(SP(), P('Sécurité d’accès (à chaque requête)', { bold: true }));
children.push(...mono(
`Requete -> jeton valide ? -> role autorise ? -> action permise ? -> reponse
(sinon : refus)`));

children.push(SP(), H2('6. Sécurité (résumé technique)'));
children.push(...bullets([
  'Authentification par jeton (Laravel Sanctum), sessions révocables.',
  'Mots de passe hachés (jamais en clair).',
  'Contrôle d’accès par rôle (RBAC) à chaque requête.',
  'Vérification serveur des connexions Google / Facebook.',
  'Anti-robot (Cloudflare Turnstile) sur la réinitialisation de mot de passe.',
  'Limitation de débit + anti-force-brute sur la connexion.',
  'Journal des connexions (audit). Secrets côté serveur uniquement.',
]));

children.push(H2('7. Pourquoi cette architecture'));
children.push(...bullets([
  'Cœur unique, plusieurs vitrines : web, mobile, administration partagent logique et base → cohérence et coûts maîtrisés.',
  'Sécurité par conception : rôles, jetons, vérifications serveur, anti-bot.',
  'Évolutivité : une fonctionnalité ajoutée dans l’API bénéficie à tous les supports.',
  'Multi-plateforme dès le départ : web + mobile sans réécrire le produit.',
]));

// ── Document ────────────────────────────────────────────────────────────────
const doc = new Document({
  creator: 'IQRA',
  title: 'IQRA — Dossier de synthèse',
  styles: {
    default: { document: { run: { font: 'Arial', size: 22 } } },
    paragraphStyles: [
      { id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 32, bold: true, font: 'Arial', color: '1F4E66' },
        paragraph: { spacing: { before: 320, after: 200 }, outlineLevel: 0 } },
      { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 26, bold: true, font: 'Arial', color: '2E75B6' },
        paragraph: { spacing: { before: 220, after: 120 }, outlineLevel: 1 } },
    ],
  },
  numbering: {
    config: [
      { reference: 'bullets', levels: [{ level: 0, format: LevelFormat.BULLET, text: '•', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 600, hanging: 300 } } } }] },
      { reference: 'nums', levels: [{ level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 600, hanging: 300 } } } }] },
    ],
  },
  sections: [{
    properties: { page: { size: { width: 12240, height: 15840 }, margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 } } },
    footers: {
      default: new Footer({ children: [new Paragraph({
        alignment: AlignmentType.CENTER,
        children: [
          new TextRun({ text: 'IQRA — Dossier de synthèse', size: 16, color: '999999' }),
          new TextRun({ text: '\t', size: 16 }),
          new TextRun({ text: 'Page ', size: 16, color: '999999' }),
          new TextRun({ children: [PageNumber.CURRENT], size: 16, color: '999999' }),
          new TextRun({ text: ' / ', size: 16, color: '999999' }),
          new TextRun({ children: [PageNumber.TOTAL_PAGES], size: 16, color: '999999' }),
        ],
        tabStops: [{ type: TabStopType.RIGHT, position: TabStopPosition.MAX }],
      })] }),
    },
    children,
  }],
});

Packer.toBuffer(doc).then((buf) => {
  fs.writeFileSync('IQRA_Dossier_BusinessPlan.docx', buf);
  console.log('OK -> IQRA_Dossier_BusinessPlan.docx (' + buf.length + ' octets)');
});

# Actions externes — ce qu'il reste à faire (hors code)

> Suivi des actions qui **ne dépendent pas du code** mais de **tes comptes /
> décisions** (Google, hébergeur, juridique, ANPDP, facturation). Pour chacune :
> ce qui est **déjà prêt** côté projet, et **ce que tu dois faire**.

**Dernière mise à jour : 13 juin 2026**

| # | Action | Prêt côté projet | Bloque |
|---|---|---|---|
| 1 | Origines Google Cloud (Flutter web) | code OK | Google Sign-In web local |
| 2 | Hébergement + déploiement | guide + workflows | mise en ligne |
| 3 | Validation juridique | politique + CGU rédigées | publication |
| 4 | Dépôt ANPDP | registre + transferts | conformité légale |
| 5 | Facturation GitHub Actions | 5 workflows écrits | CI/CD |
| 6 | Services externes (prod) | code + .env.example | auth/anti-bot/IA en prod |

---

## 1. Google Cloud — origines Flutter Web
- **Prêt** : Web Client ID configuré et cohérent (code, rien à changer).
- **À faire (toi, 2 min)** : Google Cloud Console → APIs & Services → Credentials →
  ton OAuth Client (Web) → **Authorized JavaScript origins** → ajouter
  `http://localhost:8090` et `http://127.0.0.1:8090` (+ ton domaine de prod plus
  tard) → **Save**.
- *Le Google Sign-In **mobile** marche déjà.*

## 2. Hébergement & déploiement
- **Prêt** : [`GUIDE_DEPLOIEMENT.md`](./GUIDE_DEPLOIEMENT.md) (pas-à-pas) +
  [`PRODUCTION_CHECKLIST.md`](./PRODUCTION_CHECKLIST.md) + workflows CI/CD.
- **À faire (toi)** :
  - Choisir un **hébergeur** — privilégier un **hébergeur algérien** (ICOSnet,
    Djaweb, AnwarNet) pour simplifier la loi 18-07 ; sinon VPS international
    (OVH/Hetzner/DigitalOcean) **avec autorisation de transfert ANPDP**.
  - Dimensionnement de départ : **2 vCPU / 4 Go RAM / ~60 Go SSD**.
  - Option « zéro config serveur » : **Laravel Forge** ou **Ploi** (~12 $/mois)
    automatisent nginx/PHP/queue/HTTPS/déploiements.
  - Acheter le **domaine** (`iqra.dz`) + activer **HTTPS** (certbot, inclus dans
    le guide).

## 3. Validation juridique
- **Prêt** : [`POLITIQUE_CONFIDENTIALITE.md`](./POLITIQUE_CONFIDENTIALITE.md) +
  [`CGU.md`](./CGU.md) (brouillons).
- **À faire (toi, via avocat / DPO)** :
  - Faire **relire** les deux documents (droit algérien).
  - Compléter tous les `[…]` : **raison sociale, RC, NIF, adresse, email**.
  - Vérifier les **durées de conservation**, les **bases légales**, les clauses
    de responsabilité.
  - Vérifier l'application de la **loi 18-05 (commerce électronique)** si des
    **fonctions payantes** sont ajoutées.

## 4. Dépôt ANPDP (loi 18-07)
- **Prêt** : [`REGISTRE_TRAITEMENTS.md`](./REGISTRE_TRAITEMENTS.md) (7 traitements)
  + cartographie des transferts + [`CONFORMITE_DONNEES.md`](./CONFORMITE_DONNEES.md).
- **À faire (toi)** :
  - **Déclaration préalable** du traitement à l'ANPDP.
  - **Demande d'autorisation** pour les points sensibles : **CV** (données
    potentiellement sensibles) et **transferts hors d'Algérie** (Google,
    Facebook, OpenAI, Cloudflare).
  - **Vérifier les modalités pratiques de dépôt** actuelles auprès de l'ANPDP
    (formulaire, canal) — via l'autorité ou l'avocat.

## 5. Facturation GitHub Actions (CI/CD)
- **Prêt** : 5 workflows (`backend/web/flutter/lint/deploy.yml`) déjà écrits.
- **À faire (toi)** : rétablir la **facturation GitHub Actions** → les tests +
  lint tournent automatiquement à chaque push, et `deploy.yml` sur tag.

## 6. Services externes en production
- **Prêt** : code + `.env.example` (back & front).
- **À faire (toi, en prod)** :
  - **Cloudflare Turnstile** : créer une **vraie clé** (remplacer les clés de
    test) + ajouter le domaine de prod dans *Hostname Management*.
  - **Facebook Login** : passer l'app Meta en mode **Live** + domaine prod.
  - **Google OAuth** : ajouter le **domaine de prod** dans les origines.
  - **OpenAI** : clé de prod + **alerte de budget**.
  - **SMTP** : compte d'envoi de prod (App Password Gmail, ou SendGrid/Mailgun
    pour la délivrabilité).

---

> *Tout le rédactionnel et le code sont faits ; ce document ne liste que les
> actions nécessitant tes comptes ou des décisions externes.*

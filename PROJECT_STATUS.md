# IQRA — Platform Progress Status

> Summary document intended for inclusion in the business plan dossier.
> Date: June 2026. Written for a non-technical reader; implementation details
> are intentionally kept high-level.

---

## 1. In one sentence

**IQRA** is an Algerian platform that connects, on one side, **candidates**
(job and training seekers) and, on the other, **companies** (job offers) and
**schools / training centers** (training sessions), with **AI-assisted
pre-screening** of applications.

---

## 2. For whom? (the 4 profiles)

| Profile | What they can do |
|---|---|
| **Candidate** | Browse job offers and trainings, apply / enroll, track application status, manage profile and CV. |
| **Company** | Post job offers, receive applications with the candidate's contact details **and an AI score**, accept / waitlist / reject. |
| **School / Center** | Post training sessions (with waitlist, type, location, dates), receive enrollments enriched with the AI score, manage seats. |
| **Administrator** | Oversee everything from a back-office: companies, schools, offers, trainings, applications, categories, users. |

---

## 3. On which platforms? (multi-platform)

The platform exists in **three complementary forms** that share the same
database and the same business logic:

1. **Public website + candidate/company/school area** — a modern, fast web app
   (React).
2. **Mobile app** (Android / iOS) **and web version** — a Flutter app, for a
   native phone experience.
3. **Administration back-office** — a dedicated web interface for administrators
   (Laravel), to manage all content.

The **application core** (the "brain": database, business rules, secure API) is
**single and shared** by all three surfaces, ensuring data consistency and
reducing maintenance costs.

---

## 4. Already-operational features

### Accounts & login
- Secure sign-up and login, with **4 roles** (candidate, company, school,
  administrator) and automatic routing to the right area.
- **Password reset by email** (secure link).
- **Login & sign-up via Google and Facebook** — available for **all profiles**
  (candidate, company, school).
- **Login-method management** from the profile (link / unlink Google, Facebook,
  set a password).

### Jobs & training
- **Job offers**: posting, detailed view (salary, location, type, description,
  **recruiter contact details**: phone, website, address), application with
  **CV (required)** + **education level**.
- **Trainings**: sessions with **type** (in-person, online, accelerated,
  **long-duration**), **location**, **dates**, **price / free**, **available
  seats** + automatic **waitlist**, **cancellation reason**, and a **minimum
  required education level**.
- **Education level required** on every application / enrollment (Algeria-specific
  list); **CV required for jobs, optional for trainings**.
- **Phone number required** on first application: companies and schools thus
  get a direct way to contact the candidate.
- **View details without leaving the list**: clicking a title opens the details
  in a modal (and counts the view).

### Dashboards & analytics (company / school)
- **Real-time stats**: offers/trainings, **cumulative views**, applications
  (pending / accepted), **active candidates (30 days)**.
- **Top offers / trainings** with **conversion rate** (views → applications) and
  **recent applications** — on **web (React)** and the **Flutter app**.

### Artificial intelligence
- Every application receives an **AI score (0–100)** and an **AI feedback**
  (analysis of the CV vs. the offer).
- The company / school **always keeps the final decision**: they can accept,
  waitlist or reject, **regardless of the score**.

### Notifications
- **Real-time notifications** (bell in the interface) **and email** when an
  application is received or its status changes.

### Administrator back-office
- Full management: companies, schools, offers, sessions, applications (jobs and
  training), categories, users.
- **Reversible archiving** (trash) on all sensitive content, rather than
  permanent deletion.
- Detailed, symmetric views on the **company** side **and** the **school** side.

---

## 5. Security & quality

- **Anti-bot protection (Cloudflare Turnstile)** on password reset.
- **Rate limiting** and **brute-force protection** on login.
- **Login audit log** for traceability.
- **Server-side verification** of Google / Facebook tokens (never blind trust
  in the client).
- **Automated tests**: back-end tests (API, authentication, notifications),
  end-to-end website tests, Flutter app tests.
- **Secrets never exposed**: sensitive keys stay server-side.

---

## 6. Overall progress

| Area | Status |
|---|---|
| Architecture & database | ✅ Operational |
| Authentication (email + Google + Facebook) | ✅ Operational |
| Password reset | ✅ Operational |
| Job offers (posting, application, AI) | ✅ Operational |
| Trainings (sessions, waitlist, enrollment, AI) | ✅ Operational |
| Education level (application) + conditional CV + min. required level | ✅ Operational |
| Training "Long-duration" type | ✅ Operational |
| Dashboards & analytics (web + Flutter) | ✅ Operational |
| Social sign-up for company / school (Google, Facebook) | ✅ Operational |
| Notifications (web + email) | ✅ Operational |
| Full administrator back-office | ✅ Operational |
| Contact details (phone) & recruiter contact | ✅ Operational |
| Security (anti-bot, anti-brute-force, audit) | ✅ Operational |
| Flutter mobile app | ✅ Functional |
| Automated tests | ✅ In place |

**Overall stage: a functional, demonstrable MVP (minimum viable product).**
The platform already covers the entire journey: a candidate can sign up and
apply; a company / school receives the AI-enriched application and decides; an
administrator oversees everything.

---

## 7. In progress / next steps

| Topic | Status |
|---|---|
| Google login on **Flutter Web** | 🔧 Code fixed + launch stabilized; remaining: authorize origins in Google Cloud + final test |
| Anti-bot captcha in the **Flutter** app | ⏳ Present on the React web, **to be added** on Flutter |
| Social sign-up for company/school on **Flutter** | ⏳ Done on web; to be added on the Flutter register screen |
| Continuous integration / deployment (CI/CD) | ⏸️ Paused (administrative unblocking of an account) |
| Production deployment | ⏳ To be planned |
| AI scoring engine refinement | ⏳ Continuous improvement planned |

---

## 8. Personal-data compliance (Law 18-07 / GDPR-DZ)

IQRA processes personal data for recruitment purposes (identity, contact
details, CV, applications, AI score). The protection of this data is governed by
**Algerian Law No. 18-07 of 10 June 2018**.

- **Already-solid technical foundations**: hashed passwords, brute-force
  protection, anti-bot, login audit log, server-side verification of social
  logins, secrets kept out of public code.
- **Formal compliance planned** before commercial launch: privacy policy /
  terms, filings with the **ANPDP** (national data protection authority),
  framing of **cross-border data transfers** (Google, Facebook, Cloudflare, AI),
  user-facing data deletion / export.
- **Automated-decision safeguard**: the AI score is an **aid** — the human
  (company / school) **always** keeps the final decision.

➡️ **Full gap analysis, international transfers and compliance roadmap detailed
in [`DATA_COMPLIANCE.md`](./DATA_COMPLIANCE.md).**

> *Informational decision-support document, to be validated by legal counsel /
> a DPO and with the ANPDP.*

---

## 9. Technology building blocks (technical appendix)

> Optional section, useful if a technical stakeholder reads the dossier.

- **Candidate/company/school website**: React + TypeScript (fast, modern UI).
- **Mobile & web app**: Flutter (a single codebase for Android, iOS and web).
- **API & back-office**: Laravel (PHP), **MariaDB** database (cache, sessions
  and queues handled by the database — no Redis at this stage).
- **AI analysis**: **OpenAI GPT-4o** (reads the CV PDF + score/feedback), run as
  asynchronous background jobs.
- **Email**: transactional sending via SMTP.
- **Security**: Cloudflare Turnstile, authentication tokens (Sanctum), rate
  limiting.
- **Social login**: Google Identity Services, Facebook Login.

---

## 10. Value proposition (business-plan summary)

- **A single place** for both employment **and** training in Algeria.
- **Time savings for recruiters and schools** thanks to **AI pre-screening**:
  they see the most relevant profiles first, while keeping control of the
  decision.
- **A smooth candidate experience**: web + mobile, one-click login (Google /
  Facebook), application tracking and notifications.
- **A platform already built and functional** (not just a concept): the core of
  the product is operational and demonstrable today.

---

*Document generated as a summary support. The "in progress" items reflect the
real state of development and will be updated as work progresses.*

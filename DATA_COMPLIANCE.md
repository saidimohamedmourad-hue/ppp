# IQRA — Personal-Data Compliance

> **Framework: Algerian Law No. 18-07 of 10 June 2018** on the protection of
> natural persons with regard to the processing of personal data (Algeria's
> equivalent of the GDPR), plus EU GDPR best practices as a complement.
>
> ⚠️ **Disclaimer:** this is an informational decision-support document, **not
> legal advice**. Article references and the operational status of the **ANPDP**
> (National Authority for the Protection of Personal Data) must be confirmed by
> legal counsel / a DPO before any commercial go-live.

---

## 1. Why this is central to IQRA

By nature, IQRA processes **sensitive personal data for recruitment purposes**:
identity, contact details (email, **phone**), CV (background, qualifications,
experience), applications, and an **AI-generated score**. This type of
processing is exactly what Law 18-07 governs. Compliance is therefore not a
"nice-to-have": it is an **operating condition** and a **trust argument** toward
candidates, companies and schools.

---

## 2. Data processed by the platform

| Category | Examples | Source |
|---|---|---|
| Identity | first name, last name | sign-up |
| Contact | email, **phone**, address | sign-up / 1st application |
| Professional | CV, qualifications, experience, skills | candidate profile |
| Applications | targeted offers/sessions, status, history | usage |
| Assessment | **AI score + AI feedback** on the application | automated processing |
| Account | password (hashed), roles, login logs | security |
| Social identifiers | Google / Facebook ID (if social login) | OAuth |

> **Watch point:** a CV may contain **sensitive data** within the meaning of the
> law (e.g. health references, affiliation, origin). Their processing is subject
> to a **reinforced regime** — see §6.

---

## 3. Key obligations of Law 18-07 (summary)

1. **Lawfulness & specified purpose** — processing for a clear purpose
   (employment/training matchmaking), no repurposing.
2. **Consent** of the data subject (save for legal exceptions).
3. **Minimization & accuracy** — collect only what is necessary, keep data up to
   date.
4. **Limited retention period** — do not keep data indefinitely.
5. **Data-subject rights** — information, **access, rectification, objection**,
   (and, in GDPR practice, erasure / portability).
6. **Formalities with the ANPDP** — **prior declaration** or **authorization**
   depending on the nature of the processing.
7. **Transfers outside Algeria** — regulated: require an **adequate level of
   protection** in the destination country **and ANPDP authorization**.
8. **Security & confidentiality** — technical and organizational measures;
   liability of the **data controller** and its **processors**.
9. **Sensitive data** — prohibited in principle, except in specific cases and
   with specific safeguards.

---

## 4. Compliance status — gap analysis

> Legend: ✅ in place · 🟧 partial / to formalize · ⬜ to do

### Security (security-of-processing provisions)
| Measure | Status |
|---|---|
| **Hashed** passwords (never in clear text) | ✅ |
| Authentication tokens, session expiry | ✅ |
| **Brute-force protection** + rate limiting on login | ✅ |
| **Anti-bot (Turnstile)** on password reset | ✅ |
| **Login log** (audit / traceability) | ✅ |
| Server-side verification of Google / Facebook tokens | ✅ |
| Secrets kept server-side, out of public code | ✅ |
| Encryption in transit (HTTPS) in production | 🟧 to confirm at deployment |
| Encryption at rest of sensitive data (CV) | ⬜ to assess |

### Data-subject rights
| Right | Status |
|---|---|
| **Access / rectification** of one's profile (self-service) | ✅ (profile editing) |
| **Information** (clear notice at collection time) | 🟧 to strengthen (privacy policy) |
| **Objection** / consent withdrawal | ⬜ procedure to define |
| **Erasure** (account + data deletion) | 🟧 reversible archiving exists; definitive erasure to expose to the user |
| **Portability** (data export) | ⬜ to plan |

### Documentation & formalities
| Item | Status |
|---|---|
| **Privacy policy** published | ⬜ to write and publish |
| **Legal notice / Terms of Use** | ⬜ to write |
| **Consent banner** (cookies / trackers) | ⬜ to add |
| **ANPDP declaration / authorization** | ⬜ process to initiate |
| **Records of processing activities** | ⬜ to create |
| Designation of a **contact point / DPO** | ⬜ to decide |

### Transfers outside Algeria (sensitive point — §5)
| Flow | Status |
|---|---|
| Mapping of international transfers | 🟧 identified (see §5), to formalize |
| Legal basis + ANPDP authorization of transfers | ⬜ to investigate |

---

## 5. Cross-border data transfers (to address as a priority)

Several technical components cause **personal data to flow abroad** (mainly to
the United States). Law 18-07 **strictly regulates** such transfers. To map and
legally secure:

| Service | Data concerned | Purpose |
|---|---|---|
| **Google** (social login + email sending) | email, ID, name | authentication, transactional emails |
| **Facebook / Meta** (social login) | email, ID, name | authentication |
| **Cloudflare** (Turnstile anti-bot) | browser signals, IP | bot protection |
| **OpenAI — GPT-4o** (United States) | **CV content (text) + offer details** | CV reading, automated score and feedback |

**Recommended actions:**
- Document each transfer (recipient, country, purpose, safeguards).
- Verify the legal basis and **seek ANPDP authorization** where required.
- Assess **Algeria-hosted / local** alternatives where possible (notably for the
  AI engine and email), to reduce the transfer surface.
- Clearly inform users of these transfers in the privacy policy.

---

## 6. Sensitive data (CV) and automated decision-making (AI score)

- **CV → potentially sensitive data.** Provide a notice inviting the candidate
  not to include unnecessary sensitive information, and a reinforced processing
  regime for any such data.
- **AI score = automated decision-support processing.** Two safeguards already
  in the product, to be formalized in the policy:
  1. **The human keeps the final decision** — the company/school accepts,
     waitlists or rejects **regardless of the score** (no 100% automated
     decision).
  2. Provide candidate **information** about the existence of an AI score and,
     ideally, a route for **challenge / human review**.

---

## 7. Data retention (to define)

Define and publish a **retention-period policy**, for example:
- Active account: as long as the account exists.
- Inactive account: deletion / anonymization after a defined period.
- Applications: limited retention after the role / session closes.
- Login logs: short duration, proportionate to security needs.

*(Exact durations to be validated legally.)*

---

## 8. Compliance roadmap (actionable summary)

| Priority | Workstream |
|---|---|
| 🔴 High | Write & publish **privacy policy + terms + legal notice** |
| 🔴 High | **Map cross-border transfers** and file with the **ANPDP** |
| 🔴 High | Initiate the **ANPDP declaration / authorization** of the processing |
| 🟠 Medium | Expose **account deletion** + **data export** to the user |
| 🟠 Medium | **Consent banner** (cookies/trackers) + consent capture |
| 🟠 Medium | Create the **records of processing** + designate a contact point/DPO |
| 🟢 Ongoing | HTTPS everywhere, assess encryption at rest of the CV, retention periods |

---

## 9. Message for the business plan

IQRA was designed with a **solid technical security foundation** (hashed
passwords, brute-force protection, anti-bot, login audit, server-side
verification of social logins). **Formal compliance** with **Law 18-07** —
documentation, ANPDP formalities, framing of international transfers, and
transparency toward users — is an **identified, scoped and planned** workstream
ahead of commercial launch. It is a **mark of seriousness and trust**: the
protection of candidates' and partners' data is treated as a requirement, not an
option.

---

*Informational decision-support document. To be validated by legal counsel / a
DPO and with the ANPDP before commercial operation.*
